<?php
// Fungsi untuk membersihkan output agar aman dari serangan XSS.
function bersihkan(string $teks): string
{
    return htmlspecialchars(trim($teks), ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk mengubah angka menjadi format Rupiah.
function rupiah(float|int $angka): string
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Menghitung total pembayaran berdasarkan layanan, jumlah, dan status member.
function hitungTotalBayar(string $layanan, int $jumlah, bool $isMember = false): float
{
    switch ($layanan) {
        case 'Print Hitam':
            $tarif = 500;
            break;
        case 'Print Warna':
            $tarif = 1500;
            break;
        case 'Fotokopi':
            $tarif = 300;
            break;
        case 'Jilid Spiral':
            $tarif = 10000;
            break;
        default:
            $tarif = 0;
    }

    $subtotal = $tarif * $jumlah;
    $diskon = ($jumlah > 100) ? 0.10 : 0.0;

    if ($isMember) {
        $diskon += 0.05;
    }

    return $subtotal * (1 - $diskon);
}

// Menghitung jumlah transaksi, lembar, pendapatan, dan rata-rata transaksi.
function hitungRekap(array $daftarTransaksi): array
{
    $totalTransaksi = count($daftarTransaksi);
    $totalLembar = 0;
    $totalPendapatan = 0;

    foreach ($daftarTransaksi as $transaksi) {
        $totalLembar += (int) $transaksi['jumlah'];
        $totalPendapatan += (float) $transaksi['total'];
    }

    $rataRata = ($totalTransaksi > 0)
        ? $totalPendapatan / $totalTransaksi
        : 0;

    return [
        'totalTransaksi' => $totalTransaksi,
        'totalLembar' => $totalLembar,
        'totalPendapatan' => $totalPendapatan,
        'rataRata' => $rataRata
    ];
}
