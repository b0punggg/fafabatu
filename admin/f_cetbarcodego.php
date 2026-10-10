<?php
  require __DIR__.'/../assets/vendor/autoload.php';
  use Spipu\Html2Pdf\Html2Pdf;
  use Spipu\Html2Pdf\Exception\Html2PdfException;
  use Spipu\Html2Pdf\Exception\ExceptionFormatter; 
  ob_start();
  session_start();
  include 'config.php';
  $id_user = $_SESSION['id_user'];
  $concet  = opendtcek(); 
  if(isset($_SESSION['kertas'])){$kertas  = $_SESSION['kertas'];}else{$kertas='A4';}
  $jbar=9;
  $jbar_1d=6;
  $lbl_w=33;
  $lbl_h=15;
  $gap_col=2;
  $gap_row=2;
  if($kertas=='A4'){
    $jbar=9;
    $jbar_1d=6;
  }
  if($kertas=='58'){
    $jbar=2;
    $jbar_1d=1;
  }
  if($kertas=='80'){
    $jbar=2;
    $jbar_1d=2;
  }
  if($kertas=='70'){
    $jbar=2;
    $jbar_1d=2;
  }
  $bar_w   = 22;
  $kode_w  = 11;
  $harga_w = 11;
  $lbl_w   = 33;
  $gap_col = 2;
  $gap_row = 2;
  $page_w  = 70;
  $page_h  = 100;
  $margin_lr = 1;
  $rows_per_page = 5;
  if ($kertas == '70') {
    $page_w    = 70.1;
    $page_h    = 87.4;
    $margin_lr = 1.27;
    $gap_col   = 2;
    $gap_row   = 3.05;
    $lbl_h     = 15;
    $lbl_w     = 32.78;
    $jbar_1d   = 2;
    $rows_per_page = 5;
  }
  $pad_x   = ($lbl_w - $bar_w) / 2;
  $table_w = ($lbl_w * 2) + $gap_col;

  if (!function_exists('cetak_barcode_img')) {
    function cetak_barcode_img($code, $type)
    {
      $lib = dirname(__DIR__).'/assets/vendor/tecnickcom/tcpdf/tcpdf_barcodes_1d.php';
      if (!class_exists('TCPDFBarcode')) {
        if (!is_file($lib)) {
          return '';
        }
        require_once $lib;
      }
      try {
        $obj = new TCPDFBarcode($code, $type);
        $png = $obj->getBarcodePngData(1, 60, array(0, 0, 0));
        if ($png === false || $png === '') {
          return '';
        }
        $src = @imagecreatefromstring($png);
        if ($src) {
          $sw = imagesx($src);
          $sh = imagesy($src);
          $dw = 51;
          $dh = 18;
          $dst = imagecreatetruecolor($dw, $dh);
          $white = imagecolorallocate($dst, 255, 255, 255);
          imagefilledrectangle($dst, 0, 0, $dw, $dh, $white);
          imagecopyresized($dst, $src, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
          ob_start();
          imagepng($dst);
          $png = ob_get_clean();
          imagedestroy($src);
          imagedestroy($dst);
        }
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tokofafa_bc';
        if (!is_dir($dir)) {
          mkdir($dir, 0777, true);
        }
        $file = $dir.DIRECTORY_SEPARATOR.md5($type.'|'.$code.'|nn18').'.png';
        if (!is_file($file)) {
          file_put_contents($file, $png);
        }
        return str_replace('\\', '/', $file);
      } catch (Exception $e) {
        return '';
      }
    }
  } 
 
  //  if($_SESSION['pilprint']=='CETAK-CK'){
  //   $jbar=2;
  //  }else{$jbar=3;}
   ?>
<style>
    table {
    width: auto;
    border-collapse: collapse;
    text-align: center;
    }
    tr {
      page-break-inside: avoid;
    }
    th {
      text-align: center;
      border: solid 1px black;
      background: white;
    }
    td {
      border: none;
      background: white;
      font-size: 5pt;
    }
</style>
<page backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm">
  <table cellspacing="0" cellpadding="0" style="width: <?=$table_w?>mm;">    
     <?php 
      $i=0;$x=0;
      if(isset($_GET['bcode'])){
        $cek=mysqli_query($concet,"SELECT * FROM mas_brg WHERE pilih='1' AND id_user='$id_user'");  
        if($_GET['bcode']=='1'){
          while($data=mysqli_fetch_array($cek)){
            $nm_brg=$data['nm_brg'];
            $no_urut=$data['no_urut'];
            $copies=$data['copy'];
            mysqli_query($concet,"UPDATE mas_brg SET cetak='1' WHERE no_urut='$no_urut'");
            for ($z=0; $z < $copies ; $z++) {
              if ($x == 0) {echo "<tr>";}  ?>
              <td style="width: 90px">
                <br><br>
                  <p style='font-size:7pt;text-align: center'><b><?=$nm_brg?></b></p>
                  <qrcode value="<?= $data['kd_bar'] ?>" style="border: none;width: 22mm; background-color: white; color: black;"></qrcode> 
                  <p style='font-size:7pt;text-align: center'><b><?='Rp.'.gantiti(round($data['hrg_jum1'],0))?></b></p> 
              </td> <?php              
              $x=$x+1; 
              if ( $x == $jbar ) { echo "</tr>";$x=0;}
            } 
          }   
          
          if ($x<8 && $x!=0) {echo "</tr>";}
          }else{
  $items = array();
  while($data=mysqli_fetch_array($cek)){
    $copies=(int)$data['copy'];
    if ($copies < 1) { $copies = 1; }
    $kd_bar = (string)$data['kd_bar'];
    $even_num = preg_match('/^[0-9]+$/', $kd_bar) && (strlen($kd_bar) % 2 === 0);
    $it = array(
      'nm' => function_exists('mb_substr') ? mb_substr($data['nm_brg'], 0, 20, 'UTF-8') : substr($data['nm_brg'], 0, 20),
      'kode' => $kd_bar,
      'harga' => 'Rp '.number_format((int)round($data['hrg_jum1']), 0, ',', '.'),
      'kd_bar' => $kd_bar,
      'bar_type' => $even_num ? 'C128C' : 'C128',
    );
    mysqli_query($concet,"UPDATE mas_brg SET cetak='1' WHERE no_urut='".(int)$data['no_urut']."'");
    for ($z=0; $z < $copies; $z++) { $items[] = $it; }
  }
  $per_page = $rows_per_page * $jbar_1d;
  $pages = array_chunk($items, $per_page);
  $span_gap = ($jbar_1d * 2) - 1;
  foreach ($pages as $pi => $page_items) {
    if ($pi > 0) {
      echo '</table></page><page backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm"><table cellspacing="0" cellpadding="0" style="width: '.$table_w.'mm;">';
    }
    $x = 0;
    $last = count($page_items) - 1;
    foreach ($page_items as $idx => $it) {
      if ($x == 0) { echo '<tr style="height:'.$lbl_h.'mm">'; }
      if ($x > 0) { echo '<td style="width:'.$gap_col.'mm; height:'.$lbl_h.'mm;"></td>'; }
      ?>
      <td style="width: <?=$lbl_w?>mm; height: <?=$lbl_h?>mm; vertical-align: middle; text-align: center;">
        <table cellspacing="0" cellpadding="0" style="width: <?=$lbl_w?>mm; border-collapse: collapse;">
          <tr>
            <td style="width: <?=$pad_x?>mm; font-size: 1px;"></td>
            <td colspan="2" style="width: <?=$bar_w?>mm; text-align: center; font-size: 3.5pt; font-weight: bold; height: 2.8mm; line-height: 2.8mm; padding: 0; vertical-align: bottom;"><?=htmlspecialchars($it['nm'])?></td>
            <td style="width: <?=$pad_x?>mm; font-size: 1px;"></td>
          </tr>
          <tr>
            <td style="width: <?=$pad_x?>mm; font-size: 1px;"></td>
            <td colspan="2" style="width: <?=$bar_w?>mm; text-align: left; height: 8mm; padding: 0; font-size: 1px; vertical-align: top;">
              <barcode type="<?=$it['bar_type']?>" value="<?=htmlspecialchars($it['kd_bar'])?>" label="none" style="width: <?=$bar_w?>mm; height: 7.8mm; color: #000000;"></barcode>
            </td>
            <td style="width: <?=$pad_x?>mm; font-size: 1px;"></td>
          </tr>
          <tr>
            <td style="width: <?=$pad_x?>mm; font-size: 1px;"></td>
            <td style="width: <?=$kode_w?>mm; text-align: left; font-size: 2.5pt; font-weight: bold; height: 2.8mm; line-height: 2.8mm; padding: 0; vertical-align: top;"><?=htmlspecialchars($it['kode'])?></td>
            <td style="width: <?=$harga_w?>mm; text-align: right; font-size: 3pt; font-weight: bold; height: 2.8mm; line-height: 2.8mm; padding: 0; vertical-align: top;"><?=htmlspecialchars($it['harga'])?></td>
            <td style="width: <?=$pad_x?>mm; font-size: 1px;"></td>
          </tr>
        </table>
      </td>
      <?php
      $x++;
      $row_done = ($x == $jbar_1d) || ($idx == $last);
      if ($row_done) {
        echo '</tr>';
        if ($x == $jbar_1d && $idx < $last) {
          echo '<tr><td colspan="'.$span_gap.'" style="height:'.$gap_row.'mm; font-size:1pt;"></td></tr>';
        }
        $x = 0;
      }
    }
  }
}
        mysqli_free_result($cek);unset($data);
      }  
     ?> 
  </table>
  
</page>

<?php
    unset($data,$cek);
    mysqli_close($concet);
    $c_nmfile='barkode.pdf'; 
    $content = ob_get_clean();
    try
    { 

    if($kertas=='A4'){
      $html2pdf = new Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array(4, 5, 4, 5));
    }
    if($kertas=='58'){
      $html2pdf = new Html2Pdf('P', array(58, $page_h), 'en', true, 'UTF-8', array(1, 0, 1, 0));
      $html2pdf->pdf->SetAutoPageBreak(false, 0);
    }
    if($kertas=='80'){
      $html2pdf = new Html2Pdf('P', array(80, $page_h), 'en', true, 'UTF-8', array(1, 0, 1, 0));
      $html2pdf->pdf->SetAutoPageBreak(false, 0);
    }
    if($kertas=='70'){
      $html2pdf = new Html2Pdf('P', array($page_w, $page_h), 'en', true, 'UTF-8', array($margin_lr, 0, $margin_lr, 0));
      $html2pdf->pdf->SetAutoPageBreak(false, 0);
    } 
      $html2pdf->pdf->SetDisplayMode('fullpage');
      $html2pdf->writeHTML($content);
      $html2pdf->Output($c_nmfile);
    }
    catch(Html2PdfException $e) {
      $html2pdf->clean();
      $formatter = new ExceptionFormatter($e);
      echo $formatter->getHtmlMessage();
      exit;
    }
?>
<script>window.print()</script>