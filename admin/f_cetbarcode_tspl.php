<?php
session_start();
include 'config.php';
$id_user = $_SESSION['id_user'];
$concet  = opendtcek();

function tspl_esc($s)
{
    return str_replace(array('\\', '"', "\r", "\n"), '', (string) $s);
}

function tspl_label($ox, $nm, $bar, $kode, $harga)
{
    $nm    = tspl_esc(function_exists('mb_substr') ? mb_substr($nm, 0, 18, 'UTF-8') : substr($nm, 0, 18));
    $bar   = tspl_esc($bar);
    $kode  = tspl_esc(function_exists('mb_substr') ? mb_substr($kode, 0, 12, 'UTF-8') : substr($kode, 0, 12));
    $harga = tspl_esc($harga);

    // 33 mm = 264 titik. Pinggir 24 titik (~3 mm) supaya tidak kena potongan stiker.
    $xL   = $ox + 24;
    $xBar = $ox + 56;
    $hBar = 40;
    $yNm  = 8;
    $yBar = 22;
    $yTxt = 68;
    $xh   = $ox + 236 - (strlen($harga) * 8);
    if ($xh < $xL + 88) {
        $xh = $xL + 88;
    }

    $cmd  = 'TEXT '.$xL.','.$yNm.',"1",0,1,1,"'.$nm."\"\r\n";
    $cmd .= 'BARCODE '.$xBar.','.$yBar.',"128",'.$hBar.',0,0,1,2,"'.$bar."\"\r\n";
    $cmd .= 'TEXT '.$xL.','.$yTxt.',"1",0,1,1,"'.$kode."\"\r\n";
    $cmd .= 'TEXT '.$xh.','.$yTxt.',"1",0,1,1,"'.$harga."\"\r\n";
    return $cmd;
}

$uid = mysqli_real_escape_string($concet, $id_user);
$cek = mysqli_query($concet, "SELECT kd_bar, nm_brg, hrg_jum1, copy, no_urut FROM mas_brg WHERE pilih='1' AND id_user='".$uid."'");

$labels = array();
while ($d = mysqli_fetch_assoc($cek)) {
    $item = array(
        'nm'    => $d['nm_brg'],
        'bar'   => (string) $d['kd_bar'],
        'kode'  => (string) $d['kd_bar'],
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
$tspl .= "DENSITY 10\r\n";
$tspl .= "SPEED 3\r\n";

for ($i = 0; $i < count($labels); $i += 2) {
    $tspl .= "CLS\r\n";
    $tspl .= tspl_label(0, $labels[$i]['nm'], $labels[$i]['bar'], $labels[$i]['kode'], $labels[$i]['harga']);
    if (isset($labels[$i + 1])) {
        $tspl .= tspl_label(288, $labels[$i + 1]['nm'], $labels[$i + 1]['bar'], $labels[$i + 1]['kode'], $labels[$i + 1]['harga']);
    }
    $tspl .= "PRINT 1,1\r\n";
}

mysqli_close($concet);
header('Content-Type: application/json');
echo json_encode(array(
    'tspl'   => $tspl,
    'jumlah' => count($labels),
));
