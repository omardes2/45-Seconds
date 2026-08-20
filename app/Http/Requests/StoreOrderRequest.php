<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $page = $this->route('page') ?? $this->attributes->get('landing_page');

        return [
            // Offer must belong to THIS page and be active. This blocks
            // cross-page offers and disabled offers at validation time.
            'offer_id' => [
                'required',
                'integer',
                Rule::exists('offers', 'id')
                    ->where('landing_page_id', $page?->id)
                    ->where('is_active', true),
            ],
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'min:6', 'max:40', 'regex:/^[0-9+\-\s()]+$/'],
            'city' => ['required', 'string', 'max:120'],
            'area' => ['nullable', 'string', 'max:120'],
            'address' => ['required', 'string', 'min:3', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // Any monetary field a tampering client might send is ignored;
            // we never read total/price/subtotal from the request.
        ];
    }

    public function messages(): array
    {
        return [
            'offer_id.exists' => 'العرض المختار غير متاح.',
            'phone.regex' => 'رقم الهاتف غير صالح.',
            'phone.min' => 'رقم الهاتف قصير جدًا.',
            'full_name.required' => 'الاسم مطلوب.',
            'address.required' => 'العنوان مطلوب.',
            'city.required' => 'المدينة مطلوبة.',
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'الاسم',
            'phone' => 'رقم الهاتف',
            'city' => 'المدينة',
            'address' => 'العنوان',
            'offer_id' => 'العرض',
        ];
    }
}
