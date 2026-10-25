@extends('layouts.customer')
@section('content')
<main class="page catalog-page">
    <div class="page-head"><div><div class="eyebrow">Koleksi vault</div><h1>Jelajahi Artefak</h1><p>Temukan wadah yang tepat untuk petualangan luar biasa Anda. Menampilkan {{ $units->total() }} unit inventaris.</p></div></div>
    <form class="filters ornament catalog-filters" method="get">
        <input class="field search" name="search" value="{{ request('search') }}" placeholder="Cari ramuan, artefak, atau kata kunci...">
        <select class="field" name="rarity"><option value="">Semua rarity</option>@foreach(['Common','Rare','Epic','Legendary'] as $rarity)<option @selected(request('rarity') === $rarity)>{{ $rarity }}</option>@endforeach</select>
        <select class="field" name="category_id"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
        <button class="btn small" type="submit">Cari</button>
    </form>
    <div class="grid artifact-grid">
        @forelse($units as $unit)
        <article class="artifact-card ornament">
            <div class="artifact-art {{ $unit->image ? 'has-image' : '' }}">@if($unit->image)<img src="{{ Storage::disk('public')->url($unit->image) }}" alt="{{ $unit->name }}">@else<span aria-hidden="true">✦</span>@endif</div>
            <div class="card-body">
                <div class="card-top"><h3>{{ $unit->name }}</h3><span class="badge {{ strtolower($unit->rarity) }}">{{ $unit->rarity }}</span></div>
                <div class="muted unit-code">{{ $unit->unit_code }}</div>
                <div class="price">Rp {{ number_format($unit->rental_price, 0, ',', '.') }} <small>/ 5 days</small></div>
                <div class="status">{{ $unit->status }}</div>
                <div class="card-actions"><a class="btn small" href="{{ route('catalog.show', $unit) }}">Lihat Detail</a>@auth @if($unit->status === 'AVAILABLE')<a class="btn small ghost" href="{{ route('rentals.create', ['unit_id' => $unit->id]) }}">Sewa</a>@else<span class="btn small subtle" aria-label="Unit tidak tersedia">Tidak tersedia</span>@endif @endauth</div>
            </div>
        </article>
        @empty<p class="empty">Tidak ada artefak yang sesuai dengan filter.</p>@endforelse
    </div>
    <div class="pagination-wrap">{{ $units->links() }}</div>
</main>
@endsection
