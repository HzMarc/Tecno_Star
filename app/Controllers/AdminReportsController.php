<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/AdminReportsModel.php';
require_once BASE_PATH . '/app/Models/ServiceModel.php';
require_once BASE_PATH . '/app/Models/ReinvestmentModel.php';
require_once BASE_PATH . '/app/Controllers/SaleController.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class AdminReportsController
{
    private const SECTION_LABELS = [
        'sales'=>'Ventas',
        'services'=>'Servicios',
        'monthly'=>'Ingresos por mes',
        'products'=>'Productos más vendidos',
        'inventory'=>'Inventario',
        'reinvestment'=>'Reinversión',
    ];

    public function index(): void
    {
        if (!Auth::requireRole('Administrador') || !SaleController::available()) return;
        $database = Database::connection();
        $today = new DateTimeImmutable('today', new DateTimeZone('America/El_Salvador'));
        $from = $today->modify('first day of this month')->format('Y-m-d');
        $to = $today->format('Y-m-d');
        $servicesEnabled = ServiceModel::installed($database);
        $reinvestmentEnabled = ReinvestmentModel::enabled($database);
        require BASE_PATH . '/app/Views/admin/reports.php';
    }

    public function generate(): void
    {
        if (!Auth::requireRole('Administrador') || !SaleController::available()) return;

        try {
            $sections = $this->sections($_GET['sections'] ?? []);
            $format = $_GET['format'] ?? null;
            if (!is_string($format) || !in_array($format, ['pdf','xlsx'], true)) {
                throw new InvalidArgumentException('Selecciona PDF o Excel para generar el reporte.');
            }

            $database = Database::connection();
            $servicesEnabled = ServiceModel::installed($database);
            $reinvestmentEnabled = ReinvestmentModel::enabled($database);
            if (in_array('services', $sections, true) && !$servicesEnabled) {
                throw new InvalidArgumentException('El módulo de servicios no está habilitado.');
            }
            if (in_array('reinvestment', $sections, true) && !$reinvestmentEnabled) {
                throw new InvalidArgumentException('La reinversión no está habilitada en esta instalación.');
            }

            $report = (new AdminReportsModel($database))->report($_GET, $servicesEnabled, $reinvestmentEnabled);
            if ($format === 'xlsx') {
                $this->sendWorkbook($report, $sections);
                return;
            }
            $this->sendPdf($report, $sections);
        } catch (InvalidArgumentException $error) {
            ErrorHandler::respond(422, $error->getMessage());
        }
    }

    private function sections(mixed $value): array
    {
        if (!is_array($value) || count($value) < 1 || count($value) > count(self::SECTION_LABELS)) {
            throw new InvalidArgumentException('Selecciona al menos una sección para el reporte.');
        }
        $sections = [];
        foreach ($value as $section) {
            if (!is_string($section) || !isset(self::SECTION_LABELS[$section])) {
                throw new InvalidArgumentException('Hay una sección de reporte inválida.');
            }
            $sections[$section] = true;
        }
        return array_keys($sections);
    }

    private function sendPdf(array $report, array $sections): void
    {
        $sectionNumbers = [];
        foreach (array_values($sections) as $index=>$section) $sectionNumbers[$section] = str_pad((string)($index+1), 2, '0', STR_PAD_LEFT);
        $money = static fn(float|int $amount): string => ($amount < 0 ? '-$' : '$') . number_format(abs($amount), 2);
        $number = static fn(float|int $value): string => number_format($value, 0);
        $escape = static fn(mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $maxMonthlyIncome = max(1.0, ...array_map(static fn(array $month): float => (float)$month['paid'], $report['months']));
        $stockRows = array_merge(
            array_map(static fn(array $row): array => $row + ['status'=>'Agotado'], $report['out_of_stock']),
            array_map(static fn(array $row): array => $row + ['status'=>'Pocas unidades'], $report['low_stock']),
        );
        $generatedAt = (new DateTimeImmutable('now', new DateTimeZone('America/El_Salvador')))->format('d/m/Y H:i');
        $logoPath = BASE_PATH . '/public/assets/images/logo-estrella.png';
        $logoBytes = file_get_contents($logoPath);
        if ($logoBytes === false) throw new RuntimeException('No se pudo cargar el logotipo del reporte.');
        $logoDataUri = 'data:image/png;base64,' . base64_encode($logoBytes);

        ob_start();
        require BASE_PATH . '/app/Views/admin/reports/pdf.php';
        $html = (string)ob_get_clean();

        require_once BASE_PATH . '/vendor/autoload.php';
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', BASE_PATH);
        $pdf = new Dompdf($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('letter', 'portrait');
        $pdf->render();
        $pdf->getCanvas()->page_text(495, 758, 'Página {PAGE_NUM} de {PAGE_COUNT}', $pdf->getFontMetrics()->getFont('DejaVu Sans', 'normal'), 8, [105, 118, 139]);

        $filename = $this->filename($report, 'pdf');
        if (ob_get_level() > 0) ob_clean();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, no-store');
        echo $pdf->output();
    }

    private function sendWorkbook(array $report, array $sections): void
    {
        require_once BASE_PATH . '/vendor/autoload.php';
        $book = new Spreadsheet();
        $book->getProperties()->setCreator('Tecno Star')->setTitle('Reporte Tecno Star')->setSubject('Reporte de ' . implode(', ', $sections));
        $blue = '2054DD';
        $currency = '$#,##0.00;[Red]-$#,##0.00';
        $firstSheet = true;

        $sheet = static function (string $name) use ($book, &$firstSheet, $blue): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet {
            $worksheet = $firstSheet ? $book->getActiveSheet() : $book->createSheet();
            $firstSheet = false;
            $worksheet->setTitle($name);
            $worksheet->getStyle('A1:Z1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $worksheet->getStyle('A1:Z1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($blue);
            $worksheet->freezePane('A2');
            return $worksheet;
        };

        if (in_array('sales', $sections, true)) {
            $tab = $sheet('Ventas');
            $tab->fromArray([['Indicador','Resultado'],['Período',$report['from'].' al '.$report['to']],['Ventas cobradas',$report['summary']['paid']],['Cantidad de cobros',$report['summary']['paid_count']],['Ticket promedio',$report['summary']['average']],['Ventas de productos',$report['summary']['product_sales']]], null, 'A1');
            $tab->getStyle('B3')->getNumberFormat()->setFormatCode($currency);
            $tab->getStyle('B5:B6')->getNumberFormat()->setFormatCode($currency);
            $tab->getColumnDimension('A')->setWidth(32); $tab->getColumnDimension('B')->setWidth(24);
        }
        if (in_array('services', $sections, true)) {
            $tab = $sheet('Servicios');
            $tab->fromArray([['Indicador','Resultado'],['Ingresos por servicios',$report['summary']['service_sales']],['Servicios cobrados',$report['summary']['service_count']]], null, 'A1');
            $tab->getStyle('B2')->getNumberFormat()->setFormatCode($currency);
            $tab->getColumnDimension('A')->setWidth(32); $tab->getColumnDimension('B')->setWidth(24);
        }
        if (in_array('monthly', $sections, true)) {
            $tab = $sheet('Ingresos por mes');
            $tab->fromArray([['Mes','Ventas cobradas']], null, 'A1');
            foreach ($report['months'] as $index=>$month) $tab->fromArray([[$month['label'],(float)$month['paid']]], null, 'A'.($index+2));
            $tab->getStyle('B2:B'.max(2,$tab->getHighestRow()))->getNumberFormat()->setFormatCode($currency);
            $tab->getColumnDimension('A')->setWidth(24); $tab->getColumnDimension('B')->setWidth(22);
            $tab->setAutoFilter('A1:B'.max(1,$tab->getHighestRow()));
        }
        if (in_array('products', $sections, true)) {
            $tab = $sheet('Más vendidos');
            $tab->fromArray([['Código','Producto','Unidades','Ingresos']], null, 'A1');
            foreach ($report['best_sellers'] as $index=>$product) {
                $row = $index+2;
                $tab->setCellValueExplicit('A'.$row,(string)$product['code'],DataType::TYPE_STRING);
                $tab->setCellValueExplicit('B'.$row,(string)$product['name'],DataType::TYPE_STRING);
                $tab->setCellValue('C'.$row,(float)$product['units']);
                $tab->setCellValue('D'.$row,(float)$product['revenue']);
            }
            $tab->getStyle('D2:D'.max(2,$tab->getHighestRow()))->getNumberFormat()->setFormatCode($currency);
            foreach (['A'=>20,'B'=>42,'C'=>16,'D'=>20] as $column=>$width) $tab->getColumnDimension($column)->setWidth($width);
            $tab->setAutoFilter('A1:D'.max(1,$tab->getHighestRow()));
        }
        if (in_array('inventory', $sections, true)) {
            $tab = $sheet('Inventario');
            $tab->fromArray([['Resumen','Cantidad o valor'],['Productos con existencia',$report['stock']['products']],['Unidades disponibles',$report['stock']['units']],['Productos agotados',$report['stock']['out_count']],['Productos con pocas unidades',$report['stock']['low_count']],['Valor a precio de compra',$report['stock']['cost_value']],[],['Código','Producto','Disponible','Mínimo','Estado']], null, 'A1');
            $tab->getStyle('A8:E8')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $tab->getStyle('A8:E8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($blue);
            $tab->getStyle('B6')->getNumberFormat()->setFormatCode($currency);
            $rows = array_merge(
                array_map(static fn(array $row): array => $row + ['status'=>'Agotado'], $report['out_of_stock']),
                array_map(static fn(array $row): array => $row + ['status'=>'Pocas unidades'], $report['low_stock']),
            );
            foreach ($rows as $index=>$item) {
                $row = $index+9;
                $tab->setCellValueExplicit('A'.$row,(string)$item['code'],DataType::TYPE_STRING);
                $tab->setCellValueExplicit('B'.$row,(string)$item['name'],DataType::TYPE_STRING);
                $tab->setCellValue('C'.$row,(float)$item['available']);
                $tab->setCellValue('D'.$row,(float)$item['minimum']);
                $tab->setCellValueExplicit('E'.$row,$item['status'],DataType::TYPE_STRING);
            }
            foreach (['A'=>20,'B'=>42,'C'=>18,'D'=>15,'E'=>22] as $column=>$width) $tab->getColumnDimension($column)->setWidth($width);
            $tab->freezePane('A9'); $tab->setAutoFilter('A8:E'.max(8,$tab->getHighestRow()));
        }
        if (in_array('reinvestment', $sections, true)) {
            $tab = $sheet('Reinversión');
            $tab->fromArray([['Indicador','Resultado'],['Reposición',$report['reinvestment']['replacement']],['Crecimiento',$report['reinvestment']['growth']],['Reinversión total',$report['reinvestment']['total']],['Ventas sin distribución registrada',$report['reinvestment']['missing']]], null, 'A1');
            $tab->getStyle('B2:B4')->getNumberFormat()->setFormatCode($currency);
            $tab->getColumnDimension('A')->setWidth(38); $tab->getColumnDimension('B')->setWidth(24);
        }

        $book->setActiveSheetIndex(0);
        $filename = $this->filename($report, 'xlsx');
        if (ob_get_level() > 0) ob_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, no-store');
        (new Xlsx($book))->save('php://output');
        $book->disconnectWorksheets();
    }

    private function filename(array $report, string $extension): string
    {
        return sprintf('tecno-star-reporte-%s-%s.%s', str_replace('-', '', $report['from']), str_replace('-', '', $report['to']), $extension);
    }
}
