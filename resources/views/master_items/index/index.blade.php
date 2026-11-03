@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-group mb-2">
                <a href="{{url('master-items/form/new')}}" class="btn btn-secondary">+ Master Items Baru</a>
                <a href="{{ route('master-items-export-pdf') }}" class="btn btn-danger">Export PDF</a>
                <a href="{{ route('master-items-export-excel') }}" class="btn btn-success btn-export-excel">Export Excel</a>
            </div>

            @if (session('master_item_export_id'))
                <div class="alert alert-info" id="export-status"
                    data-status-url="{{ route('master-items-export-status', session('master_item_export_id')) }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span id="export-status-text">Export menunggu antrian...</span>
                        <a href="#" id="export-download-link" class="btn btn-success btn-sm d-none">Download Excel</a>
                    </div>
                </div>
            @endif
            <div class="card">
                <div class="card-header">Daftar Master Items</div>

                <div class="card-body">
                    @include('master_items.index.filter')
                    @include('master_items.index.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
@include('master_items.index.js')
@endsection