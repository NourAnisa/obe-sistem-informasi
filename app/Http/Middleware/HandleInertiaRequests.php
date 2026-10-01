<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Root template yang dipakai untuk render halaman Inertia.
     * Filament (/admin) TIDAK pakai template ini — diisolasi via route.
     */
    protected $rootView = 'app';

    /**
     * Versi aset — digunakan Inertia untuk cache busting.
     * Otomatis berubah ketika assets di-rebuild.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Shared data — tersedia di SEMUA halaman Vue via usePage().props
     *
     * Akses di Vue:
     *   import { usePage } from '@inertiajs/vue3'
     *   const { auth, flash } = usePage().props
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'     => $request->user()->id,
                    'name'   => $request->user()->name,
                    'email'  => $request->user()->email,
                    'role'   => $request->user()->role,
                    'avatar' => $request->user()->avatar ?? null,
                ] : null,
                'role' => $request->user()?->role,
            ],
            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
                'warning' => session('warning'),
                'info'    => session('info'),
            ],
            'app' => [
                'name' => config('app.name', 'OBE UNISM'),
                'ta'   => config('obe.tahun_akademik', '2025/2026'),
            ],
        ]);
    }
}
