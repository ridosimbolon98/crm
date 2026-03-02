<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'event_type',
        'status_before',
        'status_after',
        'author',
        'note',
        'event_at',
    ];

    protected function casts(): array
    {
        return [
            'event_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Complaint, $this>
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
