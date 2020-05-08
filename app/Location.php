<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Location
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property float $latitude
 * @property float $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereUserId($value)
 * @mixin \Eloquent
 */
class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'user_id',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
