<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ModelFunctions;

class Category extends Model implements Auditable
{
    use AuditableTrait, ModelFunctions;
	
	protected $primaryKey = 'cat_id';
	protected $fillable = [
				'cat_id',
				'cat_name',
				'created_at',
				'updated_at'
			];
	
	protected static function getFieldMapping(): array
	{
		$table = (new static)->getTable();
		
		return [
			'CAT_ID'	=> 'cat_id',
			'CAT_NAME'	=> 'cat_name',
			'CR_DATE'	=> $table . '.created_at',
			'UP_DATE'	=> $table . '.updated_at',
		];
	}
	
	public function products()
	{
		return $this->hasMany(Product::class, Product::getFieldMapping()['CAT'], self::getFieldMapping()['ID']);
	}
	
	public static function getDataWithDetails(int $id = 0)
	{
		$fields = array_merge(
			self::getSafeFields(),
			[
				self::getFieldMapping()['ID'],
			]
		);
		$query = self::query();
		$query->select($fields);
		$query->withCount([
			'products as PRODUCTS'
		]);
		
		$query->when($id != 0, fn($q) => $q->where(self::getFieldMapping()['ID'], $id));
		
		$data = $query->get();
		
		return $data->toArray();
	}
	
}
