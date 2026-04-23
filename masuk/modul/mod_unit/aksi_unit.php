<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
if (empty($_SESSION['username']) AND empty($_SESSION['passuser'])) {
  echo "<link href='style.css' rel='stylesheet' type='text/css'>
 <center>Untuk mengakses modul, Anda harus login <br>";
  echo "<a href=../../index.php><b>LOGIN</b></a></center>";
} elseif (!isset($_SESSION['level']) || $_SESSION['level'] !== 'pemilik') {
  echo "<script type='text/javascript'>alert('Modul Unit Bisnis hanya bisa diakses pemilik.');window.location='../../media_admin.php';</script>";
  exit;
} else {
  include "../../../configurasi/koneksi.php";
  include "../../../configurasi/fungsi_thumb.php";
  include "../../../configurasi/library.php";

  function unit_current_datetime()
  {
      return date('Y-m-d H:i:s');
  }

  function unit_table_exists($db)
  {
      $stmt = $db->query("SHOW TABLES LIKE 'unit'");
      return $stmt && $stmt->rowCount() > 0;
  }

  function unit_next_id($db)
  {
      $stmt = $db->query("SELECT COALESCE(MAX(id_unit), 0) + 1 AS next_id FROM unit");
      return (int) $stmt->fetchColumn();
  }

  $module = isset($_GET['module']) ? $_GET['module'] : '';
  $act = isset($_GET['act']) ? $_GET['act'] : '';

    if (!unit_table_exists($db)) {
            echo "<script type='text/javascript'>alert('Tabel unit belum ada di database aktif. Jalankan script database/unit.sql terlebih dahulu.');window.location='../../media_admin.php?module=unit';</script>";
            exit;
    }

  if ($module == 'unit' AND $act == 'input_unit') {
      $nm_unit = isset($_POST['nm_unit']) ? trim($_POST['nm_unit']) : '';
      $lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';

      if ($nm_unit === '' || $lokasi === '') {
          echo "<script type='text/javascript'>alert('Nama unit dan lokasi wajib diisi.');history.go(-1);</script>";
          exit;
      }

      $stmt = $db->prepare("SELECT COUNT(*) FROM unit WHERE nm_unit = ?");
      $stmt->execute([$nm_unit]);
      $ada = (int) $stmt->fetchColumn();
      if ($ada > 0) {
          echo "<script type='text/javascript'>alert('Nama Unit Bisnis sudah ada.');history.go(-1);</script>";
          exit;
      }

      $id_unit = unit_next_id($db);
      $now = unit_current_datetime();
      $stmt = $db->prepare("INSERT INTO unit(id_unit, nm_unit, lokasi, created_at, updated_at) VALUES(?, ?, ?, ?, ?)");
      $stmt->execute([$id_unit, $nm_unit, $lokasi, $now, $now]);

    $stmt = $db->prepare("INSERT INTO setheader(unit, satu) VALUES(?, ?)");
    $stmt->execute([$id_unit, $nm_unit]);
      header('location:../../media_admin.php?module=' . $module);

      
  } elseif ($module == 'unit' AND $act == 'update_unit') {
      $id_unit = isset($_POST['id']) ? (int) $_POST['id'] : 0;
      $nm_unit = isset($_POST['nm_unit']) ? trim($_POST['nm_unit']) : '';
      $lokasi = isset($_POST['lokasi']) ? trim($_POST['lokasi']) : '';

      if ($id_unit < 1 || $nm_unit === '' || $lokasi === '') {
          echo "<script type='text/javascript'>alert('Data unit tidak valid.');history.go(-1);</script>";
          exit;
      }

      $stmt = $db->prepare("SELECT COUNT(*) FROM unit WHERE nm_unit = ? AND id_unit <> ?");
      $stmt->execute([$nm_unit, $id_unit]);
      $ada = (int) $stmt->fetchColumn();
      if ($ada > 0) {
          echo "<script type='text/javascript'>alert('Nama Unit Bisnis sudah digunakan unit lain.');history.go(-1);</script>";
          exit;
      }

      $stmt = $db->prepare("UPDATE unit SET nm_unit = ?, lokasi = ?, updated_at = ? WHERE id_unit = ?");
      $stmt->execute([$nm_unit, $lokasi, unit_current_datetime(), $id_unit]);
      header('location:../../media_admin.php?module=' . $module);
  } elseif ($module == 'unit' AND $act == 'hapus') {
      $id_unit = isset($_GET['id']) ? (int) $_GET['id'] : 0;
      if ($id_unit > 0) {
          $stmt = $db->prepare("DELETE FROM unit WHERE id_unit = ?");
          $stmt->execute([$id_unit]);
      }
      header('location:../../media_admin.php?module=' . $module);
  }
}
?>