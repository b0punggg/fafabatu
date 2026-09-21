<?php
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
$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
$filter_stok = isset($_POST['filter_stok']) ? $_POST['filter_stok'] : 'semua';
$no_po = isset($_POST['no_po']) ? strtoupper(trim($_POST['no_po'])) : '';
$tgl_po = isset($_POST['tgl_po']) ? trim($_POST['tgl_po']) : '';

$where_nm = ' WHERE 1=1 ';
if ($keyword !== '') {
  $kw = mysqli_real_escape_string($connect, '%'.$keyword.'%');
  $where_nm .= " AND (mas_brg.nm_brg LIKE '$kw' OR mas_brg.kd_brg LIKE '$kw' OR IFNULL(mas_brg.kd_bar,'') LIKE '$kw') ";
}

$filter_sql = '';
if ($filter_stok === 'ada_stok') {
  $filter_sql = ' AND IFNULL(stok.stok_juals,0) > 0 ';
} elseif ($filter_stok === 'stok_nol') {
  $filter_sql = ' AND IFNULL(stok.stok_juals,0) <= 0 ';
}

$from_sql = "FROM mas_brg
  LEFT JOIN (
    SELECT kd_brg, SUM(stok_jual) AS stok_juals
    FROM beli_brg
    WHERE kd_toko='$kd_toko'
    GROUP BY kd_brg
  ) stok ON mas_brg.kd_brg=stok.kd_brg
  LEFT JOIN kemas ON mas_brg.kd_kem1=kemas.no_urut
  $where_nm
  $filter_sql";

$sql = mysqli_query($connect, "SELECT mas_brg.kd_brg, mas_brg.nm_brg, mas_brg.kd_kem1, kemas.nm_sat1,
  IFNULL(stok.stok_juals,0) AS stok_juals
  $from_sql
  ORDER BY mas_brg.nm_brg ASC
  LIMIT $limit_start, $limit");

$sql2 = mysqli_query($connect, "SELECT COUNT(*) AS jumlah $from_sql");
$get_jumlah = $sql2 ? mysqli_fetch_array($sql2) : array('jumlah' => 0);
$jumlah_data = intval($get_jumlah['jumlah']);

$sudah = array();
if ($no_po !== '' && $tgl_po !== '') {
  $npe = mysqli_real_escape_string($connect, $no_po);
  $tpe = mysqli_real_escape_string($connect, $tgl_po);
  $qs = mysqli_query($connect, "SELECT kd_brg, qty_pesan FROM dum_pesan WHERE kd_toko='$kd_toko' AND no_po='$npe'");
  if ($qs) {
    while ($r = mysqli_fetch_assoc($qs)) {
      $sudah[$r['kd_brg']] = $r['qty_pesan'];
    }
    mysqli_free_result($qs);
  }
}
?>
<style>
  th { position: sticky; top: 0; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.4); border: 1px solid grey; padding: 3px; background: #d4e4f7; }
  td { padding: 2px 4px; border: 1px solid #ccc; }
</style>
<div class="table-responsive" style="overflow:auto;border-style:ridge;max-height:420px">
  <table class="table-hover hrf_arial" style="width:100%;border-collapse:collapse;white-space:nowrap;font-size:9pt">
    <tr align="middle">
      <th width="4%"><input type="checkbox" id="cek_all" onclick="pesanToggleAll(this)"></th>
      <th width="4%">No.</th>
      <th>NAMA BARANG</th>
      <th width="12%">STOK</th>
      <th width="10%">SATUAN</th>
      <th width="12%">HRG BELI</th>
      <th width="12%">QTY PESAN</th>
    </tr>
<?php
$no = $limit_start;
if ($sql) {
  while ($row = mysqli_fetch_assoc($sql)) {
    $no++;
    $kd = $row['kd_brg'];
    $last = pesanLastHargaBeli($connect, $kd, $kd_toko);
    $nmsat = $last['nm_sat'] !== '' ? $last['nm_sat'] : (isset($row['nm_sat1']) ? $row['nm_sat1'] : '');
    $in_po = isset($sudah[$kd]);
    $qty_val = $in_po ? $sudah[$kd] : '';
    $kd_h = htmlspecialchars($kd, ENT_QUOTES, 'UTF-8');
    $nm_h = htmlspecialchars($row['nm_brg'], ENT_QUOTES, 'UTF-8');
    $sat_h = htmlspecialchars($nmsat, ENT_QUOTES, 'UTF-8');
    $stok = floatval($row['stok_juals']);
    ?>
    <tr>
      <td align="center">
        <input type="checkbox" class="cek-brg" value="<?=$kd_h?>"
          data-nm="<?=$nm_h?>"
          <?php if ($in_po) echo 'checked'; ?>
          onchange="pesanCekRow(this)">
      </td>
      <td align="right"><?=$no.'.'?></td>
      <td><?=$nm_h?></td>
      <td align="center"><?=rtrim(rtrim(number_format($stok, 2, ',', '.'), '0'), ',')?> <?=$sat_h?></td>
      <td align="center"><?=$sat_h?></td>
      <td align="right"><?=pesanFmtUang($last['hrg_beli'])?></td>
      <td align="center">
        <input class="form-control qty-pesan-brg" type="number" min="0" step="any"
          value="<?=htmlspecialchars((string)$qty_val)?>"
          style="border:none;text-align:center;font-size:9pt;min-width:70px;background:transparent"
          onfocus="this.closest('tr').querySelector('.cek-brg').checked=true;">
      </td>
    </tr>
    <?php
  }
  mysqli_free_result($sql);
}
if ($no === $limit_start) {
  echo '<tr><td colspan="7" align="center">Tidak ada barang.</td></tr>';
}
?>
  </table>
</div>
<?php
$jumlah_page = $limit > 0 ? ceil(max(1, $jumlah_data) / $limit) : 1;
if ($jumlah_data === 0) { $jumlah_page = 1; }
?>
<div class="w3-border yz-theme-l5">
  <ul class="pagination justify-content-center hrf_res" style="margin:8px">
    <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carilistbrg(1,true)">First</a></li>
    <?php for ($i = max(1,$page-1); $i <= min($jumlah_page,$page+1); $i++) { ?>
      <li class="page-item <?=$i==$page?'active':''?>"><a class="page-link" href="javascript:void(0)" onclick="carilistbrg(<?=$i?>,true)"><?=$i?></a></li>
    <?php } ?>
    <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carilistbrg(<?=$jumlah_page?>,true)">Last</a></li>
  </ul>
</div>
<?php
if ($sql2) { mysqli_free_result($sql2); }
mysqli_close($connect);
$html = ob_get_contents();
ob_end_clean();
echo json_encode(array('hasil' => $html));
