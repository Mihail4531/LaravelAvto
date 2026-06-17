<?php

namespace App\Filament\Support;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Формирует «нормальный» XLSX: числа/деньги — настоящими числами (Excel умеет
 * суммировать и строить сводные), деньги/количества выровнены по правому краю
 * с числовым форматом, колонки автоматически подгоняются по ширине, шапка
 * закреплена и оформлена, строки чередуются по фону, итоги выделены жирным.
 *
 * Колонки описываются массивом:
 *   ['label' => 'Сумма', 'format' => 'money', 'width' => 14, 'align' => 'right']
 * Поддерживаемые format: text (по умолчанию), int, number, money, percent.
 * width и align необязательны — ширина считается по содержимому, выравнивание
 * берётся из формата (числа — вправо, текст — влево).
 */
class ExcelExporter
{
    private const FORMAT_CODES = [
        'money' => '#,##0.00\ "₽"',
        'number' => '#,##0.###',
        'int' => '#,##0',
        'percent' => '0.0%',
    ];

    /**
     * @param  string  $filename  имя файла без расширения
     * @param  array<int, array{label：string, format?: string, width?: float, align?: string}>  $columns  описание колонок
     * @param  iterable<int, array<int, mixed>>  $rows  строки данных (сырые значения)
     * @param  string|null  $sheetTitle  название листа
     * @param  array<int, array<int, mixed>>  $footerRows  строки итогов (жирные, внизу)
     * @param  string|null  $title  крупный заголовок над таблицей
     */
    public static function stream(
        string $filename,
        array $columns,
        iterable $rows,
        ?string $sheetTitle = null,
        array $footerRows = [],
        ?string $title = null,
    ): StreamedResponse {
        // Материализуем строки, чтобы посчитать авто-ширину колонок.
        $body = [];
        foreach ($rows as $row) {
            $body[] = array_values($row);
        }
        $footer = array_map('array_values', $footerRows);

        return response()->stream(
            function () use ($columns, $body, $footer, $sheetTitle, $title) {
                $options = new Options;

                // Ширина колонок: явная из описания либо авто по содержимому.
                foreach (self::columnWidths($columns, $body, $footer) as $index => $width) {
                    $options->setColumnWidth($width, $index + 1);
                }

                $writer = new Writer($options);
                $writer->openToFile('php://output');

                if ($sheetTitle) {
                    $writer->getCurrentSheet()->setName(mb_substr($sheetTitle, 0, 31));
                }

                $titleRows = 0;

                // Необязательный крупный заголовок отчёта.
                if ($title) {
                    $titleStyle = (new Style)
                        ->setFontBold()
                        ->setFontSize(14)
                        ->setFontColor('1C1917');
                    $writer->addRow(Row::fromValues([$title], $titleStyle));
                    $writer->addRow(Row::fromValues([])); // пустая строка-отступ
                    $titleRows = 2;
                }

                // Шапка.
                $headerStyleByAlign = [
                    'left' => self::headerStyle(CellAlignment::LEFT),
                    'right' => self::headerStyle(CellAlignment::RIGHT),
                    'center' => self::headerStyle(CellAlignment::CENTER),
                ];
                $headerCells = [];
                foreach ($columns as $col) {
                    $align = self::align($col);
                    $headerCells[] = Cell::fromValue((string) ($col['label'] ?? ''), $headerStyleByAlign[$align]);
                }
                $writer->addRow(new Row($headerCells));

                // Закрепляем заголовок (всё, что выше первой строки данных).
                $sheetView = (new SheetView)->setFreezeRow($titleRows + 2);
                $writer->getCurrentSheet()->setSheetView($sheetView);

                // Предрассчитанные стили ячеек по колонкам (чёт/нечет — зебра).
                $oddStyles = [];
                $evenStyles = [];
                $footerStyles = [];
                foreach ($columns as $i => $col) {
                    $align = self::align($col);
                    $format = self::FORMAT_CODES[$col['format'] ?? 'text'] ?? null;
                    $oddStyles[$i] = self::bodyStyle($align, $format, null, false);
                    $evenStyles[$i] = self::bodyStyle($align, $format, 'F4F4F5', false);
                    $footerStyles[$i] = self::bodyStyle($align, $format, 'E7E5E4', true);
                }

                // Данные.
                foreach ($body as $r => $values) {
                    $styles = ($r % 2 === 0) ? $oddStyles : $evenStyles;
                    $writer->addRow(new Row(self::cells($columns, $values, $styles)));
                }

                // Итоги.
                foreach ($footer as $values) {
                    $writer->addRow(new Row(self::cells($columns, $values, $footerStyles)));
                }

                $writer->close();
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$filename.'.xlsx"',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
            ]
        );
    }

    /**
     * Преобразует строку сырых значений в типизированные ячейки с нужными стилями.
     *
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, mixed>  $values
     * @param  array<int, Style>  $styles
     * @return array<int, Cell>
     */
    private static function cells(array $columns, array $values, array $styles): array
    {
        $cells = [];
        foreach ($columns as $i => $col) {
            $value = $values[$i] ?? null;
            $format = $col['format'] ?? 'text';

            // Числовые форматы должны нести именно числа, иначе Excel не суммирует.
            if (in_array($format, ['money', 'number', 'int', 'percent'], true)) {
                $value = ($value === null || $value === '') ? null : (float) $value;
            }

            $cells[] = Cell::fromValue($value, $styles[$i]);
        }

        return $cells;
    }

    private static function headerStyle(string $align): Style
    {
        return (new Style)
            ->setFontBold()
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('1C1917')
            ->setFontSize(11)
            ->setCellAlignment($align)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText(true)
            ->setBorder(new Border(
                new BorderPart(Border::BOTTOM, '000000', Border::WIDTH_THIN, Border::STYLE_SOLID),
            ));
    }

    private static function bodyStyle(string $align, ?string $format, ?string $bg, bool $bold): Style
    {
        $style = (new Style)
            ->setFontSize(10)
            ->setCellAlignment($align)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);

        if ($format !== null) {
            $style->setFormat($format);
        }
        if ($bg !== null) {
            $style->setBackgroundColor($bg);
        }
        if ($bold) {
            $style->setFontBold();
            $style->setBorder(new Border(
                new BorderPart(Border::TOP, '78716C', Border::WIDTH_THIN, Border::STYLE_SOLID),
            ));
        }

        return $style;
    }

    private static function align(array $col): string
    {
        if (! empty($col['align'])) {
            return $col['align'];
        }

        return in_array($col['format'] ?? 'text', ['money', 'number', 'int', 'percent'], true)
            ? CellAlignment::RIGHT
            : CellAlignment::LEFT;
    }

    /**
     * Подбирает ширину каждой колонки по самому длинному значению (с разумными
     * пределами), либо берёт явно заданную в описании колонки.
     *
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, array<int, mixed>>  $body
     * @param  array<int, array<int, mixed>>  $footer
     * @return array<int, float>
     */
    private static function columnWidths(array $columns, array $body, array $footer): array
    {
        $widths = [];
        foreach ($columns as $i => $col) {
            if (! empty($col['width'])) {
                $widths[$i] = (float) $col['width'];

                continue;
            }

            $max = mb_strlen((string) ($col['label'] ?? ''));
            foreach ([$body, $footer] as $set) {
                foreach ($set as $row) {
                    $value = $row[$i] ?? null;
                    if ($value === null) {
                        continue;
                    }
                    $len = mb_strlen(self::displayLength($value, $col['format'] ?? 'text'));
                    if ($len > $max) {
                        $max = $len;
                    }
                }
            }

            // Небольшой запас + ограничители, чтобы лист не разъезжался.
            $widths[$i] = (float) max(9, min(60, $max + 2));
        }

        return $widths;
    }

    private static function displayLength(mixed $value, string $format): string
    {
        if (in_array($format, ['money', 'number', 'int', 'percent'], true) && is_numeric($value)) {
            // Приближённая ширина для отформатированного числа (разделители + символ).
            return number_format((float) $value, $format === 'money' ? 2 : 0, ',', ' ').($format === 'money' ? ' ₽' : '');
        }

        return (string) $value;
    }
}
