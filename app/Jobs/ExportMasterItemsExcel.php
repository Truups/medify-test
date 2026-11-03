<?php

namespace App\Jobs;

use App\Models\MasterItem;
use App\Models\MasterItemExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ExportMasterItemsExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Batas waktu proses job (detik).
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Jangan ulangi job yang sudah gagal, supaya tidak terjadi duplikasi file.
     *
     * @var int
     */
    public $tries = 1;

    private string $exportId;

    public function __construct(string $exportId)
    {
        $this->exportId = $exportId;
    }

    public function handle()
    {
        $export = MasterItemExport::findOrFail($this->exportId);

        $export->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $relativePath = 'exports/' . $export->id . '.xlsx';
            Storage::disk('local')->makeDirectory('exports');

            $spreadsheet = $this->buildSpreadsheet($export->filters ?? []);
            $writer = new Xlsx($spreadsheet);
            $writer->save(Storage::disk('local')->path($relativePath));

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $export->update([
                'status' => 'completed',
                'file_path' => $relativePath,
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $export->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e)
    {
        MasterItemExport::whereKey($this->exportId)->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
        ]);
    }

    private function buildSpreadsheet(array $filters): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle('Daftar Master Items');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Master Items');

        // Header
        $sheet->fromArray(
            ['No', 'Kode', 'Nama', 'Kategori', 'Jenis', 'Harga Beli', 'Harga Jual', 'Supplier'],
            null,
            'A1'
        );

        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F4E78'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $row = 2;
        $number = 0;

        $this->buildQuery($filters)->chunkById(500, function ($items) use ($sheet, &$row, &$number) {
            foreach ($items as $item) {
                $hargaJual = (int) round($item->harga_beli + $item->harga_beli * $item->laba / 100);

                $sheet->setCellValue('A' . $row, $number + 1);
                $sheet->setCellValueExplicit('B' . $row, (string) $item->kode, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $row, (string) $item->nama, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit(
                    'D' . $row,
                    $item->categories->pluck('nama')->implode(', '),
                    DataType::TYPE_STRING
                );
                $sheet->setCellValueExplicit('E' . $row, (string) $item->jenis, DataType::TYPE_STRING);
                $sheet->setCellValue('F' . $row, $item->harga_beli);
                $sheet->setCellValue('G' . $row, $hargaJual);
                $sheet->setCellValueExplicit('H' . $row, (string) $item->supplier, DataType::TYPE_STRING);

                $number++;
                $row++;
            }
        });

        $lastRow = $row - 1;

        if ($lastRow >= 2) {
            // Format rupiah untuk harga beli dan harga jual.
            $sheet->getStyle('F2:G' . $lastRow)
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');

            $sheet->getStyle('F2:G' . $lastRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getStyle('A1:H' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFBFBFBF'],
                ],
            ],
        ]);

        if ($lastRow >= 2) {
            $sheet->getStyle('A2:H' . $lastRow)
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:H' . $lastRow);

        foreach ([
            'A' => 6,
            'B' => 12,
            'C' => 30,
            'D' => 32,
            'E' => 14,
            'F' => 16,
            'G' => 16,
            'H' => 22,
        ] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        return $spreadsheet;
    }

    private function buildQuery(array $filters)
    {
        $query = MasterItem::query()->with('categories:id,kode,nama');

        if (!empty($filters['kode'])) {
            $query->where('kode', $filters['kode']);
        }

        if (!empty($filters['nama'])) {
            $query->where('nama', 'LIKE', '%' . $filters['nama'] . '%');
        }

        if (!empty($filters['hargamin'])) {
            $query->where('harga_beli', '>=', $filters['hargamin']);
        }

        if (!empty($filters['hargamax'])) {
            $query->where('harga_beli', '<=', $filters['hargamax']);
        }

        return $query;
    }
}
