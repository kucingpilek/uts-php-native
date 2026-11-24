<?php
require_once 'functions.php';

// Daftar layanan yang diperbolehkan.
$daftarLayanan = ['Print Hitam', 'Print Warna', 'Fotokopi', 'Jilid Spiral'];

// Minimal 5 data transaksi awal (array multidimensi).
$daftarTransaksi = [
    [
        'id' => 1,
        'pelanggan' => 'Ani Saputri',
        'layanan' => 'Print Hitam',
        'jumlah' => 40,
        'member' => false,
        'total' => 20000
    ],
    [
        'id' => 2,
        'pelanggan' => 'Budi Aminah',
        'layanan' => 'Print Warna',
        'jumlah' => 25,
        'member' => true,
        'total' => 35625
    ],
    [
        'id' => 3,
        'pelanggan' => 'Dedi Santoso',
        'layanan' => 'Fotokopi',
        'jumlah' => 150,
        'member' => false,
        'total' => 40500
    ],
    [
        'id' => 4,
        'pelanggan' => 'Citra Lestari',
        'layanan' => 'Jilid Spiral',
        'jumlah' => 10,
        'member' => true,
        'total' => 95000
    ],
    [
        'id' => 5,
        'pelanggan' => 'Rina Wulandari',
        'layanan' => 'Fotokopi',
        'jumlah' => 200,
        'member' => true,
        'total' => 51000
    ]
];

$errors = [];
$namaInput = '';
$layananInput = '';
$jumlahInput = '';
$isMemberInput = false;
$pesanSukses = '';

// Memproses form transaksi menggunakan POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaInput = trim($_POST['pelanggan'] ?? '');
    $layananInput = trim($_POST['layanan'] ?? '');
    $jumlahInput = trim($_POST['jumlah'] ?? '');
    $isMemberInput = isset($_POST['member']);

    if (mb_strlen($namaInput) < 3) {
        $errors[] = 'Nama pelanggan minimal 3 karakter.';
    }

    if (!in_array($layananInput, $daftarLayanan, true)) {
        $errors[] = 'Silakan pilih jenis layanan yang tersedia.';
    }

    if (
        $jumlahInput === '' || filter_var($jumlahInput, FILTER_VALIDATE_INT) === false
        || (int) $jumlahInput < 1
    ) {
        $errors[] = 'Jumlah lembar harus berupa angka minimal 1.';
    }

    if (empty($errors)) {
        $jumlah = (int) $jumlahInput;
        $totalBayar = hitungTotalBayar($layananInput, $jumlah, $isMemberInput);

        $daftarTransaksi[] = [
            'id' => count($daftarTransaksi) + 1,
            'pelanggan' => $namaInput,
            'layanan' => $layananInput,
            'jumlah' => $jumlah,
            'member' => $isMemberInput,
            'total' => $totalBayar
        ];

        $pesanSukses = 'Transaksi berhasil ditambahkan.';
        // Kosongkan kembali nilai form setelah berhasil.
        $namaInput = '';
        $layananInput = '';
        $jumlahInput = '';
        $isMemberInput = false;
    }
}

// Filter dan pencarian menggunakan GET.
$filterLayanan = trim($_GET['layanan'] ?? '');
$kataKunci = trim($_GET['q'] ?? '');

$transaksiTampil = [];
foreach ($daftarTransaksi as $transaksi) {
    $cocokLayanan = ($filterLayanan === '' || $filterLayanan === 'Semua'
        || $transaksi['layanan'] === $filterLayanan);

    $cocokNama = ($kataKunci === ''
        || stripos($transaksi['pelanggan'], $kataKunci) !== false);

    if ($cocokLayanan && $cocokNama) {
        $transaksiTampil[] = $transaksi;
    }
}

// Statistik dihitung dari seluruh data transaksi, bukan hanya hasil filter.
$rekap = hitungRekap($daftarTransaksi);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Percetakan Kampus</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <div>
                <p class="eyebrow">Sistem Aplikasi Web · PHP Native</p>
                <h1>Kasir Percetakan Kampus</h1>
                <p class="subtitle">Aplikasi pencatatan dan rekapitulasi transaksi percetakan.</p>
            </div>
            <div class="server-label">PHP Native · 0% JavaScript</div>
        </header>

        <section class="stats">
            <article class="stat-card">
                <span class="stat-title">Total Transaksi</span>
                <strong><?= $rekap['totalTransaksi']; ?></strong>
                <small>Jumlah seluruh transaksi</small>
            </article>
            <article class="stat-card">
                <span class="stat-title">Total Lembar</span>
                <strong><?= $rekap['totalLembar']; ?></strong>
                <small>Akumulasi jumlah lembar</small>
            </article>
            <article class="stat-card">
                <span class="stat-title">Total Pendapatan</span>
                <strong><?= bersihkan(rupiah($rekap['totalPendapatan'])); ?></strong>
                <small>Pendapatan seluruh transaksi</small>
            </article>
            <article class="stat-card">
                <span class="stat-title">Rata-rata / Order</span>
                <strong><?= bersihkan(rupiah($rekap['rataRata'])); ?></strong>
                <small>Rata-rata pembayaran</small>
            </article>
        </section>

        <main class="main-grid">
            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h2>Form Input Transaksi</h2>
                        <p>Masukkan data pelanggan dan layanan.</p>
                    </div>
                    <span class="method-tag">method="POST"</span>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <strong>Periksa kembali input Anda:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= bersihkan($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($pesanSukses !== ''): ?>
                    <div class="alert alert-success"><?= bersihkan($pesanSukses); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="pelanggan">Nama Pelanggan *</label>
                        <input type="text" id="pelanggan" name="pelanggan" placeholder="Contoh: Rina Wulandari"
                            value="<?= bersihkan($namaInput); ?>">
                    </div>

                    <div class="form-group">
                        <label for="layanan">Jenis Layanan *</label>
                        <select id="layanan" name="layanan">
                            <option value="">-- Pilih Layanan --</option>
                            <?php foreach ($daftarLayanan as $layanan): ?>
                                <option value="<?= bersihkan($layanan); ?>" <?= ($layananInput === $layanan) ? 'selected' : ''; ?>>
                                    <?= bersihkan($layanan); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jumlah">Jumlah Lembar *</label>
                        <input type="number" id="jumlah" name="jumlah" min="1" placeholder="Contoh: 50"
                            value="<?= bersihkan($jumlahInput); ?>">
                    </div>

                    <label class="checkbox-row">
                        <input type="checkbox" name="member" value="1" <?= $isMemberInput ? 'checked' : ''; ?>>
                        <span>Pelanggan Member (diskon tambahan 5%)</span>
                    </label>

                    <button type="submit" class="submit-button">+ Proses & Tambah Transaksi (POST)</button>
                </form>
                <p class="form-note">Diskon 10% berlaku untuk jumlah lebih dari 100 lembar.</p>
            </section>

            <section class="panel">
                <div class="panel-heading">
                    <div>
                        <h2>Filter & Rekapitulasi Transaksi</h2>
                        <p>Gunakan filter layanan atau cari nama pelanggan.</p>
                    </div>
                    <span class="method-tag get-tag">method="GET"</span>
                </div>

                <form method="GET" action="" class="filter-form">
                    <div class="form-group">
                        <label for="filter-layanan">Layanan</label>
                        <select id="filter-layanan" name="layanan">
                            <option value="Semua" <?= ($filterLayanan === '' || $filterLayanan === 'Semua') ? 'selected' : ''; ?>>Semua</option>
                            <?php foreach ($daftarLayanan as $layanan): ?>
                                <option value="<?= bersihkan($layanan); ?>" <?= ($filterLayanan === $layanan) ? 'selected' : ''; ?>>
                                    <?= bersihkan($layanan); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group search-group">
                        <label for="q">Cari Nama Pelanggan</label>
                        <input type="search" id="q" name="q" placeholder="Nama pelanggan..."
                            value="<?= bersihkan($kataKunci); ?>">
                    </div>
                    <button type="submit" class="filter-button">Filter GET</button>
                    <a class="reset-button" href="index.php">Reset</a>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pelanggan</th>
                                <th>Layanan</th>
                                <th>Jumlah</th>
                                <th>Member</th>
                                <th>Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($transaksiTampil) > 0): ?>
                                <?php $no = 1; ?>
                                <?php foreach ($transaksiTampil as $transaksi): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= bersihkan($transaksi['pelanggan']); ?></td>
                                        <td><?= bersihkan($transaksi['layanan']); ?></td>
                                        <td><?= (int) $transaksi['jumlah']; ?></td>
                                        <td>
                                            <?= $transaksi['member'] ? 'Ya (5%)' : 'Tidak'; ?>
                                        </td>
                                        <td class="money"><?= bersihkan(rupiah($transaksi['total'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="empty-state">Tidak ada transaksi yang sesuai dengan filter.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="table-footnote">
                    Menampilkan <?= count($transaksiTampil); ?> dari <?= count($daftarTransaksi); ?> transaksi.
                </p>
            </section>
        </main>

        <footer>
            <p>Proyek UTS PHP Native · HTML5 & CSS3 · Seluruh pemrosesan dilakukan di sisi server.</p>
        </footer>
    </div>
</body>

</html>