<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateBookRequest extends StoreBookRequest
{
    /**
     * Aturan sama dengan tambah buku, ditambah opsi menghapus sampul lama.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'remove_cover' => ['nullable', 'boolean'],
        ];
    }

    /**
     * ISBN harus unik, kecuali milik buku yang sedang diedit.
     */
    protected function isbnUniqueRule(): Unique
    {
        return Rule::unique('books', 'isbn')->ignore($this->route('book'));
    }
}
