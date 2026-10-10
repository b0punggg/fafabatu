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
    $nm    = tspl_esc(function_exists('mb_substr') ? mb_substr($nm, 0, 20, 'UTF-8') : substr($nm, 0, 20));
    $bar   = tspl_esc($bar);
    $kode  = tspl_esc($kode);
    $harga = tspl_esc($harga);

    $x  = $ox + 30;
    $xh = $ox + 168;

    $cmd  = 'TEXT '.$x.',2,"1",0,1,1,"'.$nm."\"\r\n";
    $cmd .= 'BARCODE '.$x.',16,"128",72,0,0,2,2,"'.$bar."\"\r\n";
    $cmd .= 'TEXT '.$x.',90,"1",0,1,1,"'.$kode."\"\r\n";
    $cmd .= 'TEXT '.$xh.',90,"1",0,1,1,"'.$harga."\"\r\n";
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
    $copies = (int) $d['copy'];
    if ($copies < 1) {
        $copies = 1;
    }
    for ($z = 0; $z < $copies; $z++) {
        $labels[] = $item;
    }
    mysqli_query($concet, "UPDATE mas_brg SET cetak='1' WHERE no_urut='".(int) $d['no_urut']."'");
}

$col1 = 0;
$col2 = 280;

$tspl  = "SIZE 70 mm,15 mm\r\n";
$tspl .= "GAP 2 mm,0\r\n";
$tspl .= "OFFSET 0 mm\r\n";
$tspl .= "DIRECTION 1\r\n";
$tspl .= "REFERENCE 0,0\r\n";
$tspl .= "DENSITY 10\r\n";
$tspl .= "SPEED 3\r\n";

for ($i = 0; $i < count($labels); $i += 2) {
    $tspl .= "CLS\r\n";
    $tspl .= tspl_label($col1, $labels[$i]['nm'], $labels[$i]['bar'], $labels[$i]['kode'], $labels[$i]['harga']);
    if (isset($labels[$i + 1])) {
        $tspl .= tspl_label($col2, $labels[$i + 1]['nm'], $labels[$i + 1]['bar'], $labels[$i + 1]['kode'], $labels[$i + 1]['harga']);
    }
    $tspl .= "PRINT 1,1\r\n";
}

mysqli_close($concet);
header('Content-Type: application/json');
echo json_encode(array('tspl' => $tspl, 'jumlah' => count($labels)));
