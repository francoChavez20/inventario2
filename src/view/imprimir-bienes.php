<?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('./src/library/conexionn.php'); 

$conn = Conexion::connect();
if (!$conn) {
    die("Error al conectar con la base de datos.");
}

$sql = "
    SELECT b.cod_patrimonial, b.denominacion, b.marca, b.modelo, b.color, 
           b.estado_conservacion, a.detalle AS nombre_ambiente
    FROM bienes b
    LEFT JOIN ambientes_institucion a ON b.id_ambiente = a.id
    ORDER BY b.id ASC
";

$resultado = $conn->query($sql);
$bienes = [];
while ($fila = $resultado->fetch_object()) {
    $bienes[] = $fila;
}

class MYPDF extends TCPDF {
    public function Header() {
       
        $this->Image('./src/view/pp/assets/images/logo.png', 15, 4, 33);
        $this->Image('./src/view/pp/assets/images/drea.png', 170, 2, 24);

        // Encabezado de texto
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
        $this->Cell(0, 10, 'REPORTE GENERAL DE BIENES', 0, 1, 'C');
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

// Crear PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Franco');
$pdf->SetTitle('Lista General de Bienes');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

// Título en rojo
$pdf->SetTextColor(255, 0, 0); // rojo
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 10, 'LISTA GENERAL DE BIENES', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0); // volver al negro
$pdf->SetFont('helvetica', '', 9);

// Tabla HTML
$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
    <thead style="font-weight:bold; background-color:#f0f0f0;">
    <tr style="font-weight:bold; text-align:center;">
        <th width="4%"><b>N°</b></th>
        <th width="14%"><b>Cód. Patrimonial</b></th>
        <th width="18%"><b>Denominación</b></th>
        <th width="12%"><b>Marca</b></th>
        <th width="12%"><b>Modelo</b></th>
        <th width="10%"><b>Color</b></th>
        <th width="10%"><b>Estado</b></th>
        <th width="20%"><b>Ambiente</b></th>
    </tr>
</thead>

    <tbody>';

$contador = 1;
foreach ($bienes as $bien) {
    $contenido .= '<tr>
        <td width="4%" align="center">' . $contador . '</td>
        <td width="14%">' . htmlspecialchars($bien->cod_patrimonial) . '</td>
        <td width="18%">' . htmlspecialchars($bien->denominacion) . '</td>
        <td width="12%">' . htmlspecialchars($bien->marca) . '</td>
        <td width="12%">' . htmlspecialchars($bien->modelo) . '</td>
        <td width="10%">' . htmlspecialchars($bien->color) . '</td>
        <td width="10%">' . htmlspecialchars($bien->estado_conservacion) . '</td>
        <td width="20%">' . htmlspecialchars($bien->nombre_ambiente) . '</td>
    </tr>';
    $contador++;
}

$contenido .= '</tbody></table>';
$pdf->writeHTML($contenido, true, false, true, false, '');

// Salida
ob_clean();
$pdf->Output('reporte_general_bienes.pdf', 'I');
?>
