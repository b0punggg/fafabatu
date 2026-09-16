<?php
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

$head = null;
$qh = mysqli_query($connect, "SELECT mas_pesan.*, supplier.nm_sup FROM mas_pesan
  LEFT JOIN supplier ON mas_pesan.kd_sup=supplier.kd_sup
  WHERE mas_pesan.kd_toko='$kd_toko' AND mas_pesan.no_po='$no_po_esc' AND mas_pesan.tgl_po='$tgl_esc' LIMIT 1");
if ($qh && mysqli_num_rows($qh) > 0) {
  $head = mysqli_fetch_assoc($qh);
}
if ($qh) { mysqli_free_result($qh); }

$nm_toko = '';
$al_toko = '';
$cektoko = mysqli_query($connect, "SELECT * FROM toko WHERE kd_toko='$kd_toko'");
if ($cektoko && mysqli_num_rows($cektoko) > 0) {
  $tok = mysqli_fetch_assoc($cektoko);
  $nm_toko = $tok['nm_toko'];
  $al_toko = $tok['al_toko'];
  mysqli_free_result($cektoko);
}
$q = mysqli_query($connect, "SELECT dum_pesan.*, kemas.nm_sat1 FROM dum_pesan
  LEFT JOIN kemas ON dum_pesan.kd_sat=kemas.no_urut
  WHERE dum_pesan.kd_toko='$kd_toko' AND dum_pesan.no_po='$no_po_esc' AND dum_pesan.tgl_po='$tgl_esc'
  ORDER BY dum_pesan.no_urut ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Pemesanan <?php echo htmlspecialchars($no_po); ?></title>
  <link rel="stylesheet" href="../assets/css/paper.css">
  <link rel="stylesheet" href="../assets/css/w3.css">
  <link rel="stylesheet" href="../assets/css/blue-themes.css">
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <style>
    th { text-align:center; border:solid 1px #113300; font-size:10pt; }
    td { border:solid 1px #113300; font-size:10pt; border-left:none; border-right:none; border-top:none; }
    @media print { #printPageButton { display:none; } }
  </style>
</head>
<body class="F4">
  <section class="sheet padding-10mm">
    <table cellspacing="0" style="width:100%">
      <thead>
        <tr><td colspan="6" style="text-align:center;font-size:13pt;border:none"><b><?=htmlspecialchars($nm_toko)?></b></td></tr>
        <tr><td colspan="6" style="text-align:center;font-size:11pt;border:none"><b><?=htmlspecialchars($al_toko)?></b></td></tr>
        <tr><td colspan="6" style="border:none">&nbsp;</td></tr>
        <tr><td colspan="6" style="text-align:left;border:none"><b>Pemesanan barang No. <?php echo htmlspecialchars($no_po); ?> tanggal <?php echo pesanFmtTgl($tgl_po); ?></b></td></tr>
        <tr><td colspan="6" style="text-align:left;border:none"><b>Supplier: <?php echo htmlspecialchars($head && isset($head['nm_sup']) ? $head['nm_sup'] : '-'); ?></b></td></tr>
        <tr class="yz-theme-l3">
          <th style="width:5%">NO</th>
          <th>NAMA BARANG</th>
          <th style="width:10%">QTY</th>
          <th style="width:12%">SATUAN</th>
          <th style="width:15%">HARGA BELI</th>
          <th style="width:15%">JUMLAH</th>
        </tr>
      </thead>
      <tbody>
<?php
$no = 0;
$tot = 0;
if ($q) {
  while ($row = mysqli_fetch_assoc($q)) {
    $no++;
    $tot += floatval($row['jumlah']);
    ?>
        <tr>
          <td style="text-align:right;border-left:1px solid"><?php echo $no.'.'; ?></td>
          <td><?php echo htmlspecialchars($row['nm_brg']); ?></td>
          <td style="text-align:center"><?php echo $row['qty_pesan']; ?></td>
          <td style="text-align:center"><?php echo htmlspecialchars(isset($row['nm_sat1']) ? $row['nm_sat1'] : ''); ?></td>
          <td style="text-align:right"><?php echo pesanFmtUang($row['hrg_beli']); ?></td>
          <td style="text-align:right;border-right:1px solid"><?php echo pesanFmtUang($row['jumlah']); ?></td>
        </tr>
    <?php
  }
  mysqli_free_result($q);
}
?>
        <tr class="yz-theme-l3">
          <th colspan="5">TOTAL PEMESANAN</th>
          <th style="text-align:right"><?php echo pesanFmtUang($tot); ?></th>
        </tr>
      </tbody>
    </table>
    <div class="w3-center">
      <button id="printPageButton" class="btn btn-sm btn-success w3-margin-top" onclick="window.print()">Cetak PDF</button>
    </div>
  </section>
</body>
</html>
<?php mysqli_close($connect); ?>
