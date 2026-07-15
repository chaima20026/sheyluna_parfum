<?php
session_start();

// Acces reserve a l'administrateur connecte
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

include "../connexion.php";
include "../params.php";
require "../lib/fpdf.php";

// Recupere la commande demandee
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("Commande introuvable.");
}

$stmt = mysqli_prepare($conn, "SELECT * FROM commandes WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$commande = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$commande) {
    die("Commande introuvable.");
}

// Convertit l'UTF-8 (accents é, è, à...) vers l'encodage attendu par FPDF (Windows-1252)
function t($texte) {
    return mb_convert_encoding((string) $texte, 'Windows-1252', 'UTF-8');
}

$date_aff = !empty($commande['date_commande'])
    ? date('d/m/Y H:i', strtotime($commande['date_commande']))
    : '-';

// Couleurs de la charte (or)
$or = [201, 169, 110];      // C9A96E
$gris = [45, 45, 45];       // 2D2D2D

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(20, 20, 20);

// --- En-tete : marque + coordonnees ---
$pdf->SetTextColor($or[0], $or[1], $or[2]);
$pdf->SetFont('Times', 'B', 26);
$pdf->Cell(0, 12, t('Sheyluna Parfums'), 0, 1, 'C');

$pdf->SetTextColor(120, 120, 120);
$pdf->SetFont('Helvetica', 'I', 10);
$coords = [];
if (param('contact_adresse')) $coords[] = param('contact_adresse');
if (param('contact_tel'))     $coords[] = 'Tel : ' . param('contact_tel');
if (param('contact_email'))   $coords[] = param('contact_email');
if ($coords) {
    $pdf->Cell(0, 6, t(implode('  -  ', $coords)), 0, 1, 'C');
}

// Ligne de separation doree
$pdf->Ln(4);
$pdf->SetDrawColor($or[0], $or[1], $or[2]);
$pdf->SetLineWidth(0.6);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(10);

// --- Titre du document ---
$pdf->SetTextColor($gris[0], $gris[1], $gris[2]);
$pdf->SetFont('Helvetica', 'B', 18);
$pdf->Cell(0, 10, t('BON DE COMMANDE'), 0, 1, 'C');
$pdf->Ln(2);

// Numero + date
$pdf->SetFont('Helvetica', '', 11);
$pdf->SetTextColor(90, 90, 90);
$pdf->Cell(0, 7, t('N° de commande : #' . $commande['id']), 0, 1, 'C');
$pdf->Cell(0, 7, t('Date : ' . $date_aff), 0, 1, 'C');
$pdf->Ln(8);

// --- Tableau des informations ---
$lignes = [
    ['Nom du client', $commande['nom_client']],
    ['Telephone',     $commande['telephone']],
    ['Parfum choisi', $commande['parfum']],
    ['Statut',        $commande['statut'] ?? 'En attente'],
];

$pdf->SetFont('Helvetica', '', 11);
foreach ($lignes as $ligne) {
    // Cellule libelle (fond gris clair)
    $pdf->SetFillColor(245, 240, 230);
    $pdf->SetTextColor($gris[0], $gris[1], $gris[2]);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(55, 12, t($ligne[0]), 1, 0, 'L', true);

    // Cellule valeur
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(115, 12, '  ' . t($ligne[1]), 1, 1, 'L', false);
}

$pdf->Ln(20);

// --- Zone signatures ---
$pdf->SetFont('Helvetica', '', 10);
$pdf->SetTextColor(90, 90, 90);
$y = $pdf->GetY();
$pdf->Cell(85, 6, t('Signature du client'), 0, 0, 'C');
$pdf->Cell(0, 6, t('Cachet & signature Sheyluna'), 0, 1, 'C');

// --- Pied de page ---
$pdf->SetY(-25);
$pdf->SetDrawColor($or[0], $or[1], $or[2]);
$pdf->SetLineWidth(0.3);
$pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
$pdf->Ln(3);
$pdf->SetFont('Helvetica', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pied = param('copyright', '© Sheyluna Parfums');
$pdf->Cell(0, 6, t($pied), 0, 1, 'C');

// Envoi au navigateur en telechargement
$nom_fichier = 'bon_commande_' . $commande['id'] . '.pdf';
$pdf->Output('D', $nom_fichier);
