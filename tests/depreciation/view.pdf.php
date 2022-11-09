<?php
declare(strict_types=1);

use phpOMS\Autoloader;

require_once Autoloader::findPaths('Resources\tcpdf\tcpdf')[0];

$cLang = $this->getData('lang');
/** @noinspection PhpIncludeInspection */
$reportLanguage = include $basepath . '/' . \ltrim($tcoll['lang']->getPath(), '/');
$lang           = $reportLanguage[$cLang];

$amount   = (float) ($this->request->getData('amount') ?? 10000.0);
$duration = (int) ($this->request->getData('duration') ?? 10);

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('Dennis Eichhorn');
$pdf->SetAuthor('Dennis Eichhorn');
$pdf->SetTitle('Demo Mailing');
$pdf->SetSubject('Mailing');
$pdf->SetKeywords('demo helper mailing');

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(false, 0);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$pdf->SetFillColor(52, 58, 64);
$pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F');

$pdf->SetFillColor(54, 151, 219);
$pdf->Rect(0, 0, $pdf->getPageWidth(), 5, 'F');

$pdf->SetFont('helvetica', '', 32);
$pdf->SetTextColor(54, 151, 219);
$pdf->Write(0, 'Demo Mailing - ' . $this->request->getData('date') ?? 'Y-m-d', '', 0, 'C', true, 0, false, false, 0);

$pdf->Image(__DIR__ . '/logo.png', $pdf->getPageWidth() / 2 - 60 / 2, 40, 60, 60, 'PNG', '', 'C', true, 300, '', false, false, 0, false, false, false);

$pdf->SetFillColor(67, 74, 81);
$pdf->Rect(0, 110, $pdf->getPageWidth(), 145, 'F');

$html = '<table>
	<tr>
	    <th>' . $lang['Period'] . '</th>
	    <th>' . $lang['StraightLine'] . '</th>
	    <th>' . $lang['ArithmeticDegressive'] . '</th>
	    <th>' . $lang['ArithmeticProgressive'] . '</th>
	    <th>' . $lang['GeometricDegressive'] . '</th>
	    <th>' . $lang['GeometricProgressive'] . '</th>
    </tr>';

for ($i = 1; $i <= $duration; ++$i) {
	$html .= '<tr>';
	$thml .= '<td>' . $i . '</td>';
	$thml .= '<td>' . $this->getCurrency(Depreciation::getStraightLineResidualInT($amount, $duration, $i), 'medium', '') . '</td>';
	$thml .= '<td>' . $this->getCurrency(Depreciation::getArithmeticDegressiveDepreciationResidualInT($amount, 0.0, $duration, $i), 'medium', '') . '</td>';
	$thml .= '<td>' . $this->getCurrency(Depreciation::getArithmeticProgressiveDepreciationResidualInT($amount, 0.0, $duration, $i), 'medium', '') . '</td>';
	$thml .= '<td>' . $this->getCurrency(Depreciation::getGeometicProgressiveDepreciationResidualInT($amount, $amount * 0.1, $duration, $i), 'medium', '') . '</td>';
	$thml .= '<td>' . $this->getCurrency(Depreciation::getGeometicDegressiveDepreciationResidualInT($amount, $amount * 0.1, $duration, $i), 'medium', '') . '</td>';
	$thml .= '</tr>';
}

$html = '</table>';

$pdf->SetXY(15, 125);
$pdf->SetFont('helvetica', '', 14);
$pdf->SetTextColor(255, 255, 255);
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->SetFont('helvetica', '', 12);
$pdf->SetXY(15, 262);
$pdf->SetTextColor(54, 151, 219);

$text = <<<EOT
Website: karaka.app
Email: dennis.eichhorn@jingga.app
Twitter: @orange_mgmt
Twitch: spl1nes
Youtube: Karaka
EOT;
$pdf->Write(0, $text, '', 0, 'L', true, 0, false, false, 0);

$pdf->Output('mailing.pdf', 'I');
