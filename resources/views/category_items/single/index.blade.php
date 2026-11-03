@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-group mb-2">
                <a href="{{ url('category-items') }}" class="btn btn-secondary">Kembali ke Daftar Kategori</a>
            </div>

            <div class="card mb-3">
                <div class="card-header">Kategori Item</div>

                <div class="card-body">
                    <table>
                        <tr>
                            <th>Kode</th>
                            <td>:</td>
                            <td>{{ $data->kode }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>:</td>
                            <td>{{ $data->nama }}</td>
                        </tr>
                    </table>

                    <a class="btn btn-info" href="{{ url('category-items/form/edit') }}/{{ $data->id }}">Edit</a>
                    <a class="btn btn-danger" href="{{ url('category-items/delete') }}/{{ $data->id }}" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Daftar Item dengan Kategori Ini</div>

                <div class="card-body">
                    @if ($items->isEmpty())
                        <p class="text-muted mb-0">Belum ada item yang memiliki kategori ini.</p>
                    @else
                        <table class="table table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Jenis</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Supplier</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item->kode }}</td>
                                        <td>{{ $item->nama }}</td>
                                        <td>{{ $item->jenis }}</td>
                                        <td>{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                                        <td>{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                                        <td>{{ $item->supplier }}</td>
                                        <td>
                                            <a href="{{ url('master-items/view') }}/{{ $item->kode }}" class="btn btn-primary btn-sm">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
@endsection
