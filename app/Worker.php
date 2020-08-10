<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Location
 *
 * @property int $id
 * @property int $phone
 * @property string $first_name
 * @property string $last_name
 * @property float $latitude
 * @property float $longitude
 * @property \Illuminate\Support\Carbon $last_update
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker whereLastUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Worker whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Worker extends Model
{
    protected $fillable = [
        'phone',
        'first_name',
        'last_name',
        'latitude',
        'longitude',
        'last_update',
        'created_at',
    ];
}
