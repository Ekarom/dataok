var  nama;
var nis;
var nopes;
var jk;
var nisn;
var nik;
var tl;
var tgll;
//var tgll = $("#tgll").datepicker("getDate");
var akta;
var agama;
var bk;
    function cekSp1() {
    
    //var nama = document.getElementById("uname").value;
    //var jk = document.getElementById("jk");
    //var value = e.options[e.selectedIndex].value;
            nama  = $("#uname").val();
            nis   = $("#nis").val();
            nopes   = $("#nopes").val();
            jk    = $("#jk").val();
            nisn  = $("#nisn").val();
            nik   = $("#nik").val();
            tl    = $("#tl").val();
            tgll  = $("#tgll").val();
           //tgll = $("#tgll").datepicker("getDate");
            akta  = $("#akta").val();
            agama = $("#agama").val();
            bk    = $("#bk").val();
    
	 if(nama ==="" || jk == "" || nisn == "" || nik == "" || tl == "" || tgll == "" || akta == "" || agama == "") {
            
            document.getElementById('sp1').hidden = true; 
           document.getElementById('cek1').hidden = false;
        } else { 
            
            document.getElementById('sp1').hidden = false;
            document.getElementById('cek1').hidden = true;
        }
     
        
    }
       
     function cekOk1() {
   if(nama == "")
   {
   alert("Nama harus diisi!");
   }
   else if(jk == "")
   {
   alert("Jenis Kelamin harus dipilih!");
   }
   else if(nisn == "")
   {
   alert("NISN harus diisi!");
   }
   else if(nisn == "")
   {
   alert("NISN harus diisi!");
   }
   else if(nik == "")
   {
   alert("NIK Siswa Harus diisi!");
   }
    else if(tl == "")
   {
   alert("Tempat lahir Siswa Harus diisi!");
   }
   else if(tgll == "")
   {
   alert("Tanggal lahir harus diisi!");
   }
   else if(akta == "")
   {
   alert("Nomor Akta lahir harus diisi!");
   }
   else if(agama == "")
   {
   alert("Agama harus dipilih!");
   }
   else
   {
   
   //alert("Data lengkap silakan lanjut");
   cekSp1();
   }
   }  

    $(function(){
        $("#sp1").on('click', function(){
            var step  = "1";
            var nama  = $("#uname").val();
            var nis   = $("#nis").val();
            var nopes   = $("#nopes").val();
            var jk    = $("#jk").val();
            var nisn  = $("#nisn").val();
            var nik   = $("#nik").val();
            var tl    = $("#tl").val();
            var tgll  = $("#tgll").val();
            var akta  = $("#akta").val();
            var agama = $("#agama").val();
            var bk    = $("#bk").val();
           

            $.ajax({
              method: "POST",
              url:    "sp1.php",
              data: { "step": step, "nama": nama, "nis": nis , "nopes": nopes ,"jk": jk , "nisn": nisn , "nik": nik , "tl": tl ,"tgll": tgll ,"akta": akta, "agama": agama , "bk": bk},
             }).done(function( data ) {
                
                var result = $.parseJSON(data);
                if(result == "0"){
               alert('Nomor Peserta/NIS Kosong Hubungi Admin'); window.location.href = '?modul=isi';
                }
                if(result =="1")
                {
    simpan();
    }
     //  $("#message").show(3000).html(str).addClass('alert').hide(5000);
      
          });
       });
     });

////////////////////2

var tbadan;
var bbadan;
var goldarah;
var jarak;
var waktu;
var j_pendaftaran;
var tglmasuk;
var hobby;
var cita2;
var paud;
var no_paud;
var asal_s;
var nopes_ujian;
var no_ijazah;
var no_skhun;
   
function cekSp2() {
    
tbadan  = $("#tbadan").val();
bbadan   = $("#bbadan").val();
goldarah   = $("#goldarah").val();
penyakit   = $("#penyakit").val();
jarak    = $("#jarak").val();
waktu_t  = $("#waktu_t").val();
j_pendaftaran   = $("#j_pendaftaran").val();
tglmasuk    = $("#tgl_masuk").val();
hobby    = $("#hobby").val();
cita2  = $("#cita2").val();
paud  = $("#paud").val();
no_paud  = $("#no_paud").val();
asal_s  = $("#asal_s").val();
nopes_ujian = $("#nopes_ujian").val();
no_ijazah    = $("#no_ijazah").val();
no_skhun    = $("#no_skhun").val();
    
	 if(tbadan =="" || bbadan == "" || goldarah == "" || jarak == "" || waktu_t == "" || j_pendaftaran == "" || tglmasuk == "" || hobby == "" || cita2 == ""  || paud == ""  || no_paud == "" || asal_s == "" || nopes_ujian == "") {
            
            document.getElementById('sp2').hidden = true; 
           document.getElementById('cek2').hidden = false;
        } else { 
            
            document.getElementById('sp2').hidden = false;
            document.getElementById('cek2').hidden = true;
        }
     
        
    }
       
 function cekOk2() {
   if(tbadan == "")
   {
   alert("Tinggi badan harus diisi!");
   }
   else if(bbadan == "")
   {
   alert("Berat badan harus diisi!");
   }
   else if(goldarah == "")
   {
   alert("Golongan darah harus dipilih!");
   }
   else if(jarak == "")
   {
   alert("Jarak tempuh harus diisi!");
   }
   else if(waktu_t == "")
   {
   alert("Waktu tempuh harus diisi!");
   }
   else if(j_pendaftaran == "")
   {
   alert("Jenis Pendaftaran harus dipilih!");
   }
   else if(tglmasuk == "")
   {
   alert("Tanggal Diterima harus di isi!");
   }
   else if(hobby == "")
   {
   alert("Hobby harus dipilih!");
   }
   else if(cita2 == "")
   {
   alert("cita2 harus dipilih!");
   }
   else if(paud == "")
   {
   alert("Paud harus dipilih!");
   }
   else if(no_paud == "")
   {
   alert("Non Paud harus dipilih!");
   }
   else if(asal_s == "")
   {
   alert("Asal Sekolah SD harus diisi!");
   }
   else if(nopes_ujian == "")
   {
   alert("Nomor Ujian SD harus diisi!");
   }
   else if(hobby == "")
   {
   alert("Hobby harus dipilih!");
   }
   
   else
   {
   cekSp2();
   //alert("Data lengkap silakan lanjut");
    
   }
   }  

    $(function(){
        $("#sp2").on('click', function(){
            var step  = "2";
            var nis   = $("#nis").val();
            var nopes   = $("#nopes").val();
            var tbadan  = $("#tbadan").val();
            var bbadan   = $("#bbadan").val();
            var goldarah   = $("#goldarah").val();
            var penyakit   = $("#penyakit").val();
            var jarak    = $("#jarak").val();
            var waktu_t  = $("#waktu_t").val();
            var j_pendaftaran   = $("#j_pendaftaran").val();
            var tglmasuk    = $("#tgl_masuk").val();
            var hobby    = $("#hobby").val();
            var cita2  = $("#cita2").val();
            var paud  = $("#paud").val();
            var no_paud  = $("#no_paud").val();
            var asal_s  = $("#asal_s").val();
            var nopes_ujian = $("#nopes_ujian").val();
            var no_ijazah    = $("#no_ijazah").val();
            var no_skhun    = $("#no_skhun").val();

            $.ajax({
              method: "POST",
              url:    "sp1.php",
              data: { "step" : step, "nopes": nopes, "nis":nis,"tbadan": tbadan, "bbadan": bbadan , "goldarah": goldarah , "penyakit": penyakit , "jarak": jarak , "waktu_t": waktu_t , "j_pendaftaran": j_pendaftaran , "tglmasuk": tglmasuk , "hobby": hobby ,"cita2": cita2 ,"paud": paud ,"no_paud": no_paud,"asal_s": asal_s, "nopes_ujian": nopes_ujian , "no_ijazah": no_ijazah , "no_skhun": no_skhun},
             }).done(function( data ) {
             simpan();
              //  var result = $.parseJSON(data);
             
           //  $("#message").show(3000).html(str).addClass('alert').hide(5000);
          });
       });
     });
//////////////////////////////////3

var lt;
var bj;
var alamat;
var kk;
var rt;
var rw;
var kel;
var kecam;
var kpos;
var t_tinggal;
var m_trans;
var anak_ke;
var jml_s;
   
function cekSp3() {
//lt   = $("#lt").val();
//bj   = $("#bj").val();
alamat  = $("#alamat").val();
kk   = $("#kk").val();
rt   = $("#rt").val();
rw   = $("#rw").val();
kel  = $("#kel").val();
kecam = $("#kecam").val();
kpos    = $("#kpos").val();
t_tinggal  = $("#t_tinggal").val();
m_trans  = $("#m_trans").val();
anak_ke = $("#anak_ke").val();
jml_s = $("#jml_s").val();
    
	 if(alamat =="" || kk == "" || rt == "" || rw == "" || kel == "" || kecam == "" || kpos == "" || t_tinggal == "" || m_trans == "" || anak_ke == "" || jml_s == "") {
            
            document.getElementById('sp3').hidden = true; 
           document.getElementById('cek3').hidden = false;
        } else { 
            
            document.getElementById('sp3').hidden = false;
            document.getElementById('cek3').hidden = true;
        }
     
        
    }
       
    function cekOk3() {
//     if(lt == "")
//   {
//   alert("Lokasi Rumah belum diambil!");
//   }
   if(alamat == "")
   {
   alert("alamat harus diisi!");
   }
   else if(kk == "")
   {
   alert("No KK harus diisi!");
   }
   else if(rt == "")
   {
   alert("RT harus diisi!");
   }
   else if(rw == "")
   {
   alert("RW harus diisi!");
   }
   else if(kel == "")
   {
   alert("Kelurahan harus diisi!");
   }
   else if(kecam == "")
   {
   alert("Kecamatan harus diisi!");
   }
   
   else if(kpos == "")
   {
   alert("Kode Pos harus diisi!");
   }
   else if(t_tinggal == "")
   {
   alert("Tempat tinggal harus dipilih!");
   }
   else if(m_trans == "")
   {
   alert("transportasi harus diisi!");
   }
   else if(anak_ke == "")
   {
   alert("Anak Ke? harus diisi!");
   }
   else if(jml_s == "")
   {
   alert("Jumlah Saudara harus diisi!");
   }
  
   else
   {
   cekSp3();
   //alert("Data lengkap silakan lanjut");
    
   }
   }  

    $(function(){
        $("#sp3").on('click', function(){
            var step  = "3";
            var nis   = $("#nis").val();
            var nopes   = $("#nopes").val();
            var alamat  = $("#alamat").val();
            var kk  = $("#kk").val();
            var rt   = $("#rt").val();
            var rw    = $("#rw").val();
            var kel  = $("#kel").val();
            var kecam   = $("#kecam").val();
            var kpos    = $("#kpos").val();
            var t_tinggal  = $("#t_tinggal").val();
            var m_trans  = $("#m_trans").val();
            var anak_ke = $("#anak_ke").val();
            var jml_s = $("#jml_s").val();

            $.ajax({
              method: "POST",
              url:    "sp1.php",
              data: { "step" : step, "nopes": nopes, "nis":nis,"alamat": alamat, "kk": kk, "rt": rt , "rw": rw , "kel": kel , "kecam": kecam , "kpos": kpos ,"t_tinggal": t_tinggal ,"m_trans": m_trans, "anak_ke": anak_ke, "jml_s": jml_s},
             }).done(function( data ) {
               simpan();
               //var result = $.parseJSON(data);
                
             // $("#message").show(3000).html(str).addClass('success').hide(5000);
          });
       });
     });
////////////////////////////////4
    $(function(){
        $("#sp4").on('click', function(){
            var step  = "4";
            var nis   = $("#nis").val();
            var nopes   = $("#nopes").val();
            var no_kps  = $("#no_kps").val();
            var no_kip  = $("#no_kip").val();
            var no_kjp   = $("#no_kjp").val();
           
          

            $.ajax({
              method: "POST",
              url:    "sp1.php",
              data: { "step" : step, "nopes": nopes, "nis":nis,"no_kps": no_kps, "no_kip": no_kip, "no_kjp": no_kjp},
             }).done(function( data ) {
               simpan();
               // var result = $.parseJSON(data);
                
             // $("#message").show(3000).html(str).addClass('success').hide(5000);
          });
       });
     });
//////////////////////////5

var ayah;
var nik_a;
var tl_a;
var pend_a;
var pekerjaan_a;
var gaji_a;
function cekSp5() {
    
ayah  = $("#ayah").val();
nik_a  = $("#nik_a").val();
tl_a   = $("#tl_a").val();
pend_a   = $("#pend_a").val();
pekerjaan_a   = $("#pekerjaan_a").val();
gaji_a   = $("#gaji_a").val();
    
	 if(ayah =="" || nik_a == "" || tl_a == "" || pekerjaan_a == "" || gaji_a =="") {
            
            document.getElementById('sp5').hidden = true; 
           document.getElementById('cek5').hidden = false;
        } else { 
            
            document.getElementById('sp5').hidden = false;
            document.getElementById('cek5').hidden = true;
        }
     
        
    }
       
     function cekOk5() {
   if(ayah == "")
   {
   alert("Nama harus diisi!");
   }
   else if(nik_a == "")
   {
   alert("NIK harus diisi!");
   }
   else if(tl_a == "")
   {
   alert("Tanggal lahir harus diisi!");
   }
   else if(pend_a == "")
   {
   alert("Pendidikan harus diisi!");
   }
   else if(pekerjaan_a == "")
   {
   alert("Pekerjaaan harus diisi!");
   }
   else if(gaji_a == "")
   {
   alert("Penghasilan harus diisi!");
   }
  
   else
   {
   
   
   cekSp5();
   }
   }  

    $(function(){
        $("#sp5").on('click', function(){
            var step  = "5";
            var nis   = $("#nis").val();
            var nopes   = $("#nopes").val();
            var ayah  = $("#ayah").val();
            var nik_a  = $("#nik_a").val();
            var tl_a   = $("#tl_a").val();
            var pend_a   = $("#pend_a").val();
            var pekerjaan_a   = $("#pekerjaan_a").val();
            var gaji_a   = $("#gaji_a").val();
            $.ajax({
              method: "POST",
              url:    "sp1.php",
              data: { "step" : step, "nopes": nopes, "nis":nis,"ayah": ayah, "nik_a": nik_a, "tl_a": tl_a , "pend_a": pend_a , "pekerjaan_a": pekerjaan_a, "gaji_a": gaji_a},
             }).done(function( data ) {
              simpan();
              //  var result = $.parseJSON(data);
                
             // $("#message").show(3000).html(str).addClass('success').hide(5000);
          });
       });
     });
//////////////////////////6


var ibu;
var nik_i;
var tl_i;
var pend_i;
var pekerjaan_i;
var gaji_i;
function cekSp6() {
    
ibu  = $("#ibu").val();
nik_i  = $("#nik_i").val();
tl_i   = $("#tl_i").val();
pend_i   = $("#pend_i").val();
pekerjaan_i   = $("#pekerjaan_i").val();
gaji_i   = $("#gaji_i").val();
    
	 if(ibu =="" || nik_i == "" || tl_i == "" || pend_i == "" || pekerjaan_i == "" || gaji_i =="") {
            
            document.getElementById('sp6').hidden = true; 
           document.getElementById('cek6').hidden = false;
        } else { 
            
            document.getElementById('sp6').hidden = false;
            document.getElementById('cek6').hidden = true;
        }
     
        
    }
       
     function cekOk6() {
   if(ibu == "")
   {
   alert("Nama harus diisi!");
   }
   else if(nik_i == "")
   {
   alert("NIK harus diisi!");
   }
   else if(tl_i == "")
   {
   alert("Tanggal lahir harus diisi!");
   }
   else if(pend_i == "")
   {
   alert("Pendidikan harus diisi!");
   }
   else if(pekerjaan_i == "")
   {
   alert("Pekerjaaan harus diisi!");
   }
   else if(gaji_i == "")
   {
   alert("Penghasilan harus diisi!");
   }
  
   else
   {
   
   
   cekSp6();
   }
   }  

    $(function(){
        $("#sp6").on('click', function(){
            var step  = "6";
            var nis   = $("#nis").val();
            var nopes   = $("#nopes").val();
            var ibu  = $("#ibu").val();
            var nik_i  = $("#nik_i").val();
            var tl_i   = $("#tl_i").val();
            var pend_i   = $("#pend_i").val();
            var pekerjaan_i   = $("#pekerjaan_i").val();
            var gaji_i   = $("#gaji_i").val();
            $.ajax({
              method: "POST",
              url:    "sp1.php",
              data: { "step" : step, "nopes": nopes, "nis":nis,"ibu": ibu, "nik_i": nik_i, "tl_i": tl_i , "pend_i": pend_i , "pekerjaan_i": pekerjaan_i, "gaji_i": gaji_i},
             }).done(function( data ) {
               simpan();
                //var result = $.parseJSON(data);
                
              //$("#message").show(3000).html(str).addClass('success').hide(5000);
          });
       });
     });
//////////////////////////7

    $(function(){
        $("#sp7").on('click', function(){
            var step  = "7";
            var nis   = $("#nis").val();
            var nopes   = $("#nopes").val();
            var nama_w  = $("#nama_w").val();
            var nik_w  = $("#nik_w").val();
            var tl_w   = $("#tl_w").val();
            var pend_w   = $("#pend_w").val();
            var pekerjaan_w   = $("#pekerjaan_w").val();
            var gaji_w   = $("#gaji_w").val();
            $.ajax({
              method: "POST",
              url:    "sp1.php",
              data: { "step" : step, "nopes": nopes, "nis":nis,"nama_w": nama_w, "nik_w": nik_w, "tl_w": tl_w , "pend_w": pend_w , "pekerjaan_w": pekerjaan_w, "gaji_w": gaji_w},
             }).done(function( data ) {
                simpan();
                //var result = $.parseJSON(data);
                
              //$("#message").show(3000).html(str).addClass('success').hide(5000);
          });
       });
     });
///////////////////////////8

var no_tlp;
var no_hp;
var email;
var email_ortu;
function cekSp8() {
    
no_tlp  = $("#no_tlp").val();
no_hp  = $("#no_hp").val();
email   = $("#email").val();
email_ortu   = $("#email_ortu").val();
    
	 if(no_tlp =="" || email_ortu == "") {
            
            document.getElementById('sp8').hidden = true; 
           document.getElementById('cek8').hidden = false;
        } else { 
            
            document.getElementById('sp8').hidden = false;
            document.getElementById('cek8').hidden = true;
        }
     
        
    }
       
     function cekOk8() {
   if(no_tlp == "")
   {
   alert("No Hp Oertu harus diisi!");
   }
   else if(email_ortu == "")
   {
   alert("Email Ortu harus diisi!");
   }
   
   else
   {
   
   
   cekSp8();
   }
   }  


    $(function(){
        $("#sp8").on('click', function(){
            var step  = "8";
            var nis   = $("#nis").val();
            var nopes   = $("#nopes").val();
            var no_tlp  = $("#no_tlp").val();
            var no_hp  = $("#no_hp").val();
            var email   = $("#email").val();
            var email_ortu   = $("#email_ortu").val();
            
            $.ajax({
              method: "POST",
              url:    "sp1.php",
              data: { "step" : step, "nopes": nopes, "nis":nis,"no_tlp": no_tlp, "no_hp": no_hp, "email": email , "email_ortu": email_ortu},
             }).done(function( data ) {
                simpan();
                //var result = $.parseJSON(data);
                
              //$("#message").show(3000).html(str).addClass('success').hide(5000);
          });
       });
     });
///////////////////9
