<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Admin - GIS Perumahan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-cover bg-center bg-fixed font-sans text-[#050505]" style="background-image: linear-gradient(rgba(3, 24, 18, .58), rgba(3, 24, 18, .72)), url('{{ asset('bgg.jpg') }}?v=2');">
    <div class="flex min-h-screen items-center justify-center px-4 py-8">
        <section class="w-full max-w-md">
            <div class="w-full rounded-[2rem] border border-slate-200 bg-white/95 p-8 shadow-[0_24px_80px_rgba(15,23,42,.22)] backdrop-blur-sm">
                <p class="text-xs font-black uppercase tracking-[.3em] text-blue-600">Admin GIS Perumahan</p>
                <h1 class="mt-3 text-3xl font-black tracking-tight">Daftar Akun Admin</h1>
                <p class="mt-2 text-sm text-slate-500">Akun yang dibuat melalui halaman ini memiliki akses admin.</p>
                @if ($errors->any())<div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('admin.register.submit') }}" class="mt-7 space-y-5">
                    @csrf
                    <label class="block"><span class="text-sm font-black">Nama</span><input name="name" type="text" required value="{{ old('name') }}" autocomplete="name" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600"></label>
                    <label class="block"><span class="text-sm font-black">Email</span><input name="email" type="email" required value="{{ old('email') }}" autocomplete="email" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600"></label>
                    <label class="block"><span class="text-sm font-black">Password</span><div class="relative mt-2"><input id="register-password" name="password" type="password" required autocomplete="new-password" class="w-full rounded-2xl border border-slate-200 px-4 py-3 pr-12 text-sm focus:border-blue-600 focus:ring-blue-600"><button type="button" data-password-target="register-password" class="password-toggle absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 hover:text-blue-600" aria-label="Tampilkan password" title="Tampilkan password"><svg class="password-hidden-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 2l20 20"/><path d="M6.71 6.71C4.94 7.84 3.56 9.51 2.5 12c1.73 4 5.54 7 9.5 7 1.16 0 2.27-.22 3.29-.63"/><path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path d="M9.88 4.24A9.77 9.77 0 0 1 12 4c3.96 0 7.77 3 9.5 8a16.2 16.2 0 0 1-3.03 4.44"/></svg><svg class="password-visible-icon hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button></div></label>
                    <label class="block"><span class="text-sm font-black">Konfirmasi Password</span><div class="relative mt-2"><input id="register-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="w-full rounded-2xl border border-slate-200 px-4 py-3 pr-12 text-sm focus:border-blue-600 focus:ring-blue-600"><button type="button" data-password-target="register-password-confirmation" class="password-toggle absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 hover:text-blue-600" aria-label="Tampilkan password" title="Tampilkan password"><svg class="password-hidden-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 2l20 20"/><path d="M6.71 6.71C4.94 7.84 3.56 9.51 2.5 12c1.73 4 5.54 7 9.5 7 1.16 0 2.27-.22 3.29-.63"/><path d="M10.58 10.58a2 2 0 0 0 2.83 2.83"/><path d="M9.88 4.24A9.77 9.77 0 0 1 12 4c3.96 0 7.77 3 9.5 8a16.2 16.2 0 0 1-3.03 4.44"/></svg><svg class="password-visible-icon hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg></button></div></label>
                    <button type="submit" class="w-full rounded-full bg-blue-600 px-5 py-3 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-blue-700">Daftar Admin</button>
                </form>
                <a href="{{ route('admin.login') }}" class="mt-5 block text-center text-sm font-bold text-slate-500 hover:text-blue-600">Kembali ke login admin</a>
            </div>
        </section>
    </div>
    <script>
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordTarget);
                const hiddenIcon = button.querySelector('.password-hidden-icon');
                const visibleIcon = button.querySelector('.password-visible-icon');
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                button.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                button.setAttribute('title', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                hiddenIcon.classList.toggle('hidden', isHidden);
                visibleIcon.classList.toggle('hidden', !isHidden);
            });
        });
    </script>
</body>
</html>
