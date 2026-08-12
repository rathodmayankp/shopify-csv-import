@extends('layouts.app')

@section('title', $upload->original_filename)

@section('content')
    <p><a href="{{ route('dashboard.index') }}">&larr; Back to dashboard</a></p>

    <h1 class="page-title">{{ $upload->original_filename }}</h1>
    <p class="page-subtitle">
        Uploaded {{ $upload->created_at->diffForHumans() }}
        <x-status-badge :status="$upload->status" :color="$upload->statusBadgeColor()" />
    </p>

    <div class="card">
        <div class="progress-bar">
            <div class="progress-bar__fill" style="width: {{ $upload->progressPercentage() }}%"></div>
        </div>

        <div class="stats-row">
            <div class="stat">
                <div class="stat__value">{{ $upload->total_rows }}</div>
                <div class="stat__label">Total rows</div>
            </div>
            <div class="stat">
                <div class="stat__value">{{ $upload->successful_rows }}</div>
                <div class="stat__label">Imported</div>
            </div>
            <div class="stat">
                <div class="stat__value">{{ $upload->failed_rows }}</div>
                <div class="stat__label">Failed</div>
            </div>
            <div class="stat">
                <div class="stat__value">{{ $upload->total_rows - $upload->processed_rows }}</div>
                <div class="stat__label">Remaining</div>
            </div>
        </div>

        @if ($upload->failure_reason)
            <div class="flash flash--error" style="margin-top:16px; margin-bottom:0;">
                {{ $upload->failure_reason }}
            </div>
        @endif
    </div>

    <div class="card" style="padding:0;">
        @if ($products->isEmpty())
            <div class="empty-state">No product rows to show yet.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>Title</th>
                        <th>SKU</th>
                        <th>Status</th>
                        <th>Shopify ID</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->row_number }}</td>
                            <td>{{ $product->title }}</td>
                            <td>{{ $product->sku ?: '—' }}</td>
                            <td><x-status-badge :status="$product->status" :color="$product->statusBadgeColor()" /></td>
                            <td class="small">{{ $product->shopify_product_id ?: '—' }}</td>
                            <td class="small text-muted">{{ $product->error_message ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="pagination">
        {{ $products->links() }}
    </div>

    @if ($errorLogs->isNotEmpty())
        <div class="card">
            <strong>Error log</strong>
            <div style="margin-top:12px;">
                @foreach ($errorLogs as $log)
                    <div class="error-log">
                        <div class="error-log__meta">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                            @if ($log->row_number) &middot; Row {{ $log->row_number }} @endif
                        </div>
                        {{ $log->message }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
