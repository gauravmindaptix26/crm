<?php

namespace App\Models;
use App\Models\Country;

use Illuminate\Database\Eloquent\Model;

class GuestPost extends Model
{
    protected $fillable = [
        'website',
        'da',
        'pa',
        'industry',
        'country_id',
        'traffic',
        'publisher',
        'publisher_price',
        'publisher_details',
        'live_link',
        'our_price',
        'created_by'
    ];
    public function country()
{
    return $this->belongsTo(Country::class, 'country_id'); // 'country' is the foreign key
}
public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

}

