<?php
session_start();
include 'config.php';
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : '';
$concet  = opendtcek();
$uid = mysqli_real_escape_string($concet, $id_user);
$sql = mysqli_query($concet, "SELECT kd_bar, nm_brg, hrg_jum1, copy FROM mas_brg WHERE pilih='1' AND id_user='".$uid."' ORDER BY nm_brg ASC");
$items = array();
while ($d = mysqli_fetch_assoc($sql)) {
    $copies = max(1, (int) $d['copy']);
    $it = array(
        'nm'    => $d['nm_brg'],
        'bar'   => (string) $d['kd_bar'],
        'harga' => 'Rp. '.number_format((int) round($d['hrg_jum1']), 0, ',', '.'),
    );
    for ($z = 0; $z < $copies; $z++) {
        $items[] = $it;
    }
}
mysqli_close($concet);
$rows = array_chunk($items, 2);
$jumlah = count($items);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Preview Cetak Barcode</title>
  <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; background: #7a7a7a; font-family: Arial, sans-serif; }
    .toolbar {
      position: sticky; top: 0; z-index: 2;
      background: #e8e8e8; border-bottom: 1px solid #999;
      padding: 6px 10px; display: flex; align-items: center; gap: 8px;
    }
    .toolbar button {
      padding: 4px 12px; cursor: pointer; font-size: 12px;
    }
    .toolbar .info { margin-left: auto; font-size: 12px; color: #333; }
    .board {
      padding: 16px;
      display: flex; flex-wrap: wrap; gap: 14px;
      justify-content: flex-start;
    }
    .sheet {
      width: 70mm; height: 15mm;
      background: #fff; border: 1px solid #222;
      display: flex; flex-direction: row;
      page-break-after: always;
    }
    .lbl {
      width: 34mm; height: 15mm;
      padding: 0.4mm 1.2mm 0.3mm 1.2mm;
      display: flex; flex-direction: column; justify-content: flex-start;
    }
    .lbl .nm {
      font-size: 7pt; font-weight: bold; text-align: left;
      line-height: 1.1; height: 3mm; overflow: hidden; white-space: nowrap;
    }
    .lbl svg { width: 32mm; height: 8mm; display: block; }
    .lbl .bot {
      display: flex; justify-content: space-between;
      font-size: 6pt; font-weight: bold; line-height: 1.1;
    }
    @media print {
      .toolbar { display: none; }
      body { background: #fff; }
      .board { padding: 0; gap: 0; }
      .sheet { border: none; margin: 0; }
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <button type="button" onclick="window.print()">Preview PDF</button>
    <button type="button" onclick="cetakPrinter()" style="font-weight:bold">Cetak ke printer label</button>
    <button type="button" onclick="window.close()">Close</button>
    <span class="info"><?=$jumlah?> stiker · <?=count($rows)?> halaman (1 halaman = 2 label)</span>
  </div>
  <div class="board">
    <?php if (!$items) { ?>
      <p style="color:#fff">Belum ada yang dipilih.</p>
    <?php } foreach ($rows as $row) { ?>
      <div class="sheet">
        <?php foreach ($row as $it) { ?>
        <div class="lbl">
          <div class="nm"><?=htmlspecialchars($it['nm'])?></div>
          <svg class="bc" data-val="<?=htmlspecialchars($it['bar'], ENT_QUOTES)?>"></svg>
          <div class="bot">
            <span><?=htmlspecialchars($it['bar'])?></span>
            <span><?=htmlspecialchars($it['harga'])?></span>
          </div>
        </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
<script>
function cariPrinterLabel(list){
  var names = [];
  if (Array.isArray(list)) {
    for (var i = 0; i < list.length; i++) {
      names.push(typeof list[i] === 'string' ? list[i] : (list[i].name || ''));
    }
  }
  var kunci = ['4BARCODE', '3B-360', 'XP-360', '360B', 'XPRINTER'];
  for (var k = 0; k < kunci.length; k++) {
    for (var n = 0; n < names.length; n++) {
      if (names[n].toUpperCase().indexOf(kunci[k]) >= 0) return names[n];
    }
  }
  throw new Error('Printer label tidak ditemukan. Yang terpasang: ' + names.join(', ') + '. QZ Tray harus menyala.');
}
function cetakPrinter(){
  if (typeof qz === 'undefined') { alert('QZ Tray belum dimuat'); return; }
  fetch('f_cetbarcode_tspl.php').then(function(r){ return r.json(); }).then(function(res){
    if (!res.jumlah) { alert('Belum ada yang dipilih'); return; }
    var p = qz.websocket.isActive() ? Promise.resolve() : qz.websocket.connect();
    return p.then(function(){ return qz.printers.find(); })
      .then(function(list){ return cariPrinterLabel(list); })
      .then(function(printer){
        var cfg = qz.configs.create(printer, { encoding:'UTF-8', rasterize:false, scaleContent:false, altPrinting:false });
        return qz.print(cfg, [{ type:'raw', format:'command', flavor:'plain', data: res.tspl }]);
      })
      .then(function(){ alert('Terkirim ke printer label (' + res.jumlah + ' stiker)'); });
  }).catch(function(e){ alert('Gagal cetak: ' + e); });
}
document.querySelectorAll('svg.bc').forEach(function(el){
  var v = el.getAttribute('data-val') || '';
  if (!v) return;
  try {
    JsBarcode(el, v, { format:'CODE128', displayValue:false, height:32, width:1.1, margin:0 });
  } catch (e) {}
});
</script>
</body>
</html>
