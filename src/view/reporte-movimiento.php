<?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('./src/library/conexionn.php'); 

$conn = Conexion::connect();
if (!$conn) {
    die("Error al conectar con la base de datos.");
}

$sql = "
    SELECT 
        m.id,
        ao.detalle AS ambiente_origen,
        ad.detalle AS ambiente_destino,
        u.nombres_apellidos AS usuario_registro,
        m.fecha_registro,
        m.descripcion,
        i.nombre AS institucion
    FROM movimientos m
    LEFT JOIN ambientes_institucion ao ON m.id_ambiente_origen = ao.id
    LEFT JOIN ambientes_institucion ad ON m.id_ambiente_destino = ad.id
    LEFT JOIN usuarios u ON m.id_usuario_registro = u.id
    LEFT JOIN institucion i ON m.id_ies = i.id
    ORDER BY m.fecha_registro DESC
";

$resultado = $conn->query($sql);
$movimientos = [];
while ($fila = $resultado->fetch_object()) {
    $movimientos[] = $fila;
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
        $this->Cell(0, 10, 'REPORTE GENERAL DE MOVIMIENTOS', 0, 1, 'C');
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
$pdf->SetTitle('Lista de Movimientos');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 8);
$pdf->AddPage();

$pdf->SetTextColor(255, 0, 0);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'LISTA GENERAL DE MOVIMIENTOS', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 8);

$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
    <thead style="font-weight:bold; background-color:#f0f0f0;">
    <tr>
        <th width="4%" align="center"><b>N°</b></th>
        <th width="15%" align="center"><b>Ambiente Origen</b></th>
        <th width="15%" align="center"><b>Ambiente Destino</b></th>
        <th width="20%" align="center"><b>Usuario Registro</b></th>
        <th width="15%" align="center"><b>Fecha Registro</b></th>
        <th width="20%" align="center"><b>Descripción</b></th>
        <th width="11%" align="center"><b>Institución</b></th>
    </tr>
</thead>

    <tbody>';

$contador = 1;
foreach ($movimientos as $mov) {
    $contenido .= '<tr>
        <td width="4%" align="center">' . $contador . '</td>
        <td width="15%" align="left">' . htmlspecialchars($mov->ambiente_origen ?? 'N/A') . '</td>
        <td width="15%" align="left">' . htmlspecialchars($mov->ambiente_destino ?? 'N/A') . '</td>
        <td width="20%" align="left">' . htmlspecialchars($mov->usuario_registro ?? 'N/A') . '</td>
        <td width="15%" align="center">' . htmlspecialchars(date('d/m/Y', strtotime($mov->fecha_registro))) . '</td>
        <td width="20%" align="left">' . htmlspecialchars($mov->descripcion) . '</td>
        <td width="11%" align="left">' . htmlspecialchars($mov->institucion ?? 'N/A') . '</td>
    </tr>';
    $contador++;
}

$contenido .= '</tbody></table>';

$pdf->writeHTML($contenido, true, false, true, false, '');

ob_clean();
$pdf->Output('reporte_movimientos.pdf', 'I');
?>
