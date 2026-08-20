<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::ManageOffers->value)
            || $this->user()?->can(Permission::ManagePages->value);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99', 'gte:price'],
            'badge_text' => ['nullable', 'string', 'max:40'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم العرض',
            'quantity' => 'الكمية',
            'price' => 'السعر',
            'compare_at_price' => 'السعر قبل الخصم',
        ];
    }

    public function messages(): array
    {
        return [
            'compare_at_price.gte' => 'السعر قبل الخصم يجب أن يكون أكبر من أو يساوي السعر.',
        ];
    }
}
