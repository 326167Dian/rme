<?php
session_start();
 if (empty($_SESSION['username']) AND empty($_SESSION['passuser'])){
  echo "<link href='style.css' rel='stylesheet' type='text/css'>
 <center>Untuk mengakses modul, Anda harus login <br>";
  echo "<a href=../../index.php><b>LOGIN</b></a></center>";
}
else{
include "../../../configurasi/koneksi.php";
include "../../../configurasi/fungsi_thumb.php";
include "../../../configurasi/library.php";

$module=$_GET['module'];
$act=$_GET['act'];

// Input admin
if ($module=='setheader' AND $act=='update_setheader'){

    $unit = isset($_SESSION['unit']) ? (int) $_SESSION['unit'] : 0;
    if ($unit < 1) {
        echo "<script type='text/javascript'>alert('Unit login tidak valid.');window.location='../../media_admin.php?module=".$module."'</script>";
        exit;
    }
 
    $fupload_name =  $_FILES["fupload1"]["name"];
    $fupload_tandatangan = $_FILES["fupload2"]["name"];

    if ($fupload_name != '') {
        UploadLogo_Struk($fupload_name);
    }
    if ($fupload_tandatangan != '') {
        UploadTandaTangan_Struk($fupload_tandatangan);
    }
    
    $stmt = $db->prepare("SELECT id_setheader, logo, tandatangan FROM setheader WHERE unit = ? LIMIT 1");
    $stmt->execute([$unit]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $row = [
            'id_setheader' => '',
            'logo' => 'mysifalogo.png',
            'tandatangan' => ''
        ];
    }
    
    if($row['logo'] != 'mysifalogo.png' and $fupload_name !=''){
        unlink('../../images/'.$row['logo']);
    }
    if(!empty($row['tandatangan']) and $fupload_tandatangan !=''){
        unlink('../../images/'.$row['tandatangan']);
    }
    if($fupload_name == ''){
        $fupload_name = $row['logo'];
    }
    if($fupload_tandatangan == ''){
        $fupload_tandatangan = $row['tandatangan'];
    }
    

    if (!empty($row['id_setheader'])) {
        $stmt = $db->prepare("UPDATE setheader SET satu = ?, dua = ?, tiga = ?, empat = ?, lima = ?, enam = ?, tujuh = ?, delapan = ?, sembilan = ?, sepuluh = ?, sebelas = ?, duabelas = ?, tigabelas = ?, logo = ?, tandatangan = ? WHERE id_setheader = ? and unit = ?");
        $stmt->execute([$_POST['satu'], $_POST['dua'], $_POST['tiga'], $_POST['empat'], $_POST['lima'], $_POST['enam'], $_POST['tujuh'], $_POST['delapan'], $_POST['sembilan'], $_POST['sepuluh'], $_POST['sebelas'], $_POST['duabelas'], $_POST['tigabelas'], $fupload_name, $fupload_tandatangan, $row['id_setheader'], $unit]);
    } else {
        $stmt = $db->prepare("INSERT INTO setheader (unit, satu, dua, tiga, empat, lima, enam, tujuh, delapan, sembilan, sepuluh, sebelas, duabelas, tigabelas, logo, tandatangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$unit, $_POST['satu'], $_POST['dua'], $_POST['tiga'], $_POST['empat'], $_POST['lima'], $_POST['enam'], $_POST['tujuh'], $_POST['delapan'], $_POST['sembilan'], $_POST['sepuluh'], $_POST['sebelas'], $_POST['duabelas'], $_POST['tigabelas'], $fupload_name, $fupload_tandatangan]);
    }
									
	echo "<script type='text/javascript'>alert('Data berhasil diubah !');window.location='../../media_admin.php?module=".$module."'</script>";
	//header('location:../../media_admin.php?module='.$module);
	
}

}
?>
