<?php
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
ob_start();
include 'config.php';
session_start();
require_once 'f_pesan_helper.php';
$connect = opendtcek();
ensurePesanTables($connect);
$kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($connect, $_SESSION['id_toko']) : '';
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
if ($page < 1) { $page = 1; }
$limit = 15;
$limit_start = ($page - 1) * $limit;
$params = mysqli_real_escape_string($connect, $keyword);
$pecah = explode(';', $params);
$no_po = isset($pecah[0]) ? strtoupper($pecah[0]) : '';
$tgl_po = isset($pecah[1]) && $pecah[1] !== '' ? $pecah[1] : '0000-00-00';
$cari = isset($pecah[2]) ? $pecah[2] : '';

$where = "dum_pesan.kd_toko='$kd_toko' AND dum_pesan.no_po='$no_po' AND dum_pesan.tgl_po='$tgl_po'";
if ($cari !== '') {
  $c = mysqli_real_escape_string($connect, '%'.$cari.'%');
  $where .= " AND dum_pesan.nm_brg LIKE '$c'";
}

$sql = mysqli_query($connect, "SELECT dum_pesan.*, kemas.nm_sat1 FROM dum_pesan
  LEFT JOIN kemas ON dum_pesan.kd_sat=kemas.no_urut
  WHERE $where ORDER BY dum_pesan.no_urut ASC LIMIT $limit_start, $limit");
$sql2 = mysqli_query($connect, "SELECT COUNT(*) AS jumlah FROM dum_pesan WHERE $where");
$get_jumlah = $sql2 ? mysqli_fetch_array($sql2) : array('jumlah' => 0);
$jumlah_data = intval($get_jumlah['jumlah']);
?>
<style>
  th { position: sticky; top: 0px; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.4); border: 1px solid grey; padding: 3px; }
  td { padding: 1px; }
</style>
<div class="table-responsive" style="overflow-y:auto;overflow-x:auto;border-style:ridge;">
  <table class="table-stripe table-hover hrf_arial" style="width:100%;border-collapse:collapse;white-space:nowrap;">
    <tr align="middle" class="yz-theme-l4">
      <th width="3%">No.</th>
      <th>NAMA BARANG</th>
      <th width="8%">QTY</th>
      <th width="10%">SATUAN</th>
      <th width="12%">HARGA BELI</th>
      <th width="12%">JUMLAH</th>
      <th width="6%">OPSI</th>
    </tr>
<?php
$no = $limit_start;
$gtot = 0;
$qtot = mysqli_query($connect, "SELECT SUM(jumlah) AS tot FROM dum_pesan WHERE dum_pesan.kd_toko='$kd_toko' AND dum_pesan.no_po='$no_po' AND dum_pesan.tgl_po='$tgl_po'");
if ($qtot) {
  $rt = mysqli_fetch_assoc($qtot);
  $gtot = floatval($rt['tot']);
  mysqli_free_result($qtot);
}
if ($sql) {
  while ($data = mysqli_fetch_assoc($sql)) {
    $no++;
    $param = $data['no_urut'];
    $nm = htmlspecialchars($data['nm_brg'], ENT_QUOTES, 'UTF-8');
    $nmsat = htmlspecialchars(isset($data['nm_sat1']) ? $data['nm_sat1'] : '', ENT_QUOTES, 'UTF-8');
    ?>
    <tr>
      <td align="right"><?php echo $no.'.'; ?></td>
      <td align="left">&nbsp;<?php echo $nm; ?></td>
      <td align="center"><?php echo $data['qty_pesan']; ?></td>
      <td align="center"><?php echo $nmsat; ?></td>
      <td align="right"><?php echo pesanFmtUang($data['hrg_beli']); ?>&nbsp;</td>
      <td align="right"><?php echo pesanFmtUang($data['jumlah']); ?>&nbsp;</td>
      <td align="center">
        <button type="button" class="btn btn-sm btn-danger fa fa-trash" title="Hapus" onclick="if(confirm('Hapus item ini?')){delrec('<?=$param?>')}"></button>
      </td>
    </tr>
    <?php
  }
  mysqli_free_result($sql);
}
?>
    <tr class="yz-theme-l4">
      <th colspan="5" style="text-align:right">TOTAL PEMESANAN</th>
      <th style="text-align:right"><?php echo pesanFmtUang($gtot); ?>&nbsp;</th>
      <th></th>
    </tr>
  </table>
</div>
<?php
$jumlah_page = $limit > 0 ? ceil($jumlah_data / $limit) : 1;
if ($jumlah_page < 1) { $jumlah_page = 1; }
?>
<div class="w3-border yz-theme-l5">
  <nav style="margin-top:10px;">
    <ul class="pagination justify-content-center hrf_res">
      <?php if ($page <= 1) { ?>
        <li class="page-item disabled"><a class="page-link">First</a></li>
      <?php } else { ?>
        <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carinota(1,true)">First</a></li>
      <?php } ?>
      <?php
      $start = max(1, $page - 1);
      $end = min($jumlah_page, $page + 1);
      for ($i = $start; $i <= $end; $i++) {
        $cls = ($i == $page) ? 'active' : '';
        echo '<li class="page-item '.$cls.'"><a class="page-link" href="javascript:void(0)" onclick="carinota('.$i.',true)">'.$i.'</a></li>';
      }
      ?>
      <?php if ($page >= $jumlah_page) { ?>
        <li class="page-item disabled"><a class="page-link">Last</a></li>
      <?php } else { ?>
        <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carinota(<?php echo $jumlah_page; ?>,true)">Last</a></li>
      <?php } ?>
    </ul>
  </nav>
</div>
<?php
if ($sql2) { mysqli_free_result($sql2); }
mysqli_close($connect);
$html = ob_get_contents();
ob_end_clean();
echo json_encode(array('hasil' => $html));
