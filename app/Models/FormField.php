<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'key',
        'type',
        'label',
        'help_text',
        'is_required',
        'is_system',
        'validation',
        'options',
        'section',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'validation' => 'array',
            'options' => 'array',
            'position' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
