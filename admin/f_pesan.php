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

  <form id="form-pesan" action="f_pesan_act.php" method="post" style="padding:10px">
    <input type="hidden" name="no_urut" id="no_urut">
    <div class="w3-row">
      <div class="w3-col s12 m12 l4">
        <div class="w3-container w3-card" style="border-style:ridge;border-color:white;">
          <p style="font-size:16px"><strong><i class="fa fa-book"></i> Faktur Pemesanan</strong></p>
          <hr style="margin-top:-8px;background:linear-gradient(165deg, darkblue 5%, blue 40%, cyan 70%);height:1px">
          <div class="form-group row hrf_arial">
            <label class="col-sm-4 col-form-label"><b>Tgl. PO</b></label>
            <div class="col-sm-8">
              <input id="tgl_po" name="tgl_po" type="date" class="form-control hrf_arial" style="border:1px solid black" required value="<?=htmlspecialchars($tgl_set)?>" onchange="carinota(1,true)">
            </div>
          </div>
          <div class="form-group row hrf_arial" style="margin-top:-12px">
            <label class="col-sm-4 col-form-label"><b>No. PO</b></label>
            <div class="col-sm-8">
              <div class="input-group">
                <input id="no_po" name="no_po" type="text" class="form-control hrf_arial" style="border:1px solid black" required value="<?=htmlspecialchars($no_po_baru)?>" onblur="carinota(1,true)">
                <span><button type="button" class="form-control yz-theme-l4" style="border:1px solid black" onclick="document.getElementById('fnotapesan').style.display='block';carinopo(1,true);document.getElementById('cari_nopo').focus()"><i class="fa fa-caret-down"></i></button></span>
              </div>
            </div>
          </div>
          <div class="form-group row hrf_arial" style="margin-top:-12px">
            <label class="col-sm-4 col-form-label"><b>Supplier</b></label>
            <div class="col-sm-8">
              <input type="hidden" id="kd_sup" name="kd_sup">
              <div class="input-group">
                <input id="nm_sup" name="nm_sup" type="text" class="form-control hrf_arial" style="border:1px solid black" required placeholder="ketik nama supplier" onkeyup="dftsup()">
                <span><button type="button" id="btn-nmsup" class="form-control yz-theme-l4" style="border:1px solid black" onclick="dftsup()"><i class="fa fa-caret-down"></i></button></span>
              </div>
              <div id="boxsup" class="container" style="display:none;position:absolute;z-index:1;margin-left:-15px">
                <div id="viewdftsup"></div>
              </div>
            </div>
          </div>
          <div class="form-group row hrf_arial" style="margin-top:-12px">
            <label class="col-sm-4 col-form-label"><b>Keterangan</b></label>
            <div class="col-sm-8">
              <input id="ket" name="ket" type="text" class="form-control hrf_arial" style="border:1px solid black">
            </div>
          </div>
        </div>
      </div>

      <div class="w3-col s12 m12 l8">
        <div class="w3-container w3-card" style="border-style:ridge;border-color:white;">
          <p style="font-size:16px"><strong><i class="fa fa-briefcase"></i> Data Barang</strong></p>
          <hr style="margin-top:-8px;background:linear-gradient(165deg, darkblue 5%, blue 40%, cyan 70%);height:1px">
          <input type="hidden" id="kd_brg" name="kd_brg">
          <input type="hidden" id="kd_sat" name="kd_sat">
          <div class="form-group row hrf_arial">
            <label class="col-sm-3 col-form-label"><b>Nm. Barang</b></label>
            <div class="col-sm-9">
              <div class="input-group">
                <input id="nm_brg" name="nm_brg" type="text" class="form-control hrf_arial" style="border:1px solid black" placeholder="ketik nama barang" onkeyup="carinmbrg(1,true)">
                <span><button type="button" id="btn-nmbrg" class="form-control yz-theme-l4" style="border:1px solid black" onclick="carinmbrg(1,true)"><i class="fa fa-caret-down"></i></button></span>
              </div>
              <div id="boxnmbrg" style="display:none;position:absolute;z-index:1;width:100%">
                <div id="viewnmbrg" class="w3-card" style="background:#fff"></div>
              </div>
            </div>
          </div>
          <div class="form-group row hrf_arial" style="margin-top:-12px">
            <label class="col-sm-3 col-form-label"><b>Qty</b></label>
            <div class="col-sm-3">
              <input id="qty_pesan" name="qty_pesan" type="number" step="any" min="0" class="form-control hrf_arial" style="border:1px solid black" placeholder="qty">
            </div>
            <label class="col-sm-2 col-form-label"><b>Satuan</b></label>
            <div class="col-sm-4">
              <input id="nm_sat" type="text" class="form-control hrf_arial" style="border:1px solid black;background:#eee" readonly>
            </div>
          </div>
          <div class="form-group row hrf_arial" style="margin-top:-12px">
            <label class="col-sm-3 col-form-label"><b>Harga Beli</b></label>
            <div class="col-sm-9">
              <input id="hrg_beli" name="hrg_beli" type="text" class="form-control hrf_arial" style="border:1px solid black;background:#eee" readonly placeholder="harga beli terakhir">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-4">
              <button type="submit" class="w3-card-2 yz-theme-l1" style="width:100%;height:30px;font-size:10pt"><i class="fa fa-save"></i> <b>SIMPAN ITEM</b></button>
            </div>
            <div class="col-sm-4">
              <button type="button" class="btn-warning" style="width:100%;height:30px;font-size:10pt" onclick="kosongitem()"><i class="fa fa-undo"></i> <b>RESET ITEM</b></button>
            </div>
            <div class="col-sm-4">
              <button type="button" class="btn btn-info" style="width:100%;height:30px;font-size:10pt" onclick="poBaru()"><i class="fa fa-file-o"></i> <b>PO BARU</b></button>
            </div>
          </div>
          <div class="w3-margin-top" style="padding-bottom:8px">
            <a id="btn-excel" class="btn btn-success btn-sm" target="_blank"><i class="fa fa-file-excel-o"></i> Excel</a>
            <a id="btn-pdf" class="btn btn-danger btn-sm" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
          </div>
        </div>
      </div>
    </div>
  </form>

  <div id="viewact"></div>
  <div class="w3-container yz-theme-d1" style="padding:4px 10px;margin-top:8px">
    <div class="w3-row">
      <div class="w3-col l8"><span class="fa fa-television" style="color:yellow"> Daftar barang yang dipesan</span></div>
      <div class="w3-col l4">
        <div class="input-group">
          <input id="caribrg" class="form-control hrf_arial" style="font-size:10pt" placeholder="cari nama barang di PO" onkeypress="if(event.keyCode==13){carinota(1,true)}">
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
function carinmbrg(page_number, search){
  $("#boxnmbrg").slideDown("fast");
  $.ajax({
    url: 'f_pesan_cari_brg.php',
    type: 'POST',
    data: {keyword: $("#nm_brg").val(), page: page_number, search: search},
    dataType: 'json',
    success: function(response){ $("#viewnmbrg").html(response.hasil); },
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
    success: function(data){ $("#viewact").html(data); }
  });
}
function kosongitem(){
  document.getElementById('no_urut').value='';
  document.getElementById('kd_brg').value='';
  document.getElementById('nm_brg').value='';
  document.getElementById('kd_sat').value='';
  document.getElementById('nm_sat').value='';
  document.getElementById('qty_pesan').value='';
  document.getElementById('hrg_beli').value='';
  document.getElementById('nm_brg').focus();
}
function poBaru(){
  kosongitem();
  document.getElementById('tgl_po').value='<?=date('Y-m-d')?>';
  document.getElementById('no_po').value='<?=htmlspecialchars($no_po_baru, ENT_QUOTES)?>';
  document.getElementById('kd_sup').value='';
  document.getElementById('nm_sup').value='';
  document.getElementById('ket').value='';
  carinota(1,true);
}
function updateExportLinks(){
  var q = encodeURIComponent($("#no_po").val())+'&tgl='+encodeURIComponent($("#tgl_po").val());
  document.getElementById('btn-excel').href = 'f_pesan_export_excel.php?no_po='+q;
  document.getElementById('btn-pdf').href = 'f_pesan_cetak.php?no_po='+q;
}
$(document).ready(function(){
  $("#btn-nmsup").click(function(){ $("#boxsup").slideToggle("fast"); $("#nm_sup").focus(); });
  $("#nm_sup").keyup(function(){ $("#boxsup").slideDown("fast"); });
  $("#boxsup").click(function(){ $("#boxsup").slideUp("fast"); });
  $("#viewdftsup").mouseleave(function(){ $("#boxsup").slideUp("fast"); });
  $("#btn-nmbrg").click(function(){ $("#boxnmbrg").slideToggle("fast"); carinmbrg(1,true); });
  $("#nm_brg").keyup(function(){ $("#boxnmbrg").slideDown("fast"); });
  $("#boxnmbrg").click(function(){ $("#boxnmbrg").slideUp("fast"); });
  $("#viewnmbrg").mouseleave(function(){ $("#boxnmbrg").slideUp("fast"); });
  $('#form-pesan').submit(function(){
    $.ajax({
      type: 'POST',
      url: $(this).attr('action'),
      data: $(this).serialize(),
      success: function(data){ $("#viewact").html(data); }
    });
    return false;
  });
  carinota(1,true);
});
</script>
</body>
</html>
<?php if ($connect) { mysqli_close($connect); } ?>
