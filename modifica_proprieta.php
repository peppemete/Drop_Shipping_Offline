<?php
include "Drop_Shipping_Offline.php";
$mail=$_POST['mail'];
$abilita=$_POST['check'];
 
$wpdb->update($wpdb->options,['option_value'=>$mail], ['option_name'=>'dso_mail']);
$wpdb->update($wpdb->options,['option_value'=>$abilita], ['option_name'=>'dso_abilita']);

echo "<script language=\"javascript\"> window.history.back(1); </script>";
?>