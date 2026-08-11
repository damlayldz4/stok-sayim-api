<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Stok Sayım Sistemi')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-60 shrink-0 bg-slate-900 text-slate-200 flex flex-col">
            <div class="px-5 py-4 text-lg font-semibold border-b border-slate-800">
                Stok Sayım
            </div>

            <nav class="flex-1 px-2 py-4 space-y-1 text-sm">
                <a href="{{ route('dashboard') }}"
                   class="block rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : '' }}">
                    Dashboard
                </a>

                {{-- Aşağıdaki ekranlar henüz yapılmadı, bir sonraki katmanlarda
                     buraya gerçek route'larla eklenecek: --}}
                @if (auth()->user()->role === \App\Enums\UserRole::CompanyAdmin)
                    <a href="{{ route('branches.index') }}"
                       class="block rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('branches.*', 'warehouses.*', 'shelves.*') ? 'bg-slate-800 text-white' : '' }}">
                        Şubeler
                    </a>
                    <a href="{{ route('users.index') }}"
                       class="block rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white' : '' }}">
                        Kullanıcılar
                    </a>
                    <a href="{{ route('products.index') }}"
                       class="block rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('products.*') ? 'bg-slate-800 text-white' : '' }}">
                        Ürünler
                    </a>
                    <a href="{{ route('imports.index') }}"
                       class="block rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('imports.*') ? 'bg-slate-800 text-white' : '' }}">
                        CSV Yükleme
                    </a>
                    <a href="{{ route('stock-counts.index') }}"
                       class="block rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('stock-counts.*') && ! request()->routeIs('stock-counts.report*') ? 'bg-slate-800 text-white' : '' }}">
                        Sayımlar
                    </a>
                    <span class="block px-3 py-2 text-slate-500 cursor-not-allowed" title="Bir sayımın 'Rapor' butonundan erişilir">Raporlar</span>
                    <a href="{{ route('settings.index') }}"
                       class="block rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('settings.*') ? 'bg-slate-800 text-white' : '' }}">
                        Ayarlar
                    </a>
                @elseif (auth()->user()->role === \App\Enums\UserRole::SystemAdmin)
                    <a href="{{ route('companies.index') }}"
                       class="block rounded px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('companies.*') ? 'bg-slate-800 text-white' : '' }}">
                        Şirketler
                    </a>
                @endif
            </nav>

            <div class="px-3 py-4 border-t border-slate-800 text-sm">
                <div class="px-2 pb-2 text-slate-400 truncate">{{ auth()->user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left rounded px-3 py-2 hover:bg-slate-800">
                        Çıkış Yap
                    </button>
                </form>
            </div>
        </aside>

        {{-- İçerik --}}
        <main class="flex-1">
            <header class="bg-white border-b px-6 py-4">
                <h1 class="text-xl font-semibold">@yield('page_title', 'Dashboard')</h1>
            </header>

            <div class="p-6">
                @if (session('status'))
                    <div class="mb-4 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-3 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>