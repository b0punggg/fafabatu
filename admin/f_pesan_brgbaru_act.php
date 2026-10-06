<?php
ob_start();
include 'config.php';
session_start();
require_once 'f_pesan_helper.php';
$connect = opendtcek();
ensurePesanTables($connect);

$kd_toko = isset($_SESSION['id_toko']) ? $_SESSION['id_toko'] : '';
$tgl_po  = isset($_POST['tgl_po']) ? $_POST['tgl_po'] : date('Y-m-d');
$no_po   = strtoupper(trim(isset($_POST['no_po']) ? $_POST['no_po'] : ''));
$kd_sup  = isset($_POST['kd_sup']) ? trim($_POST['kd_sup']) : '';
$ket     = strtoupper(trim(isset($_POST['ket']) ? $_POST['ket'] : ''));
$nm_brg  = strtoupper(trim(isset($_POST['nm_brg']) ? $_POST['nm_brg'] : ''));
$kd_sat  = isset($_POST['kd_sat']) ? trim($_POST['kd_sat']) : '';
$nm_sat  = strtoupper(trim(isset($_POST['nm_sat']) ? $_POST['nm_sat'] : ''));
$qty     = pesanToNum(isset($_POST['qty_pesan']) ? $_POST['qty_pesan'] : 0);
$hrg     = pesanToNum(isset($_POST['hrg_beli']) ? $_POST['hrg_beli'] : 0);

$html = '';
if ($kd_toko === '' || $no_po === '' || $tgl_po === '' || $kd_sup === '') {
  $html = '<script>if(typeof popnew_error==="function"){popnew_error("No. PO, tanggal, dan supplier wajib diisi");}else{alert("No. PO, tanggal, dan supplier wajib diisi");}</script>';
} elseif ($nm_brg === '') {
  $html = '<script>if(typeof popnew_error==="function"){popnew_error("Nama barang wajib diisi");}else{alert("Nama barang wajib diisi");}</script>';
} else {
  if ($qty <= 0) {
    $qty = 1;
  }
  if ($kd_sat === '') {
    $html = '<script>if(typeof popnew_error==="function"){popnew_error("Pilih satuan");}else{alert("Pilih satuan");}</script>';
  } else {
    $err = pesanEnsureHeader($connect, $no_po, $tgl_po, $kd_toko, $kd_sup, $ket);
    if ($err !== '') {
      $html = '<script>if(typeof popnew_error==="function"){popnew_error('.json_encode($err).');}else{alert('.json_encode($err).');}</script>';
    } else {
      $kd_brg = pesanNextKdBrgBaru($connect);
      if (!pesanInsertStubMasBrg($connect, $kd_brg, $nm_brg, $kd_sat, $nm_sat, $kd_toko)) {
        $html = '<script>if(typeof popnew_error==="function"){popnew_error("Gagal membuat master barang baru");}else{alert("Gagal membuat master barang baru");}</script>';
      } else {
        pesanUpsertItem($connect, $no_po, $tgl_po, $kd_toko, $kd_sup, $kd_brg, $nm_brg, $qty, $hrg, $kd_sat);
        pesanRefreshTotal($connect, $no_po, $tgl_po, $kd_toko);
        $msg = 'Barang baru '.$kd_brg.' masuk PO';
        $html = '<script>if(typeof popnew_ok==="function"){popnew_ok('.json_encode($msg).');}document.getElementById("fbrgbaru").style.display="none";document.getElementById("baru_nm_brg").value="";document.getElementById("baru_qty").value="1";document.getElementById("baru_hrg").value="";if(typeof carinota==="function"){carinota(1,true);}if(typeof carilistbrg==="function"){carilistbrg(1,true);}</script>';
      }
    }
  }
}

mysqli_close($connect);
ob_end_clean();
echo $html;
