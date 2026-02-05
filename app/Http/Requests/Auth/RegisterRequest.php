<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
		return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
			'password' => [
				'required',
				'string',
				'confirmed',
				Password::min(4)
					->letters()
					->mixedCase()
					->numbers()
			],
			'phone' => ['nullable', 'string', 'max:20'],
			'address' => ['nullable', 'string', 'max:500'],
        ];
    }
	
	//For Error Messages
	public function messages(): array
    {
		return [
            'name.required' => 'الاسم الكامل مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق',
            'password.min' => 'كلمة المرور يجب أن تكون 4 أحرف على الأقل',
        ];
    }
	
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        
    }
}
