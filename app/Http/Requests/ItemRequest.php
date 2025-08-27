<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('item')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'material' => ['required', 'in:gold,silver,platinum,other'],
            'karat' => ['nullable', 'integer', 'min:1', 'max:24'],
            'purity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'weight_grams' => ['required', 'numeric', 'min:0.001', 'max:99999.999'],
            'price_purchase' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'price_sale' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'description' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'status' => ['sometimes', 'in:in_stock,archived'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Se il materiale è oro, il carato è obbligatorio
            if ($this->material === 'gold' && !$this->karat) {
                $validator->errors()->add('karat', 'Il carato è obbligatorio per l\'oro.');
            }

            // Se il materiale non è oro, la purezza può essere specificata
            if ($this->material !== 'gold' && $this->karat) {
                $validator->errors()->add('karat', 'Il carato è applicabile solo all\'oro.');
            }

            // Il prezzo di vendita deve essere maggiore di quello di acquisto
            if ($this->price_sale && $this->price_purchase && $this->price_sale <= $this->price_purchase) {
                $validator->errors()->add('price_sale', 'Il prezzo di vendita deve essere maggiore di quello di acquisto.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'weight_grams.min' => 'Il peso deve essere almeno 0.001 grammi.',
            'weight_grams.max' => 'Il peso non può superare 99999.999 grammi.',
            'photo.image' => 'Il file deve essere un\'immagine.',
            'photo.mimes' => 'L\'immagine deve essere in formato: jpeg, png, jpg, gif.',
            'photo.max' => 'L\'immagine non può superare 2MB.',
            'karat.min' => 'Il carato deve essere almeno 1.',
            'karat.max' => 'Il carato non può superare 24.',
            'purity.min' => 'La purezza deve essere almeno 0.',
            'purity.max' => 'La purezza non può superare 1.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'category_id' => 'categoria',
            'material' => 'materiale',
            'karat' => 'carato',
            'purity' => 'purezza',
            'weight_grams' => 'peso (grammi)',
            'price_purchase' => 'prezzo di acquisto',
            'price_sale' => 'prezzo di vendita',
            'description' => 'descrizione',
            'photo' => 'foto',
            'status' => 'stato',
        ];
    }
}
