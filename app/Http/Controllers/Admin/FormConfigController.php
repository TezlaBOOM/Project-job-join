<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormConfigController extends Controller
{
    public function index(): View
    {
        $forms = Form::with(['fields' => function ($q) {
            $q->orderBy('position');
        }])->get();

        return view('admin.forms.index', compact('forms'));
    }

    public function storeForm(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $form = Form::create([
            'name' => trim($request->input('name')),
            'is_template' => true,
        ]);

        // Seed basic system fields into template
        $this->seedDefaultFields($form);

        AuditLogger::log('form_template_created', $form, null, $form->toArray());

        return back()->with('status', "Szablon formularza „{$form->name}” został utworzony.");
    }

    public function show(int $id): View
    {
        $form = Form::with(['fields' => function ($q) {
            $q->orderBy('position');
        }])->findOrFail($id);

        return view('admin.forms.show', compact('form'));
    }

    public function storeField(Request $request, int $formId): RedirectResponse
    {
        $form = Form::findOrFail($formId);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:150'],
            'key' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:text,textarea,email,tel,number,date,select,checkbox,switch,statement,file'],
            'help_text' => ['nullable', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'options' => ['nullable', 'string'], // comma-separated for select/checkbox
        ]);

        $maxPos = $form->fields()->max('position') ?? 0;

        $optionsArray = null;
        if (! empty($validated['options'])) {
            $optionsArray = array_map('trim', explode(',', $validated['options']));
        }

        $field = FormField::create([
            'form_id' => $form->id,
            'key' => $validated['key'],
            'type' => $validated['type'],
            'label' => $validated['label'],
            'help_text' => $validated['help_text'],
            'is_required' => $request->boolean('is_required'),
            'is_system' => false,
            'options' => $optionsArray,
            'position' => $maxPos + 1,
            'is_active' => true,
        ]);

        AuditLogger::log('form_field_created', $field, null, $field->toArray());

        return back()->with('status', "Pole „{$field->label}” zostało dodane do formularza.");
    }

    public function toggleField(int $id): RedirectResponse
    {
        $field = FormField::findOrFail($id);

        if ($field->is_system) {
            return back()->withErrors(['error' => 'Pola systemowe nie mogą zostać dezaktywowane.']);
        }

        $field->is_active = ! $field->is_active;
        $field->save();

        AuditLogger::log('form_field_toggled', $field, null, $field->toArray());

        return back()->with('status', "Zmieniono status pola „{$field->label}”.");
    }

    public function moveField(int $id, string $direction): RedirectResponse
    {
        $field = FormField::findOrFail($id);
        $currentPos = $field->position;

        if ($direction === 'up') {
            $prev = FormField::where('form_id', $field->form_id)
                ->where('position', '<', $currentPos)
                ->orderBy('position', 'desc')
                ->first();

            if ($prev) {
                $field->position = $prev->position;
                $prev->position = $currentPos;
                $field->save();
                $prev->save();
            }
        } elseif ($direction === 'down') {
            $next = FormField::where('form_id', $field->form_id)
                ->where('position', '>', $currentPos)
                ->orderBy('position', 'asc')
                ->first();

            if ($next) {
                $field->position = $next->position;
                $next->position = $currentPos;
                $field->save();
                $next->save();
            }
        }

        return back()->with('status', 'Kolejność pól została zaktualizowana.');
    }

    protected function seedDefaultFields(Form $form): void
    {
        $defaults = [
            ['key' => 'first_name', 'label' => 'Imię', 'type' => 'text', 'is_required' => true, 'is_system' => true, 'position' => 1],
            ['key' => 'last_name', 'label' => 'Nazwisko', 'type' => 'text', 'is_required' => true, 'is_system' => true, 'position' => 2],
            ['key' => 'email', 'label' => 'Adres e-mail', 'type' => 'email', 'is_required' => true, 'is_system' => true, 'position' => 3],
            ['key' => 'phone', 'label' => 'Numer telefonu', 'type' => 'tel', 'is_required' => false, 'is_system' => false, 'position' => 4],
            ['key' => 'cv_file', 'label' => 'Życiorys (CV) w formacie PDF', 'type' => 'file', 'is_required' => true, 'is_system' => true, 'position' => 5],
        ];

        foreach ($defaults as $d) {
            $form->fields()->create(array_merge($d, ['is_active' => true]));
        }
    }
}
