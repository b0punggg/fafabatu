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
$limit = 8;
$limit_start = ($page - 1) * $limit;
$params = mysqli_real_escape_string($connect, $keyword);
$param = '%'.$params.'%';

if ($params === '') {
  $sql1 = mysqli_query($connect, "SELECT no_urut,kd_brg,nm_brg FROM mas_brg ORDER BY nm_brg ASC LIMIT $limit_start, $limit");
  $sql2 = mysqli_query($connect, "SELECT COUNT(*) AS jumlah FROM mas_brg");
} else {
  $sql1 = mysqli_query($connect, "SELECT no_urut,kd_brg,nm_brg FROM mas_brg WHERE nm_brg LIKE '$param' ORDER BY nm_brg ASC LIMIT $limit_start, $limit");
  $sql2 = mysqli_query($connect, "SELECT COUNT(*) AS jumlah FROM mas_brg WHERE nm_brg LIKE '$param'");
}
$get_jumlah = $sql2 ? mysqli_fetch_array($sql2) : array('jumlah' => 0);
?>
<div class="table-responsive" style="overflow-y:auto;max-height:220px;border-style:ridge">
  <table class="table-bordered table-striped table-hover hrf_res" style="width:100%">
    <tr align="middle" class="yz-theme-l3">
      <th>NAMA BARANG</th>
      <th style="width:1%">OPSI</th>
    </tr>
<?php
$no = 0;
if ($sql1) {
  while ($row = mysqli_fetch_assoc($sql1)) {
    $no++;
    $last = pesanLastHargaBeli($connect, $row['kd_brg'], $kd_toko);
    $kd = htmlspecialchars($row['kd_brg'], ENT_QUOTES, 'UTF-8');
    $nm = htmlspecialchars($row['nm_brg'], ENT_QUOTES, 'UTF-8');
    $kdsat = htmlspecialchars($last['kd_sat'], ENT_QUOTES, 'UTF-8');
    $nmsat = htmlspecialchars($last['nm_sat'], ENT_QUOTES, 'UTF-8');
    $hrg = pesanFmtUang($last['hrg_beli']);
    ?>
    <tr>
      <td style="cursor:pointer" onclick="document.getElementById('btnbrg<?=$no?>').click()"><?=$nm?></td>
      <td>
        <button type="button" id="btnbrg<?=$no?>" class="btn btn-sm btn-primary fa fa-edit" onclick="
          document.getElementById('kd_brg').value='<?=$kd?>';
          document.getElementById('nm_brg').value='<?=$nm?>';
          document.getElementById('kd_sat').value='<?=$kdsat?>';
          document.getElementById('nm_sat').value='<?=$nmsat?>';
          document.getElementById('hrg_beli').value='<?=$hrg?>';
          document.getElementById('qty_pesan').focus();
          document.getElementById('boxnmbrg').style.display='none';
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
<div class="w3-border yz-theme-l5">
  <ul class="pagination justify-content-center hrf_res" style="margin:8px">
    <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carinmbrg(1,true)">First</a></li>
    <?php for ($i = max(1,$page-1); $i <= min($jumlah_page,$page+1); $i++) { ?>
      <li class="page-item <?=$i==$page?'active':''?>"><a class="page-link" href="javascript:void(0)" onclick="carinmbrg(<?=$i?>,true)"><?=$i?></a></li>
    <?php } ?>
    <li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="carinmbrg(<?=$jumlah_page?>,true)">Last</a></li>
  </ul>
</div>
<?php
if ($sql2) { mysqli_free_result($sql2); }
mysqli_close($connect);
$html = ob_get_contents();
ob_end_clean();
echo json_encode(array('hasil' => $html));
