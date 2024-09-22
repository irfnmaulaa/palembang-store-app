<?php

use App\Models\Setting;

if (!function_exists('get_menus')) {
    function get_menus() {
        return [
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
            (object) [
                'label' => 'REC',
                'link' => 'admin.app_errors.index',
                'allowed_roles' => ['admin', 'staff']
            ],
        ];
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
        return $type == 'in' ? 'text-danger' : 'text-black';
    }
}

if (!function_exists('get_max_time_user_active')) {
    function get_max_time_user_active()
    {
        return '17:00:00';
    }
}

if (!function_exists('get_start_time_admin_verify')) {
    function get_start_time_admin_verify()
    {
        return '06:00:00';
    }
}

if (!function_exists('get_max_time_admin_verify')) {
    function get_max_time_admin_verify()
    {
        return '23:00:00';
    }
}
