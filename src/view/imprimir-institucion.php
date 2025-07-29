<?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('./src/library/conexionn.php'); 

$conn = Conexion::connect();
if (!$conn) {
    die("Error al conectar con la base de datos.");
}

$sql = "
    SELECT i.id, u.nombres_apellidos AS beneficiario, i.cod_modular, i.ruc, i.nombre
    FROM institucion i
    INNER JOIN usuarios u ON i.beneficiario = u.id
    ORDER BY i.nombre ASC
";
$resultado = $conn->query($sql);
$instituciones = [];
while ($fila = $resultado->fetch_object()) {
    $instituciones[] = $fila;
}

class MYPDF extends TCPDF {
    public function Header() {
        $this->Image('./src/view/pp/assets/images/logo.png', 15, 4, 33);
        $this->Image('./src/view/pp/assets/images/drea.png', 170, 2, 24);
        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell(0, 5, "GOBIERNO REGIONAL DE AYACUCHO\nDIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO\nDIRECCIÓN DE ADMINISTRACIÓN", 0, 'C');
        $y = $this->GetY();
        $this->SetDrawColor(0, 0, 255);
        $this->SetLineWidth(0.2);
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->SetLineWidth(0.6);
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->SetLineWidth(0.2);
        $this->Line(15, $y, 195, $y);
        $this->SetFont('helvetica', '', 10);
        $this->Ln(2);
        $this->Cell(0, 10, 'REPORTE DE INSTITUCIONES', 0, 1, 'C');
        $this->Ln(4);
    }

    public function Footer() {
        $y = $this->GetY();
        $this->SetDrawColor(0, 0, 255);
        $this->SetLineWidth(0.2);
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->SetLineWidth(0.6);
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->SetLineWidth(0.2);
        $this->Line(15, $y, 195, $y);
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Franco');
$pdf->SetTitle('Lista de Instituciones');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

$pdf->SetTextColor(255, 0, 0);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 10, 'LISTA DE INSTITUCIONES', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 9);

$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
    <thead style="font-weight:bold; background-color:#f0f0f0;">
    <tr>
        <th width="5%" align="center"><b>N°</b></th>
        <th width="25%" align="center"><b>Beneficiario</b></th>
        <th width="20%" align="center"><b>Cod. Modular</b></th>
        <th width="20%" align="center"><b>RUC</b></th>
        <th width="30%" align="center"><b>Nombre</b></th>
    </tr>
</thead>

    <tbody>';

$contador = 1;
foreach ($instituciones as $inst) {
    $contenido .= '<tr>
        <td width="5%" align="center">' . $contador . '</td>
        <td width="25%" align="left">' . htmlspecialchars($inst->beneficiario) . '</td>
        <td width="20%" align="center">' . htmlspecialchars($inst->cod_modular) . '</td>
        <td width="20%" align="center">' . htmlspecialchars($inst->ruc) . '</td>
        <td width="30%" align="left">' . htmlspecialchars($inst->nombre) . '</td>
    </tr>';
    $contador++;
}

$contenido .= '</tbody></table>';
$pdf->writeHTML($contenido, true, false, true, false, '');

ob_clean();
$pdf->Output('reporte_instituciones.pdf', 'I');
?>
