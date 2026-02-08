<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderService
{
	public function createOrder(array $data)
    {
		return DB::transaction(function () use ($data) {
			
			$subtotal = $this->calculateSubtotal($data['items']);
			$orderNumber = $this->generateOrderNumber();
			
			$order = Order::create([
				'order_number'	=> $orderNumber,
				'cutomer'		=> $data['user']->id,
				'currency'		=> $data['currency'],
				'subtotal'		=> $subtotal,
				'tax'			=> $data['tax_amount'] ?? 0,
				'shipping'		=> $data['shipping_cost'] ?? 0,
				'discount'		=> $this->calculateDiscount($subtotal, $data['discount'] ?? 0),
				'status'		=> 'PENDING',
				'shipping_address' => !empty($data['shipping_address'])? json_encode($data['shipping_address'], JSON_UNESCAPED_UNICODE) : null,
				'billing_address' => !empty($data['billing_address'])? json_encode($data['billing_address'], JSON_UNESCAPED_UNICODE) : null,
				'user_agent' => !empty($data['request_info'])? json_encode($data['request_info'], JSON_UNESCAPED_UNICODE) : null,
				'notes' 		=> $data['notes'] ?? null,
			]);
			
			$this->addOrderItems($order, $data['items']);
			
			return Order::getDataWithDetails($order->o_id)[0];
		});
	}
	
    public function updateOrder(array $data)
    {
		$subtotal = $this->calculateSubtotal($data['items']);
		if(!empty($data['OLD']['PAYMENT']) 
				&& ($subtotal != $data['OLD']['SUB_AMOUNT'] || $data['currency'] != $data['currency'])
		)
		{
			return false;
		}
		
		return DB::transaction(function () use ($data,$subtotal) {
			
			$order = Order::findOrFail($data['OLD']['ID']);
			$order->currency = $data['currency'];
			$order->subtotal = $subtotal;
			$order->tax = $data['tax_amount'] ?? 0;
			$order->status = $data['status'] ?? $data['OLD']['STATUS'];
			$order->shipping = $data['shipping_cost'] ?? 0;
			$order->discount = $this->calculateDiscount($subtotal, $data['discount'] ?? 0);
			$order->shipping_address = !empty($data['shipping_address'])? json_encode($data['shipping_address'], JSON_UNESCAPED_UNICODE) : null;
			$order->billing_address = !empty($data['billing_address'])? json_encode($data['billing_address'], JSON_UNESCAPED_UNICODE) : null;
			$order->notes = $data['notes'] ?? null;
			
			$this->updateOrderItems($data['OLD']['ITEMS'], $data['items']);
			
			$order->save();
			
			return Order::getDataWithDetails($data['OLD']['ID'])[0];
		});
	}
    
	private function generateOrderNumber()
    {
        $prefix = 'ORD';
        $date = date('Ymd');
        $unique = strtoupper(Str::random(6));
        
        return "{$prefix}-{$date}-{$unique}";
    }
	
	private function calculateSubtotal(array $items)
    {
        $subtotal = 0;
        
        foreach ($items as $item) {
            $subtotal += ($item['unit_price'] * $item['quantity']);
        }
        
        return round($subtotal, 2);
    }
    
    private function calculateDiscount(float $subtotal, ?array $discount)
    {
        if (!$discount) {
            return 0;
        }
        
        $discountAmount = 0;
        
        if ($discount['type'] === 'percentage') {
            $discountAmount = ($subtotal * $discount['value']) / 100;
        } elseif ($discount['type'] === 'fixed') {
            $discountAmount = $discount['value'];
        }
        
        // التأكد من أن الخصم لا يتجاوز المجموع الفرعي
        return round(min($discountAmount, $subtotal), 2);
    }
	
	private function addOrderItems(Order $order, array $items)
    {
		$o_items = [];
		foreach ($items as $item) {
			$o_items[] = [
					'order_id' => $order->o_id,
					'product_id' => $item['product_id'],
					'quantity' => $item['quantity'],
					'unit_price' => $item['unit_price'],
				];
		}
		OrderItem::insert($o_items);
    }
	
    private function updateOrderItems(array $old, array $items)
    {
		foreach ($items as $item) {
			$curr_item = OrderItem::findOrFail($old[$item['product_id']]['ID']);
			
			$curr_item->quantity = $item['quantity'];
			$curr_item->unit_price = $item['unit_price'];
			$curr_item->save();
		}
    }
    
    
}