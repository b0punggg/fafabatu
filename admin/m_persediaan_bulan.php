<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="img/keranjang.png">
<div class="loader1" style="z-index: 10023"><div class="loader2"><div class="loader3"></div></div></div>
<?php 
 include 'starting.php';
 include 'cekmasuk.php';
 include 'f_cetak_jual_item_helper.php';
 $connect=opendtcek();
 $kd_toko=$_SESSION['id_toko'];
 $report_brands = getReportBrandOptions($connect);
 $list_sup = array();
 $qsup = mysqli_query($connect, "SELECT kd_sup, nm_sup FROM supplier ORDER BY nm_sup ASC");
 if($qsup){
   while($rsup = mysqli_fetch_assoc($qsup)){
     $list_sup[] = $rsup;
   }
   mysqli_free_result($qsup);
 }
?>

<div id="main" style="font-size: 10pt">
  <script>	
    // Flag untuk menandai apakah sudah diklik tombol cari persediaan barang
    var filterBulanTahunAktif = false;
    
    function caripersediaan(page_number, search){
      // Show loading indicator
      var snackbar = document.getElementById('snackbar');
      if(snackbar){
        snackbar.className = 'show';
        snackbar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memuat data...';
      }
      
      $.ajax({
        url: 'm_persediaan_bulan_cari.php',
        type: 'POST',
        data: {
          keyword: $("#keyktpersediaan").val(),
          bulan: $("#bulan").val(),
          tahun: $("#tahun").val(),
          cek_stok_kosong: $("#cek_stok_kosong").is(':checked') ? 1 : 0,
          kd_brand: $("#kd_brand").val(),
          kd_sup: $("#kd_sup").val(),
          filter_bulan_tahun: filterBulanTahunAktif ? 1 : 0, // Flag apakah filter bulan/tahun aktif
          page: page_number,
          search: search
        }, 
        dataType: "json",
        timeout: 180000, // 3 minutes timeout
        beforeSend: function(e) {
          if(e && e.overrideMimeType) {
            e.overrideMimeType("application/json;charset=UTF-8");
          }
        },
        success: function(response){ 
          // Hide loading
          if(snackbar){
            snackbar.className = '';
          }
          
          if(response && response.hasil){
            $("#viewdtpersediaan").html(response.hasil);
          } else {
            console.error('Invalid response:', response);
            alert('Error: Response tidak valid');
          }
        },
        error: function (xhr, ajaxOptions, thrownError) {
          // Hide loading
          if(snackbar){
            snackbar.className = '';
          }
          
          console.error('AJAX Error:', xhr);
          console.error('Response Text:', xhr.responseText);
          
          var errorMsg = 'Error: ';
          if(xhr.status === 0){
            errorMsg += 'Timeout atau koneksi terputus. Data mungkin terlalu banyak.';
          } else if(xhr.status === 500){
            errorMsg += 'Server error: ' + xhr.responseText;
          } else {
            errorMsg += xhr.responseText || thrownError;
          }
          alert(errorMsg);
        }
      });
    }

    function cariPersediaanBarang(){
      var bulan = $("#bulan").val();
      var tahun = $("#tahun").val();
      
      if(!bulan || !tahun){
        alert('Pilih bulan dan tahun terlebih dahulu');
        return;
      }
      
      // Aktifkan flag filter bulan/tahun
      filterBulanTahunAktif = true;
      
      // Langsung panggil fungsi caripersediaan untuk menampilkan data yang difilter
      caripersediaan(1, true);
    }

    function kosongkan(){
      document.getElementById('bulan').value="<?=date('m')?>";
      document.getElementById('tahun').value="<?=date('Y')?>";
      document.getElementById('keyktpersediaan').value="";
      document.getElementById('cek_stok_kosong').checked = false;
      document.getElementById('kd_brand').value = "";
      document.getElementById('kd_sup').value = "";
      if(window.jQuery && $('#kd_brand').data('select2')){
        $('#kd_brand').val('').trigger('change');
        $('#kd_sup').val('').trigger('change');
      }
      
      // Nonaktifkan flag filter bulan/tahun untuk menampilkan semua data
      filterBulanTahunAktif = false;
      
      caripersediaan(1, true);
    }  
  </script> 

  <div id="snackbar" style="z-index: 1"></div>
  <?php 
  if(isset($_GET['pesan'])){
    $pesan=$_GET['pesan'];
    if($pesan=="simpan"){
      ?>
        <script>popnew_ok("Data berhasil disimpan");</script>
      <?php
    }else if($pesan=="hapus"){
      ?>
        <script>popnew_warning("Data berhasil dihapus");</script>
      <?php
    }else if($pesan=="gagal"){
      ?>
        <script>popnew_error("Ops.. gagal untuk transaksi");</script>
      <?php
    }
  } 
  ?>

  <div class="w3-container w3-card" style="background: linear-gradient(165deg, magenta 0%, yellow 45%, white 85%);position: sticky;top:44px;margin-top: -6px;z-index: 1;">
    <i class='fa fa-briefcase' style="font-size: 18px">&nbsp;MASTER DATA &nbsp;</i> <i class='fa fa-angle-double-right'></i>&nbsp;<span style="font-size: 18px">Persediaan Barang per Bulan</span>
  </div>

  <div class="w3-row" style="background: linear-gradient(565deg, #FFFACD 10%, white 90%);">
    <div class="col-sm-12">
      <div class="w3-container" style="padding-top:12px;padding-bottom:10px">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
        <style>
          #form-filter-persediaan .fp-label{display:block;margin-bottom:4px;font-size:10pt}
          #form-filter-persediaan .form-control{width:100%;border:1px solid black;font-size:10pt;height:32px}
          #form-filter-persediaan .fp-row{margin-left:-8px;margin-right:-8px}
          #form-filter-persediaan .fp-col{padding-left:8px;padding-right:8px;margin-bottom:10px}
          #form-filter-persediaan .fp-check{margin:0;font-weight:normal;cursor:pointer;font-size:10pt;display:inline-flex;align-items:center}
          #form-filter-persediaan .fp-check input{margin:0 6px 0 0}
          #form-filter-persediaan .fp-actions{display:flex;flex-wrap:wrap;align-items:center;gap:6px}
          #form-filter-persediaan .fp-bar{margin-top:2px;padding-top:10px;border-top:1px solid #e6d48a}
          #form-filter-persediaan .select2-container{width:100% !important}
          #form-filter-persediaan .select2-container .select2-selection--single{height:32px;border:1px solid #000}
          #form-filter-persediaan .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:30px;font-size:10pt;padding-left:8px}
          #form-filter-persediaan .select2-container--default .select2-selection--single .select2-selection__arrow{height:30px}
          #form-filter-persediaan .select2-container--default .select2-selection--single .select2-selection__placeholder{color:#555}
          .select2-container--open{z-index:2000}
          .select2-dropdown{font-size:10pt}
        </style>
        <div id="form-filter-persediaan">
          <div class="row fp-row">
            <div class="col-xs-6 col-sm-3 fp-col">
              <label class="fp-label" for="bulan"><b>Bulan</b></label>
              <select class="form-control hrf_arial" id="bulan" name="bulan">
                <option value="01">Januari</option>
                <option value="02">Februari</option>
                <option value="03">Maret</option>
                <option value="04">April</option>
                <option value="05">Mei</option>
                <option value="06">Juni</option>
                <option value="07">Juli</option>
                <option value="08">Agustus</option>
                <option value="09">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12" selected>Desember</option>
              </select>
            </div>
            <div class="col-xs-6 col-sm-3 fp-col">
              <label class="fp-label" for="tahun"><b>Tahun</b></label>
              <input class="form-control hrf_arial" id="tahun" type="number" name="tahun" value="<?=date('Y')?>">
            </div>
            <div class="col-xs-6 col-sm-3 fp-col">
              <label class="fp-label" for="kd_brand"><b>Brand</b></label>
              <select class="form-control hrf_arial" id="kd_brand" name="kd_brand" data-placeholder="SEMUA">
                <option value="">SEMUA</option>
                <?php foreach ($report_brands as $rb): ?>
                <option value="<?= htmlspecialchars($rb, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($rb, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-xs-6 col-sm-3 fp-col">
              <label class="fp-label" for="kd_sup"><b>Supplier</b></label>
              <select class="form-control hrf_arial" id="kd_sup" name="kd_sup" data-placeholder="SEMUA">
                <option value="">SEMUA</option>
                <?php foreach ($list_sup as $ls): ?>
                <option value="<?= htmlspecialchars($ls['kd_sup'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($ls['nm_sup'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row fp-row" style="display:flex;flex-wrap:wrap;align-items:center">
            <div class="col-xs-12 col-sm-6 fp-col">
              <label class="fp-check">
                <input type="checkbox" id="cek_stok_kosong" name="cek_stok_kosong" value="1">
                Sertakan stok kosong
              </label>
            </div>
            <div class="col-xs-12 col-sm-6 fp-col">
              <div class="fp-actions" style="justify-content:flex-end">
                <button type="button" onclick="cariPersediaanBarang()" class="btn btn-success btn-sm"><i class="fa fa-search"></i> Cari Persediaan Barang</button>
                <button type="button" onclick="kosongkan()" class="btn btn-warning btn-sm"><i class="fa fa-undo"></i> Reset</button>
              </div>
            </div>
          </div>
          <div class="row fp-row fp-bar" style="display:flex;flex-wrap:wrap;align-items:center">
            <div class="col-xs-12 col-sm-6 fp-col">
              <label class="fp-check">
                <input type="checkbox" id="cek_cetak_semua" name="cek_cetak_semua" value="1">
                Cetak semua data (abaikan bulan &amp; tahun)
              </label>
            </div>
            <div class="col-xs-12 col-sm-6 fp-col">
              <div class="fp-actions" style="justify-content:flex-end">
                <button type="button" onclick="cetakPDF()" class="btn btn-danger btn-sm"><i class="fa fa-file-pdf-o"></i> Cetak PDF</button>
                <button type="button" onclick="cetakExcel()" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Export Excel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pencarian -->
      <div class="yz-theme-l5 w3-border">
        <div class="w3-row">
          <div class="w3-half">
            <div id="ket_rec" class="fa fa-television" style="margin-top: 15px;margin-left: 10px;font-size: 13pt">  
            </div>
          </div>
          <div class="w3-half">
            <div class="input-group" style="margin-top: 15px">
              <input onkeyup="if(event.keyCode==13){caripersediaan(1, true);}" style="font-size: 10pt;height: 30px" type="text" class="form-control hrf_arial" placeholder="ketik pencarian [nama barang/kode]" id="keyktpersediaan">&nbsp;
              <span class="input-group-btn w3-margin-bottom">
                <button onclick="caripersediaan(1, true);" class="btn btn-primary" type="button" id="btn-ktpersediaan" style="font-size: 10pt;" title="Cari"><i class="fa fa-search"></i></button>
                <a style="font-size: 10pt;" title="Reset cari" onclick="document.getElementById('keyktpersediaan').value='';document.getElementById('btn-ktpersediaan').click();" href="#" class="btn btn-warning"><i class="fa fa-undo"></i></a>
              </span>
            </div>    
          </div>
        </div>  
      </div>
      <div class="hrf_arial" id="viewdtpersediaan" style="margin-top: 0px;"><script>caripersediaan(1,false)</script></div>
    </div>  
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
  $(document).ready(function(){
    $(".loader1").fadeOut();
    
    // Set bulan dan tahun default
    var d = new Date();
    $("#bulan").val(("0" + (d.getMonth() + 1)).slice(-2));
    $("#tahun").val(d.getFullYear());

    var sel2opt = {
      width: '100%',
      allowClear: true,
      placeholder: 'SEMUA',
      language: {
        noResults: function(){ return 'Tidak ditemukan'; },
        searching: function(){ return 'Mencari...'; }
      }
    };
    if($.fn.select2){
      $('#kd_brand').select2(sel2opt);
      $('#kd_sup').select2(sel2opt);
    }
  })
  function validasiCetak(){
  var cetakSemua = $("#cek_cetak_semua").is(':checked');
  if(cetakSemua) return true;
  var bulan = $("#bulan").val();
  var tahun = $("#tahun").val();
  if(!bulan || !tahun){
    alert("Pilih bulan dan tahun terlebih dahulu, atau centang 'Cetak semua data'");
    return false;
  }
  return true;
}

function cetakPDF(){
  if(!validasiCetak()) return;
  var cetakSemua = $("#cek_cetak_semua").is(':checked') ? 1 : 0;
  var stok = $("#cek_stok_kosong").is(':checked') ? 1 : 0;
  var brand = encodeURIComponent($("#kd_brand").val());
  var kdsup = encodeURIComponent($("#kd_sup").val());
  var url = "cetak_persediaan_pdf.php?stok="+stok+"&brand="+brand+"&kd_sup="+kdsup;
  if(cetakSemua){
    url += "&semua=1";
  } else {
    url += "&bulan="+$("#bulan").val()+"&tahun="+$("#tahun").val();
  }
  window.open(url, "_blank");
}

function cetakExcel(){
  if(!validasiCetak()) return;
  var cetakSemua = $("#cek_cetak_semua").is(':checked') ? 1 : 0;
  var stok = $("#cek_stok_kosong").is(':checked') ? 1 : 0;
  var brand = encodeURIComponent($("#kd_brand").val());
  var kdsup = encodeURIComponent($("#kd_sup").val());
  var url = "cetak_persediaan_excel.php?stok="+stok+"&brand="+brand+"&kd_sup="+kdsup;
  if(cetakSemua){
    url += "&semua=1";
  } else {
    url += "&bulan="+$("#bulan").val()+"&tahun="+$("#tahun").val();
  }
  window.location = url;
}

</script>

