<?php
 
 header('content-type:image/png');
 require_once 'vendor/autoload.php';

 $qr = new Endroid\QrCode\QrCode();
 $qr->setText('http://www.google.com');
 $qr->setSize(200);
 $qr->SetPadding(10);

 $qr->render();

?>