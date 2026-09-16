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
$limit = 12;
$limit_start = ($page - 1) * $limit;
$params = mysqli_real_escape_string($connect, $keyword);
$param = '%'.$params.'%';

if ($params === '') {
  $sql1 = mysqli_query($connect, "SELECT mas_pesan.*, supplier.nm_sup FROM mas_pesan
    LEFT JOIN supplier ON mas_pesan.kd_sup=supplier.kd_sup
    WHERE mas_pesan.kd_toko='$kd_toko' ORDER BY mas_pesan.tgl_po DESC, mas_pesan.no_urut DESC LIMIT $limit_start, $limit");
  $sql2 = mysqli_query($connect, "SELECT COUNT(*) AS jumlah FROM mas_pesan WHERE kd_toko='$kd_toko'");
} else {
  $sql1 = mysqli_query($connect, "SELECT mas_pesan.*, supplier.nm_sup FROM mas_pesan
    LEFT JOIN supplier ON mas_pesan.kd_sup=supplier.kd_sup
    WHERE mas_pesan.kd_toko='$kd_toko' AND mas_pesan.no_po LIKE '$param' ORDER BY mas_pesan.tgl_po DESC LIMIT $limit_start, $limit");
  $sql2 = mysqli_query($connect, "SELECT COUNT(*) AS jumlah FROM mas_pesan WHERE kd_toko='$kd_toko' AND no_po LIKE '$param'");
}
$get_jumlah = $sql2 ? mysqli_fetch_array($sql2) : array('jumlah' => 0);
?>
<div class="table-responsive" style="overflow-y:auto;max-height:420px;border-style:ridge">
  <table class="table-hover" style="font-size:9pt;width:100%">
    <tr align="middle" class="yz-theme-l1">
      <th>NO.</th>
      <th>NO. PO</th>
      <th>TGL. PO</th>
      <th>SUPPLIER</th>
      <th>TOTAL</th>
      <th width="2%">OPSI</th>
    </tr>
<?php
$no = $limit_start;
if ($sql1) {
  while ($data = mysqli_fetch_assoc($sql1)) {
    $no++;
    $nopo = htmlspecialchars($data['no_po'], ENT_QUOTES, 'UTF-8');
    $tgl = htmlspecialchars($data['tgl_po'], ENT_QUOTES, 'UTF-8');
    $kdsup = htmlspecialchars($data['kd_sup'], ENT_QUOTES, 'UTF-8');
    $nmsup = htmlspecialchars(isset($data['nm_sup']) ? $data['nm_sup'] : '', ENT_QUOTES, 'UTF-8');
    $ket = htmlspecialchars(isset($data['ket']) ? $data['ket'] : '', ENT_QUOTES, 'UTF-8');
    ?>
    <tr>
      <td align="right"><?=$no?></td>
      <td align="center"><?=$nopo?></td>
      <td align="center"><?=pesanFmtTgl($data['tgl_po'])?></td>
      <td align="center"><?=$nmsup?></td>
      <td align="right"><?=pesanFmtUang($data['tot_pesan'])?></td>
      <td>
        <button type="button" class="btn btn-sm btn-primary fa fa-edit" onclick="
          document.getElementById('no_po').value='<?=$nopo?>';
          document.getElementById('tgl_po').value='<?=$tgl?>';
          document.getElementById('kd_sup').value='<?=$kdsup?>';
          document.getElementById('nm_sup').value='<?=$nmsup?>';
          document.getElementById('ket').value='<?=$ket?>';
          document.getElementById('fnotapesan').style.display='none';
          carinota(1,true);
        "></button>
      </td>
    </tr>
    <?php
  }
  mysqli_free_result($sql1);
}
?>
  </table>
</div>
<?php
$jumlah_page = ceil(max(1, intval($get_jumlah['jumlah'])) / $limit);
?>
<nav>
  <ul class="pagination justify-content-center">
    <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carinopo(1,true)">First</a></li>
    <?php for ($i = max(1,$page-1); $i <= min($jumlah_page,$page+1); $i++) { ?>
      <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carinopo(<?=$i?>,true)"><?=$i?></a></li>
    <?php } ?>
    <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carinopo(<?=$jumlah_page?>,true)">Last</a></li>
  </ul>
</nav>
<?php
if ($sql2) { mysqli_free_result($sql2); }
mysqli_close($connect);
$html = ob_get_contents();
ob_end_clean();
echo json_encode(array('hasil' => $html));
