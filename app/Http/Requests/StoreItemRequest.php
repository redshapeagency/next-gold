<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
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
            'code' => 'required|string|max:255|unique:items,code',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'material' => 'required|in:gold,argento,platino,altro',
            'karat' => 'nullable|integer|min:1|max:24|required_if:material,gold',
            'purity' => 'nullable|numeric|min:0|max:100|required_if:material,argento,platino',
            'weight_grams' => 'required|numeric|min:0.001',
            'price_purchase' => 'required|numeric|min:0',
            'price_sale' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Il codice è obbligatorio.',
            'code.unique' => 'Questo codice è già presente.',
            'name.required' => 'Il nome è obbligatorio.',
            'material.required' => 'Il materiale è obbligatorio.',
            'karat.required_if' => 'Il carato è obbligatorio per l\'oro.',
            'purity.required_if' => 'La purezza è obbligatoria per argento e platino.',
            'weight_grams.required' => 'Il peso è obbligatorio.',
            'weight_grams.min' => 'Il peso deve essere maggiore di 0.',
            'price_purchase.required' => 'Il prezzo di acquisto è obbligatorio.',
            'price_sale.required' => 'Il prezzo di vendita è obbligatorio.',
            'photo.image' => 'Il file deve essere un\'immagine.',
            'photo.mimes' => 'L\'immagine deve essere di tipo: jpeg, png, jpg, gif.',
            'photo.max' => 'L\'immagine non può superare 2MB.',
        ];
    }
}
