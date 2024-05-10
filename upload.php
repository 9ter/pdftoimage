<?php

// แปลง PDF เป็นรูปภาพ
require_once __DIR__ . '/vendor/autoload.php';
use Spatie\PdfToImage\Pdf; // ต้องเป็นที่ด้านบนสุดของไฟล์

// เชื่อมต่อฐานข้อมูล MySQL
$servername = "localhost";
$username = "root";
$password = "rietc@2024";
$dbname = "pdf_to_images";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// ตรวจสอบว่ามีการอัปโหลดไฟล์ PDF และไม่มีข้อผิดพลาดในการอัปโหลด
if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
    $tempFile = $_FILES['pdfFile']['tmp_name'];

    // ดึงชื่อไฟล์และข้อมูลเพิ่มเติมของไฟล์
    $fileName = basename($_FILES['pdfFile']['name']);
    $fileInfo = pathinfo($fileName);

    // แยกชื่อไฟล์และนามสกุลของไฟล์
    $fileBaseName = $fileInfo['filename']; // ชื่อไฟล์ (ไม่รวมนามสกุล)
    $fileExtension = $fileInfo['extension']; // นามสกุลของไฟล์

    // ตั้งชื่อไฟล์ใหม่โดยเพิ่ม timestamp เข้าไป
    $newFileName = $fileBaseName . '_' . time() . '.' . $fileExtension;

    // ตำแหน่งและชื่อไฟล์ที่จะบันทึก
    $uploadDir = 'pdf/';
    $uploadPath = $uploadDir . $newFileName;



    // ย้ายไฟล์ PDF ไปยังตำแหน่งใหม่

    /*$uploadResult = move_uploaded_file($tempFile, $uploadPath);
    if (!$uploadResult) {
        $error = error_get_last();
        echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์ PDF: " . $error['message'];
    }*/


    if (move_uploaded_file($tempFile, $uploadPath)) {
        // บันทึกข้อมูลลงในฐานข้อมูล
        $sql = "INSERT INTO pdf_files (file_name, file_path) VALUES ('$fileName', '$uploadPath')";

        if ($conn->query($sql) === TRUE) {
            echo "ไฟล์ PDF ถูกอัปโหลดและบันทึกลงในฐานข้อมูลเรียบร้อยแล้ว";

            // ดึง ID ล่าสุดที่เพิ่งบันทึก
            $sql_select_id = "SELECT id FROM pdf_files ORDER BY id DESC LIMIT 1";
            $result = $conn->query($sql_select_id);

            if ($result->num_rows > 0) {
                // อ่านผลลัพธ์และเก็บ ID ไว้ในตัวแปร $idname
                while ($row = $result->fetch_assoc()) {
                    $idname = $row["id"];
                }
                echo "<br>ID =" . $idname;

                // ใช้ PdfToImage เพื่อแปลง PDF เป็นรูปภาพ
                $win = "C:\\xampp\\htdocs\\pdftoimage\\";
                $linux = "/var/www/html/testweb/pdftoimage/";
                $pdf = new Pdf($linux . $uploadPath);
                $number = $pdf->getNumberOfPages();

                // สร้างโฟลเดอร์เก็บรูปภาพตาม ID ของ PDF ในฐานข้อมูล
                $imageDir = "images/{$idname}/";
                mkdir($imageDir); // สร้างโฟลเดอร์

                // แปลงและบันทึกรูปภาพจากแต่ละหน้าของ PDF
                for ($i = 1; $i <= $number; $i++) {
                    $filename = $i;
                    $pdf->setPage($i)->saveImage("{$imageDir}{$filename}.jpg");
                }
            } else {
                echo "ไม่พบ ID ของไฟล์ที่บันทึก";
            }
        } else {
            echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลลงในฐานข้อมูล: " . $conn->error;
        }
    } else {
        echo "ไม่สามารถย้ายไฟล์ที่อัปโหลดได้. ข้อผิดพลาด: " . $_FILES['pdfFile']['error'];
    }
} else {
    echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
}

// ปิดการเชื่อมต่อ MySQL
$conn->close();
?>