<?php

namespace App\Services;

class HtmlSanitizer
{
    /**
     * Sanitize HTML allowing only safe municipal job posting tags:
     * p, ul, ol, li, strong, em, h3, h4, a, br
     */
    public static function clean(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Allowed tags
        $allowedTags = '<p><ul><ol><li><strong><em><h3><h4><a><br>';
        $stripped = strip_tags($html, $allowedTags);

        // Sanitize attributes on allowed tags
        // Remove on* event handlers, javascript: hrefs, style attributes
        $cleaned = preg_replace_callback('/<a\s+([^>]*?)>/i', function ($matches) {
            $attrs = $matches[1];
            $href = '';
            if (preg_match('/href=[\'"]([^\'"]*)[\'"]/i', $attrs, $hrefMatch)) {
                $rawHref = trim($hrefMatch[1]);
                if (preg_match('/^(https?:\/\/|mailto:)/i', $rawHref)) {
                    $href = htmlspecialchars($rawHref, ENT_QUOTES, 'UTF-8');
                }
            }

            if ($href !== '') {
                return '<a href="'.$href.'" target="_blank" rel="noopener noreferrer">';
            }

            return '<a>';
        }, $stripped);

        // Clean out any leftover attributes from other tags
        $cleaned = preg_replace('/<(p|ul|ol|li|strong|em|h3|h4|br)\s+[^>]*>/i', '<$1>', $cleaned);

        return $cleaned;
    }
}
