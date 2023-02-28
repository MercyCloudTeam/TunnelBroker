<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ASN extends Model
{

	protected $table = 'asn';

    protected $fillable = [
        'user_id','limit','validate','asn','loa','email_verified_at','email'
    ];

    //get Validate is true
    public function scopeActive($query)
    {
        return $query->where('validate',true);
    }
}
