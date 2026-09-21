<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="img/keranjang.png">
  <title>Pemesanan Barang</title>
</head>
<body>
<?php
include 'starting.php';
require_once 'f_pesan_helper.php';
$connect = opendtcek();
ensurePesanTables($connect);
$kd_toko = isset($_SESSION['id_toko']) ? $_SESSION['id_toko'] : '';
$no_po_baru = pesanNextNoPo($connect, $kd_toko);
$tgl_set = isset($_SESSION['tgl_set']) ? $_SESSION['tgl_set'] : date('Y-m-d');
?>
<div id="main" style="font-size: 10pt;">
  <div class="w3-container w3-card" style="background: linear-gradient(165deg, magenta 0%, yellow 45%, white 85%);position: sticky;top:44px;margin-top: -6px;z-index: 1;">
    <i class="fa fa-cart-arrow-down" style="font-size: 18px">&nbsp;TRANSAKSI &nbsp;</i>
    <i class="fa fa-angle-double-right"></i>&nbsp;
    <span style="font-size: 18px">Pemesanan barang</span>
    <span class="w3-right" style="font-size: 16px"><i class="fa fa-calendar-check-o"></i>&nbsp;<?=pesanFmtTgl($tgl_set)?></span>
  </div>

  <div id="fnotapesan" class="w3-modal hrf_arial" style="padding-top:50px;background-color:rgba(1,1,1,0.3);">
    <div class="w3-modal-content w3-card-4 w3-animate-zoom" style="max-width:700px;border-radius:5px;background: linear-gradient(180deg, #FAFAD2 10%, white 90%)">
      <div style="background: linear-gradient(165deg, darkblue 20%, cyan 60%, white 80%);color:white;">&nbsp;<i class="fa fa-search"></i> Cari No. PO</div>
      <span onclick="document.getElementById('fnotapesan').style.display='none'" class="w3-display-topright" style="cursor:pointer;margin-top:-3px"><img style="width:108%" src="img/tomexit2.png" alt=""></span>
      <div class="modal-body">
        <div class="input-group">
          <input id="cari_nopo" onkeyup="if(event.keyCode==13){carinopo(1,true)}" class="form-control" placeholder="ketik No. PO">
          <span class="input-group-btn">
            <button class="btn btn-primary" type="button" onclick="carinopo(1,true)">SEARCH</button>
            <button class="btn btn-warning" type="button" onclick="document.getElementById('cari_nopo').value='';carinopo(1,true)">RESET</button>
          </span>
        </div>
        <br>
        <div id="listnopo"></div>
      </div>
    </div>
  </div>

  <div style="padding:10px">
    <div class="w3-container w3-card" style="border-style:ridge;border-color:white;">
      <p style="font-size:16px"><strong><i class="fa fa-book"></i> Faktur Pemesanan</strong></p>
      <hr style="margin-top:-8px;background:linear-gradient(165deg, darkblue 5%, blue 40%, cyan 70%);height:1px">
      <div class="form-inline hrf_arial" style="flex-wrap:wrap">
        <label class="mr-2 mb-1"><b>Tgl. PO</b></label>
        <input id="tgl_po" type="date" class="form-control form-control-sm mb-1 mr-3" style="border:1px solid black" value="<?=htmlspecialchars($tgl_set)?>" onchange="carinota(1,true);carilistbrg(1,true)">
        <label class="mr-2 mb-1"><b>No. PO</b></label>
        <div class="input-group mb-1 mr-3" style="width:220px">
          <input id="no_po" type="text" class="form-control form-control-sm" style="border:1px solid black" value="<?=htmlspecialchars($no_po_baru)?>" onblur="carinota(1,true);carilistbrg(1,true)">
          <div class="input-group-append">
            <button type="button" class="btn btn-sm yz-theme-l4" style="border:1px solid black" onclick="document.getElementById('fnotapesan').style.display='block';carinopo(1,true)"><i class="fa fa-caret-down"></i></button>
          </div>
        </div>
        <label class="mr-2 mb-1"><b>Supplier</b></label>
        <input type="hidden" id="kd_sup">
        <div class="input-group mb-1 mr-3" style="width:240px">
          <input id="nm_sup" type="text" class="form-control form-control-sm" style="border:1px solid black" placeholder="ketik supplier" onkeyup="dftsup()">
          <div class="input-group-append">
            <button type="button" id="btn-nmsup" class="btn btn-sm yz-theme-l4" style="border:1px solid black" onclick="dftsup()"><i class="fa fa-caret-down"></i></button>
          </div>
        </div>
        <div id="boxsup" style="display:none;position:absolute;z-index:2"><div id="viewdftsup"></div></div>
        <label class="mr-2 mb-1"><b>Ket</b></label>
        <input id="ket" type="text" class="form-control form-control-sm mb-1 mr-3" style="border:1px solid black;width:180px">
        <button type="button" class="btn btn-info btn-sm mb-1" onclick="poBaru()"><i class="fa fa-file-o"></i> PO Baru</button>
        <a id="btn-excel" class="btn btn-success btn-sm mb-1 ml-1" target="_blank"><i class="fa fa-file-excel-o"></i> Excel</a>
        <a id="btn-pdf" class="btn btn-danger btn-sm mb-1 ml-1" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
      </div>
    </div>
  </div>

  <div class="w3-container" style="padding:6px 12px;background:#fff;border:1px solid #ddd;margin:0 10px 4px">
    <div class="form-inline" style="flex-wrap:wrap">
      <label for="filter_stok" class="mr-2 mb-1" style="font-size:9pt;font-weight:bold"><i class="fa fa-filter"></i> Filter stok:</label>
      <select id="filter_stok" class="form-control form-control-sm mb-1 mr-3" style="font-size:9pt;min-width:180px" onchange="carilistbrg(1,true)">
        <option value="semua">Semua barang</option>
        <option value="ada_stok">Hanya ada stok</option>
        <option value="stok_nol">Hanya stok 0</option>
      </select>
      <input id="cari_nmbrg" class="form-control form-control-sm mb-1 mr-2" style="font-size:9pt;min-width:200px" placeholder="cari nama / kode barang" onkeypress="if(event.keyCode==13){carilistbrg(1,true)}">
      <button type="button" class="btn btn-primary btn-sm mb-1 mr-2" onclick="carilistbrg(1,true)"><i class="fa fa-search"></i></button>
      <button type="button" class="btn btn-warning btn-sm mb-1 mr-3" onclick="document.getElementById('cari_nmbrg').value='';carilistbrg(1,true)"><i class="fa fa-undo"></i></button>
      <button type="button" class="btn btn-success btn-sm mb-1" onclick="masukkanKePO()"><i class="fa fa-check"></i> Masukkan ke PO</button>
    </div>
  </div>
  <div id="viewlistbrg" style="margin:0 10px"></div>
  <div id="viewact"></div>

  <div class="w3-container yz-theme-d1" style="padding:4px 10px;margin-top:8px">
    <div class="w3-row">
      <div class="w3-col l8"><span class="fa fa-television" style="color:yellow"> Tabel PO — barang yang dipesan</span></div>
      <div class="w3-col l4">
        <div class="input-group">
          <input id="caribrg" class="form-control hrf_arial" style="font-size:10pt" placeholder="cari nama di PO" onkeypress="if(event.keyCode==13){carinota(1,true)}">
          <span><button type="button" class="btn btn-primary" onclick="carinota(1,true)"><i class="fa fa-search"></i></button></span>
        </div>
      </div>
    </div>
  </div>
  <div id="viewnota"></div>
</div>

<script>
function carinota(page_number, search){
  $.ajax({
    url: 'f_pesancari.php',
    type: 'POST',
    data: {keyword: $("#no_po").val()+';'+$("#tgl_po").val()+';'+$("#caribrg").val(), page: page_number, search: search},
    dataType: 'json',
    success: function(response){ $("#viewnota").html(response.hasil); updateExportLinks(); },
    error: function(xhr){ alert(xhr.responseText); }
  });
}
function carilistbrg(page_number, search){
  $.ajax({
    url: 'f_pesan_list_brg.php',
    type: 'POST',
    data: {
      keyword: $("#cari_nmbrg").val(),
      filter_stok: $("#filter_stok").val(),
      no_po: $("#no_po").val(),
      tgl_po: $("#tgl_po").val(),
      page: page_number,
      search: search
    },
    dataType: 'json',
    success: function(response){ $("#viewlistbrg").html(response.hasil); },
    error: function(xhr){ alert(xhr.responseText); }
  });
}
function carinopo(page_number, search){
  $.ajax({
    url: 'f_pesancari_nopo.php',
    type: 'POST',
    data: {keyword: $("#cari_nopo").val(), page: page_number, search: search},
    dataType: 'json',
    success: function(response){ $("#listnopo").html(response.hasil); },
    error: function(xhr){ alert(xhr.responseText); }
  });
}
function dftsup(){
  $("#boxsup").slideDown("fast");
  $.ajax({
    url: 'f_belicari_sup.php',
    type: 'POST',
    data: {keyword: $("#nm_sup").val()},
    dataType: 'json',
    success: function(response){ $("#viewdftsup").html(response.hasil); },
    error: function(xhr){ alert(xhr.responseText); }
  });
}
function delrec(id){
  $.ajax({
    url: 'f_pesan_hapus_act.php',
    type: 'POST',
    data: {keydel: id},
    success: function(data){ $("#viewact").html(data); carilistbrg(1,true); }
  });
}
function poBaru(){
  document.getElementById('tgl_po').value='<?=date('Y-m-d')?>';
  document.getElementById('no_po').value='<?=htmlspecialchars($no_po_baru, ENT_QUOTES)?>';
  document.getElementById('kd_sup').value='';
  document.getElementById('nm_sup').value='';
  document.getElementById('ket').value='';
  carinota(1,true);
  carilistbrg(1,true);
}
function updateExportLinks(){
  var q = encodeURIComponent($("#no_po").val())+'&tgl='+encodeURIComponent($("#tgl_po").val());
  document.getElementById('btn-excel').href = 'f_pesan_export_excel.php?no_po='+q;
  document.getElementById('btn-pdf').href = 'f_pesan_cetak.php?no_po='+q;
}
function pesanCekRow(el){
  if (el.checked) {
    var q = el.closest('tr').querySelector('.qty-pesan-brg');
    if (q && q.value==='') q.value = '1';
  }
}
function pesanToggleAll(el){
  var ceks = document.querySelectorAll('.cek-brg');
  for (var i=0;i<ceks.length;i++){
    ceks[i].checked = el.checked;
    pesanCekRow(ceks[i]);
  }
}
function masukkanKePO(){
  if (!$("#kd_sup").val()) {
    if (typeof popnew_error==='function') popnew_error('Pilih supplier dulu'); else alert('Pilih supplier dulu');
    return;
  }
  var items = [];
  var ceks = document.querySelectorAll('.cek-brg:checked');
  for (var i=0;i<ceks.length;i++){
    var qel = ceks[i].closest('tr').querySelector('.qty-pesan-brg');
    var qty = qel ? qel.value : '';
    items.push({kd_brg: ceks[i].value, nm_brg: ceks[i].getAttribute('data-nm') || '', qty: qty});
  }
  if (items.length===0) {
    if (typeof popnew_error==='function') popnew_error('Centang barang yang akan dipesan'); else alert('Centang barang yang akan dipesan');
    return;
  }
  $.ajax({
    type: 'POST',
    url: 'f_pesan_act.php',
    data: {
      tgl_po: $("#tgl_po").val(),
      no_po: $("#no_po").val(),
      kd_sup: $("#kd_sup").val(),
      ket: $("#ket").val(),
      items: JSON.stringify(items)
    },
    success: function(data){ $("#viewact").html(data); }
  });
}
$(document).ready(function(){
  $("#btn-nmsup").click(function(){ $("#boxsup").slideToggle("fast"); $("#nm_sup").focus(); });
  $("#nm_sup").keyup(function(){ $("#boxsup").slideDown("fast"); });
  $("#boxsup").click(function(){ $("#boxsup").slideUp("fast"); });
  $("#viewdftsup").mouseleave(function(){ $("#boxsup").slideUp("fast"); });
  carinota(1,true);
  carilistbrg(1,true);
});
</script>
</body>
</html>
<?php if ($connect) { mysqli_close($connect); } ?>
