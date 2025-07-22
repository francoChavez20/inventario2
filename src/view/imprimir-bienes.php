<?php
session_start();

$datos = array(
    "sesion" => $_SESSION['sesion_id'],
    "token" => $_SESSION['sesion_token'],
    "ies" => "1", // <-- Cambia según tu institución activa
    "pagina" => 1,
    "cantidad_mostrar" => 9999,
    "busqueda_tabla_codigo" => "",
    "busqueda_tabla_ambiente" => "",
    "busqueda_tabla_denominacion" => ""
);

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => BASE_URL_SERVER . "src/control/Bien.php?tipo=listar_bienes_ordenados_tabla",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($datos),
    CURLOPT_HTTPHEADER => array(
        "x-rapidapi-host: " . BASE_URL_SERVER,
        "x-rapidapi-key: XXXX",
        "Content-Type: application/x-www-form-urlencoded"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "Error: $err";
    exit;
}

$data = json_decode($response);

if (!$data->status) {
    echo "No se pudieron obtener los bienes.";
    exit;
}

require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

class MYPDF extends TCPDF {
    public function Header() {
        $this->Image('./src/view/pp/assets/images/logo.png', 15, 4, 33);
        $this->Image('./src/view/pp/assets/images/drea.png', 170, 2, 24);
        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell(0, 5, "GOBIERNO REGIONAL DE AYACUCHO\nDIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO\nDIRECCIÓN DE ADMINISTRACIÓN", 0, 'C');
        $y = $this->GetY();
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->Line(15, $y, 195, $y);
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'REPORTE GENERAL DE BIENES', 0, 1, 'C');
        $this->Ln(4);
    }

    public function Footer() {
        $y = $this->GetY();
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->Line(15, $y, 195, $y);
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Franco');
$pdf->SetTitle('Listado General de Bienes');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

$contenido = '
<table border="1" cellpadding="3">
    <thead>
        <tr style="background-color:#f0f0f0;">
            <th width="4%">#</th>
            <th width="15%">Cód. Patrimonial</th>
            <th width="20%">Denominación</th>
            <th width="10%">Marca</th>
            <th width="10%">Modelo</th>
            <th width="8%">Color</th>
            <th width="8%">Estado</th>
            <th width="25%">Ambiente</th>
        </tr>
    </thead>
    <tbody>';

$contador = 1;
foreach ($data->contenido as $bien) {
    $contenido .= "<tr>
        <td>$contador</td>
        <td>{$bien->cod_patrimonial}</td>
        <td>{$bien->denominacion}</td>
        <td>{$bien->marca}</td>
        <td>{$bien->modelo}</td>
        <td>{$bien->color}</td>
        <td>{$bien->estado_conservacion}</td>
        <td>{$bien->id_ambiente}</td>
    </tr>";
    $contador++;
}

$contenido .= '</tbody></table>';

$pdf->writeHTML($contenido, true, false, true, false, '');

ob_clean();
$pdf->Output('reporte_general_bienes.pdf', 'I');
