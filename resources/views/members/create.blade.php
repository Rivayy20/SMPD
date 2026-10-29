@extends('layouts.app')

@section('title', 'Tambah Anggota Perpustakaan')

@section('content')
    <x-page-header
        title="Pendaftaran Anggota Baru"
        description="Pengelolaan profil anggota perpustakaan, nomor kartu, dan tipe keanggotaan."
        :breadcrumbs="[
            'Dashboard' => route('dashboard'),
            'Anggota & Kartu' => route('members.index'),
            'Tambah Anggota' => null,
        ]"
    />

    <div class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('members.store') }}" class="space-y-6">
            @csrf

            <!-- Bagian Nomor & Kartu -->
            <div class="rounded-lg bg-blue-50/50 p-4 ring-1 ring-blue-100">
                <h3 class="text-sm font-semibold text-blue-950">Informasi Keanggotaan & Kartu</h3>
                <p class="mt-0.5 text-xs text-blue-700">Nomor anggota dan nomor kartu diisi otomatis dan dapat disesuaikan.</p>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="membership_number" value="Nomor Anggota *" />
                        <x-text-input
                            id="membership_number"
                            name="membership_number"
                            type="text"
                            class="mt-1 block w-full font-mono font-medium"
                            :value="old('membership_number', $suggestedNumber)"
                            required
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('membership_number')" />
                    </div>

                    <div>
                        <x-input-label for="card_number" value="Nomor Kartu Perpustakaan (ID Card) *" />
                        <x-text-input
                            id="card_number"
                            name="card_number"
                            type="text"
                            class="mt-1 block w-full font-mono font-medium"
                            :value="old('card_number', $suggestedCardNumber)"
                            placeholder="Nomor kartu digital"
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('card_number')" />
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="membership_type_id" value="Tipe Keanggotaan *" />
                        <select
                            id="membership_type_id"
                            name="membership_type_id"
                            required
                            class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600"
                        >
                            @foreach ($membershipTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('membership_type_id') == $type->id)>
                                    {{ $type->name }} (Masa aktif {{ $type->duration_months }} bln, maks {{ $type->max_borrowed_books }} buku)
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('membership_type_id')" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status Keanggotaan *" />
                        <select
                            id="status"
                            name="status"
                            required
                            class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600"
                        >
                            <option value="active" @selected(old('status', 'active') === 'active')>Aktif</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Nonaktif</option>
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('status')" />
                    </div>
                </div>
            </div>

            <!-- Bagian Profil Anggota -->
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-slate-900">Profil Anggota</h3>

                <div>
                    <x-input-label for="name" value="Nama Lengkap *" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('name')"
                        required
                        autofocus
                        placeholder="Contoh: Muhammad Raihan"
                    />
                    <x-input-error class="mt-1" :messages="$errors->get('name')" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="identity_number" value="Nomor Identitas (NIK / NIS / NIM)" />
                        <x-text-input
                            id="identity_number"
                            name="identity_number"
                            type="text"
                            class="mt-1 block w-full font-mono"
                            :value="old('identity_number')"
                            placeholder="Nomor KTP atau Kartu Pelajar"
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('identity_number')" />
                    </div>

                    <div>
                        <x-input-label for="join_date" value="Tanggal Bergabung *" />
                        <x-text-input
                            id="join_date"
                            name="join_date"
                            type="date"
                            class="mt-1 block w-full"
                            :value="old('join_date', date('Y-m-d'))"
                            required
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('join_date')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="email" value="Alamat Email" />
                        <x-text-input
                            id="email"
                            name="email"
                            type="email"
                            class="mt-1 block w-full"
                            :value="old('email')"
                            placeholder="anggota@email.com"
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('email')" />
                    </div>

                    <div>
                        <x-input-label for="phone" value="Nomor Telepon / WhatsApp" />
                        <x-text-input
                            id="phone"
                            name="phone"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('phone')"
                            placeholder="08123456789"
                        />
                        <x-input-error class="mt-1" :messages="$errors->get('phone')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="address" value="Alamat Tempat Tinggal" />
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600"
                        placeholder="Alamat domisili lengkap..."
                    >{{ old('address') }}</textarea>
                    <x-input-error class="mt-1" :messages="$errors->get('address')" />
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a
                    href="{{ route('members.index') }}"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                >
                    <x-icon name="check-circle" class="h-4 w-4" />
                    <span>Simpan & Terbitkan Kartu</span>
                </button>
            </div>
        </form>
    </div>
@endsection
