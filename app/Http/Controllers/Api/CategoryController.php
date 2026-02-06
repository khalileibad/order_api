<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category as CATEGORIES;
use App\Http\Requests\categoryRequest;

class CategoryController extends Controller
{
	//Get Categories
	public function index(Request $request, int $id = 0)
	{
		return response()->json([
                'success' => true,
                'data' => CATEGORIES::getDataWithDetails($id),
            ]);
	}
	
	//Create New Category
    public function new_category(categoryRequest $request)
    {
		try {
			$category = CATEGORIES::create([
				'cat_name'	=> $request->name,
			]);
			
			return response()->json([
				'success' => true,
				'message' => 'تم تسجيل التصنيف بنجاح',
				'data' => [
					'category' => CATEGORIES::getDataWithDetails($category->cat_id),
				]
			], 201);
			
		}catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في حفظ التصنيف',
				'error' => $e->getMessage()
			], 500);
		}
    }
	
	/**
	 * Update Category
	 * $id : Category ID 
	*/
	public function update_category(categoryRequest $request, $id)
    {
        try {
			
			$id = (int)$id;
			$category = CATEGORIES::findOrFail($id);
			
			$category->cat_name = $request->name;
			$category->save();
			
			return response()->json([
				'success' => true,
				'message' => 'تم تحديث بيانات التصنيف بنجاح',
				'data' => [
					'category' => CATEGORIES::getDataWithDetails($category->cat_id),
				]
			], 201);
			
		}catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في حفظ التصنيف',
				'error' => $e->getMessage()
			], 500);
		}
    }

    /**
	 * Delete Category
	 * $id : Category ID 
	*/
	public function delete_category($id)
    {
        try {
			
			$id = (int)$id;
			$category = CATEGORIES::getDataWithDetails($id);
			
			if(empty($category))
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في حذف التصنيف',
					'error' => "لم يتم العثور على التصنيف"
				], 404);
			}
			
			$category = $category[0];
			if(!empty($category['PRODUCTS']))
			{
				return response()->json([
					'success' => false,
					'message' => 'فشل في حذف التصنيف',
					'error' => "لا يمكنك حذف التصنيف"
				], 422);
			}
			
			CATEGORIES::destroy($id);
			CATEGORIES::auto_increment();
			
			return response()->json([
				'success' => true,
				'message' => 'تم حذف التصنيف بنجاح',
				'data' => [
					'category' => $category,
				]
			], 201);
			
		}catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في حذف التصنيف',
				'error' => $e->getMessage()
			], 500);
		}
    }
	
}//class