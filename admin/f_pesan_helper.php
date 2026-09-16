<?php
if (!function_exists('ensurePesanTables')) {
  function ensurePesanTables($connect) {
    if (!$connect) {
      return;
    }
    mysqli_query($connect, "CREATE TABLE IF NOT EXISTS mas_pesan (
      no_urut INT NOT NULL AUTO_INCREMENT,
      tgl_po DATE NOT NULL,
      no_po VARCHAR(50) NOT NULL,
      kd_toko VARCHAR(50) NOT NULL,
      kd_sup VARCHAR(50) NOT NULL DEFAULT '',
      ket VARCHAR(255) DEFAULT '',
      tot_pesan DOUBLE NOT NULL DEFAULT 0,
      status VARCHAR(20) NOT NULL DEFAULT 'DRAFT',
      execut DATETIME DEFAULT NULL,
      PRIMARY KEY (no_urut),
      UNIQUE KEY uq_po_toko (kd_toko, no_po),
      KEY idx_pesan_tgl (kd_toko, tgl_po)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($connect, "CREATE TABLE IF NOT EXISTS dum_pesan (
      no_urut INT NOT NULL AUTO_INCREMENT,
      no_po VARCHAR(50) NOT NULL,
      tgl_po DATE NOT NULL,
      kd_toko VARCHAR(50) NOT NULL,
      kd_sup VARCHAR(50) NOT NULL DEFAULT '',
      kd_brg VARCHAR(50) NOT NULL,
      nm_brg VARCHAR(255) NOT NULL DEFAULT '',
      kd_sat VARCHAR(20) DEFAULT '',
      qty_pesan DOUBLE NOT NULL DEFAULT 0,
      hrg_beli DOUBLE NOT NULL DEFAULT 0,
      jumlah DOUBLE NOT NULL DEFAULT 0,
      PRIMARY KEY (no_urut),
      KEY idx_dum_po (kd_toko, no_po, tgl_po),
      KEY idx_dum_brg (kd_brg)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }
}

if (!function_exists('pesanToNum')) {
  function pesanToNum($s) {
    $s = trim((string)$s);
    if ($s === '') {
      return 0;
    }
    $s = str_replace(' ', '', $s);
    if (strpos($s, ',') !== false) {
      $s = str_replace('.', '', $s);
      $s = str_replace(',', '.', $s);
    }
    return floatval($s);
  }
}

if (!function_exists('pesanLastHargaBeli')) {
  function pesanLastHargaBeli($connect, $kd_brg, $kd_toko) {
    $out = array('hrg_beli' => 0, 'kd_sat' => '', 'nm_sat' => '');
    $kd_brg = mysqli_real_escape_string($connect, $kd_brg);
    $kd_toko = mysqli_real_escape_string($connect, $kd_toko);

    $sql = mysqli_query($connect, "SELECT beli_brg.hrg_beli, beli_brg.kd_sat, kemas.nm_sat1
      FROM beli_brg
      LEFT JOIN kemas ON beli_brg.kd_sat=kemas.no_urut
      WHERE beli_brg.kd_brg='$kd_brg' AND beli_brg.kd_toko='$kd_toko'
        AND INSTR(IFNULL(beli_brg.ket,''),'MUTASI')=0
        AND INSTR(IFNULL(beli_brg.ket,''),'RETUR')=0
      ORDER BY beli_brg.tgl_fak DESC, beli_brg.no_urut DESC
      LIMIT 1");
    if ($sql && mysqli_num_rows($sql) > 0) {
      $row = mysqli_fetch_assoc($sql);
      $out['hrg_beli'] = floatval($row['hrg_beli']);
      $out['kd_sat'] = $row['kd_sat'];
      $out['nm_sat'] = $row['nm_sat1'];
      mysqli_free_result($sql);
      return $out;
    }
    if ($sql) {
      mysqli_free_result($sql);
    }

    $m = mysqli_query($connect, "SELECT mas_brg.kd_kem1, kemas.nm_sat1
      FROM mas_brg
      LEFT JOIN kemas ON mas_brg.kd_kem1=kemas.no_urut
      WHERE mas_brg.kd_brg='$kd_brg' LIMIT 1");
    if ($m && mysqli_num_rows($m) > 0) {
      $row = mysqli_fetch_assoc($m);
      $out['kd_sat'] = $row['kd_kem1'];
      $out['nm_sat'] = $row['nm_sat1'];
      mysqli_free_result($m);
    } elseif ($m) {
      mysqli_free_result($m);
    }
    return $out;
  }
}

if (!function_exists('pesanRefreshTotal')) {
  function pesanRefreshTotal($connect, $no_po, $tgl_po, $kd_toko) {
    $no_po = mysqli_real_escape_string($connect, $no_po);
    $tgl_po = mysqli_real_escape_string($connect, $tgl_po);
    $kd_toko = mysqli_real_escape_string($connect, $kd_toko);
    $tot = 0;
    $q = mysqli_query($connect, "SELECT SUM(jumlah) AS tot FROM dum_pesan WHERE no_po='$no_po' AND tgl_po='$tgl_po' AND kd_toko='$kd_toko'");
    if ($q) {
      $r = mysqli_fetch_assoc($q);
      $tot = floatval($r['tot']);
      mysqli_free_result($q);
    }
    mysqli_query($connect, "UPDATE mas_pesan SET tot_pesan='$tot' WHERE no_po='$no_po' AND tgl_po='$tgl_po' AND kd_toko='$kd_toko'");
    return $tot;
  }
}

if (!function_exists('pesanNextNoPo')) {
  function pesanNextNoPo($connect, $kd_toko) {
    $prefix = 'PO-'.date('Ymd').'-';
    $kd_toko = mysqli_real_escape_string($connect, $kd_toko);
    $prefix_esc = mysqli_real_escape_string($connect, $prefix);
    $n = 1;
    $q = mysqli_query($connect, "SELECT no_po FROM mas_pesan WHERE kd_toko='$kd_toko' AND no_po LIKE '$prefix_esc%' ORDER BY no_po DESC LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
      $r = mysqli_fetch_assoc($q);
      $last = $r['no_po'];
      $seq = intval(substr($last, strlen($prefix)));
      if ($seq > 0) {
        $n = $seq + 1;
      }
      mysqli_free_result($q);
    } elseif ($q) {
      mysqli_free_result($q);
    }
    return $prefix.str_pad((string)$n, 3, '0', STR_PAD_LEFT);
  }
}

if (!function_exists('pesanFmtTgl')) {
  function pesanFmtTgl($tgl) {
    if (function_exists('gantitgl')) {
      return gantitgl($tgl);
    }
    $p = explode('-', $tgl);
    if (count($p) === 3) {
      return $p[2].'-'.$p[1].'-'.$p[0];
    }
    return $tgl;
  }
}

if (!function_exists('pesanFmtUang')) {
  function pesanFmtUang($n) {
    if (function_exists('gantitides')) {
      return gantitides($n);
    }
    return number_format((float)$n, 2, ',', '.');
  }
}
