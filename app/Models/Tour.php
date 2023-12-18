<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Tour
 *
 * @property int $id
 * @property int $hotel_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property int $max_participant_count
 * @property float $adult_price
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TourBooking> $bookings
 * @property-read int|null $bookings_count
 * @property-read int $participant_count
 * @property-read \App\Models\TourHotel $hotel
 * @method static \Illuminate\Database\Eloquent\Builder|Tour newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tour newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tour query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tour whereAdultPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tour whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tour whereHotelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tour whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tour whereMaxParticipantCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tour whereStartDate($value)
 * @mixin \Eloquent
 */
class Tour extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'start_date',
        'end_date',
        'max_participant_count',
        'adult_price',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d\TH:i',
        'end_date' => 'date:Y-m-d\TH:i',
    ];

    protected $appends = [
        'participant_count'
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(TourHotel::class, 'hotel_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(TourBooking::class);
    }

    public function getParticipantCountAttribute(): int
    {
        return $this->bookings->sum(function ($booking) {
            /** @var TourBooking $booking */
            return $booking->adults_count + $booking->children_count;
        });
    }
}
