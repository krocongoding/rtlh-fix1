<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class HouseRealizationController extends Controller
{
    /**
     * Memperbarui status dan data realisasi RTLH.
     * Dapat diakses oleh Admin maupun Surveyor.
     */
    public function update(Request $request, House $house)
    {
        $validated = $request->validate([
            'realization_status' => ['required', 'string', 'in:realized,not_realized'],
            'funding_source' => ['nullable', 'string', 'max:100'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'realized_at' => ['nullable', 'date'],
            'realization_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'realization_status.required' => 'Status realisasi wajib dipilih.',
            'realization_status.in' => 'Status realisasi harus Sudah Terealisasi atau Belum Terealisasi.',
            'budget_amount.numeric' => 'Nominal anggaran harus berupa angka yang valid.',
            'budget_amount.min' => 'Nominal anggaran tidak boleh bernilai negatif.',
        ]);

        $old = $house->toArray();

        $updateData = [
            'realization_status' => $validated['realization_status'],
            'funding_source' => $validated['funding_source'] ?? $house->funding_source,
            'budget_amount' => (array_key_exists('budget_amount', $validated) && $validated['budget_amount'] !== null && $validated['budget_amount'] !== '')
                ? $validated['budget_amount']
                : $house->budget_amount,
            'realization_notes' => $validated['realization_notes'] ?? null,
            'updated_by' => auth()->id(),
        ];

        if ($validated['realization_status'] === 'realized') {
            $updateData['realized_at'] = !empty($validated['realized_at'])
                ? $validated['realized_at']
                : ($house->realized_at ?: now());
            $updateData['realized_by'] = auth()->id();
        } else {
            $updateData['realized_at'] = null;
            $updateData['realized_by'] = null;
        }

        $house->update($updateData);

        AuditLogger::log(
            $request->user(),
            'REALIZATION_UPDATE',
            'House',
            $house->id,
            $old,
            $house->fresh()->toArray()
        );

        $statusLabel = $validated['realization_status'] === 'realized'
            ? 'Sudah Terealisasi'
            : 'Belum Terealisasi';

        return back()->with(
            'ok',
            "Status realisasi rumah ({$house->house_code}) berhasil diperbarui menjadi {$statusLabel}."
        );
    }
}
