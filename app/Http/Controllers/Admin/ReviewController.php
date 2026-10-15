<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Daftar data yang perlu direview / dipublikasikan.
     */
    public function index()
    {
        $houses = House::with(['region', 'creator'])
            ->whereIn('status', [
                'submitted',
                'verified',
            ])
            ->latest('id')
            ->paginate(20);

        return view('admin.review', compact('houses'));
    }

    /**
     * Surveyor submission -> Verified.
     */
    public function verify(Request $request, House $house)
    {
        abort_unless(
            $house->status === 'submitted',
            422,
            'Data harus berstatus submitted sebelum diverifikasi.'
        );

        $old = $house->toArray();

        $house->update([
            'status' => 'verified',
            'is_public' => false,
            'updated_by' => auth()->id(),
        ]);

        /*
        | Assessment ikut verified kalau ada.
        */

        $assessment = $house->latestAssessment;

        if ($assessment) {
            $assessment->update([
                'status' => 'verified',
            ]);
        }

        AuditLogger::log(
            $request->user(),
            'VERIFY',
            'House',
            $house->id,
            $old,
            $house->fresh()->toArray()
        );

        return back()->with(
            'ok',
            'Data berhasil diverifikasi. Data belum dipublikasikan.'
        );
    }

    /**
     * Verified -> Published.
     */
    public function publish(Request $request, House $house)
    {
        abort_unless(
            $house->status === 'verified',
            422,
            'Data harus berstatus verified sebelum dipublikasikan.'
        );

        $old = $house->toArray();

        $house->update([
            'status' => 'published',
            'is_public' => true,
            'updated_by' => auth()->id(),
        ]);

        AuditLogger::log(
            $request->user(),
            'PUBLISH',
            'House',
            $house->id,
            $old,
            $house->fresh()->toArray()
        );

        return back()->with(
            'ok',
            'Data berhasil dipublikasikan.'
        );
    }

    /**
     * Submitted / Verified -> Revision.
     */
    public function revision(Request $request, House $house)
    {
        abort_unless(
            in_array(
                $house->status,
                ['submitted', 'verified'],
                true
            ),
            422,
            'Data tidak dapat dikembalikan ke revisi dari status sekarang.'
        );

        $validated = $request->validate([
            'revision_notes' => ['required', 'string', 'min:5', 'max:3000'],
        ], [
            'revision_notes.required' => 'Catatan revisi wajib diisi agar surveyor mengetahui poin yang perlu diperbaiki.',
            'revision_notes.min' => 'Catatan revisi minimal 5 karakter.',
            'revision_notes.max' => 'Catatan revisi maksimal 3000 karakter.',
        ]);

        $old = $house->toArray();

        $house->update([
            'status' => 'revision',
            'is_public' => false,
            'revision_notes' => $validated['revision_notes'],
            'revised_at' => now(),
            'revised_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        AuditLogger::log(
            $request->user(),
            'REVISION',
            'House',
            $house->id,
            $old,
            $house->fresh()->toArray()
        );

        return back()->with(
            'ok',
            'Data berhasil dikembalikan ke Surveyor disertai catatan perbaikan.'
        );
    }
}