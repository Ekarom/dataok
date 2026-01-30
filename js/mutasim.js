            var nosurat;
            var lamp;
            var dkindki;
            var nis;
            var nama;
            var tl;
            var tgll;
            var jk;
            var asals;
            var smt;
            var tapel;
            var nisn;
            var noijazah;
            var ortu;
            var pekerjaan;
            var alamat;
            var tglb;
            var nohp;
            var noasal;
            var tgls;
            var nopes;
            var kelas;
            var formasi;
            var rombel;
            var tglm;
            var ket;
            var pass;
            var status;
function cekData() {
    
    //var nama = document.getElementById("uname").value;
    //var jk = document.getElementById("jk");
    //var value = e.options[e.selectedIndex].value;
            nosurat  = $("#nosurat").val();
            lamp   = $("#lamp").val();
            dkindki = $("#dkindki").val();
            nis    = $("#nis").val();
            nama  = $("#nama").val();
            tl    = $("#tl").val();
            tgll  = $("#tgll").val();
           //tgll = $("#tgll").datepicker("getDate");
            jk  = $("#jk").val();
            asals = $("#asals").val();
            smt    = $("#smt").val();
            tapel    = $("#tapel").val();
            nisn    = $("#nisn").val();
            noijazah    = $("#noijazah").val();
            ortu    = $("#ortu").val();
            pekerjaan    = $("#pekerjaan").val();
            alamat    = $("#alamat").val();
            tglb    = $("#tglb").val();
            nohp    = $("#nohp").val();
            noasal    = $("#noasal").val();
            tgls    = $("#tgls").val();
            nopes    = $("#nopes").val();
            pKelas    = $("#pKelas").val();
            formasi    = $("#formasi").val();
            rombel    = $("#rombel").val();
            tglm    = $("#tglm").val();
            ket    = $("#ket").val();
            pass    = $("#pass").val();
            status    = $("#status").val();
    
	 if(nosurat =="" || lamp == "" || dkindki == "" || nis == "" || nama == "" || tl == "" || tgll == "" || jk == "" || asals == "" || smt == "" || tapel ==="" || nisn == "" || ortu == "" || pekerjaan == "" || alamat == "" || tglb == "" || nohp == "" || noasal == "" || pKelas == ""|| tgls == "" || tglm == "" ) {
            
            document.getElementById('ok').hidden = true; 
           document.getElementById('cekm').hidden = false;
        } else { 
            
            document.getElementById('ok').hidden = false;
            document.getElementById('cekm').hidden = true;
        }
     
        
    }
       
 function cekIsian() {
   if(nosurat == "")
   {
   alert("Nomor Surat harus diisi!");
   }
   else if(lamp == "")
   {
   alert("Lampiran harus diisi! Tanda minus - jika tidak ada");
   }
   else if(dkindki == "")
   {
   alert("Asal Mutasi dari Daerah harus dipilih!");
   }
   else if(nis == "")
   {
   alert("NIS harus diisi!");
   }
   else if(nama == "")
   {
   alert("Nama Siswa harus diisi!");
   }
   else if(tl == "")
   {
   alert("Tempat lahir harus diisi!");
   }
   else if(tgll == "")
   {
   alert("Tanggal lahir harus diisi!");
   }
   else if(jk == "")
   {
   alert("Jenis Kelamin harus dipilih!");
   }
   else if(asals == "")
   {
   alert("Asal Sekolah harus Isi!");
   }
   else if(smt == "")
   {
   alert("Semester harus dipilih!");
   }
   else if(tapel == "")
   {
   alert("Tahun Pelajaran harus Isi!");
   }
   else if(nisn == "")
   {
   alert("NISN harus Isi!");
   }
   else if(ortu == "")
   {
   alert("Nama Ortu/wali harus Isi!");
   }
   else if(pekerjaan == "")
   {
   alert("Pekerjaan harus Isi!");
   }
   else if(alamat == "")
   {
   alert("Alamat harus Isi!");
   }
   else if(tglb == "")
   {
   alert("Tinggal Bersama harus Isi!");
   }
   else if(nohp == "")
   {
   alert("No Hp Ortu/Wali harus Isi!");
   }
   else if(noasal == "")
   {
   alert("Nomor Surat Keterangan Sekolah Asal harus Isi!");
   }
   else if(tgls == "")
   {
   alert("Tanggal Surat Keterangan Sekolah Asal harus Isi!");
   }
   else if(pKelas == "")
   {
   alert("Pilih Kelas Tujuan!");
   }
   else if(tglm == "")
   {
   alert("Tanggal Diterima harus Isi!");
   }

   else
   {
   
   //alert("Data lengkap silakan lanjut");
   cekData();
   }
   }  

    $(function(){
        $("#ok").on('click', function(){
           // var satus  = "1";
           // var nama  = $("#uname").val();
           // var nis   = $("#nis").val();
           // var jk    = $("#jk").val();
           // var nisn  = $("#nisn").val();
           // var nik   = $("#nik").val();
           // var tl    = $("#tl").val();
           // var tgll  = $("#tgll").val();
           // var akta  = $("#akta").val();
           // var agama = $("#agama").val();
           // var bk    = $("#bk").val();
           
           var step = "1";
            nosurat  = $("#nosurat").val();
            lamp   = $("#lamp").val();
            dkindki = $("#dkindki").val();
            nis    = $("#nis").val();
            nama  = $("#nama").val();
            tl    = $("#tl").val();
            tgll  = $("#tgll").val();
            jk  = $("#jk").val();
            asals = $("#asals").val();
            smt    = $("#smt").val();
            tapel    = $("#tapel").val();
            nisn    = $("#nisn").val();
            noijazah    = $("#noijazah").val();
            ortu    = $("#ortu").val();
            pekerjaan    = $("#pekerjaan").val();
            alamat    = $("#alamat").val();
            tglb    = $("#tglb").val();
            nohp    = $("#nohp").val();
            noasal    = $("#noasal").val();
            tgls    = $("#tgls").val();
           // nopes    = $("#nopes").val();
            pKelas    = $("#pKelas").val();
            formasi    = $("#formasi").val();
            rombel    = $("#rombel").val();
            tglm    = $("#tglm").val();
            ket    = $("#ket").val();
            pass    = $("#pass").val();
            status    = $("#status").val();
           

            $.ajax({
              method: "POST",
              url:    "post/mutasimpost.php",
              data: { "step": step, "nosurat": nosurat, "lamp": lamp, "dkindki": dkindki, "nis": nis , "nopes": nopes, "nama": nama ,  "tl": tl ,"tgll": tgll , "jk": jk , "asals": asals ,"smt": smt, "tapel": tapel , "nisn": nisn, "noijazah": noijazah, "ortu": ortu, "pekerjaan": pekerjaan, "alamat": alamat, "tglb": tglb, "nohp": nohp, "noasal": noasal, "tgls": tgls, "pKelas": pKelas, "formasi": formasi, "rombel": rombel, "tglm": tglm, "ket": ket, "pass": pass, "status": status},
             })
             .done(function( data ) {
                var result = $.parseJSON(data);
                if(result == "1")
                {
                alert("Nomor Induk Siswa Sudah Digunakan oleh Siswa lain, Periksa kembali sebelum memasukan nomor NIS..!");
               // alert("Nomor Induk Siswa Sudah ada, Teliti kembali sebelum memasukan nomor NIS..!");
                }
                else if(result == "0")
                {
                simpan();
              //  window.location.assign("adm/?modul=mutasim");
               setTimeout(function(){// wait for 5 secs(2)
        // window.location.href = "?modul=mutasim"; // then reload the page.(3)
       
      }, 2000); 
 
                
             
                //var result = $.parseJSON(data);
     //  $("#message").show(3000).html(str).addClass('alert').hide(5000);
      }
          });
       });
     });
     


//keluar 

            var nosurat2;
            var lamp2;
            var dkindki2;
            var nis2;
            var nama2;
            var tl2;
            var tgll2;
            var jk2;
            var asals2;
            var smt2;
            var tapel2;
            var nisn2;
            var noijazah2;
            var ortu2;
            var pekerjaan2;
            var alamat2;
            var tglb2;
            var nohp2;
            var nopes2;
            var pKelas2;
            var rombel2;
            var tglm2;
            var tujuan;
            var ket2;
           // var pKelas2;
            var status2;
   
 function cekData2() {
    
    //var nama = document.getElementById("uname").value;
    //var jk = document.getElementById("jk");
    //var value = e.options[e.selectedIndex].value;
            nosurat2  = $("#nosurat2").val();
            lamp2   = $("#lamp2").val();
            dkindki2 = $("#dkindki2").val();
            nis2    = $("#nis2").val();
            nama2  = $("#nama2").val();
            tl2    = $("#tl2").val();
            tgll2  = $("#tgll2").val();
           //tgll = $("#tgll").datepicker("getDate");
            jk2  = $("#jk2").val();
            asals2 = $("#asals2").val();
            smt2    = $("#smt2").val();
            tapel2   = $("#tapel2").val();
            nisn2    = $("#nisn2").val();
            noijazah2    = $("#noijazah2").val();
            ortu2    = $("#ortu2").val();
            pekerjaan2   = $("#pekerjaan2").val();
            alamat2    = $("#alamat2").val();
            tglb2    = $("#tglb2").val();
            nohp2    = $("#nohp2").val();
            noasal2    = $("#noasal2").val();
            tgls2    = $("#tgls2").val();
            nopes2    = $("#nopes2").val();
            pKelas2    = $("#pKelas2").val();
            //formasi    = $("#formasi").val();
            rombel2    = $("#rombel2").val();
            tglm2    = $("#tglm2").val();
            ket2    = $("#ket2").val();
           // pass    = $("#pass").val();
            status2    = $("#status2").val();
    
	 if(nosurat2 =="" || lamp2 == "" || dkindki2 == "" ||nis2 == "" || nama2 == "" || tl2 == "" || tgll2 == "" || jk2 == "" ||  smt2 == "" || tapel2 ==="" || nisn2 == "" || ortu2 == "" || pekerjaan2 == "" || alamat2 == "" || nohp2 == "" || pKelas2 == "" || rombel2 == "" || ket2 =="" || tglm2 == "") {
            
            document.getElementById('keluar').hidden = true; 
           document.getElementById('cekk').hidden = false;
        } else { 
            
            document.getElementById('keluar').hidden = false;
            document.getElementById('cekk').hidden = true;
        }
     
        
    }
       
 function cekIsian2() {
   if(nosurat2 == "")
   {
   alert("Nomor Surat harus diisi! 2");
   }
   
   else if(lamp2 == "")
   {
   alert("Lampiran harus diisi! Tanda minus - jika tidak ada");
   }
   else if(dkindki2 == "")
   {
   alert("Mau Mutasi Ke Daerah harus dipilih!");
   }
   else if(nis2 == "")
   {
   alert("NIS harus diisi!");
   }
   else if(nama2 == "")
   {
   alert("Nama Siswa harus diisi!");
   }
   else if(tl2 == "")
   {
   alert("Tempat lahir harus diisi!");
   }
   else if(tgll2 == "")
   {
   alert("Tanggal lahir harus diisi!");
   }
   else if(jk2 == "")
   {
   alert("Jenis Kelamin harus dipilih!");
   }
   
   else if(smt2 == "")
   {
   alert("Semester harus dipilih!");
   }
   else if(tapel2 == "")
   {
   alert("Tahun Pelajaran harus Isi!");
   }
   else if(nisn2 == "")
   {
   alert("NISN harus Isi!");
   }
   else if(nohp2 == "")
   {
   alert("No Hp Ortu/Wali harus Isi!");
   }
   else if(ortu2 == "")
   {
   alert("Nama Ortu/wali harus Isi!");
   }
   else if(pekerjaan2 == "")
   {
   alert("Pekerjaan harus Isi!");
   }
   else if(alamat2 == "")
   {
   alert("Alamat harus Isi!");
   }
   else if(pKelas2 == "")
   {
   alert("Pilih Kelas");
   }
    else if(rombel2 == "")
   {
   alert("Pilih Rombel");
   }
   else if(tglm2 == "")
   {
   alert("Tanggal Keluar harus Isi!");
   }
   else if(ket2 == "")
   {
   alert("isi Keterangan, Contoh : Permintaan Orang Tua");
   }
   else
   {
   
   //alert("Data lengkap silakan lanjut");
   cekData2();
   }
   }  

   function keluar(){
        
        
           // var satus  = "1";
           // var nama  = $("#uname").val();
           // var nis   = $("#nis").val();
           // var jk    = $("#jk").val();
           // var nisn  = $("#nisn").val();
           // var nik   = $("#nik").val();
           // var tl    = $("#tl").val();
           // var tgll  = $("#tgll").val();
           // var akta  = $("#akta").val();
           // var agama = $("#agama").val();
           // var bk    = $("#bk").val();
           var step  = "2";
           
            nosurat2  = $("#nosurat2").val();
            lamp2   = $("#lamp2").val();
            dkindki2 = $("#dkindki2").val();
            nis2    = $("#nis2").val();
            nama2  = $("#nama2").val();
            tl2    = $("#tl2").val();
            tgll2  = $("#tgll2").val();
            jk2  = $("#jk2").val();
            asals2 = $("#asals2").val();
            smt2    = $("#smt2").val();
            tapel2    = $("#tapel2").val();
            nisn2    = $("#nisn2").val();
            noijazah2    = $("#noijazah2").val();
            ortu2    = $("#ortu2").val();
            pekerjaan2    = $("#pekerjaan2").val();
            alamat2    = $("#alamat2").val();
            tglb2    = $("#tglb2").val();
            nohp2    = $("#nohp2").val();
            //noasal    = $("#noasal").val();
           // tgls    = $("#tgls").val();
            nopes2    = $("#nopes2").val();
            pKelas2    = $("#pKelas2").val();
            //formasi2    = $("#formasi2").val();
            rombel2    = $("#rombel2").val();
            tglm2    = $("#tglm2").val();
            tujuan    = $("#tujuan").val();
            ket2    = $("#ket2").val();
            //pass    = $("#pass").val();
            status2    = "11";
           

            $.ajax({
              method: "POST",
              url:    "post/mutasimpost.php",
              data: {"step": step, "nosurat": nosurat2, "lamp": lamp2, "dkindki": dkindki2,"nis": nis2 , "nopes": nopes2 , "nama": nama2 ,  "tl": tl2 ,"tgll": tgll2 , "jk": jk2 , "asals": asals2 ,"smt": smt2, "tapel": tapel2 , "nisn": nisn2, "noijazah": noijazah2, "ortu": ortu2, "pekerjaan": pekerjaan2, "alamat": alamat2, "tglb": tglb2, "nohp": nohp2, "pKelas": pKelas2,  "rombel": rombel2, "tglm": tglm2, "tujuan": tujuan, "ket": ket2,  "status": status2},
             })
             .done(function( data ) {
              //  var result = $.parseJSON(data);
              //  alert('data');
                simpan();
              setTimeout(function(){// wait for 5 secs(2)
         window.location.href = "?modul=mutasik"; // then reload the page.(3)
       
      }, 2000); 
      });
          
     
        }
        
        
//Tambah User
            var tahun;
            var tapel;
            var sisjalur;
            var nama;
            var tl;
            var tgll;
            var jk;
            var regppdb;
            var userid;
            var kelas;
            var pass;
           
function cekDataTs() {
    
    //var nama = document.getElementById("uname").value;
    //var jk = document.getElementById("jk");
    //var value = e.options[e.selectedIndex].value;
            tahun  = $("#tahun").val();
            tapel   = $("#tapel").val();
            sisjalur    = $("#sisjalur").val();
            nama  = $("#nama").val();
            tl    = $("#tl").val();
            tgll  = $("#tgll").val();
            jk  = $("#jk").val();
            regppdb = $("#regppdb").val();
            userid    = $("#userid").val();
            kelas  = $("#kelas").val();
            pass    = $("#pass").val();
            
	 if(tahun =="" || tapel == "" || sisjalur == "" || nama == "" || tl == "" || tgll == "" || jk == "" || regppdb == "" || userid == "" || kelas ==="") {
            
            document.getElementById('save').hidden = true; 
           document.getElementById('cekts').hidden = false;
        } else { 
            
            document.getElementById('save').hidden = false;
            document.getElementById('cekts').hidden = true;
        }
     
        
    }
       
 function cekIsianTs() {
   if(tahun == "")
   {
   alert("Tahun Masuk harus diisi!");
   }
   else if(tapel == "")
   {
   alert("Tahun Pelajaran harus diisi!");
   }
   else if(sisjalur == "")
   {
   alert("Jalur Pendaftaran harus di Pilih!");
   }
   else if(nama == "")
   {
   alert("Nama Siswa harus diisi!");
   }
   else if(tl == "")
   {
   alert("Tempat lahir harus diisi!");
   }
   else if(tgll == "")
   {
   alert("Tanggal lahir harus diisi!");
   }
   else if(jk == "")
   {
   alert("Jenis Kelamin harus dipilih!");
   }
   else if(regppdb == "")
   {
   alert("REG PPDB harus Isi!");
   }
   else if(userid == "")
   {
   alert("User Id/NIS harus di isi!");
   }
   else if(kelas == "")
   {
   alert("Kelas harus di pilih!");
   }
   
   else
   {
   
   //alert("Data lengkap silakan lanjut");
   cekDataTs();
   }
   }  

    $(function(){
        $("#save").on('click', function(){
         
            tahun  = $("#tahun").val();
            tapel   = $("#tapel").val();
            sisjalur    = $("#sisjalur").val();
            nama  = $("#nama").val();
            tl    = $("#tl").val();
            tgll  = $("#tgll").val();
            jk  = $("#jk").val();
            regppdb = $("#regppdb").val();
            userid    = $("#userid").val();
            kelas    = $("#kelas").val();
           

            $.ajax({
              method: "POST",
              url:    "post/tuser.php",
              data: { "tahun": tahun, "tapel": tapel, "sisjalur": sisjalur , "nama": nama ,  "tl": tl ,"tgll": tgll , "jk": jk , "regppdb": regppdb ,"userid": userid, "kelas": kelas},
             })
             .done(function( data ) {
                var result = $.parseJSON(data);
                if(result == "1")
                {
                alert("Nomor Induk Siswa Sudah Digunakan oleh Siswa lain, Periksa kembali sebelum memasukan nomor NIS..!");
               // alert("Nomor Induk Siswa Sudah ada, Teliti kembali sebelum memasukan nomor NIS..!");
                }
                else if(result == "0")
                {
                simpan();
              //  window.location.assign("adm/?modul=mutasim");
               setTimeout(function(){// wait for 5 secs(2)
         window.location.href = "?modul=user"; // then reload the page.(3)
       
      }, 2000); 
 
                
             
                //var result = $.parseJSON(data);
     //  $("#message").show(3000).html(str).addClass('alert').hide(5000);
      }
          });
       });
     });
     

        
        
        
    