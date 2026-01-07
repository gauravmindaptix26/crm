{{-- resources/views/admin/dsr/show.blade.php --}}
@extends('layouts.dashboard')

@section('title', $pm->name . ' - DSR Reports')

@section('content')
<div class="container mx-auto py-8 px-6 max-w-7xl">
    <!-- Back Button -->
    <div class="mb-8">
        <a href="{{ route('admin.dsr.reports') }}" class="inline-flex items-center text-[#0d9488] hover:text-[#0b7a70] font-semibold text-lg transition">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to All Project Managers
        </a>
    </div>

    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-5xl font-extrabold text-gray-800">{{ $pm->name }}'s DSR Reports</h1>
        <p class="text-xl text-gray-600 mt-4">Daily • Weekly • Monthly Performance</p>
    </div>

    <!-- DATE FILTER -->
    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 rounded-2xl p-8 mb-12 shadow-lg border border-teal-100">
        <h3 class="text-2xl font-bold mb-6 text-gray-800">Filter Reports</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Year</label>
                <select name="year" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#0d9488]/30 focus:border-[#0d9488]">
                    <option value="">All Years</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Month</label>
                <select name="month" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#0d9488]/30 focus:border-[#0d9488]">
                    <option value="">All Months</option>
                    @php
                        $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                    @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="w-full bg-[#0d9488] text-white py-3 rounded-xl hover:bg-[#0b7a70] font-semibold shadow-md transition">
                    Apply Filter
                </button>
            </div>

            <div>
                @if($month || $year != now()->year)
                    <a href="{{ route('admin.dsr.show', $pm->id) }}" class="block text-center w-full bg-gray-600 text-white py-3 rounded-xl hover:bg-gray-700 font-semibold transition">
                        Clear Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabs -->
    <div class="border-b-4 border-gray-200 mb-10">
        <nav class="flex space-x-12">
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'daily']) }}"
               class="py-4 px-2 border-b-4 font-bold text-lg {{ $tab === 'daily' ? 'border-[#0d9488] text-[#0d9488]' : 'border-transparent text-gray-600 hover:text-[#0d9488]' }}">
                Daily ({{ $reports['daily']->total() }})
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'weekly']) }}"
               class="py-4 px-2 border-b-4 font-bold text-lg {{ $tab === 'weekly' ? 'border-[#0d9488] text-[#0d9488]' : 'border-transparent text-gray-600 hover:text-[#0d9488]' }}">
                Weekly ({{ $reports['weekly']->total() }})
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'monthly']) }}"
               class="py-4 px-2 border-b-4 font-bold text-lg {{ $tab === 'monthly' ? 'border-[#0d9488] text-[#0d9488]' : 'border-transparent text-gray-600 hover:text-[#0d9488]' }}">
                Monthly ({{ $reports['monthly']->total() }})
            </a>
        </nav>
    </div>

    <div class="space-y-16">

        <!-- DAILY REPORTS -->
        <div class="{{ $tab !== 'daily' ? 'hidden' : '' }}">
            <h2 class="text-3xl font-bold text-[#0d9488] mb-8">Daily Reports</h2>
            @if($reports['daily']->isNotEmpty())
                <div class="space-y-6">
                    @foreach($reports['daily'] as $report)
                        <div class="bg-white rounded-2xl shadow-lg p-8 flex justify-between items-center hover:shadow-xl transition border border-gray-200">
                            <!-- Report Date (Clickable) -->
                            <div class="flex-1">
                                <a href="{{ route('admin.dsr.view', $report->id) }}" class="block hover:text-[#0d9488] transition">
                                    <p class="text-2xl font-bold text-gray-800">
                                        {{ $report->report_date->format('d M Y') }} ({{ $report->report_date->format('l') }})
                                    </p>
                                </a>
                            </div>

                            <!-- PM Self Rating -->
                            <div class="text-center mx-8">
                                <p class="text-sm font-semibold text-gray-600 mb-1">PM DSR Rating</p>
                                <p class="text-5xl font-black text-[#0d9488]">{{ $report->rating }}/10</p>
                            </div>

                            <!-- COO Rating Button (1–10) -->
                            <div class="text-center mx-8">
                                <p class="text-sm font-semibold text-gray-600 mb-1">COO Rating</p>
                                @php
                                    $rating = $report->coo_rating;
                                    $buttonText = $rating ? "$rating/10" : 'Rate 1–10';
                                    $buttonClass = match(true) {
                                        $rating >= 9 => 'bg-green-600 text-white hover:bg-green-700',
                                        $rating >= 7 => 'bg-blue-600 text-white hover:bg-blue-700',
                                        $rating >= 5 => 'bg-yellow-600 text-white hover:bg-yellow-700',
                                        $rating > 0  => 'bg-red-600 text-white hover:bg-red-700',
                                        default      => 'bg-gray-600 text-white hover:bg-gray-700',
                                    };
                                @endphp

                                <button type="button"
                                    data-id="{{ $report->id }}"
                                    data-rating="{{ $rating ?? '' }}"
                                    data-notes="{{ $report->coo_notes ?? '' }}"
                                    class="open-coo-rating-modal px-12 py-4 text-lg font-bold rounded-xl transition shadow-lg {{ $buttonClass }}">
                                    {{ $buttonText }}
                                </button>
                            </div>

                            <!-- View Report Button -->
                            <div>
                                <a href="{{ route('admin.dsr.view', $report->id) }}"
                                   class="bg-[#0d9488] text-white px-10 py-4 rounded-xl hover:bg-[#0b7a70] font-bold transition shadow">
                                    View Report →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{ $reports['daily']->appends(request()->query())->links() }}
            @else
                <p class="text-center text-gray-500 py-20 text-2xl">No daily reports found.</p>
            @endif
        </div>

        <!-- WEEKLY REPORTS -->
        <div class="{{ $tab !== 'weekly' ? 'hidden' : '' }}">
            <h2 class="text-3xl font-bold text-[#0d9488] mb-8">Weekly Reports</h2>
            @if($reports['weekly']->isNotEmpty())
                <div class="space-y-6">
                    @foreach($reports['weekly'] as $report)
                        <div class="bg-white rounded-2xl shadow-lg p-8 flex justify-between items-center hover:shadow-xl transition border border-gray-200">
                            <div class="flex-1">
                                <a href="{{ route('admin.dsr.view', $report->id) }}" class="block hover:text-[#0d9488] transition">
                                    <p class="text-2xl font-bold text-gray-800">
                                        Week: {{ $report->report_date->startOfWeek()->format('d M') }} - {{ $report->report_date->endOfWeek()->format('d M Y') }}
                                    </p>
                                </a>
                            </div>

                            <div class="text-center mx-8">
                                <p class="text-sm font-semibold text-gray-600 mb-1">PM Self Rating</p>
                                <p class="text-5xl font-black text-[#0d9488]">{{ $report->rating }}/10</p>
                            </div>

                            <div class="text-center mx-8">
                                <p class="text-sm font-semibold text-gray-600 mb-1">COO Rating</p>
                                @php
                                    $rating = $report->coo_rating;
                                    $buttonText = $rating ? "$rating/10" : 'Rate 1–10';
                                    $buttonClass = match(true) {
                                        $rating >= 9 => 'bg-green-600 text-white hover:bg-green-700',
                                        $rating >= 7 => 'bg-blue-600 text-white hover:bg-blue-700',
                                        $rating >= 5 => 'bg-yellow-600 text-white hover:bg-yellow-700',
                                        $rating > 0  => 'bg-red-600 text-white hover:bg-red-700',
                                        default      => 'bg-gray-600 text-white hover:bg-gray-700',
                                    };
                                @endphp

                                <button type="button"
                                    data-id="{{ $report->id }}"
                                    data-rating="{{ $rating ?? '' }}"
                                    data-notes="{{ $report->coo_notes ?? '' }}"
                                    class="open-coo-rating-modal px-12 py-4 text-lg font-bold rounded-xl transition shadow-lg {{ $buttonClass }}">
                                    {{ $buttonText }}
                                </button>
                            </div>

                            <div>
                                <a href="{{ route('admin.dsr.view', $report->id) }}"
                                   class="bg-[#0d9488] text-white px-10 py-4 rounded-xl hover:bg-[#0b7a70] font-bold transition shadow">
                                    View Report →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{ $reports['weekly']->appends(request()->query())->links() }}
            @else
                <p class="text-center text-gray-500 py-20 text-2xl">No weekly reports found.</p>
            @endif
        </div>

        <!-- MONTHLY REPORTS -->
        <div class="{{ $tab !== 'monthly' ? 'hidden' : '' }}">
            <h2 class="text-3xl font-bold text-[#0d9488] mb-8">Monthly Reports</h2>
            @if($reports['monthly']->isNotEmpty())
                <div class="space-y-6">
                    @foreach($reports['monthly'] as $report)
                        <div class="bg-white rounded-2xl shadow-lg p-8 flex justify-between items-center hover:shadow-xl transition border border-gray-200">
                            <div class="flex-1">
                                <a href="{{ route('admin.dsr.view', $report->id) }}" class="block hover:text-[#0d9488] transition">
                                    <p class="text-3xl font-bold text-gray-800">
                                        {{ $report->report_date->format('F Y') }}
                                    </p>
                                </a>
                            </div>

                            <div class="text-center mx-8">
                                <p class="text-sm font-semibold text-gray-600 mb-1">PM Self Rating</p>
                                <p class="text-6xl font-black text-[#0d9488]">{{ $report->rating }}/10</p>
                            </div>

                            <div class="text-center mx-8">
                                <p class="text-sm font-semibold text-gray-600 mb-1">COO Rating</p>
                                @php
                                    $rating = $report->coo_rating;
                                    $buttonText = $rating ? "$rating/10" : 'Rate 1–10';
                                    $buttonClass = match(true) {
                                        $rating >= 9 => 'bg-green-600 text-white hover:bg-green-700',
                                        $rating >= 7 => 'bg-blue-600 text-white hover:bg-blue-700',
                                        $rating >= 5 => 'bg-yellow-600 text-white hover:bg-yellow-700',
                                        $rating > 0  => 'bg-red-600 text-white hover:bg-red-700',
                                        default      => 'bg-gray-600 text-white hover:bg-gray-700',
                                    };
                                @endphp

                                <button type="button"
                                    data-id="{{ $report->id }}"
                                    data-rating="{{ $rating ?? '' }}"
                                    data-notes="{{ $report->coo_notes ?? '' }}"
                                    class="open-coo-rating-modal px-12 py-4 text-lg font-bold rounded-xl transition shadow-lg {{ $buttonClass }}">
                                    {{ $buttonText }}
                                </button>
                            </div>

                            <div>
                                <a href="{{ route('admin.dsr.view', $report->id) }}"
                                   class="bg-[#0d9488] text-white px-10 py-4 rounded-xl hover:bg-[#0b7a70] font-bold transition shadow">
                                    View Report →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{ $reports['monthly']->appends(request()->query())->links() }}
            @else
                <p class="text-center text-gray-500 py-20 text-2xl">No monthly reports found.</p>
            @endif
        </div>

    </div>
</div>

<!-- COO Rating Modal (1–10) -->
<div id="coo-rating-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">COO Rating (1–10)</h3>
        <form id="coo-rating-form">
            @csrf
            <input type="hidden" name="report_id" id="report-id">

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Rating</label>
                <select name="coo_rating" id="coo-rating-select" required class="w-full px-4 py-3 border border-gray-300 rounded-xl text-lg focus:ring-4 focus:ring-[#0d9488]/30 focus:border-[#0d9488]">
                    <option value="">Select rating...</option>
                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }}/10</option>
                    @endfor
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Notes (Optional)</label>
                <textarea name="coo_notes" id="coo-notes" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#0d9488]/30 focus:border-[#0d9488]"></textarea>
            </div>

            <div class="flex justify-end gap-4">
                <button type="button" id="close-rating-modal" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-xl hover:bg-gray-400 font-medium">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 bg-[#0d9488] text-white rounded-xl hover:bg-[#0b7a70] font-medium shadow">
                    Save Rating
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Open COO Rating Modal
document.querySelectorAll('.open-coo-rating-modal').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const reportId = this.dataset.id;
        const rating = this.dataset.rating || '';
        const notes = this.dataset.notes || '';

        document.getElementById('report-id').value = reportId;
        document.getElementById('coo-rating-select').value = rating;
        document.getElementById('coo-notes').value = notes;

        document.getElementById('coo-rating-modal').classList.remove('hidden');
    });
});

// Close modal
document.getElementById('close-rating-modal').addEventListener('click', () => {
    document.getElementById('coo-rating-modal').classList.add('hidden');
});

// Submit Rating
document.getElementById('coo-rating-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const reportId = formData.get('report_id');

    const url = "{{ route('admin.dsr.update-coo-status', ':id') }}".replace(':id', reportId);

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const button = document.querySelector(`.open-coo-rating-modal[data-id="${reportId}"]`);
            button.textContent = data.rating + '/10';

            button.className = button.className.replace(/bg-(green|blue|yellow|red|gray)-600/g, '');
            button.classList.add(`bg-${data.color}-600`, 'text-white', `hover:bg-${data.color}-700`);

            document.getElementById('coo-rating-modal').classList.add('hidden');
            alert('COO Rating saved successfully!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving rating.');
    });
});
</script>
@endsection