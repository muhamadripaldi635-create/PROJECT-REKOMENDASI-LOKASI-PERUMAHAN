<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - GIS Perumahan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-cover bg-center bg-fixed font-sans text-[#050505]" style="background-image: linear-gradient(rgba(3, 24, 18, .58), rgba(3, 24, 18, .72)), url('{{ asset('bgg.jpg') }}?v=2');">
    <div class="flex min-h-screen items-center justify-center px-4 py-8">
        <section class="w-full max-w-md">
            <div class="w-full rounded-[2rem] border border-slate-200 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,23,42,.22)] backdrop-blur-sm">
                <p class="text-xs font-black uppercase tracking-[.3em] text-blue-600">Login Admin</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight">Masuk Sistem</h2>
                @if ($errors->any())<div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-7 space-y-5">
                    @csrf
                    <label class="block"><span class="text-sm font-black">Email</span><input name="email" type="email" required value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600"></label>
                    <label class="block"><span class="text-sm font-black">Password</span><div class="relative mt-2"><input id="login-password" name="password" type="password" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 pr-12 text-sm focus:border-blue-600 focus:ring-blue-600"><button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 hover:text-blue-600" aria-label="Tampilkan password" title="Tampilkan password"><svg id="password-hidden-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 2l20 20"/><path d="M6.71 6.71C4.94 7.84 3.56 9.51 2.5 12c1.73 4 5.54 7 9.5 7 1.16 0 2.27-.22 3.29-.63"/><path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path d="M9.88 4.24A9.77 9.77 0 0 1 12 4c3.96 0 7.77 3 9.5 8a16.2 16.2 0 0 1-3.03 4.44"/></svg><svg id="password-visible-icon" class="hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button></div></label>
                    <button class="w-full rounded-full bg-blue-600 px-5 py-3 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-blue-700">Masuk Admin</button>
                </form>
                @if (session('success'))<div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
                <a href="{{ route('home') }}" class="mt-5 block text-center text-sm font-bold text-slate-500 hover:text-blue-600">Kembali ke halaman publik</a>
                <a href="{{ route('admin.register') }}" class="mt-3 block text-center text-sm font-bold text-blue-600 hover:text-blue-700">Daftar akun admin</a>
            </div>
        </section>
    </div>
    <script>
        const passwordInput = document.getElementById('login-password');
        const togglePassword = document.getElementById('toggle-password');
        const hiddenIcon = document.getElementById('password-hidden-icon');
        const visibleIcon = document.getElementById('password-visible-icon');
        togglePassword.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            togglePassword.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            togglePassword.setAttribute('title', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            hiddenIcon.classList.toggle('hidden', isHidden);
            visibleIcon.classList.toggle('hidden', !isHidden);
        });
    </script>
</body>
</html>
