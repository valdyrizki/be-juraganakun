<?php

namespace App\Http\Requests;

use App\Traits\ApiResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateProductRequest extends FormRequest
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
            'product_id'   => 'required|unique:products|max:4|min:4',
            'product_name' => 'required|max:255|min:5',
            'price' => 'required',
            'description' => 'required',
            'category_id' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.unique'        => 'Produk sudah terdaftar!',
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
