<?php
class AccessControl {

    public static function isAdministrator() {
        return ($_SESSION['user']['hak_akses'] ?? '') === 'administrator';
    }

    public static function can($menu, $action) {
        $role    = $_SESSION['user']['hak_akses'] ?? 'user';
        $jabatanAlias = [
            'administrator' => 'administrator',
            'direktur'      => 'direktur',
            'ka.bid'        => 'kepala_bidang',
            'kabid'         => 'kepala_bidang',
            'ka.ru'         => 'kepala_ruang',
            'karu'          => 'kepala_ruang',
            'ka.unit'       => 'kepala_unit',
            'kaunit'        => 'kepala_unit',
            'koord'         => 'koordinator',
            'koordinator'   => 'koordinator',
            'kary'          => 'karyawan',
            'karyawan'      => 'karyawan'

        ];
        $jabatan = strtolower($_SESSION['user']['nama_jabatan'] ?? '');
        $jabatan = $jabatanAlias[$jabatan] ?? $jabatan;

        // kalau jabatan direktur, paksa role = direktur
        if ($jabatan === 'direktur') {
            $role = 'direktur';
        }

        // ⬇️ Tambahkan di sini
        if ($menu === 'surat.masuk' && $action === 'view') {
            $jabatanUnit = ['kabid','karu','kaunit','koord','ka.bid','ka.ru','ka.unit','koordinator'];
            if (in_array($jabatan, $jabatanUnit)) {
                return true; // boleh view, tapi controller tetap filter unit
            }
        }

        // Jabatan unit (kepala bidang, kepala ruang, kepala unit, koordinator) → surat internal
        if (in_array($jabatan, ['kepala_bidang','kepala_ruang','kepala_unit','koordinator'])) {
            if ($menu === 'surat.internal') {
                return in_array($action, ['view-own','create-own','update-own']);
            }
        }

        $permissions = [
            'administrator'                     => [
                'dashboard'                     => ['view'],
                'pengaturan.hak_akses'          => ['view','create','update','delete'],
                'pengaturan.institusi'          => ['view','create','update','delete'],
                'master.jenis_kelamin'          => ['view','create','update','delete'],
                'master.agama'                  => ['view','create','update','delete'],
                'master.status_pernikahan'      => ['view','create','update','delete'],
                'master.status_kepegawaian'     => ['view','create','update','delete'],
                'master.golongan_pegawai'       => ['view','create','update','delete'],
                'master.kategori_kepegawaian'   => ['view','create','update','delete'],
                'master.jabatan'                => ['view','create','update','delete'],
                'master.unit_kerja'             => ['view','create','update','delete'],
                'master.lama_kerja'             => ['view','create','update','delete'],
                'master.pendidikan'             => ['view','create','update','delete'],                
                'master.sifat_surat_masuk'      => ['view','create','update','delete'],
                'master.status_surat'           => ['view','create','update','delete'],
                'master.jenis_surat_keluar'     => ['view','create','update','delete'],
                'pegawai.daftar_aktif'          => ['view','create','update','delete','print'],
                'pegawai.daftar_nonaktif'       => ['view','create','update','delete'],
                'pegawai.qrcode'                => ['view','create','update','delete'],
                'pegawai.str'                   => ['view','create','update','delete'],
                'pegawai.sip'                   => ['view','create','update','delete'],
                'pegawai.sk'                    => ['view','create','update','delete'],
                'pegawai.sertifikat'            => ['view','create','update','delete'],
                'pegawai.bpjs'                  => ['view','create','update','delete'],
                'pegawai.cuti'                  => ['view','create','update','approve','delete'],
                'pegawai.grafik'                => ['view','create','update','delete'],
                'surat.keluar'                  => ['view','create','update','delete'],
                'surat.masuk'                   => ['view','create','update','delete'],
                'surat.internal'                => ['view','create','update','delete'],
                'remunerasi.master_index'       => ['view','create','update','delete'],
                'remunerasi.tiket'              => ['view','create','update','delete','finalize','print'],
                'remunerasi.generate'           => ['view','create','update','delete'],
                'remunerasi.index'              => ['view','create','update','delete'],
                'remunerasi.riwayat'            => ['view','create','update','delete'],
                'remunerasi.cetak'              => ['view','create','update','delete','print']
                // Akun Saya disembunyikan
            ],
            'admin'                             => [
                'dashboard'                     => ['view'],
                'pengaturan.hak_akses'          => ['view','create','update','delete'],
                'pengaturan.institusi'          => ['view'],
                'master.jenis_kelamin'          => ['view'],
                'master.agama'                  => ['view'],
                'master.status_pernikahan'      => ['view'],
                'master.status_kepegawaian'     => ['view'],
                'master.golongan_pegawai'       => ['view'],
                'master.kategori_kepegawaian'   => ['view'],
                'master.jabatan'                => ['view'],
                'master.unit_kerja'             => ['view','create','update','delete','print'],
                'master.pendidikan'             => ['view'],
                'pegawai.daftar_aktif'          => ['view','create','update','delete','print'],
                'pegawai.daftar_nonaktif'       => ['view'],
                'pegawai.qrcode'                => ['view'],
                'pegawai.str'                   => ['view'],
                'pegawai.sip'                   => ['view','create','update','delete'],
                'pegawai.sk'                    => ['view','create','update','delete'],
                'pegawai.sertifikat'            => ['view','create','update','delete'],
                'pegawai.bpjs'                  => ['view'],
                'pegawai.cuti'                  => ['view','create','update','approve','delete'],
                'pegawai.grafik'                => ['view'],
                'surat.internal'                => ['view-own','create-own','update-own','delete-own'],
                'remunerasi.master_index'       => ['view','create','update','delete'],
                'remunerasi.tiket'              => ['view','create','update','delete','print'], // tanpa finalize
                'remunerasi.generate'           => ['view','create','update','delete'],
                'remunerasi.index'              => ['view','create','update','delete'],
                'remunerasi.riwayat'            => ['view','create','update','delete'],
                'remunerasi.cetak'              => ['view','create','update','delete','print'],
                'profile.edit_data_saya'        => ['view-own','update-own'] 
                ],
            'admin2'                            => [
                'dashboard'                     => ['view'],
                'pengaturan.institusi'          => ['view'],
                'master.jabatan'                => ['view'],
                'master.sifat_surat_masuk'      => ['view'],
                'master.status_surat'           => ['view'],
                'master.jenis_surat_keluar'     => ['view'],
                'pegawai.daftar_aktif'          => ['view-own','print-own'],
                'pegawai.qrcode'                => ['view-own'],
                'pegawai.str'                   => ['view-own'],
                'pegawai.sip'                   => ['view-own'],
                'pegawai.sk'                    => ['view-own'],
                'pegawai.bpjs'                  => ['view-own'],
                'pegawai.grafik'                => ['view'],
                'surat.keluar'                  => ['view','create','update','delete'],
                'surat.masuk'                   => ['view','create','update','delete'],
                'surat.internal'                => ['view-own','create-own','update-own','delete-own'],
                'profile.edit_data_saya'        => ['view-own','update-own'] 
            ],
            'admin3'                            => [
                'dashboard'                     => ['view'],
                'pengaturan.hak_akses'          => ['view','create','update','delete'],
                'pengaturan.institusi'          => ['view'],
                'master.jenis_kelamin'          => ['view'],
                'master.agama'                  => ['view'],
                'master.status_pernikahan'      => ['view'],
                'master.status_kepegawaian'     => ['view'],
                'master.golongan_pegawai'       => ['view'],
                'master.kategori_kepegawaian'   => ['view'],
                'master.jabatan'                => ['view'],
                'master.unit_kerja'             => ['view','create','update','delete','print'],
                'master.pendidikan'             => ['view'],
                'master.sifat_surat_masuk'      => ['view'],
                'master.status_surat'           => ['view'],
                'master.jenis_surat_keluar'     => ['view'],
                'pegawai.daftar_aktif'          => ['view','create','update','delete','print'],
                'pegawai.daftar_nonaktif'       => ['view'],
                'pegawai.qrcode'                => ['view'],
                'pegawai.str'                   => ['view'],
                'pegawai.sip'                   => ['view','create','update','delete'],
                'pegawai.sk'                    => ['view','create','update','delete'],
                'pegawai.sertifikat'            => ['view','create','update','delete'],
                'pegawai.bpjs'                  => ['view'],
                'pegawai.cuti'                  => ['view','create','update','approve','delete'],
                'pegawai.grafik'                => ['view'],
                'surat.keluar'                  => ['view','create','update','delete'],
                'surat.masuk'                   => ['view','create','update','delete'],
                'surat.internal'                => ['view-own','create-own','update-own','delete-own'],
                'remunerasi.master_index'       => ['view','create','update','delete'],
                'remunerasi.tiket'              => ['view','create','update','delete','print'], // tanpa finalize
                'remunerasi.generate'           => ['view','create','update','delete'],
                'remunerasi.index'              => ['view','create','update','delete'],
                'remunerasi.riwayat'            => ['view','create','update','delete'],
                'remunerasi.cetak'              => ['view','create','update','delete','print'],
                'profile.edit_data_saya'        => ['view-own','update-own']
            ],
            'user'                              => [
                'dashboard'                     => ['view'],
                'pegawai.daftar_aktif'          => ['view-own','print-own'],
                'pegawai.qrcode'                => ['view-own'],
                'pegawai.str'                   => ['view-own'],
                'pegawai.sip'                   => ['view-own'],
                'pegawai.sk'                    => ['view-own'],
                'pegawai.sertifikat'            => ['view-own'],
                'pegawai.bpjs'                  => ['view-own'],
                'pegawai.cuti'                  => ['view-own','create-own','update-own'],
                'pegawai.grafik'                => ['view'],
                'surat.internal'                => ['view-own','create-own','update-own'],
                'profile.edit_data_saya'        => ['view-own','update-own']
            ],
            'direktur'                          => [
                'dashboard'                     => ['view'],
                'pengaturan.institusi'          => ['view'],
                'master.jenis_kelamin'          => ['view'],
                'master.agama'                  => ['view'],
                'master.status_pernikahan'      => ['view'],
                'master.status_kepegawaian'     => ['view'],
                'master.golongan_pegawai'       => ['view'],
                'master.kategori_kepegawaian'   => ['view'],
                'master.jabatan'                => ['view'],
                'master.unit_kerja'             => ['view'],
                'master.pendidikan'             => ['view'],
                'master.sifat_surat_masuk'      => ['view'],
                'master.status_surat'           => ['view'],
                'master.jenis_surat_keluar'     => ['view'],
                'pegawai.daftar_aktif'          => ['view','print'],
                'pegawai.daftar_nonaktif'       => ['view','update','delete'],
                'pegawai.qrcode'                => ['view'],
                'pegawai.str'                   => ['view'],
                'pegawai.sip'                   => ['view'],
                'pegawai.sk'                    => ['view'],
                'pegawai.sertifikat'            => ['view'],
                'pegawai.bpjs'                  => ['view'],
                'pegawai.cuti'                  => ['view'],
                'pegawai.grafik'                => ['view'],
                'surat.keluar'                  => ['view'],
                'surat.masuk'                   => ['view','update'],
                'surat.internal'                => ['view','update',],
                'remunerasi.tiket'              => ['view','finalize','print'], // hanya finalize + view
                'remunerasi.generate'           => ['view'], // cukup bisa lihat
                'remunerasi.index'              => ['view'],
                'remunerasi.riwayat'            => ['view'],
                'remunerasi.cetak'              => ['view','print'],
                'profile.edit_data_saya'        => ['view-own','update-own']
            ]
        ];

        // Jika role sudah punya izin → pakai itu
        if (isset($permissions[$role][$menu]) && in_array($action, $permissions[$role][$menu])) {
            return true;
        }
        
        // Direktur → hanya bisa view di menu kepegawaian
        if ($jabatan === 'direktur') {
            // Direktur → lihat semua pegawai aktif, STR, SIP, SK
            if (in_array($menu, ['pegawai.daftar_aktif','pegawai.str','pegawai.sip','pegawai.sk'])) {
                return in_array($action, ['view','print']);
            }
            // Direktur → lihat semua pegawai nonaktif dan BPJS
            if (in_array($menu, ['pegawai.daftar_nonaktif','pegawai.bpjs'])) {
                return in_array($action, ['view','print']);
            }
        }

        // Administrator + Direktur → full akses surat masuk & keluar
        if ($jabatan === 'direktur' || $jabatan === 'administrator') {
            if ($menu === 'surat.masuk' || $menu === 'surat.keluar') {
                return in_array($action, ['view','create','update','delete']);
            }
        }

        // Jabatan lain (selain direktur & karyawan) → hanya view surat masuk unitnya
        if (in_array($jabatan, ['kepala_bidang','kepala_ruang','kepala_unit','koordinator'])) {
            if ($menu === 'surat.masuk') {
                return $action === 'view'; // filter by unit_id di controller
            }

            // Surat Internal → boleh view-own, create-own, update-own
            if ($menu === 'surat.internal') {
                return in_array($action, ['view-own','create-own','update-own']);
            }
        }

        // Karyawan → tidak boleh lihat surat masuk
        if ($jabatan === 'karyawan' && $menu === 'surat.masuk') {
            return false; // karyawan tidak boleh lihat surat masuk
        }

        return in_array($action, $permissions[$role][$menu] ?? []);
    }
}