$(function(){
        $("#update").on('click', function(){
      
           
          var  sekolah  = $("#sekolah").val();
          var  alamat  = $("#alamat").val();
          var  tlp  = $("#tlp").val();
          var  email  = $("#email").val();
          var  web  = $("#web").val();
          var  kepsek  = $("#kepsek").val();
          var  nip  = $("#nip").val();
          var  pengawas  = $("#pengawas").val();
          var  nipp  = $("#nipp").val();
          var  ktu  = $("#ktu").val();
          var  nipk  = $("#nipk").val();
            
            $.ajax({
              method: "POST",
              url:    "post/profils.php",
              data: { "sekolah": sekolah, "alamat": alamat, "tlp": tlp , "email": email ,   "web": web , "kepsek": kepsek ,"nip": nip , "pengawas": pengawas , "nipp": nipp ,"ktu": ktu ,"nipk": nipk},
             }).done(function( data ) {
                
              simpan();
               
                setTimeout(function(){// wait for 5 secs(2)
          window.location.href = "?modul=setting"; // then reload the page.(3)
       
      }, 2000); 
      
          });
       });
     });