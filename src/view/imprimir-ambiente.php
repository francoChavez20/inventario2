<?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('./src/library/conexionn.php'); 

$conn = Conexion::connect();
if (!$conn) {
    die("Error al conectar con la base de datos.");
}

$sql = "
    SELECT a.id, i.nombre AS institucion, a.encargado, a.codigo, a.detalle, a.otros_detalle
    FROM ambientes_institucion a
    INNER JOIN institucion i ON a.id_ies = i.id
    ORDER BY i.nombre ASC
";
$resultado = $conn->query($sql);
$ambientes = [];
while ($fila = $resultado->fetch_object()) {
    $ambientes[] = $fila;
}

class MYPDF extends TCPDF {
    public function Header() {
        $this->Image('./src/view/pp/assets/images/logo.png', 15, 4, 33);
        $this->Image('./src/view/pp/assets/images/drea.png', 170, 2, 24);
        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell(0, 5, "GOBIERNO REGIONAL DE AYACUCHO\nDIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO\nDIRECCIÓN DE ADMINISTRACIÓN", 0, 'C');
        $y = $this->GetY();
        $this->SetDrawColor(0, 0, 255);
        $this->SetLineWidth(0.3);
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->SetLineWidth(0.6);
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->SetLineWidth(0.3);
        $this->Line(15, $y, 195, $y);
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'REPORTE DE AMBIENTES POR INSTITUCIÓN', 0, 1, 'C');
        $this->Ln(4);
    }

    public function Footer() {
        $y = $this->GetY();
        $this->SetDrawColor(0, 0, 255);
        $this->SetLineWidth(0.3);
        $this->Line(15, $y, 195, $y); $y += 1.0;
        $this->SetLineWidth(0.6);
        $this->Line(15, $y, 195, $y); $y += 1.2;
        $this->SetLineWidth(0.3);
        $this->Line(15, $y, 195, $y);
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Franco');
$pdf->SetTitle('Ambientes por Institución');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(0, 10, 'LISTA DE AMBIENTES DE LAS INSTITUCIONES', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(0, 0, 0);

$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
    <thead style="font-weight:bold; background-color:#f0f0f0;">
    <tr>
        <th width="5%" align="center"><b>N°</b></th>
        <th width="25%" align="center"><b>Institución</b></th>
        <th width="15%" align="center"><b>Encargado</b></th>
        <th width="15%" align="center"><b>Código</b></th>
        <th width="20%" align="center"><b>Detalle</b></th>
        <th width="20%" align="center"><b>Otros</b></th>
    </tr>
</thead>

    <tbody>';

$contador = 1;
foreach ($ambientes as $a) {
    $contenido .= '<tr>
        <td width="5%" align="center">' . $contador . '</td>
        <td width="25%" align="left">' . htmlspecialchars($a->institucion) . '</td>
        <td width="15%" align="center">' . htmlspecialchars($a->encargado) . '</td>
        <td width="15%" align="center">' . htmlspecialchars($a->codigo) . '</td>
        <td width="20%" align="left">' . htmlspecialchars($a->detalle) . '</td>
        <td width="20%" align="left">' . htmlspecialchars($a->otros_detalle) . '</td>
    </tr>';
    $contador++;
}
$contenido .= '</tbody></table>';

$pdf->writeHTML($contenido, true, false, true, false, '');

ob_clean();
$pdf->Output('reporte_ambientes.pdf', 'I');
