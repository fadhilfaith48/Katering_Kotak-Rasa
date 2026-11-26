@php
    $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    $laba = (float) $laba;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan KotakRasa - {{ $labelBulan }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f1b17;
            margin: 0;
        }

        h1 {
            font-size: 18px;
            margin: 0;
            color: #1f1b17;
        }

        h2 {
            font-size: 12px;
            margin: 16px 0 6px;
            color: #584237;
        }

        p {
            margin: 3px 0;
        }

        .muted {
            color: #806b5f;
        }

        .summary {
            margin-top: 12px;
            padding: 10px;
            background: #fff8f3;
            border: 1px solid #eee5dd;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #9d4300;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.data th,
        table.data td {
            border: 1px solid #eee5dd;
            padding: 6px 7px;
            text-align: left;
            vertical-align: top;
        }

        table.data th {
            background: #fbf7f2;
            font-weight: bold;
            color: #584237;
        }

        .num {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            background: #fcf2eb;
        }

        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #eee5dd;
            color: #806b5f;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <h1>KotakRasa</h1>
    <p class="muted">Laporan keuangan bulanan - {{ $labelBulan }}</p>

    <div class="summary">
        <p class="muted">Pemasukan</p>
        <p class="summary-value">{{ $rupiah($pemasukan) }}</p>
        <p class="muted">DP hangus {{ $rupiah($dpHangus) }} termasuk di dalam pemasukan.</p>
        <p>Total pengeluaran: <strong>{{ $rupiah($totalPengeluaran) }}</strong></p>
        <p>{{ $laba >= 0 ? 'Laba bersih' : 'Rugi' }}: <strong>{{ $rupiah(abs($laba)) }}</strong></p>
    </div>

    <h2>Pengeluaran per kategori</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="num">Total (Rp)</th>
                <th class="num">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perKategori as $item)
                @php
                    $nilai = (float) $item->total;
                    $persen = $totalPengeluaran > 0 ? (int) round(($nilai / $totalPengeluaran) * 100) : 0;
                @endphp
                <tr>
                    <td>{{ $item->kategori?->nama_kategori ?? 'Tanpa kategori' }}</td>
                    <td class="num">{{ $rupiah($nilai) }}</td>
                    <td class="num">{{ $persen }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Belum ada pengeluaran pada bulan ini.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td>Total</td>
                <td class="num">{{ $rupiah($totalPengeluaran) }}</td>
                <td class="num">100%</td>
            </tr>
        </tbody>
    </table>
    <p class="muted">{{ $jumlahTransaksi }} transaksi pada bulan ini.</p>

    <h2>Tagihan belum lunas</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Pelanggan</th>
                <th>Tanggal acara</th>
                <th class="num">Total</th>
                <th class="num">Dibayar</th>
                <th class="num">Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tagihan as $item)
                <tr>
                    <td>{{ $item->nomor }}</td>
                    <td>{{ $item->pelanggan?->nama ?? 'Pelanggan' }}</td>
                    <td>{{ $item->tanggal_acara?->locale('id')->isoFormat('D MMM YYYY') ?? '-' }}</td>
                    <td class="num">{{ $rupiah($item->total) }}</td>
                    <td class="num">{{ $rupiah($item->total_dibayar) }}</td>
                    <td class="num">{{ $rupiah($item->sisa_tagihan) }}</td>
                    <td>{{ $item->label_status_bayar }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Semua tagihan lunas.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5">Total tagihan</td>
                <td class="num">{{ $rupiah($totalTagihan) }}</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <p class="muted">Pesanan batal tidak dihitung.</p>

    <div class="footer">
        Dibuat {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} - KotakRasa - data internal pemilik usaha.
    </div>
</body>
</html>
