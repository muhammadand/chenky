<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CafeOlapSeeder extends Seeder
{
    public function run(): void
    {
        // ===== USERS =====
        DB::table('users')->insert([
            ['name' => 'Admin Cafe', 'email' => 'admin@cafe.com', 'role' => 'admin', 'password' => bcrypt('password')],
            ['name' => 'Kasir 1', 'email' => 'kasir1@cafe.com', 'role' => 'kasir', 'password' => bcrypt('password')],
            ['name' => 'Kasir 2', 'email' => 'kasir2@cafe.com', 'role' => 'kasir', 'password' => bcrypt('password')],
        ]);

        // ===== CATEGORIES =====
        $categories = [
            ['name' => 'Minuman'],
            ['name' => 'Makanan'],
            ['name' => 'Snack'],
        ];
        DB::table('categories')->insert($categories);

        // ===== PRODUCTS =====
        $products = [
            ['name' => 'Kopi Latte', 'price' => 25000, 'discount' => 0, 'discount_active' => false, 'foto' => 'kopi.jpg', 'category_id' => 1],
            ['name' => 'Americano', 'price' => 22000, 'discount' => 0, 'discount_active' => false, 'foto' => 'americano.jpg', 'category_id' => 1],
            ['name' => 'Teh Tarik', 'price' => 18000, 'discount' => 0, 'discount_active' => false, 'foto' => 'tehtarik.jpg', 'category_id' => 1],
            ['name' => 'Nasi Goreng', 'price' => 30000, 'discount' => 0, 'discount_active' => false, 'foto' => 'nasigoreng.jpg', 'category_id' => 2],
            ['name' => 'Mie Goreng', 'price' => 28000, 'discount' => 0, 'discount_active' => false, 'foto' => 'miegoreng.jpg', 'category_id' => 2],
            ['name' => 'Kentang Goreng', 'price' => 15000, 'discount' => 0, 'discount_active' => false, 'foto' => 'kentang.jpg', 'category_id' => 3],
            ['name' => 'Roti Bakar', 'price' => 20000, 'discount' => 0, 'discount_active' => false, 'foto' => 'roti.jpg', 'category_id' => 3],
        ];
        DB::table('products')->insert($products);

        // ===== ORDERS + ORDER ITEMS =====
        $produkList = DB::table('products')->get();

        // Buat data penjualan selama tahun 2024
        $startDate = Carbon::create(2024, 1, 1);
        $endDate = Carbon::create(2024, 12, 31);
        $kasirList = [2, 3]; // user_id kasir1 & kasir2

        $orderId = 1;
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {

            // Rata-rata 3–10 transaksi per hari
            $transaksiPerHari = rand(3, 10);

            for ($i = 0; $i < $transaksiPerHari; $i++) {
                $namaPelanggan = 'Pelanggan ' . Str::random(3);
                $meja = rand(1, 10);

                // Insert order
                $order_id = DB::table('orders')->insertGetId([
                    'nama' => $namaPelanggan,
                    'meja' => $meja,
                    'payment_status' => 'paid',
                    'status_order' => 'selesai',
                    'payment_method' => ['tunai', 'qris', 'transfer'][rand(0, 2)],
                    'created_at' => $date->toDateTimeString(),
                    'updated_at' => $date->toDateTimeString(),
                ]);

                // Tambahkan 1–4 produk per transaksi
                $jumlahProduk = rand(1, 4);
                $produkDipilih = $produkList->random($jumlahProduk);

                foreach ($produkDipilih as $produk) {
                    $qty = rand(1, 3);
                    DB::table('order_items')->insert([
                        'order_id' => $order_id,
                        'produk' => $produk->name,
                        'quantity' => $qty,
                        'price' => $produk->price * $qty,
                        'created_at' => $date->toDateTimeString(),
                        'updated_at' => $date->toDateTimeString(),
                    ]);
                }

                $orderId++;
            }
        }

        echo "✅ Data dummy penjualan tahun 2024 berhasil dibuat!\n";
    }
}
