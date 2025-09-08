<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Export transaction to PDF
     */
    public function exportPDF(Transaction $transaction)
    {
        // Load transaction dengan relasi
        $transaction = Transaction::with(['details.sparepart', 'user'])->findOrFail($transaction->id);
        
        // Data untuk PDF
        $data = [
            'transaction' => $transaction,
            'print_time' => Carbon::now()->format('d/m/Y H:i:s'),
            'admin_name' => Auth::user()->name
        ];
        
        // Generate PDF
        $pdf = PDF::loadView('reports.transaction', $data);
        
        // Download PDF dengan nama yang sesuai
        return $pdf->download('transaction_'.$transaction->no_transaksi.'.pdf');
    }
    
    /**
     * Export transaction to Excel
     */
    public function exportExcel(Transaction $transaction)
    {
        // Load transaction dengan relasi
        $transaction = Transaction::with(['details.sparepart', 'user'])->findOrFail($transaction->id);
        
        return Excel::download(new TransactionExport($transaction), 'transaction_'.$transaction->no_transaksi.'.xlsx');
    }
    
    /**
     * Export all transactions to PDF (report)
     */
    public function exportAllPDF(Request $request)
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
        
        // Filter by date and status if provided
        $query = Transaction::with(['details.sparepart', 'user'])
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
                
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        
        $transactions = $query->get();
        
        // Data untuk PDF
        $data = [
            'transactions' => $transactions,
            'start_date' => $startDate->format('d/m/Y'),
            'end_date' => $endDate->format('d/m/Y'),
            'print_time' => Carbon::now()->format('d/m/Y H:i:s'),
            'admin_name' => Auth::user()->name
        ];
        
        // Generate PDF
        $pdf = PDF::loadView('reports.transactions', $data);
        
        // Download PDF
        return $pdf->download('transactions_report_'.$startDate->format('Ymd').'_'.$endDate->format('Ymd').'.pdf');
    }
}