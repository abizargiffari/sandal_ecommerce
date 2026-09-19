@extends('layouts.admin')

@section('title', 'Manajemen Retur')
@section('page-title', 'Manajemen Pengambilan Barang & Retur')

@section('content')
    {{-- Tab status --}}
    <div class="flex flex-wrap gap-1 mb-4 border-b">
        <a href="{{ route('admin.returns.index') }}"
           class="px-4 py-2 text-sm border-b-2 {{ ! request('status') ? 'border-gray-900 font-medium' : 'border-transparent text-gray-500' }}">
            Semua ({{ $statusCounts->sum() }})
        </a>
        @foreach (['requested' => 'Diajukan', 'approved' => 'Disetujui', 'completed' => 'Selesai', 'rejected' => 'Ditolak'] as $key => $label)
            <a href="{{ route('admin.returns.index', ['status' => $key]) }}"
               class="px-4 py-2 text-sm border-b-2 {{ request('status') === $key ? 'border-gray-900 font-medium' : 'border-transparent text-gray-500' }}">
                {{ $label }} ({{ $statusCounts[$key] ?? 0 }})
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-end">
            <a href="{{ route('admin.returns.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-800">
                + Ajukan Retur
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">No. Pesanan</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Alasan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($returns as $return)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $return->order?->order_number ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $return->order?->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $return->orderItem?->product_name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate" title="{{ $return->reason }}">
                            {{ $return->reason }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $label = ['requested' => 'Diajukan', 'approved' => 'Disetujui', 'completed' => 'Selesai', 'rejected' => 'Ditolak'][$return->status];
                                $color = [
                                    'requested' => 'bg-yellow-50 text-yellow-700',
                                    'approved' => 'bg-blue-50 text-blue-700',
                                    'completed' => 'bg-green-50 text-green-700',
                                    'rejected' => 'bg-red-50 text-red-600',
                                ][$return->status];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs {{ $color }}">{{ $label }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if (in_array($return->status, ['requested', 'approved']))
                                <details class="inline-block text-left">
                                    <summary class="text-blue-600 hover:underline cursor-pointer list-none">
                                        Proses ▾
                                    </summary>
                                    <div class="absolute bg-white border rounded shadow-lg p-3 mt-1 w-64 z-10">
                                        <form method="POST" action="{{ route('admin.returns.updateStatus', $return) }}">
                                            @csrf
                                            <label class="block text-xs font-medium mb-1">Ubah ke status</label>
                                            <select name="status" class="w-full border rounded px-2 py-1.5 text-sm mb-2">
                                                @if ($return->status === 'requested')
                                                    <option value="approved">Disetujui</option>
                                                    <option value="rejected">Ditolak</option>
                                                @elseif ($return->status === 'approved')
                                                    <option value="completed">Selesai (barang diterima kembali)</option>
                                                    <option value="rejected">Ditolak</option>
                                                @endif
                                            </select>
                                            <label class="block text-xs font-medium mb-1">Jumlah Refund (opsional)</label>
                                            <input type="number" name="refund_amount" min="0" placeholder="Rp"
                                                   class="w-full border rounded px-2 py-1.5 text-sm mb-2">
                                            <button type="submit" class="w-full bg-gray-900 text-white text-xs py-1.5 rounded">
                                                Simpan
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            @else
                                <span class="text-gray-400 text-xs">
                                    @if ($return->refund_amount)
                                        Refund: Rp {{ number_format($return->refund_amount, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada permintaan retur.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $returns->links() }}
        </div>
    </div>
@endsection
