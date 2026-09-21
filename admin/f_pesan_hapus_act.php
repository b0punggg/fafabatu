<?php
ob_start();
include 'config.php';
session_start();
require_once 'f_pesan_helper.php';
$connect = opendtcek();
ensurePesanTables($connect);
$kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($connect, $_SESSION['id_toko']) : '';
$id = isset($_POST['keydel']) ? mysqli_real_escape_string($connect, $_POST['keydel']) : '';

if ($kd_toko !== '' && $id !== '') {
  $q = mysqli_query($connect, "SELECT no_po,tgl_po FROM dum_pesan WHERE no_urut='$id' AND kd_toko='$kd_toko'");
  if ($q && mysqli_num_rows($q) > 0) {
    $r = mysqli_fetch_assoc($q);
    mysqli_query($connect, "DELETE FROM dum_pesan WHERE no_urut='$id' AND kd_toko='$kd_toko'");
    pesanRefreshTotal($connect, $r['no_po'], $r['tgl_po'], $kd_toko);
  }
  if ($q) { mysqli_free_result($q); }
}
mysqli_close($connect);
ob_end_clean();
echo '<script>if(typeof popnew_warning==="function"){popnew_warning("Item dihapus");}if(typeof carinota==="function"){carinota(1,true);}if(typeof carilistbrg==="function"){carilistbrg(1,true);}</script>';
