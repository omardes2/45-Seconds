<?php

namespace App\Http\Requests\Admin;

use App\Enums\CurrencyEnum;
use App\Enums\Permission;
use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ManageProducts->value) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99', 'gte:base_price'],
            'currency' => ['required', new Enum(CurrencyEnum::class)],
            'status' => ['required', new Enum(ProductStatus::class)],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'video_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'base_price' => 'السعر الأساسي',
            'compare_at_price' => 'السعر قبل الخصم',
            'currency' => 'العملة',
            'status' => 'الحالة',
            'main_image' => 'الصورة الرئيسية',
        ];
    }

    public function messages(): array
    {
        return [
            'compare_at_price.gte' => 'السعر قبل الخصم يجب أن يكون أكبر من أو يساوي السعر الأساسي.',
        ];
    }
}
