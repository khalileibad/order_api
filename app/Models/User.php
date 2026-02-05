<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

use OwenIt\Auditing\Contracts\Auditable; // استيراد الواجهة
use OwenIt\Auditing\Auditable as AuditableTrait; // استيراد الـ trait
use App\Traits\ModelFunctions;

class User extends Authenticatable implements JWTSubject, Auditable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, AuditableTrait, ModelFunctions;
	
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
		'role',
		'phone',
		'address'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
	
	public static function getFieldMapping(): array
	{
		$table = (new static)->getTable();
		
		return [
			'ID'		=> 'id',
			'NAME'		=> 'name',
			'EMAIL'		=> 'email',
			'PHONE'		=> 'phone',
			'ADDRESS'	=> 'address',
			
			'TYPE'		=> 'role',
			'ACTIVE'	=> 'u_active',
			'CR_DATE'	=> $table . '.created_at',
			'UP_DATE'	=> $table . '.updated_at',
		];
	}
	
	public function orders()
    {
        return $this->hasMany(Order::class, Order::getFieldMapping()['CUSTOMER'], self::getFieldMapping()['ID']);
    }
	
	/**
	* الحصول على بيانات
	*/
	public static function getData(int $id = 0)
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
			'orders as ORDERS'
		]);
		$query = $query->when($id != 0, fn($q) => $q->where(self::getFieldMapping()['ID'], $id));
		
		$users = $query->get();
		
		return $users;
	}
	
	/**
     * Get the identifier that will be stored in the subject claim of the JWT.
	*/
	public function getJWTIdentifier()
	{
		return $this->getKey();
	}
	
	/**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
	public function getJWTCustomClaims()
	{
		return [
			'role' => $this->role, // ⭐ أضف role في التوكن
			'name' => $this->name,
			'email' => $this->email,
		];
	}
	
	
}
