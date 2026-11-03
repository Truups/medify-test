<?php

namespace App\Http\Controllers;

use App\Models\CategoryItem;
use App\Models\MasterItem;
use App\Models\MasterItemExport;
use App\Jobs\ExportMasterItemsExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class MasterItemsController extends Controller
{
    public function index()
    {
        return view('master_items.index.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'hargamin' => 'nullable|numeric|min:0',
            'hargamax' => 'nullable|numeric|min:0',
        ]);

        $kode = $request->input('kode');
        $nama = $request->input('nama');

        $data_search = MasterItem::query();

        if ($request->filled('kode')) $data_search = $data_search->where('kode', $kode);
        if ($request->filled('nama')) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');

        // Filter min & max diproses terpisah, supaya masing-masing tetap
        // bekerja walau salah satunya kosong.
        if ($request->filled('hargamin')) {
            $data_search = $data_search->where('harga_beli', '>=', $request->input('hargamin'));
        }

        if ($request->filled('hargamax')) {
            $data_search = $data_search->where('harga_beli', '<=', $request->input('hargamax'));
        }

        $data_search = $data_search->with('categories:id,kode,nama')
            ->select('id', 'kode', 'nama', 'jenis', 'harga_beli', 'laba', 'supplier', 'foto_barang')
            ->orderBy('id')->get();


        return response()->json([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = new MasterItem();
        } else {
            $item = MasterItem::with('categories')->findOrFail($id);
        }
        $data['item'] = $item;
        $data['method'] = $method;
        $data['categories'] = CategoryItem::orderBy('nama')->get();
        return view('master_items.form.index', $data);
    }

    public function singleView($kode)
    {
        $data['data'] = MasterItem::with('categories')->where('kode', $kode)->firstOrFail();
        return view('master_items.single.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'harga_beli' => 'required|numeric',
            'laba' => 'required|numeric',
            'supplier' => 'required|string',
            'jenis' => 'required|string',
            'foto_barang' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:category_items,id',
        ]);

        if ($method == 'new') {
            $data_item = new MasterItem;
            $kode = MasterItem::count('id');
            $kode = $kode + 1;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);
            sleep(3);
        } else {
            $data_item = MasterItem::find($id);
            $kode = $data_item->kode;
        }

        $data_item->nama = $request->nama;
        $data_item->harga_beli = $request->harga_beli;
        $data_item->laba = $request->laba;
        $data_item->kode = $kode;
        $data_item->supplier = $request->supplier;
        $data_item->jenis = $request->jenis;

        if ($request->hasFile('foto_barang')) {
            if ($data_item->foto_barang) {
                Storage::disk('public')->delete($data_item->foto_barang);
            }

            // simpan path relatif, nama file di-random otomatis
            $data_item->foto_barang = $request->file('foto_barang')->store('foto_barang', 'public');
        }

        $data_item->save();

        // Sinkronkan relasi many to many dengan kategori yang dipilih.
        $data_item->categories()->sync($request->input('categories', []));

        return redirect('master-items');
    }

    /**
     * Export daftar master item ke file PDF menggunakan DomPDF.
     */
    public function exportPdf()
    {
        $items = MasterItem::with('categories:id,kode,nama')
            ->orderBy('nama')
            ->get();

        $pdf = Pdf::loadView('master_items.pdf.index', compact('items'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('master-items-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Buat antrian export Excel sesuai filter yang aktif.
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'hargamin' => 'nullable|numeric|min:0',
            'hargamax' => 'nullable|numeric|min:0',
        ]);

        $filters = array_filter(
            $request->only(['kode', 'nama', 'hargamin', 'hargamax']),
            function ($value) {
                return $value !== null && $value !== '';
            }
        );

        $export = MasterItemExport::create([
            'status' => 'pending',
            'filters' => $filters,
            'file_name' => 'master-items-' . now()->format('Ymd-His') . '.xlsx',
        ]);

        ExportMasterItemsExcel::dispatch($export->id);

        // Simpan di session agar link download tetap tersedia setelah halaman di-refresh.
        $request->session()->put('master_item_export_id', $export->id);

        return redirect()->route('master-items-list');
    }

    /**
     * Status export untuk polling dari halaman index.
     */
    public function exportStatus(MasterItemExport $export)
    {
        return response()->json([
            'status' => $export->status,
            'download_url' => $export->isCompleted()
                ? route('master-items-export-download', $export)
                : null,
            'error' => $export->error_message,
        ]);
    }

    /**
     * Download file Excel yang sudah selesai dibuat oleh queue.
     */
    public function downloadExport(MasterItemExport $export)
    {
        abort_unless($export->isCompleted() && $export->file_path, 404);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($export->file_path), 404, 'File export tidak ditemukan.');

        return $disk->download($export->file_path, $export->file_name);
    }

    public function delete($id)
    {
        MasterItem::find($id)->delete();
        return redirect('master-items');
    }

    public function updateRandomData()
    {
        $data = MasterItem::get();
        foreach($data as $item)
        {
            $kode = $item->id;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);

            $item->harga_beli = rand(100,1000000);
            $item->laba = rand(10,99);
            $item->kode = $kode;
            $item->supplier = $this->getRandomSupplier();
            $item->jenis = $this->getRandomJenis();
            $item->save();
        }
    }

    private function getRandomSupplier()
    {
        $array = ['Tokopaedi','Bukulapuk','TokoBagas','E Commurz','Blublu'];
        $random = rand(0,4);
        return $array[$random];
    }

    private function getRandomJenis()
    {
        $array = ['Obat','Alkes','Matkes','Umum','ATK'];
        $random = rand(0,4);
        return $array[$random];
    }
}
