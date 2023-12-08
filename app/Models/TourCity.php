<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * App\Models\TourCity
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $description
 * @property string $image_path
 * @property-read \App\Models\TourCountry $country
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TourHotel> $hotels
 * @property-read int|null $hotels_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tour> $tours
 * @property-read int|null $tours_count
 * @method static \Illuminate\Database\Eloquent\Builder|TourCity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourCity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourCity query()
 * @method static \Illuminate\Database\Eloquent\Builder|TourCity whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourCity whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourCity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourCity whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourCity whereName($value)
 * @mixin \Eloquent
 */
class TourCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(TourCountry::class, 'country_id');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(TourHotel::class, 'city_id');
    }

    public function tours(): HasManyThrough
    {
        return $this->hasManyThrough(Tour::class, TourHotel::class, 'city_id', 'hotel_id');
    }
}
