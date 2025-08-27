<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('create documents');
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:purchase,sale',
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0.01|max:999999.99',
            'items.*.unit_price' => 'required|numeric|min:0|max:999999999.99',
            'items.*.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Il tipo di documento è obbligatorio.',
            'type.in' => 'Il tipo di documento deve essere acquisto o vendita.',
            'client_id.required' => 'Il cliente è obbligatorio.',
            'client_id.exists' => 'Il cliente selezionato non è valido.',
            'date.required' => 'La data è obbligatoria.',
            'date.date' => 'La data deve essere valida.',
            'notes.max' => 'Le note non possono superare i 1000 caratteri.',
            'items.required' => 'Almeno un articolo è obbligatorio.',
            'items.array' => 'Gli articoli devono essere un array.',
            'items.min' => 'Almeno un articolo è obbligatorio.',
            'items.*.item_id.required' => 'L\'articolo è obbligatorio.',
            'items.*.item_id.exists' => 'L\'articolo selezionato non è valido.',
            'items.*.quantity.required' => 'La quantità è obbligatoria.',
            'items.*.quantity.numeric' => 'La quantità deve essere un numero.',
            'items.*.quantity.min' => 'La quantità deve essere maggiore di 0.',
            'items.*.quantity.max' => 'La quantità non può superare 999999.99.',
            'items.*.unit_price.required' => 'Il prezzo unitario è obbligatorio.',
            'items.*.unit_price.numeric' => 'Il prezzo unitario deve essere un numero.',
            'items.*.unit_price.min' => 'Il prezzo unitario deve essere maggiore o uguale a 0.',
            'items.*.unit_price.max' => 'Il prezzo unitario non può superare 999999999.99.',
            'items.*.notes.max' => 'Le note dell\'articolo non possono superare i 500 caratteri.',
        ];
    }

    protected function prepareForValidation()
    {
        // Ensure items array is properly formatted
        if ($this->has('items')) {
            $items = collect($this->items)->filter(function ($item) {
                return isset($item['item_id']) && !empty($item['item_id']);
            })->values()->all();
            
            $this->merge(['items' => $items]);
        }
    }
}
