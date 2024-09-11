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
                    'label' => 'Barang',
                    'link' => 'admin.products.index',
                    'allowed_roles' => ['admin', 'staff']
                ],
                (object) [
                    'label' => 'Transaksi',
                    'link' => 'admin.transactions.index',
                    'allowed_roles' => ['admin', 'staff']
                ],
                (object) [
                    'label' => 'Riwayat',
                    'link' => 'admin.histories.index',
                    'allowed_roles' => ['admin', 'staff']
                ],
                (object) [
                    'label' => 'Kategori',
                    'link' => 'admin.categories.index',
                    'allowed_roles' => ['admin', 'staff']
                ],
                (object) [
                    'label' => 'Cek Stok',
                    'link' => 'admin.check_stocks.index',
                    'allowed_roles' => ['admin']
                ],
                (object) [
                    'label' => 'Pengguna',
                    'link' => 'admin.users.index',
                    'allowed_roles' => ['admin']
                ],
            ];
        }

        return $menus;
    }
}

if (!function_exists('get_per_page_default')) {
    function get_per_page_default()
    {
        return 50;
    }
}

if (!function_exists('get_table_row_classname')) {
    function get_table_row_classname($type)
    {
        return $type == 'in' ? 'text-danger' : '';
    }
}
