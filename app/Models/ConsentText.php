<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsentText extends Model
{
    protected $fillable = [
        'version',
        'content',
        'active_from',
    ];

    protected function casts(): array
    {
        return [
            'active_from' => 'datetime',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
