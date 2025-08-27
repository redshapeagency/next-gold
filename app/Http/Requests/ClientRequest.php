<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['required', 'string', 'max:255'],
            'tax_code' => [
                'required',
                'string',
                'size:16',
                'regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/',
                Rule::unique('clients')->ignore($clientId),
            ],
            'id_doc_type' => ['required', 'in:carta_identita,patente,passaporto'],
            'id_doc_number' => ['required', 'string', 'max:255'],
            'id_doc_issuer' => ['required', 'string', 'max:255'],
            'id_doc_issue_date' => ['required', 'date', 'before_or_equal:today'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'zip' => ['required', 'string', 'max:10'],
            'province' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tax_code.required' => 'Il codice fiscale è obbligatorio.',
            'tax_code.size' => 'Il codice fiscale deve essere di 16 caratteri.',
            'tax_code.regex' => 'Il formato del codice fiscale non è valido.',
            'tax_code.unique' => 'Questo codice fiscale è già presente nel sistema.',
            'birth_date.before' => 'La data di nascita deve essere precedente a oggi.',
            'id_doc_issue_date.before_or_equal' => 'La data di rilascio del documento non può essere futura.',
            'province.size' => 'La provincia deve essere di 2 caratteri (es: MI, RM).',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'nome',
            'last_name' => 'cognome',
            'birth_date' => 'data di nascita',
            'birth_place' => 'luogo di nascita',
            'tax_code' => 'codice fiscale',
            'id_doc_type' => 'tipo documento',
            'id_doc_number' => 'numero documento',
            'id_doc_issuer' => 'ente rilascio',
            'id_doc_issue_date' => 'data rilascio documento',
            'address' => 'indirizzo',
            'city' => 'città',
            'zip' => 'CAP',
            'province' => 'provincia',
            'phone' => 'telefono',
            'email' => 'email',
            'notes' => 'note',
        ];
    }
}
