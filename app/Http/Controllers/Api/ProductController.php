<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product as PRODUCTS;
use App\Models\Category as CATEGORIES;
use App\Http\Requests\productRequest;

class ProductController extends Controller
{
	//Get Products
	public function index(Request $request, int $id = 0)
	{
		try{
			
			$id 		= (int) $request->input('id', $id);
			$category 	= (int) $request->input('category_id', 0);
			$active 	= $request->input('active', null);
			$stock 		= $request->input('stock_status', null);
			
			if (!auth()->check() || auth()->user()->role != 'admin' )
			{
				$stock = true;
			}
			
			$products = PRODUCTS::getDataWithDetails($id,$category,$active,$stock);
			
			if(empty($products))
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في جلب الأصناف',
					'error' => "No Products"
				], 404);
			}
			
			return response()->json([
                'success' => true,
                'data' => $products
            ]);
			
		} catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في جلب الأصناف',
                'error' => $e->getMessage()
            ], 500);
        }
		
	}
	
	//Get Categories
	public function categories(Request $request, int $id = 0)
	{
		return response()->json([
                'success' => true,
                'data' => CATEGORIES::getDataWithDetails($id),
            ]);
	}
	
	
	//Create New Product
    public function new_product(productRequest $request)
    {
		try {
			
			$product = PRODUCTS::create([
				'pro_name'		=> $request->name,
				'category_id'	=> $request->category_id,
				'pro_description'=> $request->description ?? null,
				'pro_price'		=> $request->price,
				'pro_stock'		=> $request->stock,
				'pro_sku'		=> $request->sku ?? null,
				'barcode'		=> $request->barcode ?? null,
				'attributes'	=> $request->attributes ?? null,
				'is_active'		=> $request->is_active ?? true,
				
			]);
			
			return response()->json([
				'success' => true,
				'message' => 'تم تسجيل الصنف بنجاح',
				'data' => [
					'product' => PRODUCTS::getDataWithDetails($product->pro_id),
				]
			], 201);
			
		}catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في حفظ الصنف',
				'error' => $e->getMessage()
			], 500);
		}
    }
	
	/**
	 * Update Product
	 * $id : Product ID 
	*/
	public function update_product(productRequest $request, $id)
    {
        try {
			
			$id = (int)$id;
			$product = PRODUCTS::findOrFail($id);
			
			$product->pro_name 		= $request->name;
			$product->category_id 	= $request->category_id;
			$product->pro_description = $request->description ?? $product->pro_description;
			$product->pro_price 	= $request->price;
			$product->pro_stock 	= $request->stock;
			$product->pro_sku 		= $request->sku ?? $product->pro_sku;
			$product->barcode 		= $request->barcode ?? $product->barcode;
			$product->attributes 	= $request->attributes ?? $product->attributes;
			$product->is_active 	= $request->is_active ?? $product->is_active;
			
			$product->save();
			
			return response()->json([
				'success' => true,
				'message' => 'تم تحديث بيانات الصنف بنجاح',
				'data' => [
					'product' => PRODUCTS::getDataWithDetails($product->pro_id),
				]
			], 201);
			
		}catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في حفظ الصنف',
				'error' => $e->getMessage()
			], 500);
		}
    }

    /**
	 * Delete Product
	 * $id : Product ID 
	*/
	public function delete_product($id)
    {
        try {
			
			$id = (int)$id;
			$product = PRODUCTS::getDataWithDetails($id);
			
			if(empty($product))
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في حذف الصنف',
					'error' => "لم يتم العثور على الصنف"
				], 404);
			}
			
			$product = $product[0];
			if(!empty($product['STOCK']) ||!empty($product['ORDERS']))
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في حذف الصنف',
					'error' => "لا يمكنك حذف الصنف"
				], 422);
			}
			
			PRODUCTS::destroy($id);
			PRODUCTS::auto_increment();
			
			return response()->json([
				'success' => true,
				'message' => 'تم حذف الصنف بنجاح',
				'data' => [
					'product' => $product,
				]
			], 201);
			
		}catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في حذف الصنف',
				'error' => $e->getMessage()
			], 500);
		}
    }
	
}//class