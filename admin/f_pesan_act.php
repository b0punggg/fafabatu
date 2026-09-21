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
$raw     = isset($_POST['items']) ? $_POST['items'] : '[]';
$items   = json_decode($raw, true);
if (!is_array($items)) {
  $items = array();
}

$html = '';
if ($kd_toko === '' || $no_po === '' || $tgl_po === '' || $kd_sup === '') {
  $html = '<script>if(typeof popnew_error==="function"){popnew_error("No. PO, tanggal, dan supplier wajib diisi");}else{alert("No. PO, tanggal, dan supplier wajib diisi");}</script>';
} elseif (count($items) === 0) {
  $html = '<script>if(typeof popnew_error==="function"){popnew_error("Centang barang yang akan dipesan");}else{alert("Centang barang yang akan dipesan");}</script>';
} else {
  $err = pesanEnsureHeader($connect, $no_po, $tgl_po, $kd_toko, $kd_sup, $ket);
  if ($err !== '') {
    $html = '<script>if(typeof popnew_error==="function"){popnew_error('.json_encode($err).');}else{alert('.json_encode($err).');}</script>';
  } else {
    $n = 0;
    foreach ($items as $it) {
      $kd_brg = strtoupper(trim(isset($it['kd_brg']) ? $it['kd_brg'] : ''));
      $nm_brg = strtoupper(trim(isset($it['nm_brg']) ? $it['nm_brg'] : ''));
      $qty = pesanToNum(isset($it['qty']) ? $it['qty'] : 0);
      if ($kd_brg === '') {
        continue;
      }
      if ($qty <= 0) {
        $qty = 1;
      }
      pesanUpsertItem($connect, $no_po, $tgl_po, $kd_toko, $kd_sup, $kd_brg, $nm_brg, $qty);
      $n++;
    }
    pesanRefreshTotal($connect, $no_po, $tgl_po, $kd_toko);
    $html = '<script>if(typeof popnew_ok==="function"){popnew_ok("'.$n.' barang masuk PO");}if(typeof carinota==="function"){carinota(1,true);}if(typeof carilistbrg==="function"){carilistbrg(1,true);}</script>';
  }
}

mysqli_close($connect);
ob_end_clean();
echo $html;
