<?php
$ruta= explode("/", $_GET['views']);
if (!isset($ruta[1]) || $ruta[1]=="") {
    header("location:".BASE_URL."movimientos;");
} 




    $curl = curl_init(); //inicia la sesión cURL
    curl_setopt_array($curl, array(
        CURLOPT_URL => BASE_URL_SERVER."src/control/Movimiento.php?tipo=buscar_movimiento_id&sesion=".$_SESSION['sesion_id']."&token=".
        $_SESSION['sesion_token']."&data=".$ruta[1], //url a la que se conecta
        CURLOPT_RETURNTRANSFER => true, //devuelve el resultado como una cadena del tipo curl_exec
        CURLOPT_FOLLOWLOCATION => true, //sigue el encabezado que le envíe el servidor
        CURLOPT_ENCODING => "", // permite decodificar la respuesta y puede ser"identity", "deflate", y "gzip", si está vacío recibe todos los disponibles.
        CURLOPT_MAXREDIRS => 10, // Si usamos CURLOPT_FOLLOWLOCATION le dice el máximo de encabezados a seguir
        CURLOPT_TIMEOUT => 30, // Tiempo máximo para ejecutar
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // usa la versión declarada
        CURLOPT_CUSTOMREQUEST => "GET", // el tipo de petición, puede ser PUT, POST, GET o Delete dependiendo del servicio
        CURLOPT_HTTPHEADER => array(
            "x-rapidapi-host: ".BASE_URL_SERVER,
            "x-rapidapi-key: XXXX"
        ), //configura las cabeceras enviadas al servicio
    )); //curl_setopt_array configura las opciones para una transferencia cURL

    $response = curl_exec($curl); // respuesta generada
    $err = curl_error($curl); // muestra errores en caso de existir

    curl_close($curl); // termina la sesión 

    if ($err) {
        echo "cURL Error #:" . $err; // mostramos el error
    } else {
        $respuesta = json_decode($response);
        //print_r($respuesta);
   

    ?>
    <!--
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Papeleta de Rotación de Bienes</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
    }

    h2 {
      text-align: center;
      text-transform: uppercase;
    }

    .datos {
      margin-top: 30px;
      line-height: 2;
    }

    .datos span {
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      border: 1px solid black;
      padding: 8px;
      text-align: center;
    }
  .motivo {
      font-weight: bold;
      margin-top: 20px;
    }
    .firmas {
      margin-top: 80px;
      display: flex;
      justify-content: space-between;
    }

    .firma {
      text-align: center;
      width: 45%;
    }

    .ubicacion {
      text-align: right;
      margin-top: 30px;
    }

    .subrayado {
      border-bottom: 1px solid black;
      display: inline-block;
      width: 250px;
    }
  </style>
</head>
<body>

  <h2>PAPELETA DE ROTACIÓN DE BIENES</h2>

  <div class="datos">
    <p><span class="titulo">ENTIDAD :</span> DIRECCION REGIONAL DE EDUCACION - AYACUCHO</p>
    <p><span class="titulo">AREA :</span> OFICINA DE ADMINISTRACIÓN</p>
    <p><span class="titulo">ORIGEN :</span> <?php echo $respuesta->amb_origen->codigo.' - '.$respuesta->amb_origen->detalle; ?> </p>
    <p><span class="titulo">DESTINO :</span> <?php echo $respuesta->amb_destino->codigo.' - '.$respuesta->amb_destino->detalle; ?></p>
    <p><span class="motivo">MOTIVO (*) :</span> <?php echo $respuesta->movimiento->descripcion; ?><p>

</div>
  <table>
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
    <tbody>
     <?php 
     $contador = 1;
     foreach ($respuesta->detalle as $detalle) {
        echo"<tr>";
        echo"<td>".$contador."</td>";
        echo"<td>".$detalle->cod_patrimonial."</td>";
        echo"<td>".$detalle->denominacion."</td>";
        echo"<td>".$detalle->marca."</td>";
        echo"<td>".$detalle->modelo."</td>";
        echo"<td>".$detalle->color."</td>";
        echo"<td>".$detalle->estado_conservacion."</td>";
        echo"</tr>";
        $contador+=1;

     }
     ?>
    </tbody>
  </table>

  <div class="ubicacion">
  <?php
$date = new DateTime($respuesta->movimiento->fecha_registro);

// Crear el formateador en español (Perú)
$formatter = new IntlDateFormatter(
    'es_PE',                // Idioma
    IntlDateFormatter::LONG, // Nivel de detalle (ej: 5 de julio de 2027)
    IntlDateFormatter::NONE // Solo la fecha, sin hora
);

// Mostrar la fecha formateada
echo $formatter->format($date);
?>




  </div>

  <div class="firmas">
    <div class="firma">
      <div>-------------------------------</div>
      <div>ENTREGUÉ CONFORME</div>
    </div>
    <div class="firma">
      <div>-------------------------------</div>
      <div>RECIBÍ CONFORME</div>
    </div>
  </div>

</body>
</html>
-->
    <?php
require_once('./vendor/tecnickcom/tcpdf/tcpdf.php');

$pdf = new TCPDF();
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Franco');
$pdf->SetTitle('Reporte de movimiento');

$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// salto de pagina automatico
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set font
$pdf->SetFont('dejavusans', '', 10);

    }