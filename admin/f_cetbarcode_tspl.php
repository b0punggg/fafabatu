<?php
session_start();
include 'config.php';
$id_user = $_SESSION['id_user'];
$concet  = opendtcek();

$cek = mysqli_query($concet, "SELECT kd_bar, copy, no_urut FROM mas_brg WHERE pilih='1' AND id_user='".mysqli_real_escape_string($concet,$id_user)."'");

// Kumpulkan semua label (sesuai jumlah copy)
$labels = [];
while ($d = mysqli_fetch_assoc($cek)) {
    for ($z = 0; $z < (int)$d['copy']; $z++) { $labels[] = $d['kd_bar']; }
    mysqli_query($concet, "UPDATE mas_brg SET cetak='1' WHERE no_urut='".(int)$d['no_urut']."'");
}

// Susun TSPL: 1 baris = 2 label
$tspl  = "SIZE 70 mm,15 mm\r\nGAP 3 mm,0\r\nDIRECTION 1\r\nREFERENCE 0,0\r\nDENSITY 10\r\nSPEED 3\r\n";
$tspl .= "AUTODETECT\r\n";
for ($i = 0; $i < count($labels); $i += 2) {
    $tspl .= "CLS\r\n";
    $tspl .= 'BARCODE 30,4,"128",55,1,0,2,2,"'.$labels[$i]."\"\r\n";
    if (isset($labels[$i+1])) {
        $tspl .= 'BARCODE 318,4,"128",55,1,0,2,2,"'.$labels[$i+1]."\"\r\n";
    }
    $tspl .= "PRINT 1,1\r\n";
}

mysqli_close($concet);
header('Content-Type: application/json');
echo json_encode(['tspl' => $tspl, 'jumlah' => count($labels)]);