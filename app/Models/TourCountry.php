<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * App\Models\TourCountry
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $description
 * @property string $image_path
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TourCity> $cities
 * @property-read int|null $cities_count
 * @method static \Illuminate\Database\Eloquent\Builder|TourCountry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourCountry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TourCountry query()
 * @method static \Illuminate\Database\Eloquent\Builder|TourCountry whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourCountry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourCountry whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourCountry whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TourCountry whereSlug($value)
 * @mixin \Eloquent
 */
class TourCountry extends Model
{
    use HasRelationships;
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'image_path',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(TourCity::class, 'country_id');
    }

    public function tours(): HasManyDeep
    {
        return $this->hasManyDeep(Tour::class, [TourCity::class, TourHotel::class],
                                  ['country_id', 'city_id', 'hotel_id'])->with('hotel.city');
    }
}
