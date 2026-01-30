    $(function(){
        $("#mail").on('click', function(){
      //  $('#kirim1').html('<img  src="../files/loading.gif" />').addClass('success');
        
            var info  = "1";
            var id  = $("#id").val();
            var nopes   = $("#no_pes").val();
            var nis    = $("#nis").val();
            var nama  = $("#nama").val();
            var email   = $("#email").val();
            var email2  = $("#email_ortu").val();
            var ket    = $("#ket").val();
            var waktu  = $("#waktu").val();
           
           

            $.ajax({
              method: "POST",
              url:    "sendmail.php",
              data: { "info": info, "id": id, "nopes": nopes , "nis": nis , "nama": nama , "email": email, "email2": email2 , "ket": ket ,"waktu": waktu},
              
              
             }).done(function(data) {
               
               info();
              alert("Email terkirim");
           //    $('#kirim1').remove();
                //var result = $.parseJSON(data);
     //  $("#message").show(3000).html(str).addClass('alert').hide(5000);
      
          });
       });
     });
     
 //////// verifikasi    
     
    $(function(){
        $("#verifi").on('click', function(){
          $('#kirim').html('<img  src="../files/loading.gif" />').addClass('success');
            var info  = "2";
            var id  = $("#id").val();

            $.ajax({
              method: "POST",
              url:    "sendmail.php",
              data: { "info": info, "id": id},
             }).done(function( data ) {
               
               terkirim();
              $('#kirim').html('<button type="button" id="batalverifi2" class="btn btn-block btn-success btn-lg icon icon ion-checkmark" title="Klik untuk Batal verifikasi"> Sudah Terverifikasi</button>');
                //var result = $.parseJSON(data);
     //  $("#message").show(3000).html(str).addClass('alert').hide(5000);
      
          });
       });
     });
     
  //////// verifikasi  valid  
     
    $(function(){
        $("#batal_verifi").on('click', function(){
            var info  = "3";
            var id  = $("#id").val();

            $.ajax({
              method: "POST",
              url:    "sendmail.php",
              data: { "info": info, "id": id},
             }).done(function( data ) {
                
               batal_verifi();
               $( "#dataTables-example2" ).load( "?modul=pd_valid #dataTables-example2" );
               
                //var result = $.parseJSON(data);
     //  $("#message").show(3000).html(str).addClass('alert').hide(5000);
      
          });
       });
     });  
     
   //////// verifikasi  valid  
     
    $(function(){
        $("#batalverifi2").on('click', function(){
        $('#batal').html('<img  src="../files/loading.gif" />').addClass('success');
            var info  = "3";
            var id  = $("#id").val();

            $.ajax({
              method: "POST",
              url:    "sendmail.php",
              data: { "info": info, "id": id},
             }).done(function( data ) {
                
               batal_verifi2();
                $('#batal').html('<button type="button" id="verifi" class="btn btn-block btn-danger btn-lg icon ion-unlocked" title="Klik untuk verifikasi"> Verifikasi</button>');
             //  $( "#dataTables-example2" ).load( "?modul=pd_valid #dataTables-example2" );
               
                //var result = $.parseJSON(data);
     //  $("#message").show(3000).html(str).addClass('alert').hide(5000);
      
          });
       });
     });                  
          