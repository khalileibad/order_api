<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelFunctions;

class Payment extends Model implements Auditable
{
	use AuditableTrait, ModelFunctions;
	
	protected $primaryKey = 'pay_id';
	protected $fillable = [
        'order_id',
        'cutomer',
		'currency',
		'transaction_id',
        'gateway',
		'payment_method',
        'amount',
		'status',
		'gateway_transaction_id',
		'gateway_reference',
		'gateway_response',
		'payment_url',
		'metadata',
		'description',
		'paid_at',
		'expires_at',
    ];
    
	
	public static function getFieldMapping(): array
	{
		$table = (new static)->getTable();
		
		return [
			'ID'		=> 'pay_id',
			'ORDER'		=> 'order_id',
			'CUSTOMER'	=> 'cutomer',
			'CURRENCY'	=> 'currency',
			'TRANSACTION'=> 'transaction_id',
			'GATEWAY'	=> 'gateway',
			'P_METHOD'	=> 'payment_method',
			'AMOUNT'	=> 'amount',
			'STATUS'	=> 'status',
			'G_TRANSACTION'	=> 'gateway_transaction_id',
			'G_REFRENCE'	=> 'gateway_reference',
			'G_RESPONSE'	=> 'gateway_response',
			'URL'		=> 'payment_url',
			'DESCR'		=> 'description',
			'PAIED'		=> 'paid_at',
			'EXIRES'	=> 'expires_at',
			'CR_DATE'	=> $table . '.created_at',
			'UP_DATE'	=> $table . '.updated_at',
		];
	}
	
	public function order()
	{
		return $this->belongsTo(Order::class, self::getFieldMapping()['ORDER'], Order::getFieldMapping()['ID']);
	}
	
	public function cutomer()
	{
		return $this->belongsTo(User::class, self::getFieldMapping()['CUSTOMER'], User::getFieldMapping()['ID']);
	}
	
	public static function getDataWithDetails(int $id = 0, int $order = 0)
	{
		$fields = array_merge(
			self::getSafeFields(),
			[
				self::getFieldMapping()['ID'],
			]
		);
		$query = self::query();
		$query->select($fields);
		
		$query->when($id != 0, fn($q) => $q->where(self::getFieldMapping()['ID'], $id));
		$query->when($order != 0, fn($q) => $q->where(self::getFieldMapping()['ORDER'], $order));
		
		$data = $query->get();
		
		
		return $data->toArray();
	}
}
