<?php
// Comprobante de reserva en PDF. Lo abre el botón "Descargar comprobante" de Mis reservas
// con el número de la reserva: comprobante.php?id=5
// Usa FPDF (lib/fpdf.php).

require "includes/sesion.php";
exigir_lector();
require "config/conexion.php"; // aquí viene la conexión $pdo
require "includes/consultas_lector.php";
require "lib/fpdf.php";

// Solo se genera si la reserva es de este lector y sigue Activa
$id_reserva = (int) ($_GET["id"] ?? 0);
$reserva = reserva_del_lector($pdo, $id_reserva);

if (!$reserva) {
    flash_poner("error", "No encontramos esa reserva o ya no está activa.");
    header("Location: mis_reservas.php");
    exit();
}

// Datos que van en el comprobante
$folio = $reserva["folio"] ?: "R-" . str_pad($reserva["id_reserva"], 6, "0", STR_PAD_LEFT);
$clave_lector = ($_SESSION["matricula"] ?? "") !== "" ? $_SESSION["matricula"] : ($_SESSION["numero_control"] ?? "");
$autores = autores_del_libro($pdo, $reserva["id_libro"]);

// Filas del detalle: etiqueta y valor
$detalle = [
    ["Lector", nombre_completo_del_lector($pdo)],
    ["Matrícula o clave", $clave_lector],
    ["Libro", $reserva["titulo_libro"]],
];

if ($autores !== "") {
    $detalle[] = ["Autor", $autores];
}

if ($reserva["inventario"]) {
    $detalle[] = ["Clave del ejemplar", $reserva["inventario"]];
}

$detalle[] = ["Fecha y hora de reserva", fecha_con_hora($reserva["fecha_reserva"])];
$detalle[] = ["Fecha límite para recogerla", fecha_con_hora($reserva["fecha_limite"])];

// FPDF trabaja con Windows-1252, por eso se convierte el texto para que salgan bien los acentos y la ñ
function texto_pdf($texto)
{
    return mb_convert_encoding((string) $texto, "Windows-1252", "UTF-8");
}

$pdf = new FPDF("P", "mm", "A4");
$pdf->SetMargins(25, 20, 25);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

// ----- Encabezado: logo y nombre de la biblioteca -----
$pdf->Image(__DIR__ . "/img/logo-cbta5.png", 25, 20, 0, 24);

$pdf->SetXY(55, 25);
$pdf->SetFont("Helvetica", "B", 16);
$pdf->SetTextColor(33, 33, 33);
$pdf->Cell(0, 8, texto_pdf("Biblioteca Escolar"), 0, 1);

$pdf->SetX(55);
$pdf->SetFont("Helvetica", "", 10);
$pdf->SetTextColor(75, 85, 99);
$pdf->Cell(0, 6, texto_pdf("CBTa No. 5 · Huejutla, Hgo."), 0, 1);

$pdf->SetDrawColor(229, 231, 235);
$pdf->Line(25, 50, 185, 50);

// ----- Título -----
$pdf->SetY(58);
$pdf->SetFont("Helvetica", "B", 20);
$pdf->SetTextColor(15, 81, 50);
$pdf->Cell(0, 10, texto_pdf("Comprobante de reserva"), 0, 1);

$pdf->SetFont("Helvetica", "", 11);
$pdf->SetTextColor(75, 85, 99);
$pdf->Cell(0, 7, texto_pdf("Presenta este comprobante en la biblioteca para recoger tu libro."), 0, 1);

// ----- Folio -----
$pdf->Ln(6);
$pdf->SetFillColor(230, 244, 236);
$pdf->SetFont("Helvetica", "", 10);
$pdf->SetTextColor(75, 85, 99);
$pdf->Cell(160, 9, texto_pdf("Folio de la reserva"), 0, 1, "C", true);

$pdf->SetFont("Helvetica", "B", 22);
$pdf->SetTextColor(15, 81, 50);
$pdf->Cell(160, 13, texto_pdf($folio), 0, 1, "C", true);

// ----- Detalle de la reserva -----
$pdf->Ln(8);

foreach ($detalle as [$etiqueta, $valor]) {
    $pdf->SetFont("Helvetica", "", 10);
    $pdf->SetTextColor(75, 85, 99);
    $pdf->Cell(60, 8, texto_pdf($etiqueta), 0, 0);

    $pdf->SetFont("Helvetica", $etiqueta === "Fecha límite para recogerla" ? "B" : "", 11);
    $pdf->SetTextColor(33, 33, 33);
    $pdf->MultiCell(100, 8, texto_pdf($valor), 0, "L");
}

// ----- Nota final -----
$pdf->Ln(6);
$pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
$pdf->Ln(4);

$pdf->SetFont("Helvetica", "", 9);
$pdf->SetTextColor(75, 85, 99);
$pdf->MultiCell(
    160,
    5,
    texto_pdf(
        "La reserva no es un préstamo. El préstamo se registra cuando recoges el libro en la biblioteca. " .
        "Si no lo recoges a tiempo, la reserva vence."
    ),
    0,
    "L"
);

$pdf->Ln(4);
$pdf->SetFont("Helvetica", "", 8);
$pdf->Cell(0, 5, texto_pdf("Generado el " . date("d/m/Y H:i")), 0, 1);

// El nombre del archivo solo lleva letras, números y guiones
$nombre_archivo = "comprobante-" . preg_replace("/[^A-Za-z0-9_-]/", "", $folio) . ".pdf";

// "D" = descargar el archivo
$pdf->Output("D", $nombre_archivo);
