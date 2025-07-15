<?php
$ruta = explode("/", $_GET['views']);
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

    // Generar contenido HTML
    $contenido_pdf = '
    <h2>PAPELETA DE ROTACIÓN DE BIENES</h2>
    <div class="datos">
        <p><strong>ENTIDAD :</strong> DIRECCION REGIONAL DE EDUCACION - AYACUCHO</p>
        <p><strong>AREA :</strong> OFICINA DE ADMINISTRACIÓN</p>
        <p><strong>ORIGEN :</strong> ' . $respuesta->amb_origen->codigo . ' - ' . $respuesta->amb_origen->detalle . '</p>
        <p><strong>DESTINO :</strong> ' . $respuesta->amb_destino->codigo . ' - ' . $respuesta->amb_destino->detalle . '</p>
        <p><strong>MOTIVO (*) :</strong> ' . $respuesta->movimiento->descripcion . '</p>
    </div>

    <table border="1" cellpadding="4">
        <thead>
            <tr>
                <th>ITEM</th>
                <th>CÓDIGO PATRIMONIAL</th>
                <th>NOMBRE DEL BIEN</th>
                <th>MARCA</th>             
                <th>COLOR</th>
                <th>MODELO</th>
                <th>ESTADO</th>
            </tr>
        </thead>
        <tbody>';
    
    $contador = 1;
    foreach ($respuesta->detalle as $detalle) {
        $contenido_pdf .= "<tr>";
        $contenido_pdf .= "<td>{$contador}</td>";
        $contenido_pdf .= "<td>{$detalle->cod_patrimonial}</td>";
        $contenido_pdf .= "<td>{$detalle->denominacion}</td>";
        $contenido_pdf .= "<td>{$detalle->marca}</td>";
        $contenido_pdf .= "<td>{$detalle->color}</td>";
        $contenido_pdf .= "<td>{$detalle->modelo}</td>";
        $contenido_pdf .= "<td>{$detalle->estado_conservacion}</td>";
        $contenido_pdf .= "</tr>";
        $contador++;
    }

    $contenido_pdf .= '</tbody></table>';

    // Fecha
    $date = new DateTime($respuesta->movimiento->fecha_registro);
    $formatter = new IntlDateFormatter('es_PE', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    $fecha_formateada = $formatter->format($date);

    $contenido_pdf .= '
    <div style="text-align:right; margin-top:20px;">' . $fecha_formateada . '</div>

    <div style="margin-top:80px; display:flex; justify-content:space-between;">
        <div style="text-align:center;">
            -------------------------------<br>
            ENTREGUÉ CONFORME
        </div>
        <div style="text-align:center;">
            -------------------------------<br>
            RECIBÍ CONFORME
        </div>
    </div>';


    require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

   class MYPDF extends TCPDF {

    public function Header() {
      
      $this->Image('./src/view/pp/assets/images/logo.png', 15, 4, 33);

      $this->Image('./src/view/pp/assets/images/drea.png', 170, 2, 24);



        $this->SetFont('helvetica', 'B', 12);
        $this->MultiCell(0, 5, "GOBIERNO REGIONAL DE AYACUCHO\nDIRECCIÓN REGIONAL DE EDUCACIÓN DE AYACUCHO\nDIRECCIÓN DE ADMINISTRACIÓN", 0, 'C');

       
       $y = $this->GetY();
       $this->SetLineStyle(['width' => 0.3, 'color' => [51, 125, 255]]);
        $this->Line(15, $y, 195, $y);
        $y += 1.0; 

        $this->SetLineStyle(['width' => 1.0, 'color' => [51, 125, 255]]);
        $this->Line(15, $y, 195, $y);
        $y += 1.2; 

      
        $this->SetLineStyle(['width' => 0.3, 'color' => [51, 125, 255]]);
        $this->Line(15, $y, 195, $y);


         $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 10, 'ANEXO 4', 0, 1, 'C');

        $this->Ln(4); 


    }

    // Pie de página
    public function Footer() {

         $y = $this->GetY();
       $this->SetLineStyle(['width' => 0.3, 'color' => [51, 125, 255]]);
        $this->Line(15, $y, 195, $y);
        $y += 1.0;

      
        $this->SetLineStyle(['width' => 1.0, 'color' => [51, 125, 255]]);
        $this->Line(15, $y, 195, $y);
        $y += 1.2;

        $this->SetLineStyle(['width' => 0.3, 'color' => [51, 125, 255]]);
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
    $pdf->SetTitle('Reporte de movimiento');

    $pdf->SetMargins(15, 55, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(15);
    $pdf->SetAutoPageBreak(TRUE, 20);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->AddPage();
    $pdf->writeHTML($contenido_pdf, true, false, true, false, '');

    ob_clean(); // Limp_ç
    $pdf->Output('reporte_movimiento.pdf', 'I');
  }