@extends('layouts.dashboard')

@section('title', $user->name . ' - User Profile')

@section('content')
<div class="p-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-8">👤 {{ $user->name }} - Profile</h2>

    {{-- Profile + User Notes + Placeholder Side by Side --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-10">
    {{-- Profile Card --}}
    <div class="col-span-1 h-full">
        <div class="bg-white rounded-xl shadow-md p-6 h-full flex flex-col">
            <img src="{{ $user->image ? asset('storage/' . $user->image) : asset('storage/images/default.png') }}" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover" alt="User Image">
            <h3 class="text-center text-xl font-semibold text-gray-700">{{ $user->name }}</h3>
            <p class="text-center text-gray-600">{{ $role }}</p>
            <div class="mt-4 text-sm text-gray-600 space-y-1">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Phone:</strong> {{ $user->phone_number }}</p>
                <p><strong>Department:</strong> {{ $user->department->name ?? '-' }}</p>
                <p><strong>Reporting Person:</strong> {{ $user->reportingPerson->name ?? '-' }}</p>
                <p><strong>Monthly Target:</strong> {{ $user->monthly_target }}</p>
                <p><strong>Upsell Incentive:</strong> {{ $user->upsell_incentive }}%</p>
                <p><strong>Disable Login:</strong> {{ $user->disable_login ? 'Yes' : 'No' }}</p>
                <p><strong>Employee Code:</strong> {{ $user->employee_code }}</p>
                <p><strong>Experience:</strong> {{ $user->experience ?? '-' }}</p>
                <p><strong>Qualification:</strong> {{ $user->qualification ?? '-' }}</p>
                <p><strong>Specialization:</strong> {{ $user->specialization ?? '-' }}</p>
                <p><strong>Date of Joining:</strong> {{ $user->date_of_joining ? \Carbon\Carbon::parse($user->date_of_joining)->format('d M, Y') : '-' }}</p>
            </div>
        </div>
    </div>

    {{-- User Notes --}}
    <div class="col-span-1 h-full">
        <div class="bg-white rounded-xl shadow-md p-6 h-full flex flex-col">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">📝 User Notes</h3>

            <button onclick="document.getElementById('noteModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow mb-4">➕ Add Note</button>

            <div class="overflow-y-auto space-y-4 flex-1">
                @forelse($user->userNotes as $note)
                    <div class="bg-gray-50 p-4 rounded-xl shadow-sm border-l-4 border-blue-500">
                        <p class="text-sm text-gray-500 mb-1">Added by: {{ $note->addedBy->name ?? '-' }} | Date: {{ $note->created_at->format('Y-m-d H:i:s') }}</p>
                        <p><strong>Title:</strong> {{ $note->title }}</p>
                        <p><strong>Rating:</strong>
    @if($note->rating)
        @for($i = 1; $i <= 5; $i++)
            <i class="fa{{ $i <= $note->rating ? 's' : 'r' }} fa-star text-yellow-500"></i>
        @endfor
    @else
        -
    @endif
</p>
                        <p><strong>Note Type:</strong> {{ $note->note_type }}</p>
                        <p><strong>Description:</strong> {{ $note->description }}</p>
                    </div>
                @empty
                    <p class="text-gray-600">No notes added yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-span-1 h-full">
    <div class="bg-white rounded-xl shadow-md p-6 h-full flex flex-col">
    <h3 class="text-2xl font-semibold text-gray-800 mb-4">📝 HR Notes</h3>

        <button onclick="document.getElementById('hrNoteModal').classList.remove('hidden')" 
            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow mb-4 text-left text-xl font-bold">
            ➕ Add HR Note
        </button>

        <div class="overflow-y-auto space-y-4 flex-1">
            @forelse($user->hrNotes as $note)
                <div class="bg-gray-50 p-4 rounded-xl shadow-sm border-l-4 border-green-500">
                    <p class="text-sm text-gray-500 mb-1">
                        Added by: {{ $note->addedBy->name ?? '-' }} | 
                        Date: {{ $note->created_at->format('Y-m-d H:i:s') }}
                    </p>
                    <p><strong>Title:</strong> {{ $note->title }}</p>
                    <p><strong>Note Type:</strong> {{ $note->note_type }}</p>
                    <p><strong>Rating:</strong> 
    @if($note->rating)
        @for($i = 1; $i <= 5; $i++)
            <span class="{{ $i <= $note->rating ? 'text-yellow-400' : 'text-gray-300' }}">&#9733;</span>
        @endfor
    @else
        -
    @endif
</p>

                    <p><strong>Description:</strong> {{ $note->description }}</p>
                </div>
            @empty
                <p class="text-gray-600">No HR notes added yet.</p>
            @endforelse
        </div>
    </div>
</div>


    

    <!-- Add Note Modal -->
<div id="noteModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg relative">
        <button onclick="document.getElementById('noteModal').classList.add('hidden')" class="absolute top-2 right-3 text-gray-500 hover:text-red-500">✖</button>
        <h3 class="text-xl font-bold text-gray-700 mb-4">➕ Add Note</h3>

        <form method="POST" action="{{ route('user-notes.store') }}">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" class="w-full border-gray-300 rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Note Type</label>
                <select name="note_type" class="w-full border-gray-300 rounded p-2" required>
                <option value="" disabled selected>Please select</option>

                    <option value="Learning Attitude">Learning Attitude</option>
                    <option value="Technical Skills">Technical Skills</option>
                    <option value="Team Behaviour">Team Behaviour</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Rating (1–10)</label>
                <select name="rating" class="w-full border-gray-300 rounded p-2">
                <option value="" disabled selected>Please select</option>

                    @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="4" class="w-full border-gray-300 rounded p-2"></textarea>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Save Note</button>
            </div>
        </form>
    </div>
</div>

{{-- HR Note Modal --}}
<div id="hrNoteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
        <button onclick="document.getElementById('hrNoteModal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
        <h3 class="text-2xl font-semibold mb-4 text-gray-800">Add HR Note</h3>
        <form action="{{ route('hr-notes.store') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block mb-1 font-medium">Title</label>
                    <input type="text" name="title" class="w-full border rounded px-3 py-2" required>
                </div>

                <div>
                    <label class="block mb-1 font-medium">Note Type</label>
                    <select name="note_type" class="w-full border rounded px-3 py-2" required>
                        <option value="">Select</option>
                        <option value="Timing">Timing</option>
                        <option value="Behaviour">Behaviour</option>
                        <option value="Appreciation">Appreciation</option>
                        <option value="No of Fine">No of Fine</option>
                       
                       
                        
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-1 font-medium">Rating</label>
                    <select name="rating" class="w-full border rounded px-3 py-2">
                        <option value="">Select</option>
                        @for ($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">{{ $i }} {{ $i > 1 ? '' : '' }}</option>
                        @endfor
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-1 font-medium">Description</label>
                    <textarea name="description" rows="3" class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">Save Note</button>
            </div>
        </form>
    </div>
</div>
            </div>
            <div class='hrnote'>
    @if ($role === 'Employee')
    <div class="mb-10">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">📊 Project Stats</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            @php
                $cards = [
                    ['title' => 'All Projects', 'count' => $stats['all'], 'gradient' => 'bg-gradient-to-r from-blue-500 to-indigo-600', 'icon' => '📁'],
                    ['title' => 'Working', 'count' => $stats['working'], 'gradient' => 'bg-gradient-to-r from-yellow-400 to-yellow-600', 'icon' => '🛠️'],
                    ['title' => 'Completed', 'count' => $stats['complete'], 'gradient' => 'bg-gradient-to-r from-green-400 to-emerald-600', 'icon' => '✅'],
                    ['title' => 'Paused', 'count' => $stats['pause'], 'gradient' => 'bg-gradient-to-r from-purple-400 to-purple-700', 'icon' => '⏸️'],
                    ['title' => 'Issues', 'count' => $stats['issue'], 'gradient' => 'bg-gradient-to-r from-red-500 to-pink-600', 'icon' => '🚫'],
                    ['title' => 'Temp Hold', 'count' => $stats['temp_hold'], 'gradient' => 'bg-gradient-to-r from-gray-500 to-gray-700', 'icon' => '🕒'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="{{ $card['gradient'] }} text-white rounded-xl p-5 shadow-lg">
                    <div class="flex items-center space-x-3">
                        <div class="text-3xl">{{ $card['icon'] }}</div>
                        <div>
                            <div class="text-lg font-semibold">{{ $card['title'] }}</div>
                            <div class="text-2xl font-bold">{{ $card['count'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
{{-- 🎯 User Performance Summary --}}
<div class="mb-10">
    <h3 class="text-xl font-semibold text-gray-700 mb-4">🎯 User Performance Summary</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Average User Rating --}}
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl p-5 shadow-lg">
            <div class="text-3xl">⭐</div>
            <div class="mt-2">
                <div class="text-lg font-semibold">Average User Rating</div>
                <div class="text-2xl font-bold">{{ number_format($avgUserRating, 1) }}</div>
            </div>
        </div>

        {{-- Average HR Rating --}}
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl p-5 shadow-lg">
            <div class="text-3xl">👨‍💼</div>
            <div class="mt-2">
                <div class="text-lg font-semibold">Average HR Rating</div>
                <div class="text-2xl font-bold">{{ number_format($avgHrRating, 1) }}</div>
            </div>
        </div>

        {{-- Fine Count --}}
        <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl p-5 shadow-lg">
            <div class="text-3xl">⚠️</div>
            <div class="mt-2">
                <div class="text-lg font-semibold">Fine Count</div>
                <div class="text-2xl font-bold">{{ $fineCount }}</div>
            </div>
        </div>

        {{-- Appreciation Count --}}
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl p-5 shadow-lg">
            <div class="text-3xl">👏</div>
            <div class="mt-2">
                <div class="text-lg font-semibold">Appreciation Count</div>
                <div class="text-2xl font-bold">{{ $appreciationCount }}</div>
            </div>
        </div>

    </div>
</div>

    {{-- DSR Reports Section --}}
<div class="mt-10">
    <h3 class="text-2xl font-semibold text-gray-800 mb-4">📝 User DSR Reports</h3>
    
    @if($dsrs->isEmpty())
        <p class="text-gray-600">No DSR reports found for this user.</p>
    @else
        <div class="overflow-x-auto bg-white rounded-xl shadow-md">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                    <tr>
                        <th class="px-6 py-3">Project Name</th>
                        <th class="px-6 py-3">Work Details</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Hours</th>
                        <th class="px-6 py-3">Someone Helped?</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($dsrs as $dsr)
                        <tr>
                            <td class="px-6 py-4">{{ $dsr->project->name_or_url ?? 'N/A' }}</td>
                            <td class="px-6 py-4">{{ Str::limit($dsr->work_description, 60) }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($dsr->created_at)->format('d M Y') }}</td>
                            <td class="px-6 py-4">{{ $dsr->hours }}</td>
                            <td class="px-6 py-4">
                            {{ $dsr->helper ? $dsr->helper->name : 'No' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>


    <a href="{{ route('users.index') }}" class="inline-block bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded shadow">← Back to Users</a>
</div>
@endsection
