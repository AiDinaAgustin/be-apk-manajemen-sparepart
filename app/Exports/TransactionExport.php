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
    protected $rowNumber = 0; // Tambahkan counter untuk nomor urut

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
            ['Nama Pemohon: ' . $this->transaction->nama_pemohon, '', 'Tanggal Permintaan: ' . Carbon::parse($this->transaction->created_at)->format('d F Y')],
            ['Petugas Gudang: ' . Auth::user()->name, '', 'Waktu Cetak: ' . Carbon::now()->format('d F Y : H.i')],
            [''],
            ['DETAIL PERMINTAAN SPAREPART'],
            ['No', 'No Spareparts', 'Nama Spareparts', 'Jumlah'],
        ];
    }

    public function map($detail): array
    {
        $this->rowNumber++; 
        
        return [
            $this->rowNumber, 
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
        // Merge title cell - perbaiki untuk 4 kolom
        $sheet->mergeCells('A1:D1');
        
        // Set judul menjadi bold dan besar
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Bold subtitles
        $sheet->getStyle('A6')->getFont()->setBold(true);
        
        // Header tabel menjadi bold - perbaiki untuk 4 kolom
        $sheet->getStyle('A7:D7')->getFont()->setBold(true);

        $dataLastRow = $sheet->getHighestRow();
        $sheet->getStyle('A8:D' . $dataLastRow)->getAlignment()
          ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    
        
        // Tambahkan tempat tanda tangan di bawah
        $lastRow = $sheet->getHighestRow() + 3;
        
        // Signature on the left
        $sheet->setCellValue('A' . $lastRow, 'Pemohon');
        $sheet->getStyle('A' . $lastRow)->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($lastRow + 4), $this->transaction->nama_pemohon);
        $sheet->getStyle('A' . ($lastRow + 3) . ':A' . ($lastRow + 3))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Signature on the right - pindahkan ke kolom D karena sekarang ada 4 kolom
        $sheet->setCellValue('D' . $lastRow, 'Petugas Gudang');
        $sheet->getStyle('D' . $lastRow)->getFont()->setBold(true);
        $sheet->setCellValue('D' . ($lastRow + 4), Auth::user()->name);
        $sheet->getStyle('D' . ($lastRow + 3) . ':D' . ($lastRow + 3))->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        return $sheet;
    }
}