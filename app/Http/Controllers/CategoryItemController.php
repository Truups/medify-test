<?php

namespace App\Http\Controllers;

use App\Models\CategoryItem;
use Illuminate\Http\Request;

class CategoryItemController extends Controller
{
    public function index()
    {
        return view('category_items.index.index');
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = new CategoryItem();
        } else {
            $item = CategoryItem::findOrFail($id);
        }
        $data['item'] = $item;
        $data['method'] = $method;
        return view('category_items.form.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        $request->validate([
            'nama' => 'required'
        ]);

        if ($method == 'new') {
            $data_item = new CategoryItem();
            $kode = CategoryItem::withTrashed()->max('id');
            $kode = $kode + 1;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);
            sleep(3);
        } else {
            $data_item = CategoryItem::findOrFail($id);
            $kode = $data_item->kode;
        }

        $data_item->nama = $request->nama;
        $data_item->kode = $kode;

        $data_item->save();
        return redirect('category-items');
    }

    public function singleView($kode)
    {
        $category = CategoryItem::where('kode', $kode)->firstOrFail();

        $data['data'] = $category;
        $data['items'] = $category->items()->orderBy('nama')->get();

        return view('category_items.single.index', $data);
    }

    public function delete($id)
    {
        $data_item = CategoryItem::findOrFail($id);

        // Lepas relasi many to many sebelum kategori dihapus.
        $data_item->items()->detach();
        $data_item->delete();

        return redirect('category-items');
    }

    public function search(Request $request)
    {
        $kode = $request->input('kode');
        $nama = $request->input('nama');

        $data_search = CategoryItem::query();

        if ($request->filled('kode')) $data_search = $data_search->where('kode', $kode);
        if ($request->filled('nama')) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');

        $data_search = $data_search
            ->select('id', 'kode', 'nama')->orderBy('id')->get();


        return response()->json([
            'status' => 200,
            'data' => $data_search
        ]);
    }
}
