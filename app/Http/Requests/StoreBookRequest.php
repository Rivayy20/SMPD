<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreBookRequest extends FormRequest
{
    /**
     * Akses ke modul buku dibatasi oleh middleware route ('auth', 'role:admin,petugas').
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rapikan input sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $isbn = trim((string) $this->input('isbn'));

        $this->merge([
            // ISBN disimpan tanpa spasi dan dengan huruf X kapital; kosong = null.
            'isbn' => $isbn === '' ? null : strtoupper(preg_replace('/\s+/', '', $isbn)),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:20', 'regex:/^[0-9X-]+$/', $this->isbnUniqueRule()],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'publisher_id' => ['required', 'integer', Rule::exists('publishers', 'id')],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*' => ['integer', 'distinct', Rule::exists('authors', 'id')],
            'publication_year' => ['required', 'integer', 'min:1000', 'max:'.(now()->year + 1)],
            'stock' => ['required', 'integer', 'min:0', 'max:99999'],
            'status' => ['required', Rule::in(array_keys(Book::STATUSES))],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function isbnUniqueRule(): Unique
    {
        return Rule::unique('books', 'isbn');
    }
}
