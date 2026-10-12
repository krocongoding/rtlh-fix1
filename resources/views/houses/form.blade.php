<x-layouts.app title="{{ $house->exists ? 'Edit Data RTLH' : 'Tambah Data RTLH' }}">

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            .form-section {
                background: #ffffff;
                border: 1px solid var(--slate-200);
                border-radius: var(--radius-xl);
                padding: 28px;
                margin-bottom: 24px;
                box-shadow: var(--shadow-sm);
                transition: all 0.2s ease;
            }
            .form-section:hover {
                border-color: var(--slate-300);
            }
            .form-section-header {
                display: flex;
                align-items: center;
                gap: 14px;
                margin-bottom: 22px;
                border-bottom: 1px solid var(--slate-100);
                padding-bottom: 14px;
            }
            .form-section-badge {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                font-weight: 800;
                flex-shrink: 0;
            }
            .form-section-badge.emerald { background: var(--primary-100); color: var(--primary-700); }
            .form-section-badge.blue { background: #e0f2fe; color: #0284c7; }
            .form-section-badge.amber { background: #fef3c7; color: #d97706; }
            .form-section-badge.purple { background: #ede9fe; color: #7c3aed; }
            .form-section-badge.rose { background: #ffe4e6; color: #e11d48; }

            .form-section-title {
                font-size: 18px;
                font-weight: 800;
                color: var(--slate-900);
                margin: 0;
            }
            .form-section-desc {
                font-size: 13px;
                color: var(--slate-500);
                margin-top: 2px;
            }

            .form-field {
                display: flex;
                flex-direction: column;
                margin-bottom: 16px;
            }
            .form-field label {
                font-size: 13px;
                font-weight: 700;
                color: var(--slate-700);
                margin-bottom: 6px;
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .form-field label span.req {
                color: #e11d48;
            }

            .photo-upload-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
            }
            @media(max-width: 800px) {
                .photo-upload-grid {
                    grid-template-columns: 1fr;
                }
            }

            .photo-dropzone {
                border: 2px dashed var(--slate-300);
                border-radius: var(--radius-lg);
                padding: 20px;
                text-align: center;
                background: var(--slate-50);
                cursor: pointer;
                transition: all 0.2s ease;
                position: relative;
            }
            .photo-dropzone:hover {
                border-color: var(--primary-500);
                background: var(--primary-50);
            }
            .photo-dropzone input[type="file"] {
                display: none;
            }
            .photo-preview {
                max-height: 120px;
                border-radius: 8px;
                object-fit: cover;
                margin-top: 10px;
                display: none;
                width: 100%;
            }

            .sticky-form-footer {
                position: sticky;
                bottom: 16px;
                background: rgba(15, 23, 42, 0.95);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-radius: var(--radius-xl);
                padding: 16px 24px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: var(--shadow-xl);
                z-index: 100;
                margin-top: 24px;
            }
        </style>
    @endpush

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-file-circle-plus"></i> {{ strtoupper($panel ?? 'FORMULIR') }}
            </div>
            <h1>{{ $house->exists ? 'Edit Data Rumah' : 'Tambah Data RTLH' }}</h1>
            <p class="muted">
                Lengkapi seluruh formulir fisik, koordinat geospasial, sanitasi, dan foto dokumentasi lapangan.
            </p>
        </div>

        <div class="actions">
            <a href="{{ $panel === 'surveyor' ? route('surveyor.rtlh.index') : route('houses.index') }}" class="btn">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert danger" style="border-left:4px solid #b91c1c;">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
            <div>
                <strong>Terdapat kesalahan input:</strong>
                <ul style="margin:6px 0 0 16px; font-size:13px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form class="form" enctype="multipart/form-data" method="POST" action="{{ $action }}">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        {{-- SECTION 1: IDENTITAS & LOKASI GEOSPASIAL --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-badge emerald">
                    1
                </div>
                <div>
                    <h3 class="form-section-title">Identitas Rumah & Lokasi Geospasial</h3>
                    <p class="form-section-desc">Kode unik, wilayah administratif, dimensi rumah, dan titik koordinat GPS lapangan.</p>
                </div>
            </div>

            <div class="grid3">
                <div class="form-field">
                    <label for="house_code">Kode RTLH (dibuat otomatis)</label>
                    <input
                        type="text"
                        id="house_code"
                        name="house_code"
                        value="{{ old('house_code', $house->house_code) }}"
                        placeholder="Pilih wilayah untuk melihat format kode"
                        readonly
                    >
                    <small class="muted" id="house-code-help">Kode dibuat oleh sistem berdasarkan desa/kelurahan dan nomor urut, sehingga tidak dapat sama.</small>
                </div>

                <div class="form-field">
                    <label for="survey_year">Tahun Survei <span class="req">*</span></label>
                    <input
                        type="number"
                        id="survey_year"
                        name="survey_year"
                        value="{{ old('survey_year', $house->survey_year ?: date('Y')) }}"
                        min="2000"
                        max="2100"
                        required
                    >
                </div>

                <div class="form-field">
                    <label for="region_id">Wilayah Administratif <span class="req">*</span></label>
                    <select id="region_id" name="region_id" required>
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($regions as $r)
                            <option value="{{ $r->id }}" data-region-name="{{ $r->name }}" @selected(old('region_id', $house->region_id) == $r->id)>
                                {{ $r->name }} ({{ ucfirst($r->type ?? 'Desa') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid4">
                <div class="form-field">
                    <label for="area_m2">Luas Rumah (m²)</label>
                    <input
                        type="number"
                        step="0.01"
                        id="area_m2"
                        name="area_m2"
                        value="{{ old('area_m2', $house->area_m2) }}"
                        placeholder="Contoh: 36.5"
                    >
                </div>

                <div class="form-field">
                    <label for="block">Dusun / Blok</label>
                    <input
                        type="text"
                        id="block"
                        name="block"
                        value="{{ old('block', $house->block) }}"
                        placeholder="Contoh: Blok Wage"
                    >
                </div>

                <div class="form-field">
                    <label for="rt">Nomor RT</label>
                    <input
                        type="text"
                        id="rt"
                        name="rt"
                        value="{{ old('rt', $house->rt) }}"
                        placeholder="001"
                    >
                </div>

                <div class="form-field">
                    <label for="rw">Nomor RW</label>
                    <input
                        type="text"
                        id="rw"
                        name="rw"
                        value="{{ old('rw', $house->rw) }}"
                        placeholder="002"
                    >
                </div>
            </div>

            <div class="grid2">
                <div class="form-field">
                    <label for="occupant_count">Jumlah Penghuni (Jiwa)</label>
                    <input
                        type="number"
                        min="0"
                        id="occupant_count"
                        name="occupant_count"
                        value="{{ old('occupant_count', $house->occupant_count) }}"
                        placeholder="Contoh: 4"
                    >
                </div>

                <div class="form-field">
                    <label for="household_count">Jumlah KK (Kepala Keluarga)</label>
                    <input
                        type="number"
                        min="0"
                        id="household_count"
                        name="household_count"
                        value="{{ old('household_count', $house->household_count ?: 1) }}"
                        placeholder="Contoh: 1"
                    >
                </div>
            </div>

            <div class="form-field">
                <label for="address">Alamat Lengkap Rumah</label>
                <textarea
                    id="address"
                    name="address"
                    rows="2"
                    placeholder="Tuliskan nama jalan, patokan, atau rincian lokasi..."
                >{{ old('address', $house->address) }}</textarea>
            </div>

            <div style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-lg); padding:20px; margin-top:8px;">
                <div style="margin-bottom:14px;">
                    <strong style="font-size:14px; color:var(--slate-800);"><i class="fa-solid fa-id-card" style="color:#0284c7;"></i> Informasi Administratif & Kepemilikan</strong>
                    <div class="muted" style="font-size:12px;">Isi sesuai kondisi saat survei. Data ini akan tampil sebagai ringkasan pada halaman viewer.</div>
                </div>
                <div class="grid2">
                    @foreach([
                        ['settlement_condition_id', 'Kondisi Kawasan Permukiman', 'Pilih apakah lingkungan tempat rumah berada kumuh atau tidak kumuh.', 'SETTLEMENT_CONDITION'],
                        ['room_function_id', 'Fungsi Bangunan / Ruang Utama', 'Pilih fungsi utama bangunan yang ditempati.', 'ROOM_FUNCTION'],
                        ['ownership_status_id', 'Status Penguasaan Rumah', 'Contoh: milik sendiri, sewa/kontrak, atau lainnya.', 'OWNERSHIP_STATUS'],
                        ['land_status_id', 'Status Penguasaan Tanah', 'Contoh: milik sendiri atau status lainnya.', 'LAND_STATUS'],
                    ] as [$field, $label, $help, $category])
                        <div class="form-field" style="margin-bottom:0;">
                            <label for="{{ $field }}">{{ $label }}</label>
                            <select id="{{ $field }}" name="{{ $field }}">
                                <option value="">-- Belum diisi --</option>
                                @foreach(($cats[$category]->values ?? []) as $value)
                                    <option value="{{ $value->id }}" @selected(old($field, $house->{$field}) == $value->id)>{{ $value->label }}</option>
                                @endforeach
                            </select>
                            <small class="muted" style="margin-top:5px;">{{ $help }}</small>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- TITIK KOORDINAT GPS --}}
            <div style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-lg); padding:20px; margin-top:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                    <div>
                        <strong style="font-size:14px; color:var(--slate-800);"><i class="fa-solid fa-location-dot text-emerald-600" style="color:#059669;"></i> Koordinat GPS Lapangan (WGS84)</strong>
                        <div class="muted" id="gps-status" style="font-size:12px;">Lokasi GPS akan diambil otomatis saat formulir dibuka. Pin peta tetap dapat digeser bila perlu.</div>
                    </div>
                    <button type="button" class="btn small" onclick="getCurrentLocation()" style="background:#fff;">
                        <i class="fa-solid fa-crosshairs text-emerald-600" style="color:#059669;"></i> Dapatkan GPS Saya Otomatis
                    </button>
                </div>

                <div class="grid2">
                    <div class="form-field" style="margin-bottom:0;">
                        <label for="latitude">Latitude</label>
                        <input
                            type="number"
                            step="any"
                            id="latitude"
                            name="latitude"
                            value="{{ old('latitude', $house->latitude) }}"
                            placeholder="-6.732..."
                            onchange="updateMapFromInputs()"
                        >
                    </div>

                    <div class="form-field" style="margin-bottom:0;">
                        <label for="longitude">Longitude</label>
                        <input
                            type="number"
                            step="any"
                            id="longitude"
                            name="longitude"
                            value="{{ old('longitude', $house->longitude) }}"
                            placeholder="108.552..."
                            onchange="updateMapFromInputs()"
                        >
                    </div>
                </div>

                {{-- INTERACTIVE MAP PICKER --}}
                <div style="margin-top:14px;">
                    <div style="font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">
                        <i class="fa-solid fa-map-pin"></i> Peta Lokasi (Klik atau geser pin penanda pada peta):
                    </div>
                    <div id="form-map" style="height:240px; width:100%; border-radius:8px; border:1px solid var(--slate-300); z-index:1;"></div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: KONDISI STRUKTUR --}}
        <div class="form-section">
            <div class="form-section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div class="form-section-badge blue">
                        2
                    </div>
                    <div>
                        <h3 class="form-section-title">Kondisi Struktur Utama Bangunan</h3>
                        <p class="form-section-desc">Pondasi dasar, sloof pengikat, kolom struktural, dan balok penopang.</p>
                    </div>
                </div>

                {{-- LIVE AUTO-SCORE BADGE --}}
                <div style="background:var(--slate-50); border:1px solid var(--slate-200); padding:6px 14px; border-radius:var(--radius-lg); display:flex; align-items:center; gap:12px;">
                    <div style="font-size:11px; font-weight:700; color:var(--slate-600); text-transform:uppercase;">
                        <i class="fa-solid fa-calculator" style="color:var(--primary-600);"></i> Skor Otomatis Survei:
                    </div>
                    <div style="font-size:18px; font-weight:900; color:var(--primary-700); font-family:monospace;" id="form-live-score">0</div>
                    <span id="form-live-priority" class="badge priority-low" style="font-size:11px;">Prioritas Rendah</span>
                </div>
            </div>

            <div class="grid2">
                <div class="form-field">
                    <label for="foundation">Jenis Pondasi</label>
                    @php
                        $currentFoundation = old('foundation', $house->structure?->foundation);
                        $defaultFoundations = [
                            'BATU KALI / BATU BELAH',
                            'BETON BERTULANG / TAPAK',
                            'BATA MERAH / ROLAG',
                            'KAYU / CERUCUK',
                            'BAMBU',
                            'TANPA PONDASI / MENUMPANG TANAH',
                            'LAINNYA',
                        ];
                        $foundationValues = isset($cats['FOUNDATION_TYPE']) && $cats['FOUNDATION_TYPE']->values->isNotEmpty()
                            ? $cats['FOUNDATION_TYPE']->values->pluck('label')->toArray()
                            : $defaultFoundations;
                    @endphp
                    <select id="foundation" name="foundation">
                        <option value="">-- Pilih Jenis Pondasi --</option>
                        @foreach($foundationValues as $opt)
                            <option value="{{ $opt }}" @selected(strtoupper(trim((string)$currentFoundation)) === strtoupper(trim($opt)))>
                                {{ $opt }}
                            </option>
                        @endforeach
                        @if($currentFoundation && !in_array(strtoupper(trim($currentFoundation)), array_map('strtoupper', array_map('trim', $foundationValues))))
                            <option value="{{ $currentFoundation }}" selected>
                                {{ $currentFoundation }}
                            </option>
                        @endif
                    </select>
                </div>

                @foreach([
                    ['sloof_condition_id', 'Kondisi Sloof / Balok Dasar', 'structure'],
                    ['column_condition_id', 'Kondisi Kolom / Tiang Penopang', 'structure'],
                    ['beam_condition_id', 'Kondisi Balok Pengikat', 'structure']
                ] as [$field, $label, $rel])
                    <div class="form-field">
                        <label for="{{ $field }}">{{ $label }}</label>
                        <select id="{{ $field }}" name="{{ $field }}" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old($field, $house->{$rel}?->{$field}) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SECTION 3: KOMPONEN ARSITEKTUR --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-badge purple">
                    3
                </div>
                <div>
                    <h3 class="form-section-title">Komponen Arsitektur (Lantai, Dinding, Plafon, Atap)</h3>
                    <p class="form-section-desc">Material dan tingkat kerusakan komponen penutup dan pembatas bangunan.</p>
                </div>
            </div>

            <div class="grid2">
                {{-- LANTAI --}}
                <div class="form-field">
                    <label for="floor_material_id">Bahan Material Lantai</label>
                    <select id="floor_material_id" name="floor_material_id">
                        <option value="">-- Pilih Material --</option>
                        @foreach(($cats['FLOOR_MATERIAL']->values ?? []) as $v)
                            <option value="{{ $v->id }}" @selected(old('floor_material_id', $house->floor?->material_id) == $v->id)>
                                {{ $v->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="floor_condition_id">Kondisi Lantai</label>
                    <select id="floor_condition_id" name="floor_condition_id" class="score-calc-select">
                        <option value="" data-score="0">-- Pilih Kondisi --</option>
                        @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                            @php
                                $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                            @endphp
                            <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('floor_condition_id', $house->floor?->condition_id) == $v->id)>
                                {{ $v->label }} (Skor: {{ $sc }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- DINDING --}}
                <div class="form-field">
                    <label for="wall_material_id">Bahan Material Dinding</label>
                    <select id="wall_material_id" name="wall_material_id">
                        <option value="">-- Pilih Material --</option>
                        @foreach(($cats['WALL_MATERIAL']->values ?? []) as $v)
                            <option value="{{ $v->id }}" @selected(old('wall_material_id', $house->wall?->material_id) == $v->id)>
                                {{ $v->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="wall_condition_id">Kondisi Dinding</label>
                    <select id="wall_condition_id" name="wall_condition_id" class="score-calc-select">
                        <option value="" data-score="0">-- Pilih Kondisi --</option>
                        @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                            @php
                                $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                            @endphp
                            <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('wall_condition_id', $house->wall?->condition_id) == $v->id)>
                                {{ $v->label }} (Skor: {{ $sc }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PLAFON --}}
                <div class="form-field">
                    <label for="ceiling_condition_id">Kondisi Plafon / Langit-langit</label>
                    <select id="ceiling_condition_id" name="ceiling_condition_id" class="score-calc-select">
                        <option value="" data-score="0">-- Pilih Kondisi --</option>
                        @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                            @php
                                $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                            @endphp
                            <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('ceiling_condition_id', $house->ceiling?->condition_id) == $v->id)>
                                {{ $v->label }} (Skor: {{ $sc }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- RANGKA ATAP --}}
                <div class="form-field">
                    <label for="roof_frame_condition_id">Kondisi Rangka / Kuda-kuda Atap</label>
                    <select id="roof_frame_condition_id" name="roof_frame_condition_id" class="score-calc-select">
                        <option value="" data-score="0">-- Pilih Kondisi --</option>
                        @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                            @php
                                $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                            @endphp
                            <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('roof_frame_condition_id', $house->roof?->frame_condition_id) == $v->id)>
                                {{ $v->label }} (Skor: {{ $sc }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PENUTUP ATAP --}}
                <div class="form-field">
                    <label for="roof_material_id">Bahan Penutup Atap</label>
                    <select id="roof_material_id" name="roof_material_id">
                        <option value="">-- Pilih Material --</option>
                        @foreach(($cats['ROOF_MATERIAL']->values ?? []) as $v)
                            <option value="{{ $v->id }}" @selected(old('roof_material_id', $house->roof?->material_id) == $v->id)>
                                {{ $v->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="roof_condition_id">Kondisi Penutup Atap</label>
                    <select id="roof_condition_id" name="roof_condition_id" class="score-calc-select">
                        <option value="" data-score="0">-- Pilih Kondisi --</option>
                        @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                            @php
                                $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                            @endphp
                            <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('roof_condition_id', $house->roof?->condition_id) == $v->id)>
                                {{ $v->label }} (Skor: {{ $sc }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- SECTION 4: SANITASI & UTILITAS --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-badge amber">
                    4
                </div>
                <div>
                    <h3 class="form-section-title">Pencahayaan, Ventilasi, Air & Sanitasi</h3>
                    <p class="form-section-desc">Ketersediaan air minum bersih, fasilitas jamban keluarga, jarak tinja, dan penerangan.</p>
                </div>
            </div>

            <div class="grid2">
                <div class="form-field">
                    <label for="light_opening">Bukaan Cahaya / Jendela</label>
                    <input
                        type="text"
                        id="light_opening"
                        name="light_opening"
                        value="{{ old('light_opening', $house->utility?->light_opening) }}"
                        placeholder="Contoh: Cukup / Kurang / Tidak Ada"
                    >
                </div>

                <div class="form-field">
                    <label for="ventilation">Ventilasi / Sirkulasi Udara</label>
                    <input
                        type="text"
                        id="ventilation"
                        name="ventilation"
                        value="{{ old('ventilation', $house->utility?->ventilation) }}"
                        placeholder="Contoh: Cukup / Kurang / Tanpa Ventilasi"
                    >
                </div>

                <div class="form-field">
                    <label for="water_source_id">Sumber Air Minum</label>
                    <select id="water_source_id" name="water_source_id">
                        <option value="">-- Pilih Sumber Air --</option>
                        @foreach(($cats['WATER_SOURCE']->values ?? []) as $v)
                            <option value="{{ $v->id }}" @selected(old('water_source_id', $house->sanitation?->water_source_id) == $v->id)>
                                {{ $v->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="toilet_available">Ketersediaan Jamban / Kamar Mandi</label>
                    <select id="toilet_available" name="toilet_available">
                        <option value="">-- Pilih Status --</option>
                        <option value="1" @selected(old('toilet_available', is_null($house->sanitation?->toilet_available) ? '' : (int)$house->sanitation?->toilet_available) === 1)>ADA / MEMILIKI SENDIRI</option>
                        <option value="0" @selected(old('toilet_available', is_null($house->sanitation?->toilet_available) ? '' : (int)$house->sanitation?->toilet_available) === 0)>TIDAK PUNYA / MENUMPANG</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="toilet_type_id">Jenis Jamban</label>
                    <select id="toilet_type_id" name="toilet_type_id">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach(($cats['TOILET_TYPE']->values ?? []) as $v)
                            <option value="{{ $v->id }}" @selected(old('toilet_type_id', $house->sanitation?->toilet_type_id) == $v->id)>
                                {{ $v->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="fecal_disposal_type_id">Tempat Pembuangan Akhir (TPA) Tinja</label>
                    <select id="fecal_disposal_type_id" name="fecal_disposal_type_id">
                        <option value="">-- Pilih TPA --</option>
                        @foreach(($cats['FECAL_DISPOSAL']->values ?? []) as $v)
                            <option value="{{ $v->id }}" @selected(old('fecal_disposal_type_id', $house->sanitation?->fecal_disposal_type_id) == $v->id)>
                                {{ $v->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="water_fecal_distance">Jarak Sumber Air ke TPA Tinja</label>
                    <input
                        type="text"
                        id="water_fecal_distance"
                        name="water_fecal_distance"
                        value="{{ old('water_fecal_distance', $house->sanitation?->water_fecal_distance) }}"
                        placeholder="Contoh: < 10 meter / > 10 meter"
                    >
                </div>

                <div class="form-field">
                    <label for="lighting_source_id">Sumber Penerangan / Daya Listrik</label>
                    <select id="lighting_source_id" name="lighting_source_id">
                        <option value="">-- Pilih Penerangan --</option>
                        @foreach(($cats['LIGHTING_SOURCE']->values ?? []) as $v)
                            <option value="{{ $v->id }}" @selected(old('lighting_source_id', $house->utility?->lighting_source_id) == $v->id)>
                                {{ $v->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- SECTION 5: DOKUMENTASI FOTO LAPANGAN --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-badge rose">
                    5
                </div>
                <div>
                    <h3 class="form-section-title">Dokumentasi Foto Fisik Rumah</h3>
                    <p class="form-section-desc">Unggah foto dokumentasi sudut bangunan dan ruangan yang jelas dari lapangan.</p>
                </div>
            </div>

            @php
                $typeMap = [
                    'photo_front' => 'front',
                    'photo_angle' => 'angle_45',
                    'photo_side' => 'side',
                    'photo_back' => 'back',
                    'photo_family_room' => 'family_room',
                    'photo_bathroom' => 'bathroom',
                ];
            @endphp

            <div class="photo-upload-grid">
                @foreach([
                    ['photo_front', 'Tampak Depan', 'fa-camera'],
                    ['photo_angle', 'Tampak Sudut 45°', 'fa-camera'],
                    ['photo_side', 'Tampak Samping', 'fa-camera'],
                    ['photo_back', 'Tampak Belakang', 'fa-camera'],
                    ['photo_family_room', 'Ruang Keluarga / Dalam', 'fa-couch'],
                    ['photo_bathroom', 'Kamar Mandi / Sanitasi', 'fa-bath']
                ] as [$field, $label, $icon])
                    @php
                        $photoType = $typeMap[$field] ?? '';
                        $existingPhoto = $house->photos ? $house->photos->where('type', $photoType)->first() : null;
                        $existingUrl = $existingPhoto ? asset('storage/' . $existingPhoto->path) : null;
                    @endphp

                    <div class="photo-dropzone" onclick="document.getElementById('{{ $field }}').click()">
                        <div style="font-size:24px; color:var(--slate-400); margin-bottom:6px;">
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <strong style="font-size:13px; color:var(--slate-800); display:block;">{{ $label }}</strong>
                        <span class="muted" style="font-size:11px;">
                            {{ $existingUrl ? 'Klik untuk mengganti foto' : 'Klik untuk memilih file' }}
                        </span>
                        <input
                            type="file"
                            id="{{ $field }}"
                            name="{{ $field }}"
                            accept="image/*"
                            onchange="previewImage(this, 'preview-{{ $field }}')"
                        >
                        <img
                            id="preview-{{ $field }}"
                            class="photo-preview"
                            src="{{ $existingUrl ?? '' }}"
                            alt="Preview {{ $label }}"
                            style="{{ $existingUrl ? 'display:block;' : 'display:none;' }}"
                        >
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SECTION 6: CATATAN LAPANGAN --}}
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-badge" style="background:#f1f5f9; color:var(--slate-700);">
                    6
                </div>
                <div>
                    <h3 class="form-section-title">Catatan Khusus & Rekomendasi Assessment</h3>
                    <p class="form-section-desc">Catatan tambahan dari petugas surveyor mengenai kondisi sosial ekonomi atau urgensi perbaikan.</p>
                </div>
            </div>

            <div class="form-field" style="margin-bottom:0;">
                <textarea
                    id="assessment_notes"
                    name="assessment_notes"
                    rows="4"
                    placeholder="Tuliskan catatan tambahan mengenai kondisi khusus hunian, anggota keluarga rentan (lansia/balita), atau kendala lokasi..."
                >{{ old('assessment_notes') }}</textarea>
            </div>
        </div>

        {{-- STICKY ACTION BUTTONS --}}
        <div class="sticky-form-footer">
            <div style="display:flex; align-items:center; gap:8px;">
                <a href="{{ $panel === 'surveyor' ? route('surveyor.rtlh.index') : route('houses.index') }}" class="btn small" style="background:transparent; color:#cbd5e1; border-color:rgba(255,255,255,0.2);">
                    <i class="fa-solid fa-xmark"></i> Batal
                </a>
            </div>

            <div class="actions" style="margin:0;">
                <button type="submit" class="btn" name="submit" value="0" style="background:#334155; color:#ffffff; border-color:#475569;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Draft
                </button>

                @if($panel === 'surveyor')
                    <button type="submit" class="btn primary" name="submit" value="1">
                        <i class="fa-solid fa-paper-plane"></i> Simpan & Kirim Review
                    </button>
                @else
                    <button type="submit" class="btn primary">
                        <i class="fa-solid fa-check"></i> Simpan Data Rumah
                    </button>
                @endif
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            let formMap, formMarker;

            document.addEventListener('DOMContentLoaded', function () {
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const regionInput = document.getElementById('region_id');
                const houseCodeInput = document.getElementById('house_code');

                let initLat = parseFloat(latInput.value) || -6.7320000;
                let initLng = parseFloat(lngInput.value) || 108.5520000;
                let zoomLevel = (latInput.value && lngInput.value) ? 16 : 12;

                formMap = L.map('form-map').setView([initLat, initLng], zoomLevel);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(formMap);

                formMarker = L.marker([initLat, initLng], { draggable: true }).addTo(formMap);

                // Update inputs when marker is dragged
                formMarker.on('dragend', function (e) {
                    const coord = e.target.getLatLng();
                    latInput.value = coord.lat.toFixed(7);
                    lngInput.value = coord.lng.toFixed(7);
                });

                // Update marker when map is clicked
                formMap.on('click', function (e) {
                    const coord = e.latlng;
                    formMarker.setLatLng(coord);
                    latInput.value = coord.lat.toFixed(7);
                    lngInput.value = coord.lng.toFixed(7);
                });

                regionInput.addEventListener('change', updateCodePreview);
                updateCodePreview();

                if (!latInput.value || !lngInput.value) {
                    getCurrentLocation(true);
                }

                // Kalkulasi Skor Otomatis Form Survey
                const scoreSelects = document.querySelectorAll('.score-calc-select');
                const scoreDisplay = document.getElementById('form-live-score');
                const priorityDisplay = document.getElementById('form-live-priority');

                function updateFormScore() {
                    let total = 0;
                    let hasStructuralSevere = false;

                    scoreSelects.forEach(select => {
                        const opt = select.options[select.selectedIndex];
                        const score = parseInt(opt?.dataset?.score || 0, 10);
                        total += score;

                        if (['sloof_condition_id', 'column_condition_id', 'beam_condition_id', 'roof_frame_condition_id'].includes(select.id)) {
                            if (score >= 3) {
                                hasStructuralSevere = true;
                            }
                        }
                    });

                    if (scoreDisplay) scoreDisplay.textContent = total;

                    if (priorityDisplay) {
                        if (total >= 12 || hasStructuralSevere) {
                            priorityDisplay.className = 'badge priority-high';
                            priorityDisplay.textContent = 'Prioritas Tinggi (Rusak Berat)';
                        } else if (total >= 6) {
                            priorityDisplay.className = 'badge priority-medium';
                            priorityDisplay.textContent = 'Prioritas Sedang (Rusak Sedang)';
                        } else {
                            priorityDisplay.className = 'badge priority-low';
                            priorityDisplay.textContent = 'Prioritas Rendah (Rusak Ringan)';
                        }
                    }
                }

                scoreSelects.forEach(select => {
                    select.addEventListener('change', updateFormScore);
                });

                updateFormScore();
            });

            function updateCodePreview() {
                const regionInput = document.getElementById('region_id');
                const houseCodeInput = document.getElementById('house_code');
                if ((!houseCodeInput.value && !regionInput.value) || (houseCodeInput.value && !houseCodeInput.value.endsWith('-XXXX'))) return;

                const regionName = regionInput.options[regionInput.selectedIndex]?.dataset.regionName || '';
                const prefix = regionName
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .toUpperCase().replace(/[^A-Z0-9]+/g, '-').replace(/^-|-$/g, '');
                houseCodeInput.value = prefix ? `RTLH-${prefix}-XXXX` : '';
            }

            function updateMapFromInputs() {
                const lat = parseFloat(document.getElementById('latitude').value);
                const lng = parseFloat(document.getElementById('longitude').value);

                if (!isNaN(lat) && !isNaN(lng) && formMap && formMarker) {
                    const newLatLng = L.latLng(lat, lng);
                    formMarker.setLatLng(newLatLng);
                    formMap.panTo(newLatLng);
                }
            }

            function getCurrentLocation(isAutomatic = false) {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung deteksi geolokasi.');
                    return;
                }

                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const gpsStatus = document.getElementById('gps-status');

                latInput.placeholder = 'Mengambil GPS...';
                lngInput.placeholder = 'Mengambil GPS...';
                gpsStatus.textContent = isAutomatic ? 'Mengambil lokasi GPS terkini...' : 'Memperbarui lokasi GPS terkini...';

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude.toFixed(7);
                        const lng = position.coords.longitude.toFixed(7);

                        latInput.value = lat;
                        lngInput.value = lng;

                        updateMapFromInputs();
                        const accuracy = Math.round(position.coords.accuracy);
                        gpsStatus.textContent = `Lokasi GPS terkini berhasil digunakan (akurasi sekitar ${accuracy} m).`;
                    },
                    function(error) {
                        gpsStatus.textContent = 'GPS belum tersedia. Izinkan akses lokasi atau tentukan titik pada peta.';
                        if (!isAutomatic) alert('Gagal mengambil titik koordinat GPS: ' + error.message);
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }

            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
    @endpush

</x-layouts.app>
