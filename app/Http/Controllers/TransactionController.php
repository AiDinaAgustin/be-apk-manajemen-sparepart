<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\DetailTransaction;
use App\Models\Sparepart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        // Jika user adalah admin, tampilkan semua transaksi
        if ($user->hasRole('admin')) {
            $transactions = Transaction::with(['details.sparepart', 'user'])
                            ->orderBy('created_at', 'desc')
                            ->get();
        } 
        // Jika user adalah staff, hanya tampilkan transaksi miliknya
        else {
            $transactions = Transaction::where('id_user', $user->id)
                            ->with(['details.sparepart', 'user'])
                            ->orderBy('created_at', 'desc')
                            ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'nama_pemohon' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.id_sparepart' => 'required|exists:spareparts,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);
        
        // Cek ketersediaan stok
        foreach ($validated['items'] as $item) {
            $sparepart = Sparepart::find($item['id_sparepart']);
            
            if ($sparepart->stok < $item['jumlah']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok {$sparepart->name_sparepart} tidak mencukupi"
                ], 422);
            }
        }
        
        // Buat transaksi dalam database transaction untuk memastikan integritas data
        DB::beginTransaction();
        try {
            // Generate nomor transaksi
            $latestTransaction = Transaction::latest('id')->first();
            $noTransaksi = $latestTransaction ? $latestTransaction->id + 1 : 1;
            
            // Buat transaksi baru
            $transaction = Transaction::create([
                'no_transaksi' => $noTransaksi,
                'nama_pemohon' => $validated['nama_pemohon'],
                'id_user' => $user->id,
                'status' => 'pending'
            ]);
            
            // Buat detail transaksi
            foreach ($validated['items'] as $item) {
                DetailTransaction::create([
                    'id_transaksi' => $transaction->id,
                    'id_sparepart' => $item['id_sparepart'],
                    'jumlah' => $item['jumlah']
                ]);
            }
            
            DB::commit();
            
            $transaction = Transaction::with(['details.sparepart', 'user'])->find($transaction->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Permintaan sparepart berhasil dibuat',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat permintaan sparepart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $user = Auth::user();
        
        $transaction->load(['details.sparepart', 'user']);
        
        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $user = Auth::user();
        
        // Cek jika transaksi masih pending
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi yang sudah diproses tidak dapat diubah'
            ], 422);
        }
        
        $validated = $request->validate([
            'nama_pemohon' => 'sometimes|string|max:255',
            'items' => 'sometimes|array|min:1',
            'items.*.id_sparepart' => 'required|exists:spareparts,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);
        
        // Update transaksi dalam database transaction
        DB::beginTransaction();
        try {
            // Update data transaksi
            if (isset($validated['nama_pemohon'])) {
                $transaction->update([
                    'nama_pemohon' => $validated['nama_pemohon']
                ]);
            }
            
            // Update detail transaksi jika ada
            if (isset($validated['items'])) {
                // Hapus detail transaksi lama
                DetailTransaction::where('id_transaksi', $transaction->id)->delete();
                
                // Cek ketersediaan stok untuk item baru
                foreach ($validated['items'] as $item) {
                    $sparepart = Sparepart::find($item['id_sparepart']);
                    
                    if ($sparepart->stok < $item['jumlah']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stok {$sparepart->name_sparepart} tidak mencukupi"
                        ], 422);
                    }
                    
                    // Buat detail baru
                    DetailTransaction::create([
                        'id_transaksi' => $transaction->id,
                        'id_sparepart' => $item['id_sparepart'],
                        'jumlah' => $item['jumlah']
                    ]);
                }
            }
            
            DB::commit();
            
            $transaction = Transaction::with(['details.sparepart', 'user'])->find($transaction->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Permintaan sparepart berhasil diupdate',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah permintaan sparepart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve transaction.
     */
    public function approve(Transaction $transaction): JsonResponse
    {
        $user = Auth::user();
        
        // Cek jika transaksi masih pending
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah diproses sebelumnya'
            ], 422);
        }
        
        // Load detail transaksi
        $details = $transaction->details;
        
        // Approve transaksi dalam database transaction
        DB::beginTransaction();
        try {
            // Update stok sparepart
            foreach ($details as $detail) {
                $sparepart = $detail->sparepart;
                
                // Cek stok lagi untuk memastikan masih tersedia
                if ($sparepart->stok < $detail->jumlah) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$sparepart->name_sparepart} tidak mencukupi"
                    ], 422);
                }
                
                // Kurangi stok
                $sparepart->update([
                    'stok' => $sparepart->stok - $detail->jumlah
                ]);
            }
            
            // Update status transaksi
            $transaction->update([
                'status' => 'approved'
            ]);
            
            DB::commit();
            
            $transaction = Transaction::with(['details.sparepart', 'user'])->find($transaction->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disetujui',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject transaction.
     */
    public function reject(Transaction $transaction): JsonResponse
    {
        $user = Auth::user();
        
        // Cek jika transaksi belum diproses
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi sudah diproses sebelumnya'
            ], 422);
        }
        
        // Reject transaksi
        $transaction->update([
            'status' => 'rejected'
        ]);
        
        $transaction = Transaction::with(['details.sparepart', 'user'])->find($transaction->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil ditolak',
            'data' => $transaction
        ]);
    }
}