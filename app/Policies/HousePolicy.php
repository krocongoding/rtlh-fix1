<?php

namespace App\Policies;

use App\Models\House;
use App\Models\User;

class HousePolicy
{
    /**
     * Admin dan Surveyor boleh melihat daftar rumah internal.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'admin',
            'surveyor',
        ]);
    }

    /**
     * Admin dan Surveyor boleh melihat detail rumah internal.
     */
    public function view(User $user, House $house): bool
    {
        return $user->hasAnyRole([
            'admin',
            'surveyor',
        ]);
    }

    /**
     * Hanya Admin yang boleh membuat data rumah.
     */
    public function create(User $user): bool
{
    return $user->hasAnyRole([
        'admin',
        'surveyor',
    ]);
}

    /**
     * Admin boleh mengubah rumah.
     * Surveyor juga boleh mengubah data yang memang
     * menjadi bagian dari pekerjaan survey.
     */
    public function update(User $user, House $house): bool
    {
        return $user->hasAnyRole([
            'admin',
            'surveyor',
        ]);
    }

    public function destroy(House $house)
{
    $house->delete();

    return redirect()
        ->route('houses.index')
        ->with('success', 'Data RTLH berhasil dihapus.');
}

    /**
     * Hanya Admin yang boleh menghapus data rumah.
     */
    public function delete(User $user, House $house): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Belum menggunakan restore.
     */
    public function restore(User $user, House $house): bool
    {
        return false;
    }

    /**
     * Belum menggunakan permanent delete.
     */
    public function forceDelete(User $user, House $house): bool
    {
        return false;
    }
}