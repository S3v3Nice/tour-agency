<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\TourHotel
 *
 * @property int $id
 * @property int $city_id
 * @property string $name
 * @property-read \App\Models\TourCity $city
 * @method static \Illuminate\Database\Eloquent\Builder|TourHotel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourHotel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourHotel query()
 * @method static \Illuminate\Database\Eloquent\Builder|TourHotel whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourHotel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourHotel whereName($value)
 * @mixin \Eloquent
 */
class TourHotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(TourCity::class);
    }
}
