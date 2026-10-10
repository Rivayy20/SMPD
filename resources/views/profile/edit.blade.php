@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <x-page-header
        title="Profil Saya"
        description="Kelola informasi akun, kata sandi, dan keamanan akun Anda."
        :breadcrumbs="['Dashboard' => route('dashboard'), 'Profil Saya' => null]"
    />

    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
