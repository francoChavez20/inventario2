<?php
require './vendor/autoload.php';
require_once './src/library/conexionn.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// CONECTAR A LA BD
$conexion = Conexion::connect();

// CONSULTA
$sql = "SELECT * FROM bienes ORDER BY id ASC";
$resultado = $conexion->query($sql);

// CREAR EXCEL
$spreadsheet = new Spreadsheet();
$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle("Bienes");

// FUNCIONES
function getColLetter($index) {
    $letter = '';
    while ($index > 0) {
        $index--;
        $letter = chr(65 + ($index % 26)) . $letter;
        $index = intval($index / 26);
    }
    return $letter;
}

// SI HAY DATOS
if ($resultado->num_rows > 0) {
    $campos = $resultado->fetch_fields();
    $totalColumnas = count($campos);
    $colFin = getColLetter($totalColumnas);

    // TÍTULO CENTRADO Y ESTILIZADO
    $hoja->mergeCells('A1:' . $colFin . '1');
    $hoja->setCellValue('A1', "LISTADO DE BIENES");
    $hoja->getStyle('A1')->getFont()->setSize(26)->setBold(true);
    $hoja->getStyle('A1')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    // ENCABEZADOS
    foreach ($campos as $i => $campo) {
        $col = getColLetter($i + 1);
        $hoja->setCellValue($col . '2', strtoupper($campo->name));
        $hoja->getStyle($col . '2')->getFont()->setBold(true);
        $hoja->getStyle($col . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getStyle($col . '2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('337dff');
    }

    // FILAS DE DATOS
    $filaExcel = 3;
    while ($fila = $resultado->fetch_assoc()) {
        foreach (array_values($fila) as $i => $valor) {
            $col = getColLetter($i + 1);
            $hoja->setCellValue($col . $filaExcel, $valor);
        }
        $filaExcel++;
    }

    // AJUSTAR ANCHOS Y BORDES
    $ultimaFila = $filaExcel - 1;
    for ($i = 1; $i <= $totalColumnas; $i++) {
        $col = getColLetter($i);
        $hoja->getColumnDimension($col)->setAutoSize(true);

        $rango = $col . '2:' . $col . $ultimaFila;
        $hoja->getStyle($rango)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

} else {
    $hoja->setCellValue("A1", "No hay datos en la tabla bienes.");
}

// CERRAR BD
$conexion->close();

// DESCARGA
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tabla_bienes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
