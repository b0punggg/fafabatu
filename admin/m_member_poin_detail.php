<?php
if(!session_id()) session_start();
include 'config.php';

header('Content-Type: application/json; charset=UTF-8');

$connect = opendtcek();
if(!$connect){
  echo json_encode(array('success'=>false,'pesan'=>'Koneksi database gagal'));
  exit;
}

$kd_toko = isset($_SESSION['id_toko']) ? mysqli_real_escape_string($connect, $_SESSION['id_toko']) : '';
$kd_member = isset($_POST['kd_member']) ? mysqli_real_escape_string($connect, trim($_POST['kd_member'])) : '';

if($kd_toko === '' || $kd_member === ''){
  echo json_encode(array('success'=>false,'pesan'=>'Data member tidak lengkap'));
  exit;
}

mysqli_query($connect, "CREATE TABLE IF NOT EXISTS member_poin_history (
  no_urut INT NOT NULL AUTO_INCREMENT,
  kd_member VARCHAR(50) NOT NULL,
  no_fakjual VARCHAR(50) NOT NULL,
  tgl_transaksi DATE NOT NULL,
  poin_masuk DECIMAL(15,2) DEFAULT 0.00,
  poin_keluar DECIMAL(15,2) DEFAULT 0.00,
  poin_saldo DECIMAL(15,2) DEFAULT 0.00,
  keterangan TEXT,
  kd_toko VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (no_urut),
  KEY idx_kd_member (kd_member),
  KEY idx_kd_toko (kd_toko)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$qmember = mysqli_query($connect, "SELECT kd_member,nm_member,poin FROM member WHERE kd_member='$kd_member' AND kd_toko='$kd_toko' LIMIT 1");
if(!$qmember || mysqli_num_rows($qmember) < 1){
  echo json_encode(array('success'=>false,'pesan'=>'Member tidak ditemukan'));
  exit;
}
$member = mysqli_fetch_assoc($qmember);
mysqli_free_result($qmember);

$nm_member = isset($member['nm_member']) ? $member['nm_member'] : '';
$poin_saldo = isset($member['poin']) ? floatval($member['poin']) : 0;

$history = array();
$history_tukar = array();
$total_masuk = 0;
$total_keluar = 0;
$qh = mysqli_query($connect, "SELECT tgl_transaksi,no_fakjual,poin_masuk,poin_keluar,poin_saldo,keterangan
  FROM member_poin_history
  WHERE kd_member='$kd_member' AND kd_toko='$kd_toko'
  ORDER BY created_at DESC, no_urut DESC");
if($qh){
  while($row = mysqli_fetch_assoc($qh)){
    $masuk = floatval($row['poin_masuk']);
    $keluar = floatval($row['poin_keluar']);
    if($masuk > 0){
      $total_masuk += $masuk;
      $history[] = $row;
    }
    if($keluar > 0){
      $total_keluar += $keluar;
      $history_tukar[] = $row;
    }
  }
  mysqli_free_result($qh);
}

ob_start();
?>
<div style="padding: 12px 8px 18px 8px;font-size:10pt">
  <table class="table table-bordered" style="font-size:10pt;margin-bottom:12px">
    <tr>
      <td width="28%"><b>Kode Member</b></td>
      <td><?=htmlspecialchars($member['kd_member'])?></td>
    </tr>
    <tr>
      <td><b>Nama Member</b></td>
      <td><?=htmlspecialchars($nm_member)?></td>
    </tr>
    <tr>
      <td><b>Poin saldo</b></td>
      <td style="font-weight:bold;color:#ff6b00;font-size:12pt"><?=number_format($poin_saldo, 0, ',', '.')?></td>
    </tr>
    <tr>
      <td><b>Total poin didapat</b></td>
      <td><?=number_format($total_masuk, 0, ',', '.')?></td>
    </tr>
    <tr>
      <td><b>Total poin ditukar</b></td>
      <td><?=number_format($total_keluar, 0, ',', '.')?></td>
    </tr>
  </table>

  <div style="margin-bottom:8px"><b>Riwayat poin didapat</b> <small>(poin masuk, tidak termasuk tukar poin)</small></div>
  <div class="table-responsive" style="max-height:320px;overflow:auto">
    <table class="table table-bordered table-sm table-hover" style="font-size:9pt;margin-bottom:0">
      <tr class="yz-theme-l3" align="middle">
        <th width="5%">No</th>
        <th width="14%">Tanggal</th>
        <th width="20%">No. Nota</th>
        <th width="14%">Poin didapat</th>
        <th width="14%">Saldo saat itu</th>
        <th>Keterangan</th>
      </tr>
      <?php
      if(count($history) === 0){
        ?>
        <tr>
          <td colspan="6" align="center" style="padding:14px">Belum ada riwayat poin didapat.</td>
        </tr>
        <?php
      } else {
        $no = 0;
        foreach($history as $h){
          $no++;
          $tgl = isset($h['tgl_transaksi']) ? $h['tgl_transaksi'] : '';
          $tgl_txt = ($tgl !== '' && $tgl !== '0000-00-00') ? date('d-m-Y', strtotime($tgl)) : '-';
          ?>
          <tr>
            <td align="right"><?=$no?>.</td>
            <td align="center"><?=htmlspecialchars($tgl_txt)?></td>
            <td><?=htmlspecialchars(isset($h['no_fakjual']) ? $h['no_fakjual'] : '')?></td>
            <td align="right"><?=number_format(floatval($h['poin_masuk']), 0, ',', '.')?></td>
            <td align="right"><?=number_format(floatval($h['poin_saldo']), 0, ',', '.')?></td>
            <td><?=htmlspecialchars(isset($h['keterangan']) ? $h['keterangan'] : '')?></td>
          </tr>
          <?php
        }
      }
      ?>
    </table>
  </div>

  <div style="margin:14px 0 8px 0"><b>Riwayat tukar poin</b> <small>(poin keluar)</small></div>
  <div class="table-responsive" style="max-height:320px;overflow:auto">
    <table class="table table-bordered table-sm table-hover" style="font-size:9pt;margin-bottom:0">
      <tr class="yz-theme-l3" align="middle">
        <th width="5%">No</th>
        <th width="14%">Tanggal</th>
        <th width="20%">No. Nota / Tukar</th>
        <th width="14%">Poin ditukar</th>
        <th width="14%">Saldo saat itu</th>
        <th>Keterangan</th>
      </tr>
      <?php
      if(count($history_tukar) === 0){
        ?>
        <tr>
          <td colspan="6" align="center" style="padding:14px">Belum ada riwayat tukar poin.</td>
        </tr>
        <?php
      } else {
        $no = 0;
        foreach($history_tukar as $h){
          $no++;
          $tgl = isset($h['tgl_transaksi']) ? $h['tgl_transaksi'] : '';
          $tgl_txt = ($tgl !== '' && $tgl !== '0000-00-00') ? date('d-m-Y', strtotime($tgl)) : '-';
          ?>
          <tr>
            <td align="right"><?=$no?>.</td>
            <td align="center"><?=htmlspecialchars($tgl_txt)?></td>
            <td><?=htmlspecialchars(isset($h['no_fakjual']) ? $h['no_fakjual'] : '')?></td>
            <td align="right"><?=number_format(floatval($h['poin_keluar']), 0, ',', '.')?></td>
            <td align="right"><?=number_format(floatval($h['poin_saldo']), 0, ',', '.')?></td>
            <td><?=htmlspecialchars(isset($h['keterangan']) ? $h['keterangan'] : '')?></td>
          </tr>
          <?php
        }
      }
      ?>
    </table>
  </div>
  <div style="text-align:right;margin-top:14px">
    <button type="button" class="btn btn-primary" onclick="var m=document.getElementById('modal-detail-member'); if(m){ m.style.display='none'; m.parentNode.removeChild(m);} ">Tutup</button>
  </div>
</div>
<?php
$html = ob_get_clean();
mysqli_close($connect);
echo json_encode(array('success'=>true,'hasil'=>$html));
exit;
?>
