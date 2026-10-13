<?php
session_start();
include 'config.php';
$id_user = $_SESSION['id_user'];
$concet  = opendtcek();

$barcodeLib = dirname(__DIR__).'/assets/vendor/tecnickcom/tcpdf/tcpdf_barcodes_1d.php';
if (is_file($barcodeLib) && !class_exists('TCPDFBarcode')) {
    require_once $barcodeLib;
}

function tspl_esc($s)
{
    return str_replace(array('\\', '"', "\r", "\n"), '', (string) $s);
}

function tspl_code128_bars($x0, $y, $h, $code, $maxW)
{
    if (!class_exists('TCPDFBarcode')) {
        return array('', 0);
    }
    try {
        $obj = new TCPDFBarcode($code, 'C128');
        $arr = $obj->getBarcodeArray();
    } catch (Exception $e) {
        return array('', 0);
    }
    if (empty($arr['bcode']) || empty($arr['maxw'])) {
        return array('', 0);
    }
    $units = (int) $arr['maxw'];
    $mw    = ($units > 0) ? (int) floor($maxW / $units) : 1;
    if ($mw < 1) {
        $mw = 1;
    }
    if ($mw > 2) {
        $mw = 2;
    }
    $x    = $x0;
    $run  = 0;
    $runX = $x0;
    $cmd  = '';
    foreach ($arr['bcode'] as $b) {
        $w     = ((int) $b['w']) * $mw;
        $black = !empty($b['t']);
        if ($black) {
            if ($run === 0) {
                $runX = $x;
            }
            $run += $w;
        } elseif ($run > 0) {
            $cmd .= 'BAR '.$runX.','.$y.','.$run.','.$h."\r\n";
            $run = 0;
        }
        $x += $w;
    }
    if ($run > 0) {
        $cmd .= 'BAR '.$runX.','.$y.','.$run.','.$h."\r\n";
    }
    return array($cmd, $x - $x0);
}

function tspl_label($ox, $nm, $bar, $kode, $harga)
{
    $nm    = tspl_esc(function_exists('mb_substr') ? mb_substr($nm, 0, 18, 'UTF-8') : substr($nm, 0, 18));
    $bar   = tspl_esc($bar);
    $kode  = tspl_esc(function_exists('mb_substr') ? mb_substr($kode, 0, 14, 'UTF-8') : substr($kode, 0, 14));
    $harga = tspl_esc($harga);

    $cellW = 264;
    $pad   = 12;
    $xL    = $ox + $pad;
    $xR    = $ox + $cellW - $pad;
    $hBar  = 72;
    $yNm   = 4;
    $yBar  = 24;
    $yTxt  = 98;

    $xh = $xR - (strlen($harga) * 8);
    if ($xh < $xL + 90) {
        $xh = $xL + 90;
    }

    $bars = tspl_code128_bars($xL, $yBar, $hBar, $bar, $cellW - ($pad * 2));
    $barW = $bars[1];
    $xBar = $xL;
    if ($barW > 0 && $barW < ($cellW - $pad * 2)) {
        $xBar = $ox + (int) floor(($cellW - $barW) / 2);
        $bars = tspl_code128_bars($xBar, $yBar, $hBar, $bar, $cellW - ($pad * 2));
    }

    $cmd  = 'TEXT '.$xL.','.$yNm.',"0",0,1,1,"'.$nm."\"\r\n";
    if ($bars[0] !== '') {
        $cmd .= $bars[0];
    } else {
        $cmd .= 'BARCODE '.$xBar.','.$yBar.',"128",'.$hBar.',0,0,1,2,"'.$bar."\"\r\n";
    }
    $cmd .= 'TEXT '.$xL.','.$yTxt.',"1",0,1,1,"'.$kode."\"\r\n";
    $cmd .= 'TEXT '.$xh.','.$yTxt.',"1",0,1,1,"'.$harga."\"\r\n";
    return $cmd;
}

$uid = mysqli_real_escape_string($concet, $id_user);
$cek = mysqli_query($concet, "SELECT kd_bar, nm_brg, hrg_jum1, copy, no_urut FROM mas_brg WHERE pilih='1' AND id_user='".$uid."'");

$labels = [];
while ($d = mysqli_fetch_assoc($cek)) {
    $item = array(
        'nm'    => $d['nm_brg'],
        'bar'   => $d['kd_bar'],
        'kode'  => $d['kd_bar'],
        'harga' => 'Rp '.number_format((int) round($d['hrg_jum1']), 0, ',', '.'),
    );
    $copies = max(1, (int) $d['copy']);
    for ($z = 0; $z < $copies; $z++) {
        $labels[] = $item;
    }
    mysqli_query($concet, "UPDATE mas_brg SET cetak='1' WHERE no_urut='".(int) $d['no_urut']."'");
}

$tspl  = "SIZE 70 mm,15 mm\r\n";
$tspl .= "GAP 3 mm,0\r\n";
$tspl .= "OFFSET 0 mm\r\n";
$tspl .= "DIRECTION 1\r\n";
$tspl .= "REFERENCE 0,0\r\n";
$tspl .= "DENSITY 15\r\n";
$tspl .= "SPEED 2\r\n";

for ($i = 0; $i < count($labels); $i += 2) {
    $tspl .= "CLS\r\n";
    $tspl .= tspl_label(0, $labels[$i]['nm'], $labels[$i]['bar'], $labels[$i]['kode'], $labels[$i]['harga']);
    if (isset($labels[$i + 1])) {
        $tspl .= tspl_label(280, $labels[$i + 1]['nm'], $labels[$i + 1]['bar'], $labels[$i + 1]['kode'], $labels[$i + 1]['harga']);
    }
    $tspl .= "PRINT 1,1\r\n";
}

mysqli_close($concet);
header('Content-Type: application/json');
echo json_encode(array('tspl' => $tspl, 'jumlah' => count($labels)));
