<div class="px-3 py-3">

    @if($orders->isNotEmpty())

        <div class="text-muted small fw-medium mb-2">Riwayat Order</div>

        @foreach($orders as $order)
            <div class="app-card mb-3">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div style="font-size:24px;opacity:{{ $order->status === 'completed' ? '1' : '0.5' }};">
                            {{ match($order->service_type) {
                                'antar'  => '🛵',
                                'makan'  => '🍱',
                                'custom' => '📦',
                                default  => '🛵'
                            } }}
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $order->order_number }}</div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ $order->updated_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>
                    <span class="badge rounded-pill
                        {{ $order->status === 'completed' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}"
                        style="font-size:10px;">
                        {{ $order->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                    </span>
                </div>

                {{-- Deskripsi order --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-truncate" style="max-width:65%;">
                        @if($order->service_type === 'makan' && $order->makanDetails->isNotEmpty())
                            {{ $order->makanDetails->pluck('tenant.store_name')->join(', ') }}
                        @elseif($order->service_type === 'custom' && $order->customDetail)
                            {{ Str::limit($order->customDetail->item_description, 35) }}
                        @else
                            {{ $order->location->destination_address ?? '-' }}
                        @endif
                    </span>
                    <span class="fw-semibold small {{ $order->status === 'completed' ? 'text-dark' : 'text-muted' }}">
                        Rp {{ number_format($order->driver_earnings, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Detail bawah --}}
                <div class="d-flex justify-content-between align-items-center pt-2"
                    style="border-top:1px solid #f0f0f0;">
                    <span class="text-muted" style="font-size:11px;">
                        👤 {{ $order->customer->name }}
                    </span>
                    <span class="text-muted" style="font-size:11px;">
                        {{ match($order->service_type) {
                            'antar'  => 'Tolong Antar',
                            'makan'  => 'Tolong Makan',
                            'custom' => 'Tolong Custom',
                            default  => '-'
                        } }}
                    </span>
                </div>

            </div>
        @endforeach

        <div class="px-1">
            {{ $orders->links() }}
        </div>

    @else

        <div class="text-center py-5">
            <div style="font-size:48px;">📭</div>
            <p class="fw-semibold mt-3 mb-1">Belum Ada Riwayat</p>
            <p class="text-muted small">Order yang sudah selesai akan muncul di sini.</p>
            <a href="{{ route('driver.dashboard') }}" class="btn btn-danger rounded-3 px-4 mt-2">
                Kembali ke Dashboard
            </a>
        </div>

    @endif

</div>