<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderExport implements FromCollection
{
    public function collection()
    {
        return Order::select('nama', 'created_at', 'total_harga')
            ->where('status_order', 'diterima')
            ->get();
    }
}