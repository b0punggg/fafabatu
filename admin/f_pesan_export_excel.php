<?php
ob_start();
if (!session_id()) session_start();
include 'cekmasuk.php';
include 'config.php';
require_once 'f_pesan_helper.php';
$connect = opendtcek();
ensurePesanTables($connect);

$kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($connect, $_SESSION['id_toko']) : '';
$no_po = isset($_GET['no_po']) ? strtoupper(trim($_GET['no_po'])) : '';
$tgl_po = isset($_GET['tgl']) ? trim($_GET['tgl']) : '';
if ($kd_toko === '' || $no_po === '' || $tgl_po === '') {
  exit('No. PO / tanggal tidak lengkap');
}
$no_po_esc = mysqli_real_escape_string($connect, $no_po);
$tgl_esc = mysqli_real_escape_string($connect, $tgl_po);

$nm_sup = '';
$qh = mysqli_query($connect, "SELECT supplier.nm_sup FROM mas_pesan LEFT JOIN supplier ON mas_pesan.kd_sup=supplier.kd_sup
  WHERE mas_pesan.kd_toko='$kd_toko' AND mas_pesan.no_po='$no_po_esc' AND mas_pesan.tgl_po='$tgl_esc' LIMIT 1");
if ($qh && mysqli_num_rows($qh) > 0) {
  $h = mysqli_fetch_assoc($qh);
  $nm_sup = $h['nm_sup'];
  mysqli_free_result($qh);
} elseif ($qh) {
  mysqli_free_result($qh);
}

$q = mysqli_query($connect, "SELECT dum_pesan.*, kemas.nm_sat1 FROM dum_pesan
  LEFT JOIN kemas ON dum_pesan.kd_sat=kemas.no_urut
  WHERE dum_pesan.kd_toko='$kd_toko' AND dum_pesan.no_po='$no_po_esc' AND dum_pesan.tgl_po='$tgl_esc'
  ORDER BY dum_pesan.no_urut ASC");

$filename = 'Pemesanan_'.$no_po.'_'.date('Ymd_His').'.xls';
ob_clean();
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename='.$filename);
header('Pragma: no-cache');
header('Expires: 0');

echo "<table border='1'>";
echo "<tr><th colspan='7' style='font-size:14pt'>Pemesanan Barang</th></tr>";
echo "<tr><th colspan='7' style='text-align:left'>No. PO: ".htmlspecialchars($no_po)." | Tanggal: ".pesanFmtTgl($tgl_po)." | Supplier: ".htmlspecialchars($nm_sup)."</th></tr>";
echo "<tr style='background:#f2f2f2;font-weight:bold'><th>No.</th><th>Kode Barang</th><th>Nama Barang</th><th>Qty</th><th>Satuan</th><th>Harga Beli</th><th>Jumlah</th></tr>";

$no = 0;
$tot = 0;
if ($q) {
  while ($row = mysqli_fetch_assoc($q)) {
    $no++;
    $tot += floatval($row['jumlah']);
    echo "<tr>";
    echo "<td align='right'>".$no."</td>";
    echo "<td>".htmlspecialchars($row['kd_brg'])."</td>";
    echo "<td>".htmlspecialchars($row['nm_brg'])."</td>";
    echo "<td align='center'>".$row['qty_pesan']."</td>";
    echo "<td>".htmlspecialchars(isset($row['nm_sat1']) ? $row['nm_sat1'] : '')."</td>";
    echo "<td align='right'>".pesanFmtUang($row['hrg_beli'])."</td>";
    echo "<td align='right'>".pesanFmtUang($row['jumlah'])."</td>";
    echo "</tr>";
  }
  mysqli_free_result($q);
}
if ($no === 0) {
  echo "<tr><td colspan='7' align='center'>Tidak ada item.</td></tr>";
} else {
  echo "<tr><th colspan='6' align='right'>TOTAL</th><th align='right'>".pesanFmtUang($tot)."</th></tr>";
}
echo "</table>";
mysqli_close($connect);
exit;
