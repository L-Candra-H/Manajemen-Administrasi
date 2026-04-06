<?php

function isExpired($tanggal): bool {
    $today = new DateTime('today');
    $target = new DateTime($tanggal);
    return $target < $today;
}

function isExpiringSoon($tanggal, $days = 30): bool {
    $today = new DateTime('today');
    $target = new DateTime($tanggal);
    $limit  = (clone $today)->modify("+$days days");
    return $target >= $today && $target <= $limit;
}

function getPeriodeRange(string $periode, string $mode): array {
    $tahun = substr($periode, 0, 4);
    $bulan = (int)substr($periode, 5, 2);

    switch (strtolower($mode)) {
        case 'bulanan':
            $jumlahBulan = 1;
            $periodeMulai = new DateTime("$tahun-$bulan-01");
            $periodeSelesai = (clone $periodeMulai)->modify('last day of this month');
            break;

        case 'triwulan':
            $jumlahBulan = 3;
            // hitung awal triwulan (1, 4, 7, 10)
            $startMonth = (floor(($bulan - 1) / 3) * 3) + 1;
            $periodeMulai = new DateTime("$tahun-$startMonth-01");
            $periodeSelesai = (clone $periodeMulai)->modify('+2 months')->modify('last day of this month');
            break;

        case 'semester':
            $jumlahBulan = 6;
            // semester 1 mulai Januari, semester 2 mulai Juli
            $startMonth = ($bulan <= 6 ? 1 : 7);
            $periodeMulai = new DateTime("$tahun-$startMonth-01");
            $periodeSelesai = (clone $periodeMulai)->modify('+5 months')->modify('last day of this month');
            break;

        case 'tahunan':
            $jumlahBulan = 12;
            $periodeMulai = new DateTime("$tahun-01-01");
            $periodeSelesai = new DateTime("$tahun-12-31");
            break;

        default:
            $jumlahBulan = 1;
            $periodeMulai = new DateTime("$tahun-$bulan-01");
            $periodeSelesai = (clone $periodeMulai)->modify('last day of this month');
    }

    return [$periodeMulai, $periodeSelesai, $jumlahBulan];
}
