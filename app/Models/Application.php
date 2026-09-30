<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Application extends Model
{
    use SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_INTERVIEW = 'interview';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_QUALIFIED = 'qualified';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'public_id',
        'reference_code',
        'job_offer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'answers',
        'status',
        'internal_notes',
        'consent_version_id',
        'consent_at',
        'future_consent',
        'cancelled_at',
        'cancel_token_hash',
        'cancel_token_expires_at',
        'source',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'consent_at' => 'datetime',
            'future_consent' => 'boolean',
            'cancelled_at' => 'datetime',
            'cancel_token_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Application $application) {
            if (empty($application->public_id)) {
                $application->public_id = (string) Str::ulid();
            }
            if (empty($application->reference_code)) {
                $year = date('Y');
                $random = strtoupper(Str::random(6));
                $application->reference_code = "REK-{$year}-{$random}";
            }
        });
    }

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    public function consentText(): BelongsTo
    {
        return $this->belongsTo(ConsentText::class, 'consent_version_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ApplicationFile::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function mailLogs(): HasMany
    {
        return $this->hasMany(MailLog::class)->orderBy('sent_at', 'desc');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'Nowe',
            self::STATUS_UNDER_REVIEW => 'W trakcie oceny',
            self::STATUS_INTERVIEW => 'Zaproszony na rozmowę',
            self::STATUS_REJECTED => 'Odrzucone',
            self::STATUS_QUALIFIED => 'Zakwalifikowany / Zatrudniony',
            self::STATUS_CANCELLED => 'Anulowane przez kandydata',
            default => $this->status,
        };
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED || $this->cancelled_at !== null;
    }
}
