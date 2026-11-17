<?php

namespace App\Http\Controllers;

use App\Models\FasilitasUmum;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFasilitasUmumController extends Controller
{
    public function index(Request $request): View
    {
        $query = FasilitasUmum::latest();

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', $kategori);
        }

        $fasilitas = $query->paginate(15)->withQueryString();
        $totalFasilitas = FasilitasUmum::count();
        
        $kategoriOptions = FasilitasUmum::query()
            ->distinct('kategori')
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('admin.fasilitas.index', compact(
            'fasilitas', 'totalFasilitas', 'kategoriOptions'
        ));
    }

    public function create(): View
    {
        return view('admin.fasilitas.create');
    }

    public function store(Request $request)
    {
        $this->normalizeCoordinates($request);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:180'],
            'kategori' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'nama.required' => 'Nama fasilitas wajib diisi',
            'kategori.required' => 'Kategori wajib diisi',
            'latitude.required' => 'Latitude wajib diisi',
            'longitude.required' => 'Longitude wajib diisi',
        ]);

        $validated['source_no'] = ((int) FasilitasUmum::max('source_no')) + 1;
        $validated['is_manual'] = true;
        FasilitasUmum::create($validated);

        return redirect()->route('admin.fasilitas.index')
            ->with('success', 'Fasilitas umum berhasil ditambahkan');
    }

    public function edit(FasilitasUmum $fasilitas): View
    {
        return view('admin.fasilitas.edit', compact('fasilitas'));
    }

    public function update(Request $request, FasilitasUmum $fasilitas)
    {
        $this->normalizeCoordinates($request);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:180'],
            'kategori' => ['required', 'string', 'max:100'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $fasilitas->update($validated);

        return redirect()->route('admin.fasilitas.index')
            ->with('success', 'Fasilitas umum berhasil diperbarui');
    }

    public function destroy(FasilitasUmum $fasilitas)
    {
        $fasilitas->delete();

        return redirect()->route('admin.fasilitas.index')
            ->with('success', 'Fasilitas umum berhasil dihapus');
    }

    private function normalizeCoordinates(Request $request): void
    {
        foreach (['latitude', 'longitude'] as $field) {
            $value = $request->input($field);

            if ($value !== null && is_numeric($value) && abs((float) $value) > 180) {
                $request->merge([$field => (float) $value / 10000000]);
            }
        }
    }
}
