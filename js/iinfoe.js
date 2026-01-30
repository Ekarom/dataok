            var idu;
            var info;
           
        
    function cekDatap() {
    
    //var nama = document.getElementById("uname").value;
    //var jk = document.getElementById("jk");
    //var value = e.options[e.selectedIndex].value;
            info  = $("#infop").val();
       
            
    
	 if(info =="" ) {
            
            document.getElementById('okp').hidden = true; 
           document.getElementById('cekp').hidden = false;
        } else { 
            
            document.getElementById('okp').hidden = false;
            document.getElementById('cekp').hidden = true;
        }
     
        
    }
       
 function cekIsianp() {
   if(info == "")
   {
   alert("Informasi harus diisi!");
   }

   else
   {
   
   //alert("Data lengkap silakan lanjut");
   cekDatap();
   }
   }  

    $(function(){
        $("#okp").on('click', function(){
           idp  = $("#idp").val();
           infop  = $("#infop").val();

            $.ajax({
              method: "POST",
              url:    "post/infoupdate.php",
              data: { "idp": idp,"infop": infop},
             }).done(function( data ) {
                
              simpan();
                //window.location.assign("adm/?modul=mutasim");
                setTimeout(function(){// wait for 5 secs(2)
         window.location.href = "?modul=info_skl"; // then reload the page.(3)
        location.reload();
     }, 2000); 
      
          });
       });
     });
