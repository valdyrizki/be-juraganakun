<?php

namespace App\Http\Requests;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
            'email'   => 'required|email|max:255|min:8',
            'password' => 'required|min:8',
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
