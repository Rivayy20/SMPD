{{--
    Dialog konfirmasi hapus. Pasang satu kali per halaman, lalu picu dari tombol mana pun:

    <x-confirm-delete title="Hapus buku?" />

    <button type="button" x-data
        x-on:click="$dispatch('confirm-delete', { action: '{{ route('books.destroy', $book) }}', name: @js($book->title) })">
        Hapus
    </button>

    Jika pemicunya komponen (<x-button ...>), ganti @js(...) dengan {{ \Illuminate\Support\Js::from(...) }},
    karena direktif @js tidak dikompilasi di dalam atribut komponen.
--}}
@props([
    'title' => 'Hapus data?',
    'description' => 'Tindakan ini tidak dapat dibatalkan.',
])

<div
    x-data="{ action: '', name: '' }"
    x-on:confirm-delete.window="action = $event.detail.action; name = $event.detail.name; $dispatch('open-modal', 'confirm-delete')"
>
    <x-modal name="confirm-delete" maxWidth="md" focusable>
        <form method="POST" x-bind:action="action" class="p-6">
            @csrf
            @method('DELETE')

            <div class="flex items-start gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <x-icon name="trash" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                    <p class="mt-1 break-words text-sm text-slate-600">
                        <span class="font-semibold text-slate-800" x-text="name"></span>
                        akan dihapus. {{ $description }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button variant="secondary" type="button" x-on:click="$dispatch('close')">Batal</x-button>
                <x-button variant="danger">Ya, hapus</x-button>
            </div>
        </form>
    </x-modal>
</div>
