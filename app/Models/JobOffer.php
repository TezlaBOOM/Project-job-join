<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class JobOffer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'public_id',
        'slug',
        'title',
        'department_id',
        'contract_type_id',
        'category_id',
        'location_id',
        'working_time',
        'description',
        'requirements',
        'nice_to_have',
        'offer_text',
        'required_documents',
        'form_id',
        'statements',
        'deadline_at',
        'published_at',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'statements' => 'array',
            'deadline_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JobOffer $offer) {
            if (empty($offer->public_id)) {
                $offer->public_id = (string) Str::ulid();
            }
            if (empty($offer->slug)) {
                $baseSlug = Str::slug($offer->title);
                $suffix = strtolower(Str::random(5));
                $offer->slug = "{$baseSlug}-{$suffix}";
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('deadline_at', '>=', now());
    }

    public function isOpen(): bool
    {
        return $this->status === 'published' && $this->deadline_at->isFuture();
    }
}
