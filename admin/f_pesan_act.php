<?php
ob_start();
include 'config.php';
session_start();
require_once 'f_pesan_helper.php';
$connect = opendtcek();
ensurePesanTables($connect);

$kd_toko = isset($_SESSION['id_toko']) ? $_SESSION['id_toko'] : '';
$no_urut = isset($_POST['no_urut']) ? trim($_POST['no_urut']) : '';
$tgl_po  = isset($_POST['tgl_po']) ? $_POST['tgl_po'] : date('Y-m-d');
$no_po   = strtoupper(trim(isset($_POST['no_po']) ? $_POST['no_po'] : ''));
$kd_sup  = isset($_POST['kd_sup']) ? trim($_POST['kd_sup']) : '';
$ket     = strtoupper(trim(isset($_POST['ket']) ? $_POST['ket'] : ''));
$kd_brg  = strtoupper(trim(isset($_POST['kd_brg']) ? $_POST['kd_brg'] : ''));
$nm_brg  = strtoupper(trim(isset($_POST['nm_brg']) ? $_POST['nm_brg'] : ''));
$kd_sat  = isset($_POST['kd_sat']) ? trim($_POST['kd_sat']) : '';
$qty     = pesanToNum(isset($_POST['qty_pesan']) ? $_POST['qty_pesan'] : 0);
$hrg     = pesanToNum(isset($_POST['hrg_beli']) ? $_POST['hrg_beli'] : 0);

$html = '';
if ($kd_toko === '' || $no_po === '' || $tgl_po === '' || $kd_sup === '') {
  $html = '<script>if(typeof popnew_error==="function"){popnew_error("No. PO, tanggal, dan supplier wajib diisi");}else{alert("No. PO, tanggal, dan supplier wajib diisi");}</script>';
} elseif ($kd_brg === '' || $qty <= 0) {
  $html = '<script>if(typeof popnew_error==="function"){popnew_error("Pilih barang dan isi qty");}else{alert("Pilih barang dan isi qty");}</script>';
} else {
  $kd_toko_esc = mysqli_real_escape_string($connect, $kd_toko);
  $no_po_esc = mysqli_real_escape_string($connect, $no_po);
  $tgl_po_esc = mysqli_real_escape_string($connect, $tgl_po);
  $kd_sup_esc = mysqli_real_escape_string($connect, $kd_sup);
  $ket_esc = mysqli_real_escape_string($connect, $ket);
  $kd_brg_esc = mysqli_real_escape_string($connect, $kd_brg);
  $nm_brg_esc = mysqli_real_escape_string($connect, $nm_brg);
  $kd_sat_esc = mysqli_real_escape_string($connect, $kd_sat);

  $last = pesanLastHargaBeli($connect, $kd_brg, $kd_toko);
  if ($hrg <= 0) {
    $hrg = floatval($last['hrg_beli']);
  }
  if ($kd_sat === '') {
    $kd_sat = $last['kd_sat'];
    $kd_sat_esc = mysqli_real_escape_string($connect, $kd_sat);
  }
  $jumlah = round($qty * $hrg, 2);

  $cekhead = mysqli_query($connect, "SELECT * FROM mas_pesan WHERE no_po='$no_po_esc' AND kd_toko='$kd_toko_esc'");
  if ($cekhead && mysqli_num_rows($cekhead) > 0) {
    $head = mysqli_fetch_assoc($cekhead);
    if ($head['kd_sup'] !== '' && $head['kd_sup'] !== $kd_sup) {
      $html = '<script>if(typeof popnew_error==="function"){popnew_error("Satu PO hanya untuk satu supplier");}else{alert("Satu PO hanya untuk satu supplier");}</script>';
    } else {
      mysqli_query($connect, "UPDATE mas_pesan SET tgl_po='$tgl_po_esc', kd_sup='$kd_sup_esc', ket='$ket_esc' WHERE no_po='$no_po_esc' AND kd_toko='$kd_toko_esc'");
      mysqli_query($connect, "UPDATE dum_pesan SET tgl_po='$tgl_po_esc', kd_sup='$kd_sup_esc' WHERE no_po='$no_po_esc' AND kd_toko='$kd_toko_esc'");
    }
    mysqli_free_result($cekhead);
  } else {
    $now = date('Y-m-d H:i:s');
    mysqli_query($connect, "INSERT INTO mas_pesan (tgl_po,no_po,kd_toko,kd_sup,ket,tot_pesan,status,execut) VALUES('$tgl_po_esc','$no_po_esc','$kd_toko_esc','$kd_sup_esc','$ket_esc','0','DRAFT','$now')");
  }

  if ($html === '') {
    $no_urut_esc = mysqli_real_escape_string($connect, $no_urut);
    if ($no_urut !== '') {
      mysqli_query($connect, "UPDATE dum_pesan SET kd_sup='$kd_sup_esc', kd_brg='$kd_brg_esc', nm_brg='$nm_brg_esc', kd_sat='$kd_sat_esc', qty_pesan='$qty', hrg_beli='$hrg', jumlah='$jumlah' WHERE no_urut='$no_urut_esc' AND kd_toko='$kd_toko_esc'");
    } else {
      $cekitem = mysqli_query($connect, "SELECT no_urut FROM dum_pesan WHERE no_po='$no_po_esc' AND tgl_po='$tgl_po_esc' AND kd_toko='$kd_toko_esc' AND kd_brg='$kd_brg_esc' LIMIT 1");
      if ($cekitem && mysqli_num_rows($cekitem) > 0) {
        $it = mysqli_fetch_assoc($cekitem);
        $idu = intval($it['no_urut']);
        mysqli_query($connect, "UPDATE dum_pesan SET kd_sup='$kd_sup_esc', nm_brg='$nm_brg_esc', kd_sat='$kd_sat_esc', qty_pesan='$qty', hrg_beli='$hrg', jumlah='$jumlah' WHERE no_urut='$idu'");
        mysqli_free_result($cekitem);
      } else {
        if ($cekitem) { mysqli_free_result($cekitem); }
        mysqli_query($connect, "INSERT INTO dum_pesan (no_po,tgl_po,kd_toko,kd_sup,kd_brg,nm_brg,kd_sat,qty_pesan,hrg_beli,jumlah) VALUES('$no_po_esc','$tgl_po_esc','$kd_toko_esc','$kd_sup_esc','$kd_brg_esc','$nm_brg_esc','$kd_sat_esc','$qty','$hrg','$jumlah')");
      }
    }
    pesanRefreshTotal($connect, $no_po, $tgl_po, $kd_toko);
    $html = '<script>if(typeof popnew_ok==="function"){popnew_ok("Item pemesanan disimpan");}document.getElementById("no_urut").value="";document.getElementById("kd_brg").value="";document.getElementById("nm_brg").value="";document.getElementById("kd_sat").value="";document.getElementById("nm_sat").value="";document.getElementById("qty_pesan").value="";document.getElementById("hrg_beli").value="";if(typeof carinota==="function"){carinota(1,true);}</script>';
  }
}

mysqli_close($connect);
ob_end_clean();
echo $html;
