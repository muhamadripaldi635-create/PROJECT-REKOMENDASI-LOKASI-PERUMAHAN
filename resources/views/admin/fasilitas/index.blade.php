@extends('layouts.admin')
@section('title', 'Fasilitas Umum')
@section('page_title', 'Fasilitas Umum')
@section('content')
<div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
    <form method="GET" class="grid w-full gap-3 md:max-w-3xl md:grid-cols-[minmax(0,1fr)_220px_auto]">
        <input name="q" value="{{ request('q') }}" class="min-w-0 rounded-2xl border-line px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600" placeholder="Cari nama fasilitas atau kategori...">
        <select name="kategori" class="min-w-0 rounded-2xl border-line px-4 py-3 text-sm focus:border-blue-600 focus:ring-blue-600">
            <option value="">Semua Kategori</option>
            @foreach($kategoriOptions as $kat)
                <option value="{{ $kat }}" @selected(request('kategori') === $kat)>{{ $kat }}</option>
            @endforeach
        </select>
        <button class="rounded-2xl btn-blue px-5 py-3 text-sm font-black">Cari</button>
    </form>
    <a href="{{ route('admin.fasilitas.create') }}" class="rounded-2xl btn-blue px-6 py-3 text-sm font-black whitespace-nowrap">+ Tambah Fasilitas</a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-2xl border-2 border-green-200 bg-green-50 p-4 text-sm font-bold text-green-700">
        ✓ {{ session('success') }}
    </div>
@endif

<div class="grid gap-4">
    @forelse($fasilitas as $item)
        <article class="card rounded-[2rem] p-6">
            <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[.22em] text-blue-600">{{ $item->kategori }}</p>
                    <h2 class="mt-2 text-xl font-black">{{ $item->nama }}</h2>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-600">
                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                            <span class="font-semibold">📍 Koordinat:</span>
                            <code class="break-all rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs">{{ $item->latitude }}, {{ $item->longitude }}</code>
                        </div>
                    </div>
                    @if($item->source_no)
                        <p class="mt-2 text-xs text-slate-500">No. Sumber: {{ $item->source_no }}</p>
                    @endif
                <div class="flex flex-wrap gap-2 md:justify-end">
                    <a href="{{ route('admin.fasilitas.edit', $item) }}" class="rounded-2xl border-2 border-blue-600 px-4 py-2 text-sm font-bold text-blue-600 hover:bg-blue-50">
                        ✏️ Edit
                    </a>
                    <form action="{{ route('admin.fasilitas.destroy', $item) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-2xl border-2 border-red-500 px-4 py-2 text-sm font-bold text-red-500 hover:bg-red-50" onclick="return confirm('Yakin hapus fasilitas ini?')">
                            🗑️ Hapus
                        </button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="card rounded-[2rem] p-12 text-center font-bold text-slate-500">
            Belum ada data fasilitas umum.
        </div>
    @endforelse
</div>

<div class="mt-8">
    {{ $fasilitas->links() }}
</div>
@endsection
