<?php

include "../config/konek.php";

//ijazah
$in ='../../images';
$out ='../../upload/ijazah/';
                   $files = scandir($in);  
                     //$name is extract folder from zip file  
                     //$i=0;
                     foreach($files as $file)  
                     {  
                         // $tmp = explode(".", $file);  
                          //$file_ext = end($tmp);   
                         // $allowed_ext = array('jpg', 'png');  
                         // $nama = substr($file,0,-7);
                         
                           
                               
                               $sqld = mysqli_query($sqlconn,"select * from data_pd where file_i ='$file'");
                               $d = mysqli_fetch_array($sqld);
                               // $nisp = $d['userid'];
                                $cek = $d['file_i']; 
                               
                               //$new_name = $file.'.' . $file_ext;
                               if(isset($cek))
                               {
                               if($file == $cek)
                               {                          
                              copy($in.'/'.$file, $out . $file);  
                              //unlink($path.$name.'/'.$file);
                              echo $file." - Ok <br/>"; 
                             // unlink($in.'/'.$file);
                              //echo $file." - on Folder In Success Delete <br/>";
                            }
                            else
                            {
                            echo $file." - Tidak Ada di database <br/>";  
                            }
                          }
                                } 
                     ?>
