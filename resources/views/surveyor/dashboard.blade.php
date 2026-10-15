<x-layouts.app title="Surveyor Field Workspace">

    <div class="pagehead">
        <div>
            <div class="kicker">
                WORKSPACE SURVEYOR LAPANGAN
            </div>
            <h1>Field Workspace</h1>
            <p class="muted">
                Pendataan terstandar RTLH, input indikator fisik bangunan, titik GPS, dan pengiriman verifikasi ke Dinas.
            </p>
        </div>

        <div class="actions">
            <a class="btn primary large" href="{{ route('surveyor.rtlh.create') }}">
                <i class="fa-solid fa-plus"></i> Tambah Data RTLH Baru
            </a>
        </div>
    </div>

    {{-- ALERT REVISI JIKA ADA --}}
    @if($revision > 0)
        <div class="alert danger" style="border-left: 4px solid #e11d48;">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
            <div>
                <strong>Perhatian!</strong> Ada <strong>{{ $revision }}</strong> data rumah yang perlu perbaikan/revisi sesuai catatan Admin verifikator.
                <a href="{{ route('surveyor.rtlh.index', ['status' => 'revision']) }}" style="color:#991b1b; font-weight:700; text-decoration:underline; margin-left:6px;">
                    Lihat Data Revisi →
                </a>
            </div>
        </div>
    @endif

    {{-- STATS WIDGETS --}}
    <div class="grid3">
        <div class="stat-widget">
            <div class="stat-widget-icon blue">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div class="stat-label">Total Data Saya</div>
            <div class="stat">{{ number_format($mine) }}</div>
            <div class="stat-desc">Keseluruhan input saya</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon" style="background:var(--slate-100); color:var(--slate-600);">
                <i class="fa-solid fa-file-pen"></i>
            </div>
            <div class="stat-label">Draft Disimpan</div>
            <div class="stat">{{ number_format($draft) }}</div>
            <div class="stat-desc">Belum dikirim ke review</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon amber">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="stat-label">Menunggu Review</div>
            <div class="stat">{{ number_format($submitted) }}</div>
            <div class="stat-desc">Sedang ditinjau Admin</div>
        </div>
    </div>

    <div class="grid3" style="margin-top:20px;">
        <a href="{{ route('surveyor.rtlh.index', ['status' => 'revision']) }}" style="text-decoration:none; color:inherit; display:block;">
            <div class="stat-widget" style="{{ $revision > 0 ? 'border-color:#fecaca; background:#fff1f2;' : '' }}">
                <div class="stat-widget-icon rose">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div class="stat-label" style="{{ $revision > 0 ? 'color:#be123c;' : '' }}">Perlu Revisi</div>
                <div class="stat" style="{{ $revision > 0 ? 'color:#be123c;' : '' }}">{{ number_format($revision) }}</div>
                <div class="stat-desc">Perbaikan indikator/foto</div>
            </div>
        </a>

        <div class="stat-widget">
            <div class="stat-widget-icon purple">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-label">Verified</div>
            <div class="stat" style="color:#7c3aed;">{{ number_format($verified) }}</div>
            <div class="stat-desc">Telah disetujui verifikator</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon emerald">
                <i class="fa-solid fa-globe"></i>
            </div>
            <div class="stat-label">Published</div>
            <div class="stat" style="color:#059669;">{{ number_format($published) }}</div>
            <div class="stat-desc">Tampil di portal publik</div>
        </div>
    </div>

    {{-- SURVEYOR TIPS & ACTIONS --}}
    <div class="grid2" style="margin-top: 24px;">
        <div class="card">
            <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-900); margin-bottom: 12px;">
                <i class="fa-solid fa-location-arrow text-emerald-600" style="color:#059669;"></i> Akses Cepat Pendataan
            </h3>
            <p class="muted" style="font-size: 13px; line-height: 1.6; margin-bottom: 20px;">
                Pastikan data yang diinput telah mencakup koordinat latitude/longitude akurat serta informasi struktur dan sanitasi yang lengkap.
            </p>

            <div class="actions">
                <a class="btn primary" href="{{ route('surveyor.rtlh.create') }}">
                    <i class="fa-solid fa-circle-plus"></i> Input RTLH Baru
                </a>
                <a class="btn" href="{{ route('surveyor.rtlh.index') }}">
                    <i class="fa-solid fa-list"></i> Lihat Daftar Data Saya
                </a>
            </div>
        </div>

        <div class="card" style="background: var(--slate-50);">
            <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-900); margin-bottom: 12px;">
                <i class="fa-solid fa-circle-info text-sky-600" style="color:#0284c7;"></i> Panduan Lapangan (SOP)
            </h3>
            <ul style="padding-left: 18px; font-size: 13px; color: var(--slate-700); line-height: 1.8;">
                <li>Aktifkan GPS perangkat dengan mode akurasi tinggi saat berada di depan rumah.</li>
                <li>Periksa kondisi fisik struktur (pondasi sloof, kolom penopang, balok, rangka atap).</li>
                <li>Catat ketersediaan jamban keluarga dan jenis pembuangan tinja (septic tank).</li>
                <li>Lakukan pengecekan kembali sebelum menekan tombol <em>Kirim Review</em>.</li>
            </ul>
        </div>
    </div>

</x-layouts.app>