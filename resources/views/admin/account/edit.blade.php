@extends('layouts.admin')
@section('title', 'Akun Admin')
@section('page_title', 'Akun Admin')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="card rounded-[2rem] p-6 sm:p-8">
        <div class="mb-8 flex items-center gap-4">
            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-100 text-xl font-black text-blue-700">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-2xl font-black">Pengaturan Akun</h2>
                <p class="mt-1 text-sm text-slate-500">Perbarui identitas dan keamanan akun admin.</p>
            </div>
        </div>

        <form action="{{ route('admin.account.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="mb-2 block text-sm font-bold">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600 @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-bold">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600 @error('email') border-red-500 @enderror">
                @error('email')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="border-t border-line pt-6">
                <h3 class="font-black">Ganti Password</h3>
                <p class="mt-1 text-sm text-slate-500">Kosongkan bagian ini jika password tidak ingin diubah.</p>
            </div>

            <div>
                <label for="current_password" class="mb-2 block text-sm font-bold">Password Saat Ini</label>
                <input id="current_password" type="password" name="current_password" autocomplete="current-password" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600 @error('current_password') border-red-500 @enderror">
                @error('current_password')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="new_password" class="mb-2 block text-sm font-bold">Password Baru</label>
                    <input id="new_password" type="password" name="new_password" autocomplete="new-password" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600 @error('new_password') border-red-500 @enderror">
                    @error('new_password')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="new_password_confirmation" class="mb-2 block text-sm font-bold">Konfirmasi Password Baru</label>
                    <input id="new_password_confirmation" type="password" name="new_password_confirmation" autocomplete="new-password" class="w-full rounded-2xl border-line px-4 py-3 focus:border-blue-600 focus:ring-blue-600">
                </div>
            </div>

            <button type="submit" class="rounded-2xl btn-blue px-6 py-3 font-bold">Simpan Perubahan</button>
        </form>
        <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="rounded-2xl border border-red-200 bg-red-50 px-6 py-3 font-bold text-red-700 hover:bg-red-100">
                Keluar
            </button>
        </form>
    </section>
</div>
@endsection
