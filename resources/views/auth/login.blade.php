<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş — Stok Sayım Sistemi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm bg-white rounded-lg shadow p-8">
        <h1 class="text-xl font-semibold mb-1">Stok Sayım Sistemi</h1>
        <p class="text-sm text-slate-500 mb-6">Devam etmek için giriş yap.</p>

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-50 text-red-700 border border-red-200 px-4 py-3 text-sm">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="username" class="block text-sm font-medium mb-1">Kullanıcı Adı</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-800">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-1">Şifre</label>
                <input type="password" id="password" name="password" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-800">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember">
                Beni hatırla
            </label>

            <button type="submit"
                    class="w-full bg-slate-900 text-white rounded px-4 py-2 text-sm font-medium hover:bg-slate-800">
                Giriş Yap
            </button>
        </form>
    </div>
</body>
</html>
