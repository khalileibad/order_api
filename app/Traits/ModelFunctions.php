<?php

namespace App\Traits;

trait ModelFunctions
{
	protected static function boot()
	{
		parent::boot();
		
		static::deleted(function ($model) {
			$model->auto_increment();
		});
	}
	
	public static function auto_increment()
	{
		$tableName = (new static)->getTable(); 
		
		(new \Illuminate\Support\Facades\DB)::statement("ALTER TABLE `{$tableName}` AUTO_INCREMENT = 1;");
	}
	
	/**
	* الحصول على أسماء الحقول الآمنة للاستعلام
	*/
	public static function getSafeFields(): array
	{
		$aliasedFields = [];

		foreach (self::getFieldMapping() as $alias => $dbField) {
			$aliasedFields[$alias] = "{$dbField} AS {$alias}";
		}
		return $aliasedFields;
	}
	
}