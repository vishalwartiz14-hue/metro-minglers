<x-app-layout>
    <div class="moderation-page">
        <header><p>MEMBER SAFETY</p><h1>Member reports</h1><span>Review reports submitted from public member profiles.</span></header>
        @if (session('status'))<div class="member-toast">{{ session('status') }}</div>@endif
        @forelse ($reports as $report)
            <article class="moderation-report">
                <div class="moderation-report__meta"><span class="moderation-status moderation-status--{{ $report->status }}">{{ ucfirst($report->status) }}</span><time>{{ $report->created_at->format('M j, Y · g:i A') }}</time></div>
                <h2>{{ ucfirst($report->reason) }}</h2>
                <p><b>Reported member:</b> {{ $report->reported?->name ?: 'Deleted member' }} @if($report->reported)<small>{{ $report->reported->email }}</small>@endif</p>
                <p><b>Submitted by:</b> {{ $report->reporter?->name ?: 'Deleted member' }} @if($report->reporter)<small>{{ $report->reporter->email }}</small>@endif</p>
                @if($report->details)<blockquote>{{ $report->details }}</blockquote>@endif
                <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                    @csrf @method('PATCH')
                    <label>Status<select name="status"><option value="open" @selected($report->status === 'open')>Open</option><option value="reviewed" @selected($report->status === 'reviewed')>Reviewed</option><option value="closed" @selected($report->status === 'closed')>Closed</option></select></label>
                    <button type="submit">Save status</button>
                </form>
            </article>
        @empty
            <div class="blocked-members-empty">No member reports have been submitted.</div>
        @endforelse
        <div class="moderation-pagination">{{ $reports->links() }}</div>
    </div>
</x-app-layout>
