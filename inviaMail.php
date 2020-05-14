<?php
include "Drop_Shipping_Offline.php";
$ieri = date('Y/m/d', mktime(0,0,0,date(m),date(d)-1,date(Y)));
$testo =creaReport($ieri);
global $mail;
global $abilita;
if($abilita='si' && $mail!=null){
  $mail_header  = NULL;
  $mail_header .= "MIME-Version: 1.0<br>\n";
  $mail_header .= "Content-type: text/html; charset=iso-8859-1<br>\n";
  $mail_header .= 'From: "GIEMME graphics" <mete46@hotmail.it>'. "\n";
  $mail_header .= 'Reply-To: "GIEMME graphics" <mete46@hotmail.it>' . "\n";
  $mail_header .= 'Return-Path: "GIEMME graphics" <mete46@hotmail.it>';
  $mail_header .= "\n";

mail($mail, "Mail from DSO plugin", $text, $mail_header);}

?>