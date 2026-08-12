@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">All CSV uploads and their Shopify import progress.</p>

    <div class="card" style="padding: 0;">
        @if ($uploads->isEmpty())
            <div class="empty-state">
                No uploads yet. <a href="{{ route('uploads.create') }}">Upload your first CSV</a>.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Success</th>
                        <th>Failed</th>
                        <th>Uploaded</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($uploads as $upload)
                        <tr>
                            <td>{{ $upload->original_filename }}</td>
                            <td><x-status-badge :status="$upload->status" :color="$upload->statusBadgeColor()" /></td>
                            <td style="min-width:120px;">
                                <div class="progress-bar">
                                    <div class="progress-bar__fill" style="width: {{ $upload->progressPercentage() }}%"></div>
                                </div>
                                <span class="small text-muted">{{ $upload->processed_rows }}/{{ $upload->total_rows }}</span>
                            </td>
                            <td>{{ $upload->successful_rows }}</td>
                            <td>{{ $upload->failed_rows }}</td>
                            <td class="small text-muted">{{ $upload->created_at->diffForHumans() }}</td>
                            <td><a href="{{ route('dashboard.show', $upload) }}">View &rarr;</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="pagination">
        {{ $uploads->links() }}
    </div>
@endsection
