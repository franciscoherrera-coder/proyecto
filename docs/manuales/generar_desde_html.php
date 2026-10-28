<?php

declare(strict_types=1);

ini_set('pcre.backtrack_limit', '50000000');

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

$root = dirname(__DIR__, 2);
$dir = __DIR__;
$jobs = [
    [
        $argv[1] ?? 'C:/Users/Usuario/Desktop/Sistemas/3er año/IngdeSoftware3/proyectoo/Manual_de_Usuario_ISFT_38_con_portada.pdf',
        "$dir/Manual_Usuario_ISFT_38_final.html",
        "$dir/Manual_de_Usuario_ISFT_38_final.pdf",
        'Manual de Usuario',
    ],
    [
        $argv[2] ?? 'C:/Users/Usuario/Desktop/Sistemas/3er año/IngdeSoftware3/proyectoo/Manual_Tecnico_ISFT_38_con_portada.pdf',
        "$dir/Manual_Tecnico_ISFT_38_final.html",
        "$dir/Manual_Tecnico_ISFT_38_final.pdf",
        'Manual del Desarrollador',
    ],
];

foreach ($jobs as [$cover, $htmlFile, $output, $title]) {
    if (!is_file($cover) || !is_file($htmlFile)) {
        throw new RuntimeException("Falta un archivo de entrada para $title");
    }
    $pdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'tempDir' => "$root/storage/app/mpdf-temp",
        'default_font' => 'dejavusans',
    ]);
    $pdf->SetTitle("$title — Sistema de Asistencia ISFT N.° 38");
    $pdf->SetAuthor('Francisco Herrera');
    $pdf->SetSourceFile($cover);
    $template = $pdf->ImportPage(1);
    $pdf->AddPage('P', '', '', '', '', 0, 0, 0, 0, 0, 0);
    $pdf->UseTemplate($template, 0, 0, 210, 297);
    $pdf->SetHTMLHeader('<div style="font-size:8pt;color:#647184;border-bottom:1px solid #ccd4dd">ISFT N.° 38 · Sistema de Asistencia · ' . $title . '</div>');
    $pdf->SetHTMLFooter('<div style="font-size:8pt;color:#647184;border-top:1px solid #ccd4dd">Francisco Herrera <span style="float:right">Página {PAGENO} de {nbpg}</span></div>');
    $pdf->AddPage('P', '', '', '', '', 18, 18, 24, 22, 8, 8);
    $pdf->SetBasePath($dir . '/');
    $html = (string) file_get_contents($htmlFile);
    $html = preg_replace_callback(
        '/<img([^>]*?)src="([^"]+)"([^>]*)>/i',
        static function (array $match) use ($dir): string {
            $source = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (str_starts_with($source, 'data:') || preg_match('#^https?://#i', $source)) {
                return $match[0];
            }

            $absolutePath = realpath($dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $source));
            if ($absolutePath === false || !is_file($absolutePath)) {
                throw new RuntimeException("No se encontró la imagen del manual: $source");
            }

            $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/png',
            };
            $embedded = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($absolutePath));
            return '<img' . $match[1] . 'src="' . $embedded . '"' . $match[3] . '>';
        },
        $html
    );
    $pdf->WriteHTML($html);
    $pdf->Output($output, Destination::FILE);
    echo "Generado: $output\n";
}
