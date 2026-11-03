<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Master Items</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        h1 {
            font-size: 16px;
            margin: 0 0 4px;
        }

        .meta {
            font-size: 9px;
            color: #666;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 5px 6px;
            vertical-align: top;
        }

        thead th {
            background-color: #e9ecef;
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Daftar Master Items</h1>
    <div class="meta">Dicetak: {{ now()->format('d-m-Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Kode</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Jenis</th>
                <th class="text-end">Harga Beli</th>
                <th class="text-end">Harga Jual</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->kode }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->categories->pluck('nama')->implode(', ') ?: '-' }}</td>
                    <td>{{ $item->jenis }}</td>
                    <td class="text-end">{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                    <td>{{ $item->supplier }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data master item.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
