<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
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
     * @return array
     */
    public function rules()
    {
        $rules = [
            'code' => [
                'required',
                Rule::unique('products')->whereNull('deleted_at'),
                Rule::unique('product_variants')->whereNull('deleted_at'),
                'name'         => 'required',
                'Type_barcode' => 'required',
                'category_id'  => 'required',
                'type'         => 'required',
                'tax_method'   => 'required',
                'unit_id'      => Rule::requiredIf($this->type != 'is_service'),
                'cost'         => Rule::requiredIf($this->type == 'is_single'),
                'price'        => Rule::requiredIf($this->type != 'is_variant'),
            ],
            'warehouses_ids' => ['required', 'array']
        ];

        if ($this->has('variants')) {
            $rules += [
                'variants' => ['required', 'array'],
                'variants.*' => ['required', 'array'],
                'variants.*.text' => ['required'],
                'variants.*.code' => [
                    'required',
                    'distinct:strict',
                    Rule::unique('product_variants', 'code')->whereNull('deleted_at'),
                    Rule::unique('products', 'code')->whereNull('deleted_at'),
                ],
                'variants.*.cost' => ['required', 'integer', 'min:0'],
                'variants.*.price' => ['required', 'integer', 'min:0'],
            ];
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge(json_decode($this->payload, true));
    }
}
