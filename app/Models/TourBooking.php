<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TourBooking
 *
 * @property int $id
 * @property int $tour_id
 * @property int $user_id
 * @property int $adults_count
 * @property int $children_count
 * @property float $total_amount
 * @property int $is_verified
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $formatted_created_at
 * @property-read \App\Models\Tour $tour
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking query()
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereAdultsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereChildrenCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereTourId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourBooking whereUserId($value)
 * @mixin \Eloquent
 */
class TourBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'adults_count',
        'children_count',
        'total_amount',
    ];

    protected $attributes = [
        'is_verified' => false,
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }
}
