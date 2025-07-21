<?php

require './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// CONEXIÓN A LA BD
$host = "localhost";
$dbname = "inventario";
$user = "root";
$password = "root";

$conexion = new mysqli($host, $user, $password, $dbname);
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// CONSULTA
$sql = "SELECT * FROM bienes ORDER BY id ASC";
$resultado = $conexion->query($sql);

// CREAR EXCEL
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator("yp")
    ->setLastModifiedBy("yo")
    ->setTitle("Bienes")
    ->setDescription("Listado de bienes");

$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle("Bienes");

// FUNCIÓN PARA CONVERTIR NÚMEROS A LETRAS DE COLUMNA (A, B, C, ...)
function getColLetter($index) {
    $letter = '';
    while ($index > 0) {
        $index--;
        $letter = chr(65 + ($index % 26)) . $letter;
        $index = intval($index / 26);
    }
    return $letter;
}

// SI HAY RESULTADOS
if ($resultado->num_rows > 0) {
    $campos = $resultado->fetch_fields();
    foreach ($campos as $i => $campo) {
        $col = getColLetter($i + 1);
        $hoja->setCellValue($col . '1', strtoupper($campo->name));
    }

    $filaExcel = 2;
    while ($fila = $resultado->fetch_assoc()) {
        foreach (array_values($fila) as $i => $valor) {
            $col = getColLetter($i + 1);
            $hoja->setCellValue($col . $filaExcel, $valor);
        }
        $filaExcel++;
    }
} else {
    $hoja->setCellValue("A1", "No hay datos en la tabla bienes.");
}

// CERRAR CONEXIÓN
$conexion->close();

// FORZAR DESCARGA DEL ARCHIVO
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_bienes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output'); // Salida directa al navegador
exit;
