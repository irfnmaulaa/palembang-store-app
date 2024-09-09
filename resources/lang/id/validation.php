
<?php

return [
    /*
    |---------------------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi
    |---------------------------------------------------------------------------------------
    |
    | Baris bahasa berikut ini berisi standar pesan kesalahan yang digunakan oleh
    | kelas validasi. Beberapa aturan mempunyai banyak versi seperti aturan 'size'.
    | Jangan ragu untuk mengoptimalkan setiap pesan yang ada di sini.
    |
    */

    'accepted'        => 'Data harus diterima.',
    'active_url'      => 'Data bukan URL yang valid.',
    'after'           => 'Data harus berisi tanggal setelah :date.',
    'after_or_equal'  => 'Data harus berisi tanggal setelah atau sama dengan :date.',
    'alpha'           => 'Data hanya boleh berisi huruf.',
    'alpha_dash'      => 'Data hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num'       => 'Data hanya boleh berisi huruf dan angka.',
    'array'           => 'Data harus berisi sebuah array.',
    'before'          => 'Data harus berisi tanggal sebelum :date.',
    'before_or_equal' => 'Data harus berisi tanggal sebelum atau sama dengan :date.',
    'between'         => [
        'numeric' => 'Data harus bernilai antara :min sampai :max.',
        'file'    => 'Data harus berukuran antara :min sampai :max kilobita.',
        'string'  => 'Data harus berisi antara :min sampai :max karakter.',
        'array'   => 'Data harus memiliki :min sampai :max anggota.',
    ],
    'boolean'        => 'Data harus bernilai true atau false',
    'confirmed'      => 'Konfirmasi Data tidak cocok.',
    'date'           => 'Data bukan tanggal yang valid.',
    'date_equals'    => 'Data harus berisi tanggal yang sama dengan :date.',
    'date_format'    => 'Data tidak cocok dengan format :format.',
    'different'      => 'Data dan :other harus berbeda.',
    'digits'         => 'Data harus terdiri dari :digits angka.',
    'digits_between' => 'Data harus terdiri dari :min sampai :max angka.',
    'dimensions'     => 'Data tidak memiliki dimensi gambar yang valid.',
    'distinct'       => 'Data memiliki nilai yang duplikat.',
    'email'          => 'Data harus berupa alamat surel yang valid.',
    'ends_with'      => 'Data harus diakhiri salah satu dari berikut: :values',
    'exists'         => 'Data yang dipilih tidak valid.',
    'file'           => 'Data harus berupa sebuah berkas.',
    'filled'         => 'Data harus memiliki nilai.',
    'gt'             => [
        'numeric' => 'Data harus bernilai lebih besar dari :value.',
        'file'    => 'Data harus berukuran lebih besar dari :value kilobita.',
        'string'  => 'Data harus berisi lebih besar dari :value karakter.',
        'array'   => 'Data harus memiliki lebih dari :value anggota.',
    ],
    'gte' => [
        'numeric' => 'Data harus bernilai lebih besar dari atau sama dengan :value.',
        'file'    => 'Data harus berukuran lebih besar dari atau sama dengan :value kilobita.',
        'string'  => 'Data harus berisi lebih besar dari atau sama dengan :value karakter.',
        'array'   => 'Data harus terdiri dari :value anggota atau lebih.',
    ],
    'image'    => 'Data harus berupa gambar.',
    'in'       => 'Data yang dipilih tidak valid.',
    'in_array' => 'Data tidak ada di dalam :other.',
    'integer'  => 'Data harus berupa bilangan bulat.',
    'ip'       => 'Data harus berupa alamat IP yang valid.',
    'ipv4'     => 'Data harus berupa alamat IPv4 yang valid.',
    'ipv6'     => 'Data harus berupa alamat IPv6 yang valid.',
    'json'     => 'Data harus berupa JSON string yang valid.',
    'lt'       => [
        'numeric' => 'Data harus bernilai kurang dari :value.',
        'file'    => 'Data harus berukuran kurang dari :value kilobita.',
        'string'  => 'Data harus berisi kurang dari :value karakter.',
        'array'   => 'Data harus memiliki kurang dari :value anggota.',
    ],
    'lte' => [
        'numeric' => 'Data harus bernilai kurang dari atau sama dengan :value.',
        'file'    => 'Data harus berukuran kurang dari atau sama dengan :value kilobita.',
        'string'  => 'Data harus berisi kurang dari atau sama dengan :value karakter.',
        'array'   => 'Data harus tidak lebih dari :value anggota.',
    ],
    'max' => [
        'numeric' => 'Data maskimal bernilai :max.',
        'file'    => 'Data maksimal berukuran :max kilobita.',
        'string'  => 'Data maskimal berisi :max karakter.',
        'array'   => 'Data maksimal terdiri dari :max anggota.',
    ],
    'mimes'     => 'Data harus berupa berkas berjenis: :values.',
    'mimetypes' => 'Data harus berupa berkas berjenis: :values.',
    'min'       => [
        'numeric' => 'Data minimal bernilai :min.',
        'file'    => 'Data minimal berukuran :min kilobita.',
        'string'  => 'Data minimal berisi :min karakter.',
        'array'   => 'Data minimal terdiri dari :min anggota.',
    ],
    'not_in'               => 'Data yang dipilih tidak valid.',
    'not_regex'            => 'Format Data tidak valid.',
    'numeric'              => 'Data harus berupa angka.',
    'password'             => 'Kata sandi salah.',
    'present'              => 'Data wajib ada.',
    'regex'                => 'Format Data tidak valid.',
    'required'             => 'Data wajib diisi.',
    'required_if'          => 'Data wajib diisi bila :other adalah :value.',
    'required_unless'      => 'Data wajib diisi kecuali :other memiliki nilai :values.',
    'required_with'        => 'Data wajib diisi bila terdapat :values.',
    'required_with_all'    => 'Data wajib diisi bila terdapat :values.',
    'required_without'     => 'Data wajib diisi bila tidak terdapat :values.',
    'required_without_all' => 'Data wajib diisi bila sama sekali tidak terdapat :values.',
    'same'                 => 'Data dan :other harus sama.',
    'size'                 => [
        'numeric' => 'Data harus berukuran :size.',
        'file'    => 'Data harus berukuran :size kilobyte.',
        'string'  => 'Data harus berukuran :size karakter.',
        'array'   => 'Data harus mengandung :size anggota.',
    ],
    'starts_with' => 'Data harus diawali salah satu dari berikut: :values',
    'string'      => 'Data harus berupa string.',
    'timezone'    => 'Data harus berisi zona waktu yang valid.',
    'unique'      => 'Data sudah ada sebelumnya.',
    'uploaded'    => 'Data gagal diunggah.',
    'url'         => 'Format Data tidak valid.',
    'uuid'        => 'Data harus merupakan UUID yang valid.',

    /*
    |---------------------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi Kustom
    |---------------------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan pesan validasi untuk atribut sesuai keinginan dengan menggunakan
    | konvensi "attribute.rule" dalam penamaan barisnya. Hal ini mempercepat dalam menentukan
    | baris bahasa kustom yang spesifik untuk aturan atribut yang diberikan.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |---------------------------------------------------------------------------------------
    | Kustom Validasi Atribut
    |---------------------------------------------------------------------------------------
    |
    | Baris bahasa berikut digunakan untuk menukar 'placeholder' atribut dengan sesuatu yang
    | lebih mudah dimengerti oleh pembaca seperti "Alamat Surel" daripada "surel" saja.
    | Hal ini membantu kita dalam membuat pesan menjadi lebih ekspresif.
    |
    */

    'attributes' => [
    ],
];
