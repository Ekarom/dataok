            var idu;
            var info;
           
        
    function cekData() {
    
    //var nama = document.getElementById("uname").value;
    //var jk = document.getElementById("jk");
    //var value = e.options[e.selectedIndex].value;
            info  = $("#info").val();
       
            
    
	 if(info =="" ) {
            
            document.getElementById('ok').hidden = true; 
           document.getElementById('ceki').hidden = false;
        } else { 
            
            document.getElementById('ok').hidden = false;
            document.getElementById('ceki').hidden = true;
        }
     
        
    }
       
 function cekIsian() {
   if(info == "")
   {
   alert("Informasi harus diisi!");
   }

   else
   {
   
   //alert("Data lengkap silakan lanjut");
   cekData();
   }
   }  

    $(function(){
        $("#ok").on('click', function(){
           idu  = $("#idu").val();
           info  = $("#info").val();

            $.ajax({
              method: "POST",
              url:    "post/infosklupdate.php",
              data: { "idu": idu,"info": info},
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
