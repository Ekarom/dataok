<?php

include "../config/konek.php";


$in ='in/';
$out ='out/';
                   $files = scandir($in);  
                     //$name is extract folder from zip file  
                     //$i=0;
                     foreach($files as $file)  
                     {  
                          $tmp = explode(".", $file);  
                          $file_ext = end($tmp);   
                          $allowed_ext = array('jpg', 'png');  
                          $nama = substr($file,0,-7);
                         
                           
                               
                               $sqld = mysqli_query($sqlconn,"select * from user where nama ='$nama'");
                               $d = mysqli_fetch_array($sqld);
                                $nisp = $d['userid'];
                                $cek = $d['nama']; 
                               
                               $new_name = $nisp.'.' . $file_ext;
                               if($nama == $cek)
                               {                          
                              copy($in.'/'.$file, $out . $new_name);  
                              //unlink($path.$name.'/'.$file);
                              echo $nama." - Ok <br/>"; 
                            }
                            else
                            {
                            echo $nama." - Tidak Ada di database <br/>";  
                            }
                          }
                                 
                     ?>
