<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TourPayment
 *
 * @property int $id
 * @property int $booking_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TourBooking $booking
 * @method static \Illuminate\Database\Eloquent\Builder|TourPayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourPayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourPayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|TourPayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourPayment whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourPayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourPayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourPayment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TourPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(TourBooking::class);
    }
}
