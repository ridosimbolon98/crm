<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    use HasFactory;

    public const CAPA_STATUS_DRAFT = 'Draft';
    public const CAPA_STATUS_SUBMITTED = 'Submitted';
    public const CAPA_STATUS_APPROVED = 'Approved';
    public const CAPA_STATUS_REJECTED = 'Rejected';
    public const CAPA_STATUS_CLOSED = 'Closed';

    public const CAPA_STATUS_OPTIONS = [
        self::CAPA_STATUS_DRAFT,
        self::CAPA_STATUS_SUBMITTED,
        self::CAPA_STATUS_APPROVED,
        self::CAPA_STATUS_REJECTED,
        self::CAPA_STATUS_CLOSED,
    ];

    public const STATUS_OPTIONS = [
        'Open',
        'Investigating',
        'Action Plan',
        'Resolved',
        'Closed',
    ];

    public const SEVERITY_OPTIONS = [
        'Low',
        'Medium',
        'High',
        'Critical',
    ];

    public const CHANNEL_OPTIONS = [
        'Phone',
        'WhatsApp',
        'Email',
        'Sales Representative',
        'Distributor',
        'Social Media',
    ];

    protected $fillable = [
        'ticket_number',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'source',
        'brand_id',
        'complaint_category_id',
        'complaint_channel',
        'production_code',
        'complaint_date',
        'severity',
        'status',
        'capa_status',
        'assigned_to',
        'target_resolution_date',
        'capa_due_date',
        'description',
        'extra_payload',
        'resolution_summary',
        'capa_root_cause',
        'capa_corrective_action',
        'capa_preventive_action',
        'capa_submitted_at',
        'capa_approved_at',
        'capa_approved_by',
        'capa_rejected_reason',
        'compensation_type',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'complaint_date' => 'date',
            'target_resolution_date' => 'date',
            'capa_due_date' => 'date',
            'capa_submitted_at' => 'datetime',
            'capa_approved_at' => 'datetime',
            'closed_at' => 'datetime',
            'extra_payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<ComplaintCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'complaint_category_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function capaApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'capa_approved_by');
    }

    /**
     * @return HasMany<ComplaintUpdate, $this>
     */
    public function updates(): HasMany
    {
        return $this->hasMany(ComplaintUpdate::class)->latest('event_at');
    }

    /**
     * @return HasMany<ComplaintAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class)->latest();
    }
}
