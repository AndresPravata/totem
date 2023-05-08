<?php

require __DIR__ . '/ticket/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

$nombre_impresora = "POS-58"; 

$connector = new WindowsPrintConnector($nombre_impresora);
$printer = new Printer($connector);

$printer->setJustification(Printer::JUSTIFY_CENTER);

try {
	$logo = EscposImage::load("geek.png", false);
    $printer->bitImage($logo);
} catch(Exception $e) {}

$printer->text("\n"."Veterinaria Luffi" . "\n");
$printer->text("Direccion: Cnel. Suarez 451" . "\n");
$printer->text("Tel: 0260 459-9286" . "\n");
$printer->text("\n");

$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text("Nombre: Juan Perez\n");
$printer->text("Fecha de turno: 2023-05-01\n");
$printer->text("Turno N°: 001\n");

$printer->text("\n");
$printer->text("-----------------------------"."\n");
$printer->setJustification(Printer::JUSTIFY_LEFT);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "citas";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT * from tabla where id = {$_POST['ID']}";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $printer->text("ID del paciente: " . $row["id_paciente"] . "\n");
        $printer->text("Fecha programada: " . $row["date_sched"] . "\n");
        $printer->text("-----------------------------"."\n");
    }
} else {
    $printer->text("No se encontraron citas programadas.\n");
}

$conn->close();

$printer->feed(3);
$printer->cut();
$printer->pulse();
$printer->close();

?>
