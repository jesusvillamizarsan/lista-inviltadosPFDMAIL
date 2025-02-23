<?php
require_once('TCPDF-main/tcpdf.php');

class MYPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 20);
        $this->Cell(0, 15, 'Lista de Invitados', 0, false, 'C', 0);
    }
}

// Crear nuevo documento PDF
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Establecer información del documento
$pdf->SetCreator('Tu Evento');
$pdf->SetAuthor('Organizador');
$pdf->SetTitle('Lista de Invitados');

// Establecer márgenes
$pdf->SetMargins(15, 30, 15);
$pdf->AddPage();

// Incluir el archivo de conexión
require_once 'conexion.php';

try {
    // Obtener resumen
    $stmt = $conn->query("SELECT 
        SUM(CASE WHEN asistencia = 'asistire' THEN 1 ELSE 0 END) as total_asistiran,
        SUM(CASE WHEN asistencia = 'no_asistire' THEN 1 ELSE 0 END) as total_no_asistiran,
        SUM(CASE WHEN asistencia = 'quizas' THEN 1 ELSE 0 END) as total_quizas,
        SUM(CASE WHEN asistencia = 'asistire' THEN num_personas ELSE 0 END) as total_personas
        FROM confirmaciones");
    $resumen = $stmt->fetch(PDO::FETCH_ASSOC);

    // Agregar resumen al PDF
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Resumen', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 7, 'Total confirmados: ' . $resumen['total_asistiran'], 0, 1, 'L');
    $pdf->Cell(0, 7, 'No podrán asistir: ' . $resumen['total_no_asistiran'], 0, 1, 'L');
    $pdf->Cell(0, 7, 'Quizás asistan: ' . $resumen['total_quizas'], 0, 1, 'L');
    $pdf->Cell(0, 7, 'Total personas que asistirán: ' . $resumen['total_personas'], 0, 1, 'L');

    $pdf->Ln(10);

    // Crear tabla
    $pdf->SetFont('helvetica', 'B', 11);
    $header = array('Nombre', 'Email', 'Asistencia', 'Personas', 'Fecha');
    $w = array(45, 60, 30, 20, 35);

    // Agregar encabezados de tabla
    foreach ($header as $i => $col) {
        $pdf->Cell($w[$i], 7, $col, 1, 0, 'C');
    }
    $pdf->Ln();

    // Obtener y agregar datos
    $stmt = $conn->query("SELECT * FROM confirmaciones ORDER BY fecha_respuesta DESC");
    $pdf->SetFont('helvetica', '', 10);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $asistencia = match ($row['asistencia']) {
            'asistire' => 'Allí estaré',
            'no_asistire' => 'No podré',
            default => 'Lo intentaré'
        };

        $pdf->Cell($w[0], 6, $row['nombre'], 1);
        $pdf->Cell($w[1], 6, $row['email'], 1);
        $pdf->Cell($w[2], 6, $asistencia, 1);
        $pdf->Cell($w[3], 6, $row['num_personas'] ?: '-', 1, 0, 'C');
        $pdf->Cell($w[4], 6, date('d/m/Y', strtotime($row['fecha_respuesta'])), 1);
        $pdf->Ln();
    }
} catch (PDOException $e) {
    // Manejo de errores mejorado
    error_log("Error en la generación del PDF: " . $e->getMessage());
    die("Error al generar el PDF. Por favor, contacte al administrador.");
}

// Generar PDF
$pdf->Output('lista_invitados.pdf', 'D');
