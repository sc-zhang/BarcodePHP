<?php

$barNumber=$_GET['barNumber'];
$magHeight=$_GET['magHeight'];
$magWidth=$_GET['magWidth'];
$dpi=$_GET['dpi'];
$showNumber=$_GET['showNumber'];

require_once("BarcodeEAN.class.php");

$barcode=new BarcodeEAN();
$barcode->SetValue($barNumber,$magHeight,$magWidth,$dpi,$showNumber);
$barcode->ShowBarcode();
