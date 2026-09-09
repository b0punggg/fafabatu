
<?php 
  date_default_timezone_set('Asia/Jakarta');
if (!function_exists('opendtcek')) {
  function opendtcek()
  {
    // $host = "localhost";
    // $username = "ADMIN1";
    // $password = "$2y$10$5xiK8zoUiV38wsNe43Z9guuQ7SUW5wT.WQVeadyPdV/cQsM7HjQH."; 
    // $database = "tokorahayu";
    
    return mysqli_connect('localhost','root','','fafa');
  }
}

if (!function_exists('nm_bln')) {
  function nm_bln($bln)
  {
    $nama = array(
      '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    );
    $i = (int) $bln;
    return isset($nama[$i]) ? $nama[$i] : (string) $bln;
  }
}

// Pastikan fungsi kd_barc39() tidak dideklarasikan dua kali
if (!function_exists('kd_barc39')) {
function kd_barc39($strkon){ 
  $a=array("A"=>"1","B"=>"2","C"=>"3","D"=>"4","E"=>"5",
           "F"=>"6","G"=>"7","H"=>"8","I"=>"9","J"=>"0",
           "K"=>"9","L"=>"8","M"=>"7","N"=>"6","O"=>"5",
           "P"=>"4","Q"=>"3","R"=>"2","S"=>"1","T"=>"0",
           "U"=>"1","V"=>"2","W"=>"3","X"=>"2","Y"=>"1","Z"=>"0");

  $b=array("1"=>"A","2"=>"B","3"=>"C","4"=>"D","5"=>"E",
  "6"=>"F","7"=>"G","8"=>"H","9"=>"I","0"=>"J");                  

  date_default_timezone_set('Asia/Jakarta');         
  $maks     = 4;
  $kdbar    = "";
  $strkon_a = "$strkon";
  $dat      = substr(str_replace(":","",date("H:m:s",time()*rand(1,100))),2,4);  
  $pattern  = "/[\W_0-9]/i";
  $strkon   = preg_replace($pattern, "", trim(strtoupper($strkon)));
 
  if(strlen($strkon)>=1){ 
    if(strlen($strkon)>$maks){
      $kdbar="";
      for ($x = 0; $x <= $maks-1; $x++) {
        $kdbar=$kdbar.$a[substr($strkon,$x,1)];
      } 
    }else{
      $kdbar="";
      $len=strlen($strkon);
      for ($c = 0; $c <= $len-1; $c++) {
        $kdbar=$kdbar.$a[substr($strkon,$c,1)];
      }
      $kdbar=str_pad($kdbar, 4, rand(0,9), STR_PAD_LEFT);
    }
   return $dat.$kdbar;
  }else{
    $maks=4;
    if(strlen($strkon_a)>$maks){
      $kdbar="";$kdbar1="";
      for ($x = 0; $x <= 3; $x++) {
        $kdbar=$kdbar.$b[substr($strkon_a,$x,1)];
      }
      for ($x = 0; $x <= 3; $x++) {
        $kdbar1=$kdbar1.$a[substr($kdbar,$x,1)];
      } 
      
    }else{
      $kdbar=""; $kdbar1=""; 
      $len=strlen($strkon_a);
      for ($c = 0; $c <= $len-1; $c++) {
        $kdbar=$kdbar.$b[substr($strkon_a,$c,1)];
      }
      for ($cc = 0; $cc <= $len-1; $cc++) {
        $kdbar1=$kdbar1.$a[substr($kdbar,$cc,1)];
      }
      
    } 
    $kdbar1=str_pad($kdbar1, 6, rand(1,9), STR_PAD_LEFT);
    return $kdbar1.$dat;
  }
}
}

// Pastikan fungsi createDb() tidak dideklarasikan dua kali
if (!function_exists('createDb')) {
function createDb($cpanel_theme, $cPanelUser, $cPanelPass, $dbName)
{
    $buildRequest = "/frontend/" . $cpanel_theme . "/sql/addb.html?db=" . $dbName;

    $openSocket = fsockopen('localhost', 2083);
    if (!$openSocket) {
        return "Socket error";
        exit();
    }

    $authString = $cPanelUser . ":" . $cPanelPass;
    $authPass = base64_encode($authString);
    $buildHeaders = "GET " . $buildRequest . "\r\n";
    $buildHeaders .= "HTTP/1.0\r\n";
    $buildHeaders .= "Host:localhost\r\n";
    $buildHeaders .= "Authorization: Basic " . $authPass . "\r\n";
    $buildHeaders .= "\r\n";

    fputs($openSocket, $buildHeaders);
    while (!feof($openSocket)) {
        fgets($openSocket, 128);
    }
    fclose($openSocket);
}
}

// Pastikan fungsi createUser() tidak dideklarasikan dua kali
if (!function_exists('createUser')) {
function createUser($cpanel_theme, $cPanelUser, $cPanelPass, $userName, $userPass)
{
}
}

// Pastikan fungsi addUserToDb() tidak dideklarasikan dua kali
if (!function_exists('addUserToDb')) {
function addUserToDb($cpanel_theme, $cPanelUser, $cPanelPass, $userName, $dbName, $privileges)
{
    $buildRequest = "/cpsess1246347400/frontend/" . $cpanel_theme . "/sql/addusertodb.html?user=" . $cPanelUser . "_" . $userName . "&db=" . $cPanelUser . "_" . $dbName . "&privileges=" . $privileges;

    $openSocket = fsockopen('localhost', 2083);
    if (!$openSocket) {
        return "Socket error";
        exit();
    }

    $authString = $cPanelUser . ":" . $cPanelPass;
    $authPass = base64_encode($authString);
    $buildHeaders = "GET " . $buildRequest . "\r\n";
    $buildHeaders .= "HTTPS/srv93.niagahoster.com\r\n";
    $buildHeaders .= "Host:localhost\r\n";
    $buildHeaders .= "Authorization: Basic " . $authPass . "\r\n";
    $buildHeaders .= "\r\n";

    fputs($openSocket, $buildHeaders);
    while (!feof($openSocket)) {
        fgets($openSocket, 128);
    }
    fclose($openSocket);
}
}

// Pastikan fungsi tglingat() tidak dideklarasikan dua kali
if (!function_exists('tglingat')) {
function tglingat($tanggal,$hr){
  $date=date_create($tanggal);
  $hrs=$hr.' days';
  date_add($date,date_interval_create_from_date_string($hrs));
  return date_format($date,"Y-m-d");
}
}

// Pastikan fungsi konjumbrg() tidak dideklarasikan dua kali
if (!function_exists('konjumbrg')) {
function konjumbrg($sat_brg,$kd_brg){
    $connect3 = opendtcek(1);
    $kd_toko=$_SESSION['id_toko'];
    $datsql=mysqli_query($connect3,"select * from mas_brg where kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
    $cek=mysqli_fetch_array($datsql);
    $jml_brg_sat=0;
    if($sat_brg==$cek['kd_kem3']){
      $jml_brg_sat=mysqli_escape_string($connect3,$cek['jum_kem3']);
    }
    if($sat_brg==$cek['kd_kem2']){
      $jml_brg_sat=mysqli_escape_string($connect3,$cek['jum_kem2']);
    }
    if($sat_brg==$cek['kd_kem1']){
      $jml_brg_sat=mysqli_escape_string($connect3,$cek['jum_kem1']);
    }
    unset($datsql,$cek);
    mysqli_close($connect3);
    return $jml_brg_sat;
  }
}

// Pastikan fungsi konjumbrg2() tidak dideklarasikan dua kali
if (!function_exists('konjumbrg2')) {
function konjumbrg2($sat_brg,$kd_brg,$hub){
    //$connect3 = opendtcek(1);
    $kd_toko=$_SESSION['id_toko'];
    $datsql=mysqli_query($hub,"SELECT * FROM mas_brg WHERE kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
    $cek=mysqli_fetch_array($datsql);
    $jml_brg_sat=0;
    if($sat_brg==$cek['kd_kem3']){
      $jml_brg_sat=$cek['jum_kem3'];
    }
    if($sat_brg==$cek['kd_kem2']){
      $jml_brg_sat=$cek['jum_kem2'];
    }
    if($sat_brg==$cek['kd_kem1']){
      $jml_brg_sat=$cek['jum_kem1'];
    }
    unset($datsql,$cek);

    //mysqli_close($connect3);
    return $jml_brg_sat;
  }
}

// Pastikan fungsi konhrgbelibrg() tidak dideklarasikan dua kali
if (!function_exists('konhrgbelibrg')) {
function konhrgbelibrg($sat_brg, $kd_brg, $no_urutbeli) {
    $connect3 = opendtcek();

    $cekbeli = mysqli_query($connect3, "SELECT * FROM beli_brg WHERE no_urut='$no_urutbeli'");
    $databeli = mysqli_fetch_assoc($cekbeli);
    $hrg_beli = isset($databeli['hrg_beli']) ? $databeli['hrg_beli'] : 0;
    unset($cekbeli, $databeli);

    $kd_toko = $_SESSION['id_toko'];
    $datsql = mysqli_query($connect3, "SELECT * FROM mas_brg WHERE kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
    $cek = mysqli_fetch_assoc($datsql);

    // Misal definisikan konversi manual (bisa juga ambil dari tabel)
    $konversi2 = 5;  // 1 kem2 = 5 kem1
    $konversi3 = 10; // 1 kem3 = 10 kem2

    if ($sat_brg == $cek['kd_kem3']) {
        $hrg_beli = $hrg_beli * $konversi3 * $konversi2;
    } elseif ($sat_brg == $cek['kd_kem2']) {
        $hrg_beli = $hrg_beli * $konversi2;
    } elseif ($sat_brg == $cek['kd_kem1']) {
        $hrg_beli = $hrg_beli;
    }

    mysqli_close($connect3);
    return $hrg_beli;
}
}

// Stok satuan terbesar (kem1) dari stok satuan terkecil
if (!function_exists('stok_satuan_terbesar')) {
  function stok_satuan_terbesar($stok_juals, $jum_kem1) {
    $stok = floatval($stok_juals);
    $faktor = floatval($jum_kem1);
    if ($faktor <= 0) {
      $faktor = 1;
    }
    return $stok / $faktor;
  }
}

// Nilai persediaan = stok satuan terbesar × harga beli (per satuan besar)
if (!function_exists('nilai_persediaan_stok_besar')) {
  function nilai_persediaan_stok_besar($stok_juals, $hrg_beli, $jum_kem1) {
    return stok_satuan_terbesar($stok_juals, $jum_kem1) * floatval($hrg_beli);
  }
}

// Pastikan fungsi carisatkecil() tidak dideklarasikan dua kali
if (!function_exists('carisatkecil')) {
  function carisatkecil($kd_brg){
    $connect4 = opendtcek();
    $kd_toko=$_SESSION['id_toko'];
    $datsql=mysqli_query($connect4,"select * from mas_brg where kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
    $cek=mysqli_fetch_array($datsql);
    $satkecil='';
    if($cek['kd_kem3']==1 && $cek['kd_kem2']==1){
    $satkecil=$cek['kd_kem1'].';'.$cek['jum_kem1'];
    }
    if($cek['kd_kem3']==1 && $cek['kd_kem2']>1){
      $satkecil=$cek['kd_kem2'].';'.$cek['jum_kem2'];;
    }
    if($cek['kd_kem3']>1){
      $satkecil=$cek['kd_kem3'].';'.$cek['jum_kem3'];;
    }
    unset($datsql,$cek);
    mysqli_close($connect4);
    return $satkecil;
  }
}

// Pastikan fungsi carisatkecil2() tidak dideklarasikan dua kali
if (!function_exists('carisatkecil2')) {
  function carisatkecil2($kd_brg,$hub){
    $kd_toko=$_SESSION['id_toko'];
    $datsql=mysqli_query($hub,"SELECT * from mas_brg where kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
    $cek=mysqli_fetch_array($datsql);
    $satkecil='';
    if($cek['kd_kem3']==1 && $cek['kd_kem2']==1){
    $satkecil=$cek['kd_kem1'].';'.$cek['jum_kem1'];
    }
    if($cek['kd_kem3']==1 && $cek['kd_kem2']>1){
      $satkecil=$cek['kd_kem2'].';'.$cek['jum_kem2'];;
    }
    if($cek['kd_kem3']>1){
      $satkecil=$cek['kd_kem3'].';'.$cek['jum_kem3'];;
    }
    unset($datsql,$cek);
    // if (empty($cek['kd_kem1']) || empty($cek['jum_kem1']) ){
    //   echo $kd_brg;
    // }
    return $satkecil;
  }
}

// Pastikan fungsi carisatbesar() tidak dideklarasikan dua kali
if (!function_exists('carisatbesar')) {
  function carisatbesar($kd_brg){
    $connect4 = opendtcek();
    $kd_toko=$_SESSION['id_toko'];
    $datsql=mysqli_query($connect4,"SELECT * FROM mas_brg WHERE kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
    $cek=mysqli_fetch_array($datsql);

    if($cek['kd_kem1']>1){
      $satbesar=$cek['kd_kem1'].';'.$cek['jum_kem1'];
    }
    unset($datsql,$cek);
    mysqli_close($connect4);
    return $satbesar;
  }
}

// Pastikan fungsi carisatbesar2() tidak dideklarasikan dua kali
if (!function_exists('carisatbesar2')) {
  function carisatbesar2($kd_brg,$hub){
    //$connect4 = opendtcek();
    $kd_toko=$_SESSION['id_toko'];
    $datsql=mysqli_query($hub,"SELECT * FROM mas_brg WHERE kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
    $cek=mysqli_fetch_array($datsql);

    if($cek['kd_kem1']>1){
      $satbesar=$cek['kd_kem1'].';'.$cek['jum_kem1'];
    }
    unset($datsql,$cek);
    //mysqli_close($connect4);
    return $satbesar;
  }
}

// Pastikan fungsi carisatbesar3() tidak dideklarasikan dua kali
if (!function_exists('carisatbesar3')) {
  function carisatbesar3($kd_brg,$hub){
    //$connect4 = opendtcek();
    $kd_toko=$_SESSION['id_toko'];
    $datsql=mysqli_query($hub,"SELECT * FROM mas_brg WHERE kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
    $cek=mysqli_fetch_array($datsql);

    if($cek['kd_kem1']>1){
      $satbesar=$cek['kd_kem1'].';'.$cek['nm_kem1'].';'.$cek['jum_kem1'];
    }
    unset($datsql,$cek);
    //mysqli_close($connect4);
    return $satbesar;
  }
}

// Pastikan fungsi carihrgjual() tidak dideklarasikan dua kali
if (!function_exists('carihrgjual')) {
function carihrgjual($kd_brg,$kd_sat){
  $connect4 = opendtcek();
  $kd_toko=$_SESSION['id_toko'];
  $datsql=mysqli_query($connect4,"select * from mas_brg where kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
  $cek=mysqli_fetch_array($datsql);
  
  if($cek['kd_kem1']==$kd_sat){
    $hrg=$cek['hrg_jum1'];
  }
  if($cek['kd_kem2']==$kd_sat){
    $hrg=$cek['hrg_jum2'];
  }
  if($cek['kd_kem3']==$kd_sat){
    $hrg=$cek['hrg_jum3'];
  }
  unset($datsql,$cek);
  mysqli_close($connect4);
  return $hrg;
}
}

// Pastikan fungsi caristokmas() tidak dideklarasikan dua kali
if (!function_exists('caristokmas')) {
function caristokmas($kd_brg)
{
  $concari = opendtcek();
  $kd_toko=$_SESSION['id_toko'];
  $cek=mysqli_query($concari,"SELECT jml_brg,brg_msk,brg_klr from mas_brg where kd_brg='$kd_brg' AND kd_toko='$kd_toko'");
  $jml=mysqli_fetch_assoc($cek);
  $stok=$jml['jml_brg'].';'.$jml['brg_msk'].';'.$jml['brg_klr'];
  mysqli_close($concari);
  //echo '$stok='.$stok;
  return $stok;
}
}

// Pastikan fungsi caristok() tidak dideklarasikan dua kali
if (!function_exists('caristok')) {
function caristok($kd_brg,$hub)
{
  $kd_toko=$_SESSION['id_toko'];
  $cek=mysqli_query($hub,"SELECT SUM(stok_jual) AS jmlstok from beli_brg where kd_toko='$kd_toko' AND kd_brg='$kd_brg'");
  $jml=mysqli_fetch_assoc($cek);
  $stok=mysqli_real_escape_string($hub,$jml['jmlstok']);
  unset($jml);mysqli_free_result($cek);
  return $stok;
}
}

// Pastikan fungsi caristokbeli() tidak dideklarasikan dua kali
if (!function_exists('caristokbeli')) {
function caristokbeli($no_urut,$kd_brg)
{
  $concari = opendtcek();
  $cek=mysqli_query($concari,"SELECT stok_jual AS jmlstok from beli_brg where no_urut='$no_urut'");
  $jml=mysqli_fetch_assoc($cek);
  $stok=$jml['jmlstok'];
  unset($jml);
  mysqli_close($concari);
  return $stok;
}
}

// Pastikan fungsi ceknmkem() tidak dideklarasikan dua kali
if (!function_exists('ceknmkem')) {
function ceknmkem($field,$hub){
    //$connect2 = opendtcek();
    if(!$hub || !is_object($hub)) {
        return '';
    }
    $datsql=mysqli_query($hub,"Select * from kemas where no_urut='$field'");
    if(!$datsql) {
        return '';
    }
    $cari=mysqli_fetch_array($datsql);
    if(!$cari || !isset($cari['nm_sat2'])) {
        return '';
    }
    $nama=mysqli_real_escape_string($hub,$cari['nm_sat2']);
    unset($datsql);
    //mysqli_close($connect2);
    return $nama;
}
}

// Pastikan fungsi ceknmkem2() tidak dideklarasikan dua kali
if (!function_exists('ceknmkem2')) {
function ceknmkem2($field,$hub){
    //$connect2 = opendtcek(1);
    if(!$hub || !is_object($hub)) {
        return '';
    }
    $datsql=mysqli_query($hub,"Select * from kemas where no_urut='$field'");
    if(!$datsql) {
        return '';
    }
    $cari=mysqli_fetch_array($datsql);
    if(!$cari || !isset($cari['nm_sat1'])) {
        return '';
    }
    $nama=mysqli_real_escape_string($hub,$cari['nm_sat1']);
    unset($datsql);
    //mysqli_close($connect2);
    return $nama;
}
}

// Pastikan fungsi cekdisc() tidak dideklarasikan dua kali
if (!function_exists('cekdisc')) {
function cekdisc($kd_brg,$kd_sat,$concek){
  // $cekcon=opendtcek();
  $cek=mysqli_query($concek,"SELECT * FROM disctetap WHERE kd_brg='$kd_brg' AND kd_sat='$kd_sat' ORDER BY no_urut");
  $cari=mysqli_fetch_assoc($cek); 
  if (!empty($cari['kd_sat'])){
    $hasil=$cari['kd_sat'].';'.$cari['hrg_jual'].';'.$cari['lim_jual'];
  } else { $hasil=""; }
  return $hasil;
  //mysqli_close($cekcon);
}
}

// Pastikan fungsi adadisc() tidak dideklarasikan dua kali
if (!function_exists('adadisc')) {
function adadisc($kd_brg){
  $cekada=opendtcek();
  $cek=mysqli_query($cekada,"SELECT * FROM disctetap WHERE kd_brg='$kd_brg'");
  if (mysqli_num_rows($cek)>=1){
    return true;
  }else{
    return false;
  }
  unset($cek);
  mysqli_close($cekada);
}
}

// Pastikan fungsi cekdiscpromo() tidak dideklarasikan dua kali
if (!function_exists('cekdiscpromo')) {
function cekdiscpromo($kd_brg, $tgl_jual, $concek){
  // Cek apakah ada promo discount aktif untuk barang ini
  $kd_toko = $_SESSION['id_toko'];
  $tgl_jual = mysqli_real_escape_string($concek, $tgl_jual);
  $kd_brg = mysqli_real_escape_string($concek, $kd_brg);
  
  // Cek promo aktif berdasarkan tanggal
  $query_promo = "SELECT dpd.disc_rupiah, dpd.disc_persen 
                  FROM disc_promo_detail dpd
                  INNER JOIN disc_promo dp ON dpd.no_promo = dp.no_promo
                  WHERE dpd.kd_brg = '$kd_brg' 
                  AND dp.kd_toko = '$kd_toko'
                  AND dp.tgl_awal <= '$tgl_jual' 
                  AND dp.tgl_akhir >= '$tgl_jual'
                  ORDER BY dpd.no_urut DESC
                  LIMIT 1";
  
  $result = mysqli_query($concek, $query_promo);
  if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $data; // Return array dengan disc_rupiah dan disc_persen
  }
  
  return false;
}
}

// Pastikan fungsi applydiscpromo() tidak dideklarasikan dua kali
if (!function_exists('applydiscpromo')) {
function applydiscpromo($hrg_jual, $kd_brg, $tgl_jual, $concek){
  // Apply discount promo ke harga
  $disc_promo = cekdiscpromo($kd_brg, $tgl_jual, $concek);
  
  if ($disc_promo !== false) {
    $disc_rp = floatval($disc_promo['disc_rupiah']);
    $disc_pr = floatval($disc_promo['disc_persen']);
    
    // Apply percentage discount dulu
    if ($disc_pr > 0) {
      $hrg_jual = $hrg_jual * (1 - $disc_pr / 100);
    }
    
    // Apply rupiah discount
    if ($disc_rp > 0) {
      $hrg_jual = $hrg_jual - $disc_rp;
    }
    
    // Pastikan tidak negatif
    if ($hrg_jual < 0) {
      $hrg_jual = 0;
    }
  }
  
  return $hrg_jual;
}
}

// Pastikan fungsi getdiscpromoitem() tidak dideklarasikan dua kali
if (!function_exists('getdiscpromoitem')) {
function getdiscpromoitem($kd_brg, $tgl_jual, $concek){
  // Get discount item (rupiah) dari promo untuk digunakan sebagai discitem
  $disc_promo = cekdiscpromo($kd_brg, $tgl_jual, $concek);
  
  if ($disc_promo !== false) {
    $disc_rp = floatval($disc_promo['disc_rupiah']);
    $disc_pr = floatval($disc_promo['disc_persen']);
    
    // Hitung total discount dalam rupiah
    $hrg_jual = 0; // Akan diisi dari harga jual yang sebenarnya
    $total_disc = 0;
    
    if ($disc_pr > 0) {
      // Perlu harga jual untuk hitung persentase
      // Return disc_pr untuk digunakan sebagai discitem persen
      return array('disc_rupiah' => $disc_rp, 'disc_persen' => $disc_pr);
    } else {
      return array('disc_rupiah' => $disc_rp, 'disc_persen' => 0);
    }
  }
  
  return false;
}
}

// Pastikan fungsi hapusPromoBerakhir() tidak dideklarasikan dua kali
if (!function_exists('hapusPromoBerakhir')) {
function hapusPromoBerakhir($connect, $kd_toko = ''){
  // Fungsi untuk menghapus promo yang periode sudah berakhir (tgl_akhir < tanggal hari ini)
  // Fungsi ini dipanggil otomatis saat halaman dimuat atau saat list promo dimuat
  
  date_default_timezone_set('Asia/Jakarta');
  $tgl_sekarang = date('Y-m-d');
  
  // Cek apakah tabel disc_promo ada
  $table_check = mysqli_query($connect, "SHOW TABLES LIKE 'disc_promo'");
  if (mysqli_num_rows($table_check) == 0) {
    return 0; // Tabel belum ada, tidak ada yang perlu dihapus
  }
  
  // Query untuk mendapatkan promo yang sudah berakhir
  $where_toko = '';
  if (!empty($kd_toko)) {
    $kd_toko_escaped = mysqli_real_escape_string($connect, $kd_toko);
    $where_toko = "AND kd_toko = '$kd_toko_escaped'";
  }
  
  $query_promo_berakhir = "SELECT no_promo FROM disc_promo WHERE tgl_akhir < '$tgl_sekarang' $where_toko";
  $result_promo_berakhir = mysqli_query($connect, $query_promo_berakhir);
  
  if (!$result_promo_berakhir) {
    return 0; // Error query, return 0
  }
  
  $jumlah_dihapus = 0;
  
  // Hapus setiap promo yang sudah berakhir
  while ($row = mysqli_fetch_assoc($result_promo_berakhir)) {
    $no_promo = mysqli_real_escape_string($connect, $row['no_promo']);
    
    // Hapus detail promo terlebih dahulu
    $delete_detail = "DELETE FROM disc_promo_detail WHERE no_promo = '$no_promo'";
    mysqli_query($connect, $delete_detail);
    
    // Hapus promo
    $delete_promo = "DELETE FROM disc_promo WHERE no_promo = '$no_promo'";
    $result_delete = mysqli_query($connect, $delete_promo);
    
    if ($result_delete) {
      $jumlah_dihapus++;
    }
  }
  
  return $jumlah_dihapus;
}
}

// Pastikan fungsi spasicenter() tidak dideklarasikan dua kali
if (!function_exists('spasicenter')) {
function spasicenter($str,$pjg)
{
  $spa="";   
  if (strlen($str)>=$pjg){
    $strjadi=substr($str,0,$pjg);
  } else {
    $a=$pjg-strlen($str);
    if($a>1){
      $a=round($a/2,0);
      for ($i=0; $i < $a ; $i++) 
      { 
       $spa=$spa.' ';
      }    
      $strjadi=$spa.$str.$spa;
    }else{
      $strjadi=$str;
    }
  }
  return $strjadi;
}
}

// Pastikan fungsi spasistr() tidak dideklarasikan dua kali
if (!function_exists('spasistr')) {
function spasistr($str,$pjg)
{
  $spa="";   
  if (strlen($str)>=$pjg){
    $strjadi=substr($str,0,$pjg);
    return $strjadi;
  } else {
    $a=$pjg-strlen($str);
    // echo $a;
    for ($i=0; $i < $a ; $i++) 
    { 
     $spa=$spa.' ';
    }
    $strjadi=$str.$spa;
    return $strjadi;
  }
}
}

// Pastikan fungsi spasinum() tidak dideklarasikan dua kali
if (!function_exists('spasinum')) {
function spasinum($str,$pjg)
{
  $spa="";   
  if (strlen($str)>=$pjg){
    $strjadi=substr($str,0,$pjg);
    return $strjadi;
  } else {
    $a=$pjg-strlen($str);
    // echo $a;
    for ($i=0; $i < $a ; $i++) 
    { 
     $spa=$spa." ";
    }
    $strjadi=$spa.$str;
    return $strjadi;
  }
}
}

// Pastikan fungsi spasi() tidak dideklarasikan dua kali
if (!function_exists('spasi')) {
function spasi($pjg){
  $spa='';
  for ($i=0; $i < $pjg ; $i++) 
  { 
   $spa=$spa.'&nbsp;';
  }
  return $spa;
}
}

// Pastikan fungsi write_num() tidak dideklarasikan dua kali
if (!function_exists('write_num')) {
function write_num($input,$printer,$xpos,$ypos){
  
      $s=strlen($input);
      $x=$xpos;
      for ($i=1;$i<$s+1;$i++){
          $u=$i*-1;
          printer_draw_text_custom($printer,substr($input,$u,1),$x,$ypos);
          $x=$x-11;
      }
}
}

// Pastikan fungsi nm_harini() tidak dideklarasikan dua kali
if (!function_exists('nm_harini')) {
  function nm_harini($tanggal){
      $hari=date("D",strtotime($tanggal));
      switch ($hari) {
        case 'Sun':
          $hari_ini="MINGGU";
          break;
        case 'Mon':
          $hari_ini="SENIN";
          break;
        case 'Tue':
          $hari_ini="SELASA";
          break;  
        case 'Wed':
          $hari_ini="RABU";
          break;  
        case 'Thu':
          $hari_ini="KAMIS";
          break;  
        case 'Fri':
          $hari_ini="JUMAT";
          break;  
        case 'Sat':
          $hari_ini="SABTU";
          break;  
      }
      return $hari_ini;
    }
}

// Pastikan fungsi gantiti() tidak dideklarasikan dua kali
if (!function_exists('gantiti')) {
  function gantiti($b){
    $_minus = false;
    $c='';
    if ($b<0) {$_minus = true; $b=$b*-1;}
      $panjang =strlen($b);
      $j = 0;
      for ($i = $panjang; $i > 0; $i--){
        $j = $j + 1;
        if ((($j % 3) == 1) && ($j != 1)){
          $c = substr($b,$i-1,1) . "." . $c;
        } else {
          $c = substr($b,$i-1,1) . $c;
        }
      }
    if ($_minus) {$c = "-".$c;} 
      return $c;
   }
}

// Pastikan fungsi gantitides() tidak dideklarasikan dua kali
if (!function_exists('gantitides')) {
  function gantitides($b){
    $_minus = false;
    $c='';$x=0;$cek=0;
    $b=round($b,2); 
    
    if ($b>0){
      $cek=strpos($b,'.');  
      if ($cek>0){
        $x=explode('.',$b);
        if (strlen($x[1])==1){
          $b=$x[0].'.'.$x[1].'0';
        }  
      }else {
        $b=$b.'.00';  
      }
    }
    $des=substr($b,strlen($b)-3,1);
    if($des<>'.'){    
      $x=explode('.',$b);  
      $b=$x[0];
      $des='00';
    } else {
      $x=explode('.',$b);  
      $b=$x[0];
      $des=$x[1];
    }  
      if ($b<0) {$_minus = true; $b=$b*-1;}
        $panjang =strlen($b);
        $j = 0;
        for ($i = $panjang; $i > 0; $i--){
          $j = $j + 1;
          if ((($j % 3) == 1) && ($j != 1)){
            $c = substr($b,$i-1,1) . "." . $c;
          } else {
            $c = substr($b,$i-1,1) . $c;
          }
        }
        
      if ($_minus) {$c = "-".$c;} 
      return $c . ",".$des;
  }
}

// Pastikan fungsi backnumdes() tidak dideklarasikan dua kali
if (!function_exists('backnumdes')) {
  function backnumdes($x){
    if ($x>0){
      $a=str_replace(".","",$x);
      $b=str_replace(",",".",$a);    
    }else{
      if ($x==0){$b='0.00';} else {
        $a=str_replace(".","",$x);
        $b=str_replace(",",".",$a);    
      }
    }
    return $b;
  }
}

// Pastikan fungsi backnum() tidak dideklarasikan dua kali
if (!function_exists('backnum')) {
  function backnum($x){
      $jum=substr_count($x,".");
      $tt=explode(".", $x);
      $jml1="";
      for($i = 0; $i <= $jum; $i++){
         $jml1=$jml1.$tt[$i];
      }
      return $jml1;
   }
}
  
// Pastikan fungsi gantitglsave() tidak dideklarasikan dua kali
if (!function_exists('gantitglsave')) {
  function gantitglsave($tgl)
    {
      $pecahlan=explode('-', $tgl);
      $x=$pecahlan[0].'-'.$pecahlan[1].'-'.$pecahlan[2];
      return $x;
    }
}

// Pastikan fungsi gantitgl() tidak dideklarasikan dua kali
if (!function_exists('gantitgl')) {
  function gantitgl($tgl1)
    {
      $pecah=explode('-', $tgl1);
      $x=$pecah[2].'-'.$pecah[1].'-'.$pecah[0];
      return $x;
    }
}

// Pastikan fungsi tmzone() tidak dideklarasikan dua kali
if (!function_exists('tmzone')) {
  function tmzone($tgl){
      date_default_timezone_set('Asia/Jakarta');
      // $pecah=explode('-',$tgl);
      // $thn=$pecah[0];
      // $bln=$pecah[1];
      // $hr=$pecah[2];
      $bln=date("m",strtotime($tgl));
      $hr=date("d",strtotime($tgl));
      $thn=date("Y",strtotime($tgl));
      $tanggal=mktime(0,0,0,$bln,$hr,$thn);
      $jam=date("H:i:s");
     $a=date("H");
     if (($a>=6) && ($a<=15)) {
        $msk="Pagi";  
     }elseif (($a>11) && ($a<=15)) {
      $msk="Siang";
     }
     $tgl1=date("Y-m-d",$tanggal);
     if ($tgl1==date("Y-m-d",strtotime("1970-01-01"))){
            $tgl1=strtotime("0000-00-00");
            return $tgl1;
     }else{return $tgl1;}
  }
}

  // Mengubah datetime menjadi teks "x menit/jam/hari yang lalu"
  if (!function_exists('timeago')) {
    function timeago($datetime, $full = false) {
      date_default_timezone_set('Asia/Jakarta');
      $now  = new DateTime;
      $ago  = new DateTime($datetime);
      $diff = $now->diff($ago);

      // Calculate weeks manually (DateInterval doesn't have 'w' property)
      $w = floor($diff->d / 7);
      $d = $diff->d - ($w * 7);

      $string = array(
        'y' => array('val' => $diff->y, 'txt' => 'tahun'),
        'm' => array('val' => $diff->m, 'txt' => 'bulan'),
        'w' => array('val' => $w, 'txt' => 'minggu'),
        'd' => array('val' => $d, 'txt' => 'hari'),
        'h' => array('val' => $diff->h, 'txt' => 'jam'),
        'i' => array('val' => $diff->i, 'txt' => 'menit'),
        's' => array('val' => $diff->s, 'txt' => 'detik'),
      );
      foreach ($string as $k => &$item) {
        if ($item['val'] > 0) {
          $item = $item['val'] . ' ' . $item['txt'];
        } else {
          unset($string[$k]);
        }
      }

      if (!$full) $string = array_slice($string, 0, 1);
      return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
    }
  }

  
// Pastikan fungsi penyebut() tidak dideklarasikan dua kali
if (!function_exists('penyebut')) {
  function penyebut($nilai) {
    $nilai = abs($nilai);
    $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
    $temp = "";
    if ($nilai < 12) {
      $temp = " ". $huruf[$nilai];
    } else if ($nilai <20) {
      $temp = penyebut($nilai - 10). " Belas";
    } else if ($nilai < 100) {
      $temp = penyebut($nilai/10)." Puluh". penyebut($nilai % 10);
    } else if ($nilai < 200) {
      $temp = " Seratus" . penyebut($nilai - 100);
    } else if ($nilai < 1000) {
      $temp = penyebut($nilai/100) . " Ratus" . penyebut($nilai % 100);
    } else if ($nilai < 2000) {
      $temp = " Seribu" . penyebut($nilai - 1000);
    } else if ($nilai < 1000000) {
      $temp = penyebut($nilai/1000) . " Ribu" . penyebut($nilai % 1000);
    } else if ($nilai < 1000000000) {
      $temp = penyebut($nilai/1000000) . " Juta" . penyebut($nilai % 1000000);
    } else if ($nilai < 1000000000000) {
      $temp = penyebut($nilai/1000000000) . " Milyar" . penyebut(fmod($nilai,1000000000));
    } else if ($nilai < 1000000000000000) {
      $temp = penyebut($nilai/1000000000000) . " Trilyun" . penyebut(fmod($nilai,1000000000000));
    }     
    return $temp;
  }
}

// Pastikan fungsi penyebutsen() tidak dideklarasikan dua kali
if (!function_exists('penyebutsen')) {
  function penyebutsen($nilai) {
    $nilai = abs($nilai);
    $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
    $temp = "";
    if ($nilai < 12) {
      $temp = " ". $huruf[$nilai];
    } else if ($nilai <20) {
      $temp = penyebutsen($nilai - 10). " Belas";
    } else if ($nilai < 100) {
      $temp = penyebutsen($nilai/10)." Puluh". penyebut($nilai % 10);
    }  
    return $temp;
  }
}

// Pastikan fungsi terbilang() tidak dideklarasikan dua kali
if (!function_exists('terbilang')) {
  function terbilang($xnilai) { 
    if (strpos($xnilai,'.')>0){
      $x=explode('.',$xnilai);
      $nilai=$x[0];
      $nilai2=$x[1];
    } else {
      $nilai=$xnilai;
      $nilai2=0;
    }  

      if($nilai<0) {
        $hasil = "Minus ". trim(penyebut($nilai));
      } else {
        $hasil = trim(penyebut($nilai));
      }         

      if ($nilai2 > 0){
        return $hasil.' Rupiah '.trim(penyebutsen($nilai2)).' Sen';  
        
      } else {
        return $hasil.' Rupiah';  
      }
    
  }
}
?>


