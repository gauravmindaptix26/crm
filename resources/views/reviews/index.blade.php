@extends('layouts.dashboard')

@section('title', 'Team Reviews')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Team Reviews — Month: {{ \Carbon\Carbon::parse($month)->format('F Y') }}</h1>

    <form method="GET" class="mb-4">
        <label for="month">Select month:</label>
        <input type="month" id="month" name="month" value="{{ $month }}" onchange="this.form.submit()" class="border p-1 rounded"/>
    </form>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border rounded-lg shadow-sm">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="p-2">Employee</th>
                    <th class="p-2">Email</th>
                    <th class="p-2">Quality of Work</th>
                    <th class="p-2">Communication</th>
                    <th class="p-2">Ownership</th>
                    <th class="p-2">Team Collaboration</th>
                    <th class="p-2">Overall Rating</th>
                    <th class="p-2">Comments</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($team as $emp)
                    @php $r = $reviews[$emp->id] ?? null; @endphp
                    <tr class="border-b">
                        <td class="p-2 font-medium">{{ $emp->name }}</td>
                        <td class="p-2 text-sm text-gray-600">{{ $emp->email }}</td>
                        <td class="p-2 text-center">{{ $r->quality_of_work ?? '-' }}</td>
                        <td class="p-2 text-center">{{ $r->communication ?? '-' }}</td>
                        <td class="p-2 text-center">{{ $r->ownership ?? '-' }}</td>
                        <td class="p-2 text-center">{{ $r->team_collaboration ?? '-' }}</td>
                        <td class="p-2 text-center font-semibold text-blue-600">{{ $r->overall_rating ?? '-' }}</td>
                        <td class="p-2">{{ $r->comments ?? '-' }}</td>
                        <td class="p-2">
                            <button 
                                class="bg-indigo-600 text-white px-3 py-1 rounded text-sm edit-btn" 
                                onclick="document.getElementById('form-{{ $emp->id }}').classList.toggle('hidden')">
                                {{ $r ? 'Edit' : 'Add' }}
                            </button>
                        </td>
                    </tr>

                    <!-- Hidden edit/add form -->
                    <tr id="form-{{ $emp->id }}" class="hidden bg-gray-50">
                        <td colspan="9" class="p-4">
                            <form method="POST" action="{{ route('pm.reviews.store') }}">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                <input type="hidden" name="review_month" value="{{ $month }}">

                                <div class="grid grid-cols-4 gap-3 mb-3">
                                    @php
                                        $q = $r->quality_of_work ?? '';
                                        $c = $r->communication ?? '';
                                        $o = $r->ownership ?? '';
                                        $t = $r->team_collaboration ?? '';
                                    @endphp

                                    <div>
                                        <label class="block text-sm font-medium">Quality of Work</label>
                                        <select name="quality_of_work" required class="w-full border p-1 rounded">
                                            @for($i=1;$i<=10;$i++)
                                                <option value="{{ $i }}" {{ $q == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Communication</label>
                                        <select name="communication" required class="w-full border p-1 rounded">
                                            @for($i=1;$i<=10;$i++)
                                                <option value="{{ $i }}" {{ $c == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Ownership</label>
                                        <select name="ownership" required class="w-full border p-1 rounded">
                                            @for($i=1;$i<=10;$i++)
                                                <option value="{{ $i }}" {{ $o == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Team Collaboration</label>
                                        <select name="team_collaboration" required class="w-full border p-1 rounded">
                                            @for($i=1;$i<=10;$i++)
                                                <option value="{{ $i }}" {{ $t == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="block text-sm font-medium">Comments (optional)</label>
                                    <textarea name="comments" rows="2" class="w-full border p-1 rounded">{{ $r->comments ?? '' }}</textarea>
                                </div>

                                <div>
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
                                        Save Review
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-gray-500">No active employees found in your team.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
