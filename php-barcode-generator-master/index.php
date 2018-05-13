<?php
	include "src/BarcodeGenerator.php";
	include "src/BarcodeGeneratorHTML.php";
	$generator = new Picqer\Barcode\BarcodeGeneratorHTML();
	echo $generator->getBarcode('zainul abideen', $generator::TYPE_CODE_128);
?>