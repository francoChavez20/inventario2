<?php

require './vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Crear hoja de cálculo
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator("yp")
    ->setLastModifiedBy("yo")
    ->setTitle("yo")
    ->setDescription("yo");

$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setTitle("hoja1");

/*// Encabezados de columna
$activeWorksheet->setCellValue('A2', 'N°1');
$activeWorksheet->setCellValue('B2', 'X');
$activeWorksheet->setCellValue('C2', 'N°2');
$activeWorksheet->setCellValue('D2', '=');
$activeWorksheet->setCellValue('E2', 'Resultado');

// Llenar filas con datos del 1 al 10
for ($i = 1; $i <= 10; $i++) {
    $fila = $i + 2; // empieza desde la fila 3
    $activeWorksheet->setCellValue('A' . $fila, 1);         // Número 1
    $activeWorksheet->setCellValue('B' . $fila, 'X');         // Símbolo X
    $activeWorksheet->setCellValue('C' . $fila, $i);         // Número 2 (igual que el primero)
    $activeWorksheet->setCellValue('D' . $fila, '=');         // Símbolo igual
    $activeWorksheet->setCellValue('E' . $fila, 1 * $i);     // Resultado
}*/
/*$ruta = explode("/", $_GET['views']);
if (!isset($ruta[1]) || $ruta[1] == "") {
    header("location:" . BASE_URL . "movimientos;");
}

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => BASE_URL_SERVER . "src/control/Movimiento.php?tipo=buscar_movimiento_id&sesion=" . $_SESSION['sesion_id'] . "&token=" . $_SESSION['sesion_token'] . "&data=" . $ruta[1],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
        "x-rapidapi-host: " . BASE_URL_SERVER,
        "x-rapidapi-key: XXXX"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $respuesta = json_decode($response);
}
*/
// Guardar archivo
$writer = new Xlsx($spreadsheet);
$writer->save('tabla_multiplicacion.xlsx');
