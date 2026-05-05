<?php
session_start();
include_once '../../../configurasi/koneksi.php';
$aksi = "modul/mod_cekdarah/aksi_cekdarah.php";
if ($_GET['action'] == "table_data") {
    $columns = array(
        0 => 'id_cekdarah',
        1 => 'nm_pelanggan',
        2 => 'petugas',
        3 => 'gula',
        4 => 'asamurat',
        5 => 'kolesterol',
        6 => 'tensi',
        7 => 'waktu',
        8 => 'id_cekdarah'
    );

    $querycount = $db->prepare("SELECT count(c.id_cekdarah) as jumlah FROM cekdarah c");
    $querycount->execute();
    $datacount = $querycount->fetch(PDO::FETCH_ASSOC);

    $totalData = $datacount['jumlah'];
    $totalFiltered = $totalData;

    $limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;

    // default ordering: newest first
    $order = 'id_cekdarah';
    $dir = 'DESC';
    if (isset($_POST['order']['0']['column']) && $_POST['order']['0']['column'] !== '') {
        $colIndex = intval($_POST['order']['0']['column']);
        if (isset($columns[$colIndex])) {
            $order = $columns[$colIndex];
        }
        $dir = (isset($_POST['order']['0']['dir']) && strtoupper($_POST['order']['0']['dir']) === 'ASC') ? 'ASC' : 'DESC';
    }

    if (empty($_POST['search']['value'])) {
        $query = $db->prepare("SELECT c.*, p.nm_pelanggan FROM cekdarah c LEFT JOIN pelanggan p ON p.id_pelanggan = c.id_pelanggan ORDER BY $order $dir LIMIT :limit OFFSET :start");
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':start', $start, PDO::PARAM_INT);
        $query->execute();
    } else {
        $search = $_POST['search']['value'];
        $searchLike = '%' . $search . '%';
        $query = $db->prepare("SELECT c.*, p.nm_pelanggan FROM cekdarah c LEFT JOIN pelanggan p ON p.id_pelanggan = c.id_pelanggan WHERE (p.nm_pelanggan LIKE ? OR c.petugas LIKE ? OR c.gula LIKE ? OR c.asamurat LIKE ? OR c.kolesterol LIKE ? OR c.tensi LIKE ? OR c.waktu LIKE ?) ORDER BY $order $dir LIMIT :limit OFFSET :start");
        $query->bindValue(1, $searchLike, PDO::PARAM_STR);
        $query->bindValue(2, $searchLike, PDO::PARAM_STR);
        $query->bindValue(3, $searchLike, PDO::PARAM_STR);
        $query->bindValue(4, $searchLike, PDO::PARAM_STR);
        $query->bindValue(5, $searchLike, PDO::PARAM_STR);
        $query->bindValue(6, $searchLike, PDO::PARAM_STR);
        $query->bindValue(7, $searchLike, PDO::PARAM_STR);
        $query->bindValue(':limit', $limit, PDO::PARAM_INT);
        $query->bindValue(':start', $start, PDO::PARAM_INT);
        $query->execute();

        $querycount = $db->prepare("SELECT count(c.id_cekdarah) as jumlah FROM cekdarah c LEFT JOIN pelanggan p ON p.id_pelanggan = c.id_pelanggan WHERE (p.nm_pelanggan LIKE ? OR c.petugas LIKE ? OR c.gula LIKE ? OR c.asamurat LIKE ? OR c.kolesterol LIKE ? OR c.tensi LIKE ? OR c.waktu LIKE ?)");
        $querycount->execute([$searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike]);
        $datacount = $querycount->fetch(PDO::FETCH_ASSOC);
        $totalFiltered = $datacount['jumlah'];
    }

    $data = array();
    if (!empty($query)) {
        $no = $start + 1;
        while ($value = $query->fetch(PDO::FETCH_ASSOC)) {
            $nestedData = array();
            $nestedData['no'] = $no;
            $nestedData['nm_pelanggan'] = $value['nm_pelanggan'];
            $nestedData['petugas'] = $value['petugas'];
            $nestedData['gula'] = $value['gula'];
            $nestedData['asamurat'] = $value['asamurat'];
            $nestedData['kolesterol'] = $value['kolesterol'];
            $nestedData['tensi'] = $value['tensi'];
            $nestedData['waktu'] = $value['waktu'];

            $lupa = isset($_SESSION['level']) ? $_SESSION['level'] : '';
            if ($lupa == 'pemilik') {
                $nestedData['aksi'] = "<div class='dropdown'>
  <button class='btn btn-default dropdown-toggle' type='button' id='dropdownMenu{$value['id_cekdarah']}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
    action
    <span class='caret'></span>
  </button>
  <ul class='dropdown-menu' aria-labelledby='dropdownMenu{$value['id_cekdarah']}'>
    <li style='background-color:yellow;'><a href='?module=cekdarah&act=edit&id={$value['id_cekdarah']}'>EDIT</a></li>
    <li style='background-color:red;'><a href=javascript:confirmdelete('{$aksi}?module=cekdarah&act=hapus&id={$value['id_cekdarah']}')>HAPUS</a></li>
    <li style='background-color:aqua;'><a href='modul/mod_cekdarah/print.php?id={$value['id_cekdarah']}' target='_blanks'>PRINT</a></li>
  </ul>
</div>";
            } else {
                $nestedData['aksi'] = "<div class='dropdown'>
  <button class='btn btn-default dropdown-toggle' type='button' id='dropdownMenu{$value['id_cekdarah']}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
    action
    <span class='caret'></span>
  </button>
  <ul class='dropdown-menu' aria-labelledby='dropdownMenu{$value['id_cekdarah']}'>
    <li style='background-color:aqua;'><a href='modul/mod_cekdarah/print.php?id={$value['id_cekdarah']}' target='_blanks'>PRINT</a></li>
  </ul>
</div>";
            }

            $data[] = $nestedData;
            $no++;
        }
    }

    $json_data = [
        "draw" => intval($_POST['draw']),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    ];

    echo json_encode($json_data);
}
?>
