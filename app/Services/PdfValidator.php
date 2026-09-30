<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;

class PdfValidator
{
    /**
     * Validate an uploaded file to ensure it is a safe, valid PDF document.
     *
     * @return array{0: bool, 1: ?string} [isValid, errorMessage]
     */
    public static function validate(UploadedFile $file, int $maxMb = 5): array
    {
        $configuredMax = (int) Setting::get('file_max_mb', (string) $maxMb);
        $maxBytes = $configuredMax * 1024 * 1024;

        if ($file->getSize() > $maxBytes) {
            return [false, "Rozmiar pliku {$file->getClientOriginalName()} przekracza dozwolony limit {$configuredMax} MB."];
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'pdf') {
            return [false, "Dołącz plik w formacie PDF. Plik {$file->getClientOriginalName()} ma niedozwolone rozszerzenie."];
        }

        $mime = $file->getMimeType();
        if (! in_array($mime, ['application/pdf', 'application/x-pdf'], true)) {
            return [false, "Plik {$file->getClientOriginalName()} nie jest poprawnym dokumentem PDF."];
        }

        $filePath = $file->getRealPath();
        if (! $filePath || ! file_exists($filePath)) {
            return [false, "Błąd odczytu pliku {$file->getClientOriginalName()}."];
        }

        $handle = fopen($filePath, 'rb');
        if (! $handle) {
            return [false, "Błąd otwarcia pliku {$file->getClientOriginalName()}."];
        }

        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') {
            return [false, "Plik {$file->getClientOriginalName()} posiada nieprawidłowy nagłówek i nie jest prawidłowym plikiem PDF."];
        }

        // Deep inspection for malicious elements
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [false, "Nie udało się zweryfikować zawartości pliku {$file->getClientOriginalName()}."];
        }

        // Reject PDFs with embedded JS or Launch action
        if (preg_match('/\/JavaScript|\/JS\s|\/Launch/i', $content)) {
            return [false, "Plik {$file->getClientOriginalName()} zawiera niedozwolone elementy aktywne (skrypty/akcje wykonywalne)."];
        }

        return [true, null];
    }
}
