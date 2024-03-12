<?php

namespace App\Http\Requests;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{

    use ApiResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email'   => 'required|email|unique:users|max:255|min:8',
            'username' => 'required|max:255|min:5',
            'password' => 'required|min:8',
            'firstname' => 'max:20',
            'lastname' => 'max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'        => 'Email sudah terdaftar, silahkan login atau hubungi admin via WA : +6283818213645',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        // throw new HttpResponseException(response()->json([
        //     'success' => false,
        //     'message' => 'Validation errors',
        //     'data'    => $validator->errors()
        // ], 422));

        throw new HttpResponseException($this->errorResponse("Validation errors", $validator->errors(), 422));
    }
}
