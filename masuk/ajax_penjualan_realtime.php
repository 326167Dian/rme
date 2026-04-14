<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['namauser']) || empty($_SESSION['passuser'])) {
    if (empty($_SESSION['username']) || empty($_SESSION['login'])) {
        http_response_code(401);
        echo json_encode([
            'status' => false,
            'message' => 'Sesi login tidak valid'
        ]);
        exit;
    }
}

if (empty($_SESSION['login']) || $_SESSION['login'] != 1) {
    http_response_code(401);
    echo json_encode([
        'status' => false,
        'message' => 'Silakan login kembali'
    ]);
    exit;
}

include "../configurasi/koneksi.php";

try {
    $bulanRequest = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
    $tahunRequest = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

    if ($bulanRequest < 1 || $bulanRequest > 12) {
        $bulanRequest = (int)date('n');
    }

    if ($tahunRequest < 2000 || $tahunRequest > 2100) {
        $tahunRequest = (int)date('Y');
    }

    $awalBulan = sprintf('%04d-%02d-01', $tahunRequest, $bulanRequest);
    $akhirBulanPenuh = date('Y-m-t', strtotime($awalBulan));

    $bulanSekarang = (int)date('n');
    $tahunSekarang = (int)date('Y');
    if ($bulanRequest === $bulanSekarang && $tahunRequest === $tahunSekarang) {
        $akhirPeriode = date('Y-m-d');
    } else {
        $akhirPeriode = $akhirBulanPenuh;
    }

    $awalBulanSebelumnya = date('Y-m-01', strtotime($awalBulan . ' -1 month'));
    $akhirBulanSebelumnya = date('Y-m-t', strtotime($awalBulanSebelumnya));

    $sql = "SELECT tgl, COUNT(*) AS total_swamedikasi
            FROM riwayat_pelanggan
            WHERE tgl BETWEEN :awal_bulan AND :akhir_periode
            GROUP BY tgl
            ORDER BY tgl ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':awal_bulan' => $awalBulan,
        ':akhir_periode' => $akhirPeriode
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $mapPenjualan = [];
    foreach ($rows as $row) {
        $tanggal = $row['tgl'];
        $mapPenjualan[$tanggal] = (int)$row['total_swamedikasi'];
    }

    $sqlPrev = "SELECT tgl, COUNT(*) AS total_swamedikasi
                FROM riwayat_pelanggan
                WHERE tgl BETWEEN :awal_prev AND :akhir_prev
                GROUP BY tgl
                ORDER BY tgl ASC";

    $stmtPrev = $db->prepare($sqlPrev);
    $stmtPrev->execute([
        ':awal_prev' => $awalBulanSebelumnya,
        ':akhir_prev' => $akhirBulanSebelumnya
    ]);

    $rowsPrev = $stmtPrev->fetchAll(PDO::FETCH_ASSOC);
    $mapPenjualanPrevByHari = [];
    foreach ($rowsPrev as $rowPrev) {
        $hari = date('d', strtotime($rowPrev['tgl']));
        $mapPenjualanPrevByHari[$hari] = (int)$rowPrev['total_swamedikasi'];
    }

    $dataHarian = [];
    $dataBulanLalu = [];
    $tanggalLoop = $awalBulan;
    while ($tanggalLoop <= $akhirPeriode) {
        $total = 0;
        if (isset($mapPenjualan[$tanggalLoop])) {
            $total = $mapPenjualan[$tanggalLoop];
        }

        $hariLoop = date('d', strtotime($tanggalLoop));
        $totalPrev = 0;
        if (isset($mapPenjualanPrevByHari[$hariLoop])) {
            $totalPrev = $mapPenjualanPrevByHari[$hariLoop];
        }

        $dataHarian[] = [
            'tgl' => $tanggalLoop,
            'total_swamedikasi' => $total
        ];

        $dataBulanLalu[] = [
            'hari' => $hariLoop,
            'total_swamedikasi' => $totalPrev
        ];

        $tanggalLoop = date('Y-m-d', strtotime($tanggalLoop . ' +1 day'));
    }

    echo json_encode([
        'status' => true,
        'data' => $dataHarian,
        'data_bulan_lalu' => $dataBulanLalu,
        'periode_label' => date('F Y', strtotime($awalBulan)),
        'periode_sebelumnya_label' => date('F Y', strtotime($awalBulanSebelumnya)),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Gagal memuat data swamedikasi'
    ]);
}
