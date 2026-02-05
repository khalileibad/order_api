<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ModelFunctions;

class Product extends Model implements Auditable
{
    use AuditableTrait, ModelFunctions, SoftDeletes;
	
	protected $primaryKey = 'pro_id';
	protected $fillable = [
				'pro_id',
				'pro_name',
				'category_id',
				'pro_description',
				'pro_price',
				'pro_stock',
				'pro_sku',
				'barcode',
				'image',
				'attributes',
				'is_active',
				'created_at',
				'updated_at'
			];
	
	protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'attributes' => 'array'
    ];
	
	protected static function getFieldMapping(): array
	{
		$table = (new static)->getTable();
		
		return [
			'ID'        => 'pro_id',
			'NAME'      => 'pro_name',
			'CAT'		=> 'category_id',
			'DESCR'		=> 'pro_description',
			'PRICE'		=> 'pro_price',
			'STOCK'		=> 'pro_stock',
			'SKU'		=> 'pro_sku',
			'BARCODE'	=> 'barcode',
			'P_IMG'		=> 'image',
			'ATTR'		=> 'attributes',
			'ACTICE'	=> 'is_active',
			'CR_DATE'	=> $table . '.created_at',
			'UP_DATE'	=> $table . '.updated_at',
		];
	}
	
	public function category()
	{
		return $this->belongsTo(Category::class, self::getFieldMapping()['CAT'], Category::getFieldMapping()['CAT_ID']);
	}
	
	/*public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'unit_price', 'total_price')
            ->withTimestamps();
    }*/
	
	public static function getDataWithDetails(int $id = 0,int $category = 0,$active = null,$stock = null)
	{
		$fields = array_merge(
			self::getSafeFields(),
			[
				self::getFieldMapping()['ID'],
				self::getFieldMapping()['CAT'],
				DB::raw('CONCAT("'.asset('IMG/products/').'/",image) AS IMG')
			]
		);
		$query = self::query();
		$query->select($fields);
		/*$query->withCount([
			'orders as ORDERS'
		]);
		*/
		$query->when($id != 0, fn($q) => $q->where(self::getFieldMapping()['ID'], $id));
		$query->when($category != 0, fn($q) => $q->where(self::getFieldMapping()['CAT'], $category));
		$query->when($active != null, fn($q) => $q->where(self::getFieldMapping()['ACTICE'], $category));
		
		if ($stock !== null)
		{
			if($stock)
			{
				$query->where(self::getFieldMapping()['STOCK'], '>', 0);
			}else{
				$query->where(self::getFieldMapping()['STOCK'], '<=', 0);
			}
		}
		
		$data = $query->get();
		
		$data->transform(function($product) {
			
			$product->CAT_NAME	= $product->category->cat_name;
			
			unset($product->category);
			return $product;
		});
		
		return $data->toArray();
	}
	
}
