<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $query = Region::kotaCirebon()
            ->whereIn('type', ['village', 'desa'])
            ->withCount([
                'houses as total_houses',
                'houses as realized_count' => function ($q) {
                    $q->where('realization_status', 'realized');
                },
                'houses as not_realized_count' => function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('realization_status', '!=', 'realized')
                            ->orWhereNull('realization_status');
                    });
                },
            ])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where('name', 'ilike', "%{$search}%");
        }

        $regions = $query->paginate(20)->withQueryString();

        return view('regions.index', compact('regions'));
    }

    public function create()
    {
        $parents = Region::kotaCirebon()
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('regions.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:regions,id'],
            'code' => ['required', 'string', 'max:255', 'unique:regions,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:30'],
            'level' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Region::create($validated);

        return redirect()
            ->route('regions.index')
            ->with('success', 'Wilayah berhasil ditambahkan.');
    }
}