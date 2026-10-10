<x-guest-layout>
    <x-slot:title>Masuk</x-slot:title>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Masuk ke SMPD</h1>
        <p class="mt-1 text-sm text-slate-600">Masuk untuk mengakses sistem manajemen perpustakaan.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3" :status="session('status')" role="status" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="nama@contoh.com"
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                @class([
                    'mt-1.5 block w-full rounded-lg px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2',
                    'border-slate-300 focus:border-blue-700 focus:ring-blue-600/30' => ! $errors->has('email'),
                    'border-red-400 focus:border-red-500 focus:ring-red-500/30' => $errors->has('email'),
                ])
            >
            <x-input-error id="email-error" role="alert" :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Kata sandi -->
        <div>
            <div class="flex items-center justify-between gap-4">
                <x-input-label for="password" value="Kata Sandi" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="rounded text-sm font-medium text-blue-800 hover:text-blue-900 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
                        Lupa kata sandi?
                    </a>
                @endif
            </div>

            <div class="relative mt-1.5" x-data="{ show: false }">
                <input
                    id="password"
                    type="password"
                    :type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                    @class([
                        'block w-full rounded-lg py-2.5 pl-3 pr-11 text-sm text-slate-900 shadow-sm focus:outline-none focus:ring-2',
                        'border-slate-300 focus:border-blue-700 focus:ring-blue-600/30' => ! $errors->has('password'),
                        'border-red-400 focus:border-red-500 focus:ring-red-500/30' => $errors->has('password'),
                    ])
                >
                <button
                    type="button"
                    class="absolute inset-y-0 right-0 flex items-center rounded-r-lg px-3 text-slate-400 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600"
                    aria-controls="password"
                    aria-label="Tampilkan kata sandi"
                    :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    :aria-pressed="show.toString()"
                    @click="show = ! show"
                >
                    <x-icon name="eye" class="h-5 w-5" x-show="! show" />
                    <x-icon name="eye-slash" class="h-5 w-5" x-cloak x-show="show" />
                </button>
            </div>
            <x-input-error id="password-error" role="alert" :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Ingat saya -->
        <label for="remember_me" class="inline-flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-800 shadow-sm focus:ring-2 focus:ring-blue-600 focus:ring-offset-1">
            <span class="text-sm text-slate-600">Ingat saya</span>
        </label>

        <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2">
            Masuk
        </button>
    </form>

    <x-slot:footer>
        <div class="flex flex-col items-center gap-3 text-center text-sm text-slate-600">
            <p>Belum memiliki akun? Hubungi Admin perpustakaan.</p>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 rounded font-medium text-blue-800 hover:text-blue-900 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Kembali ke beranda
            </a>
        </div>
    </x-slot:footer>
</x-guest-layout>
