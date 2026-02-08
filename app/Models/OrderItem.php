<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelFunctions;

class OrderItem extends Model implements Auditable
{
    use AuditableTrait, ModelFunctions;
	
	protected $primaryKey = 'item_id';
	
	protected $fillable = [
				'item_id',
				'order_id',
				'product_id',
				'quantity',
				'unit_price',
				'created_at',
				'updated_at'
			];
	protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
    ];
	
	protected static function getFieldMapping(): array
	{
		$table = (new static)->getTable();
		
		return [
			'ID'		=> 'item_id',
			'ORDER'		=> 'order_id',
			'PRODUCT'	=> 'product_id',
			'QUANTITY'	=> 'quantity',
			'PRICE'		=> 'unit_price',
			'CR_DATE'	=> $table . '.created_at',
			'UP_DATE'	=> $table . '.updated_at',
		];
	}
	
	public function product()
	{
		return $this->belongsTo(Product::class, self::getFieldMapping()['PRODUCT'], Product::getFieldMapping()['ID']);
	}
	
	public function order()
	{
		return $this->belongsTo(Order::class, self::getFieldMapping()['ORDER'], Order::getFieldMapping()['ID']);
	}
	
}
