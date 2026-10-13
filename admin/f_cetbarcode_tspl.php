<?php
session_start();
include 'config.php';
$id_user = $_SESSION['id_user'];
$concet  = opendtcek();

$barcodeLib = dirname(__DIR__).'/assets/vendor/tecnickcom/tcpdf/tcpdf_barcodes_1d.php';
if (is_file($barcodeLib) && !class_exists('TCPDFBarcode')) {
    require_once $barcodeLib;
}

function tspl_font()
{
    $cands = array(
        'C:\\Windows\\Fonts\\arialbd.ttf',
        'C:\\Windows\\Fonts\\arial.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
    );
    foreach ($cands as $f) {
        if (is_file($f)) {
            return $f;
        }
    }
    return '';
}

function tspl_fit_text($text, $font, $size, $maxW)
{
    $text = (string) $text;
    if ($font === '' || !function_exists('imagettfbbox')) {
        return $text;
    }
    while (strlen($text) > 1) {
        $b = imagettfbbox($size, 0, $font, $text);
        $w = abs($b[2] - $b[0]);
        if ($w <= $maxW) {
            break;
        }
        $text = substr($text, 0, -1);
    }
    return $text;
}

function tspl_text($im, $x, $y, $size, $color, $font, $text)
{
    if ($font !== '' && function_exists('imagettftext')) {
        imagettftext($im, $size, 0, $x, $y + $size, $color, $font, $text);
        return;
    }
    imagestring($im, 5, $x, $y, $text, $color);
}

function tspl_text_right($im, $right, $y, $size, $color, $font, $text)
{
    if ($font !== '' && function_exists('imagettfbbox')) {
        $b = imagettfbbox($size, 0, $font, $text);
        $w = abs($b[2] - $b[0]);
        imagettftext($im, $size, 0, $right - $w, $y + $size, $color, $font, $text);
        return;
    }
    imagestring($im, 5, $right - (strlen($text) * 9), $y, $text, $color);
}

function tspl_draw_barcode($im, $x, $y, $maxW, $h, $code, $black)
{
    if (!class_exists('TCPDFBarcode') || $code === '') {
        return;
    }
    try {
        $obj = new TCPDFBarcode($code, 'C128');
        $arr = $obj->getBarcodeArray();
    } catch (Exception $e) {
        return;
    }
    if (empty($arr['bcode']) || empty($arr['maxw'])) {
        return;
    }
    $units = (int) $arr['maxw'];
    $mw    = (int) floor($maxW / $units);
    if ($mw < 1) {
        $mw = 1;
    }
    $total = $units * $mw;
    $cx    = $x + (int) floor(($maxW - $total) / 2);
    foreach ($arr['bcode'] as $b) {
        $w = ((int) $b['w']) * $mw;
        if (!empty($b['t'])) {
            imagefilledrectangle($im, $cx, $y, $cx + $w - 1, $y + $h - 1, $black);
        }
        $cx += $w;
    }
}

function tspl_draw_label($im, $ox, $nm, $bar, $harga, $black, $font)
{
    $cellW = 264;
    $pad   = 10;
    $nm    = tspl_fit_text($nm, $font, 13, $cellW - $pad * 2);
    $kode  = tspl_fit_text($bar, $font, 11, 130);
    $harga = tspl_fit_text($harga, $font, 11, 110);
    tspl_text($im, $ox + $pad, 3, 13, $black, $font, $nm);
    tspl_draw_barcode($im, $ox + $pad, 24, $cellW - $pad * 2, 70, $bar, $black);
    tspl_text($im, $ox + $pad, 98, 11, $black, $font, $kode);
    tspl_text_right($im, $ox + $cellW - $pad, 98, 11, $black, $font, $harga);
}

function tspl_bitmap_hex($im)
{
    $w = imagesx($im);
    $h = imagesy($im);
    $bytes = (int) ceil($w / 8);
    $out = '';
    for ($y = 0; $y < $h; $y++) {
        for ($bx = 0; $bx < $bytes; $bx++) {
            $byte = 0;
            for ($bit = 0; $bit < 8; $bit++) {
                $x = $bx * 8 + $bit;
                if ($x >= $w) {
                    continue;
                }
                $rgb = imagecolorat($im, $x, $y);
                $r   = ($rgb >> 16) & 0xFF;
                if ($r < 128) {
                    $byte |= (0x80 >> $bit);
                }
            }
            $out .= chr($byte);
        }
    }
    return strtoupper(bin2hex($out));
}

function tspl_page_hex($left, $right, $font)
{
    $W = 560;
    $H = 120;
    $im = imagecreatetruecolor($W, $H);
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    imagefilledrectangle($im, 0, 0, $W - 1, $H - 1, $white);
    tspl_draw_label($im, 0, $left['nm'], $left['bar'], $left['harga'], $black, $font);
    if ($right) {
        tspl_draw_label($im, 280, $right['nm'], $right['bar'], $right['harga'], $black, $font);
    }
    $bmp = tspl_bitmap_hex($im);
    imagedestroy($im);
    $cmd  = "SIZE 70 mm,15 mm\r\n";
    $cmd .= "GAP 3 mm,0\r\n";
    $cmd .= "DIRECTION 0\r\n";
    $cmd .= "REFERENCE 0,0\r\n";
    $cmd .= "DENSITY 15\r\n";
    $cmd .= "SPEED 2\r\n";
    $cmd .= "CLS\r\n";
    $cmd .= 'BITMAP 0,0,70,'.$H.",0,";
    return strtoupper(bin2hex($cmd)).$bmp.strtoupper(bin2hex("\r\nPRINT 1,1\r\n"));
}

$uid = mysqli_real_escape_string($concet, $id_user);
$cek = mysqli_query($concet, "SELECT kd_bar, nm_brg, hrg_jum1, copy, no_urut FROM mas_brg WHERE pilih='1' AND id_user='".$uid."'");

$labels = array();
while ($d = mysqli_fetch_assoc($cek)) {
    $item = array(
        'nm'    => $d['nm_brg'],
        'bar'   => (string) $d['kd_bar'],
        'harga' => 'Rp '.number_format((int) round($d['hrg_jum1']), 0, ',', '.'),
    );
    $copies = max(1, (int) $d['copy']);
    for ($z = 0; $z < $copies; $z++) {
        $labels[] = $item;
    }
    mysqli_query($concet, "UPDATE mas_brg SET cetak='1' WHERE no_urut='".(int) $d['no_urut']."'");
}

$font  = tspl_font();
$pages = array();
for ($i = 0; $i < count($labels); $i += 2) {
    $right = isset($labels[$i + 1]) ? $labels[$i + 1] : null;
    $pages[] = tspl_page_hex($labels[$i], $right, $font);
}

mysqli_close($concet);
header('Content-Type: application/json');
echo json_encode(array(
    'jumlah' => count($labels),
    'pages'  => $pages,
));
