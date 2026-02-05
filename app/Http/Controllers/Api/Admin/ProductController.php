<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product as PRODUCTS;
use App\Http\Requests\productRequest;

class ProductController extends Controller
{
	public function index(Request $request)
	{
		try{
			
			$id 		= (int) $request->input('id', 0);
			$category 	= (int) $request->input('category_id', 0);
			$active 	= $request->input('active', null);
			$stock 		= $request->input('stock_status', null);
			
			$products = PRODUCTS::getDataWithDetails($id,$category,$active,$stock);
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}


/*

*/
