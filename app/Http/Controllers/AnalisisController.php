<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use App\Models\User;
class AnalisisController extends Controller
{
    /**
     * Menampilkan hasil analisis pelanggan berdasarkan order
     */

   public function index()
    {
        $orders = Order::with('items')->get();

        $dataAnalisis = collect($orders)
            ->groupBy('nama')
            ->map(function ($ordersByCustomer, $nama) {
                $allItems = collect();

                foreach ($ordersByCustomer as $order) {
                    $allItems = $allItems->merge($order->items);
                }

                $jumlahTransaksi = $ordersByCustomer->count();

                $frekuensiKunjungan = $ordersByCustomer
                    ->groupBy(function ($order) {
                        return date('Y-m', strtotime($order->created_at));
                    })
                    ->count();

                $menuFavorit = $allItems
                    ->groupBy('produk')
                    ->sortByDesc(fn($group) => $group->sum('quantity'))
                    ->keys()
                    ->first();

                return [
                    'nama_pelanggan' => $nama,
                    'jumlah_transaksi' => $jumlahTransaksi,
                    'frekuensi_kunjungan' => $frekuensiKunjungan,
                    'menu_favorit' => $menuFavorit ?? '-',
                ];
            })
            ->values();

        return view('analisis.index', compact('dataAnalisis'));
    }

    public function analisisKMeans(Request $request)
    {
        // Contoh simulasi cluster sederhana (belum KMeans sebenarnya)
        $data = collect($request->data);

        $hasil = $data->map(function ($item) {
            if ($item['frekuensi_kunjungan'] >= 5 && $item['jumlah_transaksi'] >= 10) {
                $status = 'loyal';
            } elseif ($item['frekuensi_kunjungan'] >= 2) {
                $status = 'potensial';
            } else {
                $status = 'inactive';
            }
            return array_merge($item, ['cluster' => $status]);
        });

        return response()->json(['hasil' => $hasil]);
    }

    public function terapkanCluster(Request $request)
    {
        $hasil = $request->hasil;

        foreach ($hasil as $data) {
            $user = User::where('name', $data['nama_pelanggan'])->first();
            if ($user) {
                $user->update(['status' => $data['cluster']]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Status pelanggan berhasil diperbarui.']);
    }

}
