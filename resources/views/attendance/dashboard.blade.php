@extends('layouts.dashboard')
@section('content')

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('leaves.create') }}" 
               class="font-semibold text-white bg-gradient-to-r from-teal-400 via-teal-500 to-teal-600 hover:bg-gradient-to-br focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg px-5 py-2.5 text-center transition duration-200">
                Request Leave
            </a>
        </div>
<div class="bg-gray-50 min-h-screen pt-24 pb-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 mb-10">Leave Balances</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            @foreach($balances as $type => $data)
                <div class="bg-white rounded-2xl shadow-lg p-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6">{{ $type }}</h3>
                    <div class="relative flex justify-center items-center mb-6">
                        <canvas id="chart-{{ str_replace(' ', '-', $type) }}" class="w-56 h-56"></canvas>
                        <div class="absolute text-center">
                            <span class="text-5xl font-extrabold text-gray-900">
                                {{ $data['available'] === '∞' ? '∞' : number_format($data['available'], 1) }}
                            </span>
                            <span class="block text-sm font-medium text-gray-500 mt-1">Days Available</span>
                        </div>
                    </div>
                    <div class="space-y-4 text-gray-600">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">Available</span>
                            <span class="text-sm font-semibold">{{ $data['available'] === '∞' ? 'Unlimited' : number_format($data['available'], 1) . ' days' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">Consumed</span>
                            <span class="text-sm font-semibold">{{ $data['consumed'] }} days</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">Accrued So Far</span>
                            <span class="text-sm font-semibold">{{ $data['accrued'] === '∞' ? 'Unlimited' : number_format($data['accrued'], 1) . ' days' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium">Annual Quota</span>
                            <span class="text-sm font-semibold">{{ $data['quota'] === '∞' ? 'Unlimited' : ($data['quota'] * 4) . ' days' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('leaves.history') }}" class="mt-6 block text-teal-600 text-sm font-semibold hover:text-teal-700 transition-colors">View Details</a>
                </div>
            @endforeach
        </div>

        <h2 class="text-3xl font-bold text-gray-900 mb-10">Leave History</h2>

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-teal-50 text-gray-700 font-semibold text-sm">
                        <tr>
                            <th class="px-6 py-4">Leave Type</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Leave Date</th>
                            <th class="px-6 py-4">Approved By</th>
                            <th class="px-6 py-4">Requested By</th>
                            <th class="px-6 py-4">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                            <tr class="border-t border-gray-100 hover:bg-gray-50 transition duration-200">
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $request->leavePolicy->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($request->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $request->date_range }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $request->approver->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $request->user->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $request->note ?? 'No note' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 text-sm">
                                    No leave requests found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            {{ $requests->links() }}
        </div>
    </div>
</div>

@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($balances as $type => $data)
                new Chart(document.getElementById('chart-{{ str_replace(' ', '-', $type) }}'), {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [
                                {{ $data['available'] === '∞' ? 100 : $data['available'] }},
                                {{ $data['consumed'] }}
                            ],
                            backgroundColor: [
                                '{{ $type === 'Medical Leave' ? '#22C55E' : ($type === 'Paid Leave' ? '#3B82F6' : '#F59E0B') }}',
                                '#E5E7EB'
                            ],
                            borderWidth: 0,
                            borderRadius: 10
                        }]
                    },
                    options: {
                        cutout: '85%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        responsive: true,
                        maintainAspectRatio: true
                    }
                });
            @endforeach
        });
    </script>
