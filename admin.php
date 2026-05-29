<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Akses Ditolak! Anda bukan admin. <a href='index.php'>Kembali</a>");
}

require_once 'src/AdminManager.php';
require_once 'src/Catalog.php';
$adminManager = new App\AdminManager(__DIR__ . '/data/orders.json');
$katalog = new App\Catalog(__DIR__ . '/data/products.json');

$pesanAksi = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi_produk'])) {
    if ($_POST['aksi_produk'] == 'simpan') {
        $katalog->saveProduct($_POST['kode'], $_POST['nama'], $_POST['harga'], $_POST['stok']);
        $pesanAksi = "Produk berhasil disimpan!";
    } elseif ($_POST['aksi_produk'] == 'hapus') {
        $katalog->deleteProduct($_POST['kode']);
        $pesanAksi = "Produk berhasil dihapus!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_pesanan'])) {
    try {
        $adminManager->updateStatusPesanan($_POST['id_pesanan'], $_POST['status_baru']);
        $pesanAksi = "Status pesanan berhasil diupdate!";
    } catch (Exception $e) {
        $pesanAksi = "Error: " . $e->getMessage();
    }
}

$produkList = $katalog->getAllProducts();
$pesananList = array_reverse($adminManager->getAllOrders());
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand">Panel Admin</span>
            <div>
                <a href="index.php" class="btn btn-sm btn-outline-light me-2">Ke Website</a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($pesanAksi): ?>
            <div class="alert alert-info"><?= htmlspecialchars($pesanAksi) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">Tambah / Edit Produk</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="aksi_produk" value="simpan">
                            <input type="text" name="kode" class="form-control mb-2" placeholder="Kode (Misal: PRD-004)" required>
                            <input type="text" name="nama" class="form-control mb-2" placeholder="Nama Produk" required>
                            <input type="number" name="harga" class="form-control mb-2" placeholder="Harga" required>
                            <input type="number" name="stok" class="form-control mb-3" placeholder="Stok" required>
                            <button class="btn btn-primary w-100">Simpan Produk</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">Daftar Produk</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($produkList as $kode => $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($item['nama']) ?></strong><br>
                                    <small>Rp <?= number_format($item['harga'], 0, ',', '.') ?> | Stok: <?= $item['stok'] ?></small>
                                </div>
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="aksi_produk" value="hapus">
                                    <input type="hidden" name="kode" value="<?= $kode ?>">
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini?')">Hapus</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">Daftar Pesanan Masuk</div>
                    <div class="card-body p-0">
                        <?php if (empty($pesananList)): ?>
                            <p class="text-center text-muted p-4">Belum ada pesanan.</p>
                        <?php else: ?>
                        <table class="table mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Email</th>
                                    <th>Alamat</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Ubah Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pesananList as $order): ?>
                                    <tr>
                                        <td><small><?= $order['id_pesanan'] ?></small></td>
                                        <td><small><?= htmlspecialchars($order['email']) ?></small></td>
                                        <td><small><?= htmlspecialchars($order['alamat']) ?></small></td>
                                        <td>Rp <?= number_format($order['total_bayar'], 0, ',', '.') ?></td>
                                        <td><span class="badge bg-secondary"><?= $order['status'] ?></span></td>
                                        <td>
                                            <form method="POST" class="d-flex gap-1">
                                                <input type="hidden" name="id_pesanan" value="<?= $order['id_pesanan'] ?>">
                                                <select name="status_baru" class="form-select form-select-sm">
                                                    <option value="Menunggu Pembayaran">Menunggu</option>
                                                    <option value="Diproses">Diproses</option>
                                                    <option value="Dikirim">Dikirim</option>
                                                    <option value="Selesai">Selesai</option>
                                                    <option value="Dibatalkan">Dibatalkan</option>
                                                </select>
                                                <button class="btn btn-sm btn-success">OK</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
