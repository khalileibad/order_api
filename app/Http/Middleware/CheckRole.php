<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckRole
{
	/**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  // أدوار مسموحة
     * @return mixed
     */
	public function handle(Request $request, Closure $next, ...$roles)
	{
		try {
			$user = JWTAuth::parseToken()->authenticate();
			
			if (!$user) {
				return response()->json([
					'success' => false,
					'message' => 'User not found'
				], 401);
			}
			
			if (!in_array($user->role, $roles)) {
				$roleNames = [
					'admin' => 'مدير',
					'customer' => 'عميل'
				];
				
				$requiredNames = array_map(function($role) use ($roleNames) {
					return $roleNames[$role] ?? $role;
				}, $roles);
				
				return response()->json([
					'success' => false,
					'message' => 'Insufficient permissions. Required: ' . implode(' or ', $requiredNames),
					'user_role' => $user->role,
					'required_roles' => $roles
				], 403);
            }
			
			$request->merge(['user' => $user]);
			
			return $next($request);
		
		} catch (JWTException $e) {
			return response()->json([
				'success' => false,
				'message' => 'Authentication failed',
				'error' => $e->getMessage()
			], 401);
		}
		
    }//Handle
	
	
	
}