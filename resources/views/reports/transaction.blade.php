<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Permintaan Sparepart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .info {
            margin-bottom: 20px;
            width: 100%;
        }
        .info-row {
            width: 100%;
            clear: both;
            margin-bottom: 10px;
        }
        .info-left {
            float: left;
            width: 48%;
        }
        .info-right {
            float: right;
            width: 48%;
            text-align: left;
        }
        .info-item {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            clear: both;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .signature {
            width: 100%;
            margin-top: 50px;
            clear: both;
        }
        .signature-left {
            float: left;
            width: 45%;
            text-align: center;
        }
        .signature-right {
            float: right;
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 5px;
        }
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>    
    <div class="info">
        <div class="info-row">
            <div class="info-left">
                <div class="info-item"><strong>Nama Pemohon:</strong> {{ $transaction->nama_pemohon }}</div>
                <div class="info-item"><strong>Petugas Gudang:</strong> {{ $admin_name }}</div>
            </div>
            <div class="info-right">
            <div class="info-item"><strong>Tanggal Permintaan:</strong> {{ date('d F Y', strtotime($transaction->created_at)) }}</div>
            <div class="info-item"><strong>Waktu Cetak:</strong> {{ date('d F Y H:i:s', strtotime($print_time)) }}</div>
            </div>
        </div>
    </div>
    
    <div class="clearfix"></div>
    
    <h3>DETAIL PERMINTAAN SPAREPART</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Sparepart</th>
                <th>Nama Sparepart</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->details as $detail)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $detail->sparepart->id }}</td>
                <td>{{ $detail->sparepart->name_sparepart }}</td>
                <td>{{ $detail->jumlah }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="signature">
        <div class="signature-left">
            <div>Pemohon</div>
            <div class="signature-line"></div>
            <div>{{ $transaction->nama_pemohon }}</div>
        </div>
        <div class="signature-right">
            <div>Petugas Gudang</div>
            <div class="signature-line"></div>
            <div>{{ $admin_name }}</div>
        </div>
    </div>
</body>
</html>