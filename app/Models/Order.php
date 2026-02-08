<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelFunctions;

class Order extends Model implements Auditable
{
    use AuditableTrait, ModelFunctions;
	
	protected $primaryKey = 'o_id';
	protected $fillable = [
        'o_id',
        'order_number',
        'cutomer',
		'currency',
		'subtotal',
		'tax',
		'shipping',
		'discount',
		'status',
		'shipping_address',
		'billing_address',
		'user_agent',
		'notes',
    ];
	
	public static function getFieldMapping(): array
	{
		$table = (new static)->getTable();
		
		return [
			'ID'		=> 'o_id',
			'NUMBER'	=> 'order_number',
			'CUSTOMER'	=> 'cutomer',
			'CURRENCY'	=> 'currency',
			'SUB_AMOUNT'=> 'subtotal',
			'TAXS'		=> 'tax',
			'SHIPPING'	=> 'shipping',
			'DISCOUNT'	=> 'discount',
			'STATUS'	=> 'status',
			'SHIPPING_ADD'	=> 'shipping_address',
			'BILLING_ADD'	=> 'billing_address',
			'USER_AGENT'	=> 'user_agent',
			'NOTES'		=> 'notes',
			'CR_DATE'	=> $table . '.created_at',
			'UP_DATE'	=> $table . '.updated_at',
		];
	}
	
	public function items()
	{
		return $this->hasMany(OrderItem::class, OrderItem::getFieldMapping()['ORDER'], self::getFieldMapping()['ID']);
	}
	
	public function payment()
	{
		return $this->belongsTo(Payment::class, self::getFieldMapping()['ID'], Payment::getFieldMapping()['ORDER']);
	}
	
	public function cutomer_data()
	{
		return $this->belongsTo(User::class, self::getFieldMapping()['CUSTOMER'], User::getFieldMapping()['ID']);
	}
	
	public static function getDataWithDetails(int $id = 0, string $no = "", int $user = 0)
	{
		$fields = array_merge(
			self::getSafeFields(),
			[
				self::getFieldMapping()['ID'],
				self::getFieldMapping()['CUSTOMER'],
			]
		);
		$query = self::query();
		$query->select($fields);
		
		$query->when($id != 0, fn($q) => $q->where(self::getFieldMapping()['ID'], $id));
		$query->when($no != '', fn($q) => $q->where(self::getFieldMapping()['NUMBER'], $no));
		$query->when($user != 0, fn($q) => $q->where(self::getFieldMapping()['CUSTOMER'], $user));
		
		$data = $query->get();
		
		$data->transform(function($order) {
			
			$order->TOTAL = $order->SUB_AMOUNT + $order->TAXS + $order->SHIPPING - $order->DISCOUNT;
			
			$items = $order->items->map(function ($item)
			{
				return [
					'ID'		=> $item->item_id,
					'PRODUCT'	=> $item->product_id,
					'QUANTITY'	=> $item->quantity,
					'PRICE' 	=> $item->unit_price,
					'PRODUCT_NAME'	=> $item->product->pro_name ?? "",
				];
			});
			$order->ITEMS = $items->keyBy('PRODUCT')->toArray();
			unset($order->items);
			
			$order->CUSTOMER_NAME = $order->cutomer_data->name;
			$order->CUSTOMER_PHONE = $order->cutomer_data->phone;
			$order->CUSTOMER_EMAIL = $order->cutomer_data->email;
			
			if($order->payment)
			{
				$order->PAYMENT = $order->payment->pay_id;
				$order->TRANSACTION = $order->payment->transaction_id;
				$order->GATEWAY = $order->payment->gateway;
				$order->P_METHOD = $order->payment->payment_method;
				$order->P_AMOUNT = $order->payment->amount;
				$order->P_AMOUNT = $order->payment->amount;
				$order->P_STATUS = $order->payment->status;
				$order->G_TRANSACTION = $order->payment->gateway_transaction_id;
				$order->G_REFRENCE = $order->payment->gateway_reference;
				$order->G_RESPONSE = $order->payment->gateway_response;
				$order->URL = $order->payment->payment_url;
				$order->G_DESCR = $order->payment->description;
				$order->PAIED = $order->payment->paid_at;
				$order->EXIRES = $order->payment->expires_at;
			}else{
				$order->PAYMENT = null;
			}
			return $order;
		});
		
		return $data->toArray();
	}
	
}
