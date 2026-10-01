<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('obe.nama_sistem', 'Sistem Informasi Kurikulum OBE') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-unism-primary to-blue-800 min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg,#1a56db,#1e429f)">
    <div class="w-full max-w-md px-6">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-blue-700 font-bold text-2xl">{{ strtoupper(substr(config('obe.universitas_singkat', 'U'), 0, 1)) }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Sistem Informasi OBE</h1>
                <p class="text-gray-500 text-sm mt-1">{{ config('obe.universitas', 'Sistem Kurikulum Pendidikan Tinggi') }}</p>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                @foreach($errors->all() as $err)
                <p class="text-red-600 text-sm">{{ $err }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="nama@email.ac.id">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="••••••••">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded">
                        <span>Ingat saya</span>
                    </label>
                </div>
                <button type="submit" class="w-full py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition text-sm">
                    Masuk
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:underline">← Kembali ke Beranda</a>
            </div>

        </div>
    </div>
</body>
</html>