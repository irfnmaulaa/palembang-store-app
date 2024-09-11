<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = collect([
            ['No', 'ID', 'Nama Pengguna', 'Hak Akses']
        ]);

        return $data->merge(User::all()->map(function ($user, $i) {
            return [ $i + 1, $user->name, $user->username, $user->role_label ];
        }));
    }
}
