<?php

/*
|--------------------------------------------------------------------------
| Navigasi Sidebar SMPD
|--------------------------------------------------------------------------
|
| Satu-satunya tempat untuk mengatur menu sidebar (dan kartu "Akses Cepat"
| di dashboard). Setiap item memiliki kunci berikut:
|
| - label       : Teks menu.
| - icon        : Nama ikon pada resources/views/components/icon.blade.php.
| - route       : Nama route Laravel. Selama route ini belum didaftarkan,
|                 menu tampil sebagai placeholder "Segera" (bukan tautan),
|                 sehingga tidak muncul error "Route not defined".
| - active      : Pola nama route untuk menandai menu aktif, mis. 'books.*'.
| - roles       : (opsional) Role yang boleh melihat menu. Kosong = semua.
|                 Ini hanya untuk tampilan; route tetap wajib dilindungi
|                 middleware 'role:...'.
| - module      : (opsional) true = tampil di "Akses Cepat" dashboard.
| - description : (opsional) Deskripsi singkat untuk kartu Akses Cepat.
|
*/

return [

    'sidebar' => [
        [
            'heading' => 'Utama',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'home',
                    'route' => 'dashboard',
                    'active' => 'dashboard',
                ],
            ],
        ],

        [
            'heading' => 'Modul Perpustakaan',
            'items' => [
                [
                    'label' => 'Katalog & Buku',
                    'icon' => 'book',
                    'route' => 'books.index',
                    'active' => 'books.*',
                    'module' => true,
                    'description' => 'Kelola data buku, kategori, dan penulis.',
                ],
                [
                    'label' => 'Anggota & Kartu',
                    'icon' => 'id-card',
                    'route' => 'members.index',
                    'active' => 'members.*',
                    'module' => true,
                    'description' => 'Kelola anggota, jenis keanggotaan, dan kartu.',
                ],
                [
                    'label' => 'Penerbit & Supplier',
                    'icon' => 'truck',
                    'route' => 'publishers.index',
                    'active' => 'publishers.*',
                    'module' => true,
                    'description' => 'Kelola data penerbit dan distributor buku.',
                ],
                [
                    'label' => 'Fasilitas & Area',
                    'icon' => 'map',
                    'route' => 'facilities.index',
                    'active' => 'facilities.*',
                    'module' => true,
                    'description' => 'Kelola area baca dan fasilitas perpustakaan.',
                ],
            ],
        ],

        [
            'heading' => 'Administrasi',
            'items' => [
                [
                    'label' => 'User Management',
                    'icon' => 'users',
                    'route' => 'users.index',
                    'active' => 'users.*',
                    'roles' => ['admin'],
                ],
            ],
        ],
    ],

];
