@extends('layouts.dashboard')

@section('content')
<div class="container">
    <h2 class="mb-4">My Previous DSRs</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" style="table-layout: fixed; width: 100%;">
            <thead class="text-center">
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 55%;">Work Details</th>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 10%;">Hours</th>
                    <th style="width: 15%;">Someone Helped</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dsrs as $index => $dsr)
                    <tr>
                        <td class="align-top text-center">{{ $dsrs->firstItem() + $index }}</td>
                        <td class="align-top" style="white-space: pre-line; overflow-wrap: break-word; word-break: break-word;">
                            {{ trim($dsr->work_description) }}
                        </td>
                        <td class="align-top text-center">{{ \Carbon\Carbon::parse($dsr->created_at)->format('d M Y') }}</td>
                        <td class="align-top text-center">{{ $dsr->hours }}</td>
                        <td class="align-top text-center">{{ $dsr->helper->name ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No DSRs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $dsrs->links() }}
    </div>
</div>
@endsection
