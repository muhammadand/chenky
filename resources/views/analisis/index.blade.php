@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">

    {{-- ✅ Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
        <h1 class="text-2xl font-bold text-slate-800">Analisis Pelanggan (CRM Analytical)</h1>

        <div class="flex gap-2">
            <button id="btnAnalisis" 
                class="bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition duration-200 text-sm">
                ⚙️ Analisis Sekarang
            </button>

            <a href="{{ route('analisis.index') }}" 
                class="bg-amber-700 hover:bg-amber-800 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition duration-200 text-sm">
                🔄 Refresh Data
            </a>
        </div>
    </div>

    {{-- ✅ Deskripsi --}}
    <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg mb-6 text-sm leading-relaxed">
        Analisis ini menampilkan hasil pengelompokan pelanggan berdasarkan 
        <strong>jumlah transaksi</strong>, <strong>frekuensi kunjungan</strong>, dan <strong>menu favorit</strong>. 
        Data ini digunakan sebagai dasar penerapan metode <strong>K-Means Clustering</strong>.
    </div>

    {{-- ✅ Tabel Analisis --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full text-left text-sm" id="tabelData">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-4 border-b border-slate-300 w-12 text-center">No</th>
                    <th class="p-4 border-b border-slate-300">Nama Pelanggan</th>
                    <th class="p-4 border-b border-slate-300">Jumlah Transaksi</th>
                    <th class="p-4 border-b border-slate-300">Frekuensi Kunjungan (per Bulan)</th>
                    <th class="p-4 border-b border-slate-300">Menu Favorit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dataAnalisis as $i => $data)
                    <tr>
                        <td class="p-4 border-b border-slate-200 text-center">{{ $i + 1 }}</td>
                        <td class="p-4 border-b border-slate-200">{{ $data['nama_pelanggan'] }}</td>
                        <td class="p-4 border-b border-slate-200">{{ $data['jumlah_transaksi'] }}</td>
                        <td class="p-4 border-b border-slate-200">{{ $data['frekuensi_kunjungan'] }}</td>
                        <td class="p-4 border-b border-slate-200">{{ $data['menu_favorit'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-slate-500">Tidak ada data pelanggan untuk dianalisis.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ✅ Modal Hasil Analisis --}}
    <div id="modalHasil" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg max-w-3xl w-full p-6">
            <h2 class="text-lg font-bold mb-4">Hasil Analisis K-Means</h2>
            <div id="hasilAnalisisTable" class="max-h-64 overflow-y-auto border rounded-md p-3 text-sm"></div>

            <div class="flex justify-end gap-3 mt-4">
                <button id="btnTerapkan" class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg text-sm">Terapkan</button>
                <button id="btnClose" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded-lg text-sm">Tutup</button>
            </div>
        </div>
    </div>

</div>

{{-- ✅ Script Analisis --}}
<script>
document.getElementById('btnAnalisis').addEventListener('click', function() {
    const rows = [...document.querySelectorAll('#tabelData tbody tr')];
    const data = rows.map(row => {
        const cells = row.querySelectorAll('td');
        return {
            nama_pelanggan: cells[1]?.innerText.trim(),
            jumlah_transaksi: parseInt(cells[2]?.innerText.trim() || 0),
            frekuensi_kunjungan: parseInt(cells[3]?.innerText.trim() || 0)
        };
    });

    fetch("{{ route('analisis.proses') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ data })
    })
    .then(res => res.json())
    .then(res => {
        const hasil = res.hasil;
        let html = `
        <table class="min-w-full border text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Nama</th>
                    <th class="p-2 border">Transaksi</th>
                    <th class="p-2 border">Frekuensi</th>
                    <th class="p-2 border">Cluster</th>
                </tr>
            </thead>
            <tbody>
                ${hasil.map(h => `
                    <tr>
                        <td class="p-2 border">${h.nama_pelanggan}</td>
                        <td class="p-2 border">${h.jumlah_transaksi}</td>
                        <td class="p-2 border">${h.frekuensi_kunjungan}</td>
                        <td class="p-2 border font-semibold text-${h.cluster === 'loyal' ? 'green' : h.cluster === 'potensial' ? 'amber' : 'red'}-600">
                            ${h.cluster.toUpperCase()}
                        </td>
                    </tr>`).join('')}
            </tbody>
        </table>`;
        document.getElementById('hasilAnalisisTable').innerHTML = html;
        document.getElementById('modalHasil').classList.remove('hidden');
        document.getElementById('btnTerapkan').dataset.hasil = JSON.stringify(hasil);
    });
});

document.getElementById('btnTerapkan').addEventListener('click', function() {
    const hasil = JSON.parse(this.dataset.hasil);
    fetch("{{ route('analisis.terapkan') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ hasil })
    })
    .then(res => res.json())
    .then(res => {
        alert(res.message);
        location.reload();
    });
});

document.getElementById('btnClose').addEventListener('click', function() {
    document.getElementById('modalHasil').classList.add('hidden');
});
</script>
@endsection
