<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable; // استيراد الواجهة
use OwenIt\Auditing\Auditable as AuditableTrait; // استيراد الـ trait
use App\Traits\ModelFunctions;

class Order extends Model
{
    use AuditableTrait, ModelFunctions;
	
	protected $fillable = [
        'cutomer',
        'paid_amount',
        'taxes',
		'status',
		'payment',
    ];
	
	public static function getFieldMapping(): array
	{
		$table = (new static)->getTable();
		
		return [
			'ID'		=> 'o_id',
			'CUSTOMER'	=> 'cutomer',
			'AMOUNT'	=> 'paid_amount',
			'TAXS'		=> 'taxes',
			'STATUS'	=> 'status',
			'PAYMENT'	=> 'payment',
			'CR_DATE'	=> $table . '.created_at',
			'UP_DATE'	=> $table . '.updated_at',
		];
	}
	
	
}
