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
    $kode  = tspl_esc($kode);
    $harga = tspl_esc($harga);
    $lab   = 264;
    $bw    = 144;
    $bx    = $ox + (int) (($lab - $bw) / 2);
    $hx    = $bx + $bw - (strlen($harga) * 8);
    if ($hx < $bx + 70) {
        $hx = $bx + 70;
    }
    $cmd  = 'TEXT '.$bx.',6,"1",0,1,1,"'.$nm."\"\r\n";
    $cmd .= 'BARCODE '.$bx.',18,"128",70,0,0,1,1,"'.$bar."\"\r\n";
    $cmd .= 'TEXT '.$bx.',88,"1",0,1,1,"'.$kode."\"\r\n";
    $cmd .= 'TEXT '.$hx.',88,"1",0,1,1,"'.$harga."\"\r\n";
    return $cmd;
}

$uid = mysqli_real_escape_string($concet, $id_user);
$cek = mysqli_query($concet, "SELECT kd_bar, kd_brg, nm_brg, hrg_jum1, copy, no_urut FROM mas_brg WHERE pilih='1' AND id_user='".$uid."'");

$labels = [];
while ($d = mysqli_fetch_assoc($cek)) {
    $kode  = trim((string) $d['kd_brg']) !== '' ? $d['kd_brg'] : $d['kd_bar'];
    $harga = 'Rp '.number_format((int) round($d['hrg_jum1']), 0, ',', '.');
    $item  = array(
        'nm'    => $d['nm_brg'],
        'bar'   => $d['kd_bar'],
        'kode'  => $kode,
        'harga' => $harga,
    );
    $copies = (int) $d['copy'];
    for ($z = 0; $z < $copies; $z++) {
        $labels[] = $item;
    }
    mysqli_query($concet, "UPDATE mas_brg SET cetak='1' WHERE no_urut='".(int) $d['no_urut']."'");
}

$tspl  = "SIZE 70 mm,15 mm\r\nGAP 3 mm,0\r\nDIRECTION 1\r\nREFERENCE 0,0\r\nDENSITY 10\r\nSPEED 3\r\n";
$tspl .= "AUTODETECT\r\n";
for ($i = 0; $i < count($labels); $i += 2) {
    $tspl .= "CLS\r\n";
    $tspl .= tspl_label(8, $labels[$i]['nm'], $labels[$i]['bar'], $labels[$i]['kode'], $labels[$i]['harga']);
    if (isset($labels[$i + 1])) {
        $tspl .= tspl_label(288, $labels[$i + 1]['nm'], $labels[$i + 1]['bar'], $labels[$i + 1]['kode'], $labels[$i + 1]['harga']);
    }
    $tspl .= "PRINT 1,1\r\n";
}

mysqli_close($concet);
header('Content-Type: application/json');
echo json_encode(array('tspl' => $tspl, 'jumlah' => count($labels)));
