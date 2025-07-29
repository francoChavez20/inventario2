<?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');
require_once('./src/library/conexionn.php'); // Tu archivo de conexión

// ---------------- CONEXIÓN A BASE DE DATOS ----------------
$conn = Conexion::connect();
if (!$conn) {
    die("Error al conectar con la base de datos.");
}

// ---------------- CONSULTA DE USUARIOS ----------------
$sql = "
    SELECT dni, nombres_apellidos, correo, telefono, estado 
    FROM usuarios
    ORDER BY nombres_apellidos ASC
";

$resultado = $conn->query($sql);
$usuarios = [];
while ($fila = $resultado->fetch_object()) {
    $usuarios[] = $fila;
}

// ---------------- PDF PERSONALIZADO ----------------
class MYPDF extends TCPDF {
    public function Header() {
        $this->Image('./src/view/pp/assets/images/logo.png', 15, 4, 33);
        $this->Image('./src/view/pp/assets/images/drea.png', 170, 2, 24);
        $this->SetFont('helvetica', 'B', 12);

        // Texto centrado
        $this->MultiCell(0, 5, "GOBIERNO REGIONAL DE AYACUCHO\nDIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO\nDIRECCIÓN DE ADMINISTRACIÓN", 0, 'C');

        $y = $this->GetY();
        // Líneas azules: superior, media más gruesa, inferior
        $this->SetDrawColor(0, 0, 255); // Azul
        $this->SetLineWidth(0.2);
        $this->Line(15, $y, 195, $y); $y += 1.0;

        $this->SetLineWidth(0.8); // Línea más gruesa del medio
        $this->Line(15, $y, 195, $y); $y += 1.2;

        $this->SetLineWidth(0.2);
        $this->Line(15, $y, 195, $y);

        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'REPORTE GENERAL DE USUARIOS', 0, 1, 'C');
        $this->Ln(4);
    }

    public function Footer() {
        $y = $this->GetY();
        $this->SetDrawColor(0, 0, 255); // Azul
        $this->SetLineWidth(0.2);
        $this->Line(15, $y, 195, $y); $y += 1.0;

        $this->SetLineWidth(0.8); // Línea media más gruesa
        $this->Line(15, $y, 195, $y); $y += 1.2;

        $this->SetLineWidth(0.2);
        $this->Line(15, $y, 195, $y);

        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// ---------------- GENERAR PDF ----------------
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Franco');
$pdf->SetTitle('Lista de Usuarios');
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetFont('helvetica', '', 9);
$pdf->AddPage();

// Título rojo
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(255, 0, 0); // Rojo
$pdf->Cell(0, 10, 'LISTA GENERAL DE USUARIOS', 0, 1, 'C');
$pdf->Ln(2);
$pdf->SetTextColor(0, 0, 0); // Volver a negro
$pdf->SetFont('helvetica', '', 9);

$contenido = '
<table border="1" cellpadding="3" cellspacing="0" width="100%">
  <thead style="font-weight:bold; background-color:#f0f0f0;">
    <tr>
        <th width="5%" align="center"><b>N°</b></th>
        <th width="15%" align="center"><b>DNI</b></th>
        <th width="30%" align="center"><b>Nombres y Apellidos</b></th>
        <th width="25%" align="center"><b>Correo</b></th>
        <th width="15%" align="center"><b>Teléfono</b></th>
        <th width="10%" align="center"><b>Estado</b></th>
    </tr>
</thead>

    <tbody>';

$contador = 1;
foreach ($usuarios as $u) {
    $contenido .= '<tr>
        <td width="5%" align="center">' . $contador . '</td>
        <td width="15%" align="center">' . htmlspecialchars($u->dni) . '</td>
        <td width="30%" align="left">' . htmlspecialchars($u->nombres_apellidos) . '</td>
        <td width="25%" align="left">' . htmlspecialchars($u->correo) . '</td>
        <td width="15%" align="center">' . htmlspecialchars($u->telefono) . '</td>
        <td width="10%" align="center">' . htmlspecialchars($u->estado) . '</td>
    </tr>';
    $contador++;
}

$contenido .= '</tbody></table>';
$pdf->writeHTML($contenido, true, false, true, false, '');

// Salida
ob_clean();
$pdf->Output('reporte_usuarios.pdf', 'I');
