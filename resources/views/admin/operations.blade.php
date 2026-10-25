@extends('layouts.admin')

@section('content')
@php($titles = ['payments' => 'Pembayaran Rental', 'handover' => 'Serah Terima Artefak', 'returns' => 'Pengembalian Artefak', 'fines' => 'Pengelolaan Denda'])
<div class="page-head"><div><div class="eyebrow">Alur operasional</div><h1>{{ $titles[$resource] }}</h1><p>Proses data sesuai alur rental PotionVault.</p></div></div>
@if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
@if($errors->any())<div class="notice">{{ $errors->first() }}</div>@endif
<section class="panel ornament"><div class="table-wrap"><table class="data-table"><thead><tr><th>Referensi</th><th>Customer</th><th>Unit/detail</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($items as $item)
@php($rental = $resource === 'returns' || $resource === 'fines' ? $item->rental : $item)
<tr><td>#PV-{{ str_pad($rental->id, 6, '0', STR_PAD_LEFT) }}</td><td>{{ $rental->user->name }}</td><td>@if($resource === 'returns' || $resource === 'fines'){{ $item->unit->unit_code }} · {{ $item->unit->name }}@else{{ $rental->items->count() }} unit, Rp{{ number_format($rental->rental_total, 0, ',', '.') }}@endif</td><td>{{ $resource === 'returns' || $resource === 'fines' ? ($item->fine_payment_status === 'UNPAID' ? 'Denda belum lunas' : 'Aktif') : ($rental->payment?->status ?? $rental->status) }}</td><td>
@if($resource === 'payments' && $rental->payment?->status === 'REFUND_PENDING')
    <form method="post" action="{{ route('admin.payments.refund', $rental) }}">@csrf<input class="field" name="refund_notes" placeholder="Catatan refund"><button class="btn small" type="submit">Catat refund</button></form>
@elseif($resource === 'payments')
    <form method="post" action="{{ route('admin.payments.confirm', $rental) }}">@csrf<button class="btn small" type="submit">Konfirmasi pembayaran</button></form>
@elseif($resource === 'handover')
    <form method="post" action="{{ route('admin.handover.confirm', $rental) }}">@csrf<button class="btn small" type="submit">Konfirmasi serah terima</button></form>
@elseif($resource === 'returns')
    <form method="post" action="{{ route('admin.returns.confirm', $item) }}">@csrf<select class="field" name="condition"><option>GOOD</option><option>MAINTENANCE</option><option>DAMAGED</option></select><button class="btn small" type="submit">Konfirmasi pengembalian</button></form>
@else
    <form method="post" action="{{ route('admin.fines.pay', $item) }}">@csrf<button class="btn small" type="submit">Tandai sudah lunas</button></form>
@endif
</td></tr>
@empty<tr><td colspan="5" class="empty">Tidak ada data yang memerlukan tindakan.</td></tr>@endforelse
</tbody></table></div></section>
@endsection
