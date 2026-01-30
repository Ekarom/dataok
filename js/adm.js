
    $(function(){
       
     //  $('.btn').click(function(event){
       
      
        $("#update").on('click', function(){
           // var step  = "1";
            if (!confirm('Apakah anda yakin untuk merubah data tersebut?'))
         return;
             event.preventDefault();
            var no_pes  = $("#no_pes").val();
            var nama  = $("#nama").val();
            var nis   = $("#nis").val();
            var jk    = $("#jk").val();
            var nisn  = $("#nisn").val();
            var nik   = $("#nik").val();
            var tl    = $("#tl").val();
            var tgll  = $("#tgll").val();
            var akta  = $("#akta").val();
            var agama = $("#agama").val();
            var negara = $("#negara").val();
            var bk    = $("#bk").val();
            
            ///2
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
            
            ///3
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
            
            ///4
            var no_kps  = $("#kps_pkh").val();
            var no_kip  = $("#no_kip").val();
            var no_kjp   = $("#no_kjp").val();
            
          ///5
          
          var ayah  = $("#ayah").val();
            var nik_a  = $("#nik_a").val();
            var tl_a   = $("#tl_a").val();
            var pend_a   = $("#pend_a").val();
            var pekerjaan_a   = $("#pekerjaan_a").val();
            var gaji_a   = $("#gaji_a").val();
          ///6
          
          var ibu  = $("#ibu").val();
            var nik_i  = $("#nik_i").val();
            var tl_i   = $("#tl_i").val();
            var pend_i   = $("#pend_i").val();
            var pekerjaan_i   = $("#pekerjaan_i").val();
            var gaji_i   = $("#gaji_i").val();
          ///7
          var nama_w  = $("#nama_w").val();
            var nik_w  = $("#nik_w").val();
            var tl_w   = $("#tl_w").val();
            var pend_w   = $("#pend_w").val();
            var pekerjaan_w   = $("#pekerjaan_w").val();
            var gaji_w   = $("#gaji_w").val();
          //8
          
            var no_tlp  = $("#no_tlp").val();
            var no_hp  = $("#no_hp").val();
            var email   = $("#email").val();
            var email_ortu   = $("#email_ortu").val();
            var sis_jalur   = $("#sis_jalur").val();
            var pringkat   = $("#pringkat").val();
          
          
            $.ajax({
              method: "POST",
              url:    "post/update.php",
              data: { "no_pes": no_pes,"nama": nama, "nis": nis , "jk": jk , "nisn": nisn , "nik": nik , "tl": tl ,"tgll": tgll ,"akta": akta, "agama": agama, "negara": negara  , "bk": bk, "tbadan": tbadan, "bbadan": bbadan , "goldarah": goldarah , "penyakit": penyakit , "jarak": jarak , "waktu_t": waktu_t , "j_pendaftaran": j_pendaftaran , "tglmasuk": tglmasuk , "hobby": hobby ,"cita2": cita2 ,"paud": paud ,"no_paud": no_paud,"asal_s": asal_s, "nopes_ujian": nopes_ujian , "no_ijazah": no_ijazah , "no_skhun": no_skhun, "alamat": alamat, "kk": kk, "rt": rt , "rw": rw , "kel": kel , "kecam": kecam , "kpos": kpos ,"t_tinggal": t_tinggal ,"m_trans": m_trans, "anak_ke": anak_ke, "jml_s": jml_s, "no_kps": no_kps, "no_kip": no_kip, "no_kjp": no_kjp, "ayah": ayah, "nik_a": nik_a, "tl_a": tl_a , "pend_a": pend_a , "pekerjaan_a": pekerjaan_a, "gaji_a": gaji_a, "ibu": ibu, "nik_i": nik_i, "tl_i": tl_i , "pend_i": pend_i , "pekerjaan_i": pekerjaan_i, "gaji_i": gaji_i, "nama_w": nama_w, "nik_w": nik_w, "tl_w": tl_w , "pend_w": pend_w , "pekerjaan_w": pekerjaan_w, "gaji_w": gaji_w, "no_tlp": no_tlp, "no_hp": no_hp, "email": email , "email_ortu": email_ortu , "sis_jalur": sis_jalur , "pringkat": pringkat},
             
            error: function (request, error) { 
            
            //console.log(arguments); 
            alert("Gagal disimpan, NIs Kosong/Jalur Pendaftaran & peringkat harus di Isi!");
            },
            success: function(data) { 
             
          simpan();
         setTimeout(function(){// wait for 5 secs(2)
          location.reload(); // then reload the page.(3)
       
     }, 2000); 
 
          
           
  
        
            }
          
            
});
       
       });
       });
    

