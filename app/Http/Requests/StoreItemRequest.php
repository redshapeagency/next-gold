<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('create items');
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'weight' => 'required|numeric|min:0|max:999999.99',
            'karat' => 'nullable|in:24,22,18,14,10',
            'price' => 'required|numeric|min:0|max:999999999.99',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'La categoria è obbligatoria.',
            'category_id.exists' => 'La categoria selezionata non è valida.',
            'name.required' => 'Il nome è obbligatorio.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',
            'description.max' => 'La descrizione non può superare i 1000 caratteri.',
            'weight.required' => 'Il peso è obbligatorio.',
            'weight.numeric' => 'Il peso deve essere un numero.',
            'weight.min' => 'Il peso deve essere maggiore di 0.',
            'weight.max' => 'Il peso non può superare 999999.99.',
            'karat.in' => 'Il valore dei carati deve essere uno tra: 24, 22, 18, 14, 10.',
            'price.required' => 'Il prezzo è obbligatorio.',
            'price.numeric' => 'Il prezzo deve essere un numero.',
            'price.min' => 'Il prezzo deve essere maggiore di 0.',
            'price.max' => 'Il prezzo non può superare 999999999.99.',
            'photo.image' => 'Il file deve essere un\'immagine.',
            'photo.mimes' => 'L\'immagine deve essere in formato: jpeg, png, jpg, gif.',
            'photo.max' => 'L\'immagine non può superare i 2MB.',
            'notes.max' => 'Le note non possono superare i 1000 caratteri.',
            'status.required' => 'Lo stato è obbligatorio.',
            'status.in' => 'Lo stato deve essere attivo o inattivo.',
        ];
    }
}
