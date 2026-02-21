<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class EmSalesReportExport
{
    // ===========================================================
    // MÉTODO 1: exportCSV
    // ===========================================================

    /**
     * Genera y envía directamente un archivo CSV al navegador.
     *
     * @param array  $data      Array de arrays asociativos con los datos
     * @param array  $columns   Mapa ['clave_dato' => 'Cabecera visible'] — define orden y columnas
     * @param string $filename  Nombre base del archivo sin extensión
     * @return void  Envía headers + contenido y termina la ejecución
     */
    public static function exportCSV(array $data, array $columns, string $filename = 'export'): void
    {
        // Limpiar cualquier salida generada previamente
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $dateSuffix = date('Y-m-d_H-i-s');
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);

        // Headers HTTP para forzar descarga
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '_' . $dateSuffix . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // BOM UTF-8 para que Excel reconozca la codificación correctamente
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        if ($output === false) {
            exit;
        }

        // Fila de cabeceras: usar los valores (labels visibles) del mapa de columnas
        $headers = array_values($columns);
        fputcsv($output, $headers, ';');

        // Filas de datos: solo las keys definidas en $columns, en ese orden
        $keys = array_keys($columns);
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($keys as $key) {
                // Si la clave no existe en la fila, poner cadena vacía
                $value = $row[$key] ?? '';
                // Convertir floats para que Excel use coma decimal en locales europeos
                if (is_float($value)) {
                    $value = number_format($value, 2, ',', '');
                }
                $csvRow[] = $value;
            }
            fputcsv($output, $csvRow, ';');
        }

        fclose($output);
        exit;
    }

    // ===========================================================
    // MÉTODO 2: exportExcel
    // ===========================================================

    /**
     * Genera y envía un archivo .xlsx usando PhpSpreadsheet (incluido en PS 8+).
     * Si PhpSpreadsheet no está disponible, cae automáticamente a exportCSV().
     *
     * @param array  $data      Array de arrays asociativos con los datos
     * @param array  $columns   Mapa ['clave_dato' => 'Cabecera visible']
     * @param string $filename  Nombre base del archivo sin extensión
     * @return void
     */
    public static function exportExcel(array $data, array $columns, string $filename = 'export'): void
    {
        // Fallback a CSV si PhpSpreadsheet no está disponible
        if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            self::exportCSV($data, $columns, $filename);
            return;
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Sales Report');

            $keys    = array_keys($columns);
            $headers = array_values($columns);
            $colCount = count($headers);

            // --- Fila 1: Cabeceras con estilo negrita + fondo azul ---
            foreach ($headers as $colIndex => $label) {
                $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
                $sheet->setCellValue($cellCoord, $label);
                $sheet->getStyle($cellCoord)->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF3578B5'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }

            // --- Filas de datos desde fila 2 ---
            $rowNum = 2;
            foreach ($data as $row) {
                foreach ($keys as $colIndex => $key) {
                    $value = $row[$key] ?? '';
                    $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . $rowNum;
                    $sheet->setCellValue($cellCoord, $value);

                    // Formato numérico para floats e ints
                    if (is_float($value)) {
                        $sheet->getStyle($cellCoord)
                              ->getNumberFormat()
                              ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    } elseif (is_int($value)) {
                        $sheet->getStyle($cellCoord)
                              ->getNumberFormat()
                              ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
                    }
                }
                ++$rowNum;
            }

            // --- Auto-size de todas las columnas ---
            for ($i = 1; $i <= $colCount; ++$i) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            // --- Freeze primera fila (cabeceras siempre visibles) ---
            $sheet->freezePane('A2');

            // --- Headers HTTP y envío ---
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $dateSuffix   = date('Y-m-d_H-i-s');
            $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $safeFilename . '_' . $dateSuffix . '.xlsx"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                'EmSalesReportExport::exportExcel - ' . $e->getMessage(),
                3,
                null,
                'EmSalesReport'
            );
            // En caso de error, intentar CSV como último recurso
            self::exportCSV($data, $columns, $filename);
        }
    }
}
