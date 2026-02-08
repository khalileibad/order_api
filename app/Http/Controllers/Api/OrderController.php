<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Services\OrderService;
use App\Models\Order;

class OrderController extends Controller
{
	protected $orderService;
	
	public function __construct(OrderService $orderService)
	{
		$this->orderService = $orderService;
	}
	
	public function index(Request $request, $order_id = 0)
	{
		try{
			$no = "";
			$user = 0;
			
			if(!is_numeric($order_id))
			{
				$no = $order_id;
				$order_id = 0;
			}
			if (!auth()->check() || auth()->user()->role != 'admin' )
			{
				$user = $request->user()->id;
			}
			
			$order = Order::getDataWithDetails($order_id,$no,$user);
			
			if(empty($order))
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في جلب الطلب',
					'error' => "No Order"
				], 404);
			}
			
			return response()->json([
                'success' => true,
                'data' => $order
            ]);
			
		} catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب الأصناف',
                'error' => $e->getMessage()
            ], 500);
        }
	}
	
	public function new_order(OrderRequest $request)
    {
		try {
			$orderData = $request->validated();
            
			$orderData['user'] = $request->user();
			$orderData['request_info'] = [
				'user_agent' => $request->userAgent(),
				'ip_address' => $request->ip(),
				'method' => $request->method(),
				'source' => 'api',
			];
			
			$order = $this->orderService->createOrder($orderData);
            
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => [
                    'order' => $order,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('Create order failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الطلب',
                'error' => config('app.debug') ? $e->getMessage() : 'حدث خطأ غير متوقع'
            ], 500);
        }
    }
	
	public function update_order(UpdateOrderRequest $request, $order_id)
    {
		try {
			$no = "";
			$user = 0;
			
			if(!is_numeric($order_id))
			{
				$no = $order_id;
				$order_id = 0;
			}
			if (!auth()->check() || auth()->user()->role != 'admin' )
			{
				$user = $request->user()->id;
			}
			
			$order = Order::getDataWithDetails($order_id,$no,$user);
			
			if(empty($order))
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في جلب الطلب',
					'error' => "No Order"
				], 404);
			}
			$order = $order[0];
			
			if($user != 0 && $order['STATUS'] != 'PENDING')
			{
				return response()->json([
					'success' => false,
					'message' => 'حالة الطلب الحالية '.$order['STATUS'].", لا يمكنك تحديث بياناتها",
					'error' => "No Permission"
				], 403);
			}
			
			$orderData = $request->validated();
            $orderData['OLD'] = $order;
			
			$order = $this->orderService->updateOrder($orderData);
            
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الطلب بنجاح',
                'data' => [
                    'order' => $order,
                ]
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('Update order failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'فشل في إنشاء الطلب',
                'error' => config('app.debug') ? $e->getMessage() : 'حدث خطأ غير متوقع'
            ], 500);
        }
    }
	
	
	
}
