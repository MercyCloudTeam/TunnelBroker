<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class ASN extends Model
{
	use HasDateTimeFormatter;

	protected $table = 'asn';

    protected $fillable = [
        'user_id','limit','validate','asn','loa','email_verified_at','email'
    ];

    public function scopeActive($query)
    {
        return $query->where('validate', 1);
    }
}
