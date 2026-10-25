@extends('layouts.customer')
@section('content')
<main class="page">
    <div class="page-head">
        <div><div class="eyebrow">Rental transaction</div><h1>#PV-{{ str_pad($rental->id, 6, '0', STR_PAD_LEFT) }}</h1><p>Rental request detail and unit-level return statuses.</p></div>
        <span class="badge legendary">{{ $rental->status }}</span>
    </div>
    @if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
    <div class="two-col">
        <section class="panel ornament">
            <h2>Selected Artifacts</h2>
            @foreach($rental->items as $item)
                @php($itemStatus = match (true) {
                    in_array($rental->status, ['CANCELLED', 'REJECTED', 'EXPIRED'], true) => $rental->status,
                    $item->returned_at !== null => 'RETURNED',
                    $item->due_at?->isPast() => 'OVERDUE',
                    default => 'ACTIVE',
                })
                <article class="rental-item rental-item-expanded">
                    <div class="mini-art">✦</div>
                    <div class="rental-item-content"><h3 class="serif">{{ $item->unit->name }}</h3><span class="muted">{{ $item->unit->unit_code }} · {{ $item->unit->rarity }}</span><div class="rental-item-details"><span><small>Status unit</small><b>{{ $itemStatus }}</b></span><span><small>Handover</small><b>{{ $item->borrowed_at?->format('d/m/Y H:i') ?? '—' }}</b></span><span><small>Jatuh tempo</small><b>{{ $item->due_at?->format('d/m/Y H:i') ?? '—' }}</b></span><span><small>Dikembalikan</small><b>{{ $item->returned_at?->format('d/m/Y H:i') ?? '—' }}</b></span><span><small>Kondisi kembali</small><b>{{ $item->return_condition ?? '—' }}</b></span><span><small>Denda</small><b>{{ (float) $item->fine_amount > 0 ? 'Rp '.number_format($item->fine_amount, 0, ',', '.') : 'Tidak ada denda' }}</b></span></div></div>
                    <span class="badge {{ strtolower($itemStatus) }}">{{ $itemStatus }}</span>
                </article>
            @endforeach
        </section>
        <aside class="panel ornament">
            <h2>Transaction Details</h2>
            <div class="summary-row"><span>Waktu pengajuan</span><b>{{ $rental->created_at?->format('d/m/Y H:i') ?? '—' }}</b></div>
            <div class="summary-row"><span>Approval</span><b>{{ $rental->approved_at?->format('d/m/Y H:i') ?? '—' }}</b></div>
            <div class="summary-row"><span>Batas pengambilan</span><b>{{ $rental->expires_at?->format('d/m/Y H:i') ?? '—' }}</b></div>
            <div class="summary-row"><span>Status rental</span><b>{{ $rental->status }}</b></div>
            <div class="summary-row"><span>Status pembayaran</span><b>{{ optional($rental->payment)->status ?? 'UNPAID' }}</b></div>
            <div class="summary-row total"><span>Total</span><span>Rp {{ number_format($rental->rental_total, 0, ',', '.') }}</span></div>
            @if($rental->status === 'PENDING')<details class="inline-confirm"><summary class="btn danger">Cancel Rental Request</summary><div class="inline-confirm-box"><span>Cancel this rental request?</span><form method="post" action="{{ route('rentals.cancel', $rental) }}">@csrf<button class="btn small danger" type="submit">Yes, cancel</button></form><button class="btn small subtle" type="button" onclick="this.closest('details').open=false">No</button></div></details>@endif
        </aside>
    </div>
</main>
@endsection
