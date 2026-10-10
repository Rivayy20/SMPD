<x-guest-layout>
    <x-slot:title>Lupa Kata Sandi</x-slot:title>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Lupa Kata Sandi</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3" :status="session('status')" role="status" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>

    <x-slot:footer>
        <div class="text-center text-sm">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded font-medium text-blue-800 hover:text-blue-900 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Kembali ke halaman masuk
            </a>
        </div>
    </x-slot:footer>
</x-guest-layout>
