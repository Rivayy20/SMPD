<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Membaca config/navigation.php lalu menyaringnya berdasarkan role
 * dan menentukan URL serta status aktif setiap menu.
 */
class Navigation
{
    /**
     * Grup menu sidebar yang boleh dilihat oleh user.
     *
     * @return array<int, array{heading: string, items: array<int, array<string, mixed>>}>
     */
    public static function sidebar(?User $user): array
    {
        return collect(config('navigation.sidebar', []))
            ->map(function (array $group) use ($user) {
                $group['items'] = collect($group['items'] ?? [])
                    ->filter(fn (array $item) => static::canSee($item, $user))
                    ->map(fn (array $item) => static::resolve($item))
                    ->values()
                    ->all();

                return $group;
            })
            ->reject(fn (array $group) => $group['items'] === [])
            ->values()
            ->all();
    }

    /**
     * Menu modul perpustakaan (item dengan 'module' => true) untuk Akses Cepat.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function modules(?User $user): array
    {
        return collect(static::sidebar($user))
            ->flatMap(fn (array $group) => $group['items'])
            ->where('module', true)
            ->values()
            ->all();
    }

    protected static function canSee(array $item, ?User $user): bool
    {
        $roles = $item['roles'] ?? [];

        return $roles === [] || ($user && in_array($user->role, $roles, true));
    }

    protected static function resolve(array $item): array
    {
        $routeName = $item['route'] ?? null;
        $available = $routeName && Route::has($routeName);

        return array_merge($item, [
            'url' => $available ? route($routeName) : null,
            'active' => $available && request()->routeIs($item['active'] ?? $routeName),
        ]);
    }
}
