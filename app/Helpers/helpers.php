<?php

use App\Models\Setting;

if (!function_exists('get_menus')) {
    function get_menus() {
        $menus = Setting::where('key', 'menus')->first();
        if ($menus && json_decode($menus->value) && json_last_error() === JSON_ERROR_NONE) {
            $menus = json_decode($menus->value);
        } else {
            $menus = [
                (object) [
                    'label' => 'Transaksi',
                    'link' => 'admin.transactions.index',
                    'allowed_roles' => ['super', 'admin', 'staff']
                ],
                (object) [
                    'label' => 'Riwayat',
                    'link' => 'admin.histories.index',
                    'allowed_roles' => ['super', 'admin', 'staff']
                ],
                (object) [
                    'label' => 'Barang',
                    'link' => 'admin.products.index',
                    'allowed_roles' => ['super', 'admin', 'staff']
                ],
                (object) [
                    'label' => 'Kategori',
                    'link' => 'admin.categories.index',
                    'allowed_roles' => ['super', 'admin', 'staff']
                ],
                (object) [
                    'label' => 'Pengguna',
                    'link' => 'admin.users.index',
                    'allowed_roles' => ['super']
                ],
                (object) [
                    'label' => 'Pengaturan',
                    'link' => 'admin.settings.index',
                    'allowed_roles' => ['super']
                ],
            ];
        }

        return $menus;
    }
}
