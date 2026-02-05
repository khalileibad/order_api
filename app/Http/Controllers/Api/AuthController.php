<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth; 
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    function __construct()
	{
		
	}
	//For register New customer
	public function register(RegisterRequest $request)
	{
		try {
			$user = User::create([
				'name' => $request->name,
				'email' => $request->email,
				'password' => Hash::make($request->password),
				'role' => 'customer',
				'phone' => $request->phone,
				'address' => $request->address,
			]);
			
			$token = JWTAuth::fromUser($user);
			
			return response()->json([
				'success' => true,
				'message' => 'تم تسجيل المستخدم بنجاح',
				'data' => [
					'user' => [
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email,
						'role' => $user->role,
						'phone' => $user->phone,
					],
					'access_token' => $token,
					'token_type' => 'Bearer'
				]
			], 201);
			
		}catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في التسجيل',
				'error' => $e->getMessage()
			], 500);
		}
    }//Register
	
	//Login
	public function login(LoginRequest $request)
	{
		try {
			$credentials = $request->only('email', 'password');
			
			// ⭐ محاولة الحصول على JWT token
			if (!$token = JWTAuth::attempt($credentials)) {
				return response()->json([
					'success' => false,
					'message' => 'Invalid credentials'
				], 401);
			}
			
			$user = JWTAuth::user();
			
			return response()->json([
				'success' => true,
				'message' => 'Login successful',
				'data' => [
					'user' => [
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email,
						'role' => $user->role,
					],
					'access_token' => $token,
					'token_type' => 'Bearer',
					'expires_in' => config('jwt.ttl') * 60
				]
			]);

		} catch (JWTException $e) {
			return response()->json([
				'success' => false,
				'message' => 'Could not create token',
				'error' => $e->getMessage()
			], 500);
		}
		
	}//Login
	
	public function logout(Request $request)
	{
		try {
			JWTAuth::invalidate(JWTAuth::getToken());
			
			return response()->json([
				'success' => true,
				'message' => 'تم تسجيل الخروج بنجاح'
			]);
		
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في تسجيل الخروج',
				'error' => $e->getMessage()
			], 500);
		}
	}//logout
	
	
	//Get current user info
	public function me(Request $request)
	{
		try {
			$user = JWTAuth::parseToken()->authenticate();
			$user->loadCount(['orders']);
			
			return response()->json([
				'success' => true,
				'data' => [
					'user' => [
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email,
						'role' => $user->role,
						'phone' => $user->phone,
						'address' => $user->address,
						'stats' => [
							'orders_count' => $user->orders_count,
						]
					]
				]
			]);
		
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في الحصول على بيانات المستخدم',
				'error' => $e->getMessage()
			], 500);
		}
    }//me
	
    //Update Token
	public function refresh(Request $request)
	{
		try {
			$newToken = JWTAuth::refresh(JWTAuth::getToken());
            
			return response()->json([
				'success' => true,
				'data' => [
					'access_token' => $newToken,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60
				]
			]);
			
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'فشل في تحديث التوكن',
				'error' => $e->getMessage()
			], 500);
		}
	}//refresh
	
}
