<?php
require_once __DIR__ . '/vendor/autoload.php';

use Spatie\PdfToImage\Pdf;

$pdf = new Pdf('test.pdf');

$number = $pdf->getNumberOfPages(); // คืนค่าจำนวนเต็ม

for ($i = 1; $i <= $number; $i++) {
    $filename = $i;
    $pdf->setPage($i)->saveImage("images/{$filename}.jpg");
}
?>
