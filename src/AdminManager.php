<?php
namespace App;

use Exception;

class AdminManager
{
    private $filePesanan;

    public function __construct($filePesanan)
    {
        $this->filePesanan = $filePesanan;
    }

    public function getAllOrders()
    {
        if (!file_exists($this->filePesanan)) return [];
        $data = file_get_contents($this->filePesanan);
        return json_decode($data, true) ?? [];
    }

    // SKPL-F05.2: Update Status Pesanan
    public function updateStatusPesanan($idPesanan, $statusBaru)
    {
        $orders = $this->getAllOrders();
        $pesananDitemukan = false;

        $statusValid = ['Menunggu Pembayaran', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan'];
        if (!in_array($statusBaru, $statusValid)) {
            throw new Exception("Status '$statusBaru' tidak valid.");
        }

        foreach ($orders as $index => $order) {
            if ($order['id_pesanan'] === $idPesanan) {
                $orders[$index]['status'] = $statusBaru;
                $pesananDitemukan = true;
                break;
            }
        }

        if (!$pesananDitemukan) {
            throw new Exception("Pesanan dengan ID $idPesanan tidak ditemukan.");
        }

        file_put_contents($this->filePesanan, json_encode($orders, JSON_PRETTY_PRINT));
        return true;
    }
}
