{{--
    Notifikasi sesi global. Dari controller:
        return redirect()->route('books.index')->with('success', 'Buku berhasil disimpan.');
        return back()->with('error', 'Buku gagal dihapus.');
--}}
@foreach (['success' => 'check-circle', 'error' => 'exclamation-circle'] as $type => $icon)
    @if (session($type))
        <div
            x-data="{ show: true }"
            x-show="show"
            role="{{ $type === 'error' ? 'alert' : 'status' }}"
            @class([
                'mb-6 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm',
                'border-green-200 bg-green-50 text-green-800' => $type === 'success',
                'border-red-200 bg-red-50 text-red-800' => $type === 'error',
            ])
        >
            <x-icon :name="$icon" class="mt-0.5 h-5 w-5 shrink-0" />
            <p class="flex-1">{{ session($type) }}</p>
            <button type="button" class="rounded p-0.5 opacity-70 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current" @click="show = false">
                <span class="sr-only">Tutup notifikasi</span>
                <x-icon name="x-mark" class="h-4 w-4" />
            </button>
        </div>
    @endif
@endforeach
