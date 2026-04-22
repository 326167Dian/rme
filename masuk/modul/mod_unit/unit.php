<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function format_unit_datetime($datetime)
{
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }

    return date('d-m-Y H:i:s', strtotime($datetime));
}

function unit_table_exists($db)
{
    $stmt = $db->query("SHOW TABLES LIKE 'unit'");
    return $stmt && $stmt->rowCount() > 0;
}

if (empty($_SESSION['username']) AND empty($_SESSION['passuser'])) {
    echo "<link href=../css/style.css rel=stylesheet type=text/css>";
    echo "<div class='error msg'>Untuk mengakses Modul anda harus login.</div>";
} elseif (!isset($_SESSION['level']) || $_SESSION['level'] !== 'pemilik') {
    echo "<div class='alert alert-danger'>Modul Unit Bisnis hanya bisa diakses pemilik.</div>";
} else {

$aksi = "modul/mod_unit/aksi_unit.php";

if (!unit_table_exists($db)) {
        echo "<div class='alert alert-danger'>Tabel <b>unit</b> belum ada di database aktif. Silakan jalankan SQL berikut terlebih dahulu.</div>";
        echo "<pre style='background:#f7f7f7;border:1px solid #ddd;padding:10px;'>CREATE TABLE unit (
    `id_unit` bigint(20) UNSIGNED NOT NULL,
    `nm_unit` varchar(255) NOT NULL,
    lokasi varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id_unit)
) ENGINE=InnoDB ;</pre>";
} else {

switch (isset($_GET['act']) ? $_GET['act'] : '') {
    default:
        $stmt = $db->query("SELECT * FROM unit ORDER BY id_unit ASC");
        $tampil_unit = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

            <div class="box box-primary box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">DATA UNIT BISNIS</h3>
                    <div class="box-tools pull-right">
                        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <a class='btn btn-success btn-flat' href='?module=unit&act=tambah'>TAMBAH</a>
                    <br><br>

                    <table id="table-unit" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Unit</th>
                                <th>Nama Unit Bisnis</th>
                                <th>Lokasi</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th width="90">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        foreach ($tampil_unit as $r) {
                            $created = format_unit_datetime($r['created_at']);
                            $updated = format_unit_datetime($r['updated_at']);
                            echo "<tr>";
                            echo "<td>$no</td>";
                            echo "<td>{$r['id_unit']}</td>";
                            echo "<td>" . htmlspecialchars($r['nm_unit'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>" . htmlspecialchars($r['lokasi'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>$created</td>";
                            echo "<td>$updated</td>";
                            echo "<td>";
                            echo "<a href='?module=unit&act=edit&id={$r['id_unit']}' title='EDIT' class='btn btn-warning btn-xs'>EDIT</a> ";
                            echo "<a href=javascript:confirmdelete('$aksi?module=unit&act=hapus&id={$r['id_unit']}') title='HAPUS' class='btn btn-danger btn-xs'>HAPUS</a>";
                            echo "</td>";
                            echo "</tr>";
                            $no++;
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

<?php
        break;

    case 'tambah':
        $nextStmt = $db->query("SELECT COALESCE(MAX(id_unit), 0) + 1 AS next_id FROM unit");
        $nextId = (int) $nextStmt->fetchColumn();
        echo "
          <div class='box box-primary box-solid'>
                <div class='box-header with-border'>
                    <h3 class='box-title'>TAMBAH UNIT BISNIS</h3>
                    <div class='box-tools pull-right'>
                        <button class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i></button>
                    </div>
                </div>
                <div class='box-body'>
                        <form method=POST action='$aksi?module=unit&act=input_unit' enctype='multipart/form-data' class='form-horizontal'>
                              <div class='form-group'>
                                    <label class='col-sm-2 control-label'>ID Unit</label>
                                     <div class='col-sm-3'>
                                        <input type=text name='id_preview' class='form-control' value='$nextId' readonly>
                                     </div>
                              </div>
                              <div class='form-group'>
                                    <label class='col-sm-2 control-label'>Nama Unit Bisnis</label>
                                     <div class='col-sm-6'>
                                        <input type=text name='nm_unit' class='form-control' required='required' autocomplete='off'>
                                     </div>
                              </div>
                              <div class='form-group'>
                                    <label class='col-sm-2 control-label'>Lokasi</label>
                                     <div class='col-sm-6'>
                                        <input type=text name='lokasi' class='form-control' required='required' autocomplete='off'>
                                     </div>
                              </div>
                              <div class='form-group'>
                                    <label class='col-sm-2 control-label'></label>
                                        <div class='col-sm-5'>
                                            <input class='btn btn-primary' type=submit value=SIMPAN>
                                            <input class='btn btn-danger' type=button value=BATAL onclick=self.history.back()>
                                        </div>
                                </div>
                              </form>
                </div>
            </div>";
        break;

    case 'edit':
        $stmt = $db->prepare("SELECT * FROM unit WHERE id_unit = ?");
        $stmt->execute([isset($_GET['id']) ? (int) $_GET['id'] : 0]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            echo "<div class='alert alert-danger'>Data Unit Bisnis tidak ditemukan.</div>";
            break;
        }
        $nm_unit = htmlspecialchars($r['nm_unit'], ENT_QUOTES, 'UTF-8');
        $lokasi = htmlspecialchars($r['lokasi'], ENT_QUOTES, 'UTF-8');
        echo "
          <div class='box box-primary box-solid'>
                <div class='box-header with-border'>
                    <h3 class='box-title'>UBAH UNIT BISNIS</h3>
                    <div class='box-tools pull-right'>
                        <button class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i></button>
                    </div>
                </div>
                <div class='box-body'>
                        <form method=POST action='$aksi?module=unit&act=update_unit' enctype='multipart/form-data' class='form-horizontal'>
                              <input type=hidden name=id value='{$r['id_unit']}'>
                              <div class='form-group'>
                                    <label class='col-sm-2 control-label'>ID Unit</label>
                                     <div class='col-sm-3'>
                                        <input type=text class='form-control' value='{$r['id_unit']}' readonly>
                                     </div>
                              </div>
                              <div class='form-group'>
                                    <label class='col-sm-2 control-label'>Nama Unit Bisnis</label>
                                     <div class='col-sm-6'>
                                        <input type=text name='nm_unit' class='form-control' value='$nm_unit' required='required' autocomplete='off'>
                                     </div>
                              </div>
                              <div class='form-group'>
                                    <label class='col-sm-2 control-label'>Lokasi</label>
                                     <div class='col-sm-6'>
                                        <input type=text name='lokasi' class='form-control' value='$lokasi' required='required' autocomplete='off'>
                                     </div>
                              </div>
                              <div class='form-group'>
                                    <label class='col-sm-2 control-label'></label>
                                        <div class='col-sm-5'>
                                            <input class='btn btn-primary' type=submit value=SIMPAN>
                                            <input class='btn btn-danger' type=button value=BATAL onclick=self.history.back()>
                                        </div>
                                </div>
                              </form>
                </div>
            </div>";
        break;
}
}
}
?>

<script type="text/javascript">
$(function() {
    $('#table-unit').DataTable();
});
</script>
