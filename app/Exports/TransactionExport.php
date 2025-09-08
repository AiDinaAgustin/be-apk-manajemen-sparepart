<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class TransactionExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['details.sparepart', 'user']);
    }

    public function collection()
    {
        return $this->transaction->details;
    }

    public function headings(): array
    {
        return [
            ['LAPORAN PERMINTAAN SPAREPART'],
            [''],
            ['Nama Pemohon: ' . $this->transaction->nama_pemohon, '', 'Tanggal Permintaan: ' . Carbon::parse($this->transaction->created_at)->format('d/m/Y')],
            ['Petugas Gudang: ' . Auth::user()->name, '', 'Waktu Cetak: ' . Carbon::now()->format('d/m/Y H:i:s')],
            [''],
            ['DETAIL PERMINTAAN SPAREPART'],
            ['ID', 'Nama Sparepart', 'Jumlah'],
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->sparepart->id,
            $detail->sparepart->name_sparepart,
            $detail->jumlah,
        ];
    }

    public function title(): string
    {
        return 'Permintaan Sparepart';
    }

    public function styles(Worksheet $sheet)
    {
        // Merge title cell
        $sheet->mergeCells('A1:C1');
        
        // Set judul menjadi bold dan besar
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Bold subtitles
        $sheet->getStyle('A7')->getFont()->setBold(true);
        
        // Header tabel menjadi bold
        $sheet->getStyle('A8:C8')->getFont()->setBold(true);
        
        // Tambahkan tempat tanda tangan di bawah
        $lastRow = $sheet->getHighestRow() + 3;
        
        // Signature on the left
        $sheet->setCellValue('A' . $lastRow, 'Pemohon');
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($lastRow + 4), $this->transaction->nama_pemohon);
        $sheet->getStyle('A' . ($lastRow + 3) . ':A' . ($lastRow + 3))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Signature on the right
        $sheet->setCellValue('C' . $lastRow, 'Petugas Gudang');
        $sheet->getStyle('C' . $lastRow)->getFont()->setBold(true);
        $sheet->setCellValue('C' . ($lastRow + 4), Auth::user()->name);
        $sheet->getStyle('C' . ($lastRow + 3) . ':C' . ($lastRow + 3))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        return $sheet;
    }
}