@extends('layouts.dashboard')

@section('content')

@if(session('success'))
    <div class="w-full max-w-6xl mx-auto mt-4">
        
        <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded relative" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-green-500" role="button" onclick="this.parentElement.parentElement.remove();" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 5.652a1 1 0 00-1.414-1.414L10 7.172 7.066 4.238a1 1 0 10-1.414 1.414L8.828 10l-3.176 3.176a1 1 0 101.414 1.414L10 12.828l2.934 2.934a1 1 0 001.414-1.414L11.172 10l3.176-3.176z"/>
                </svg>
            </span>
        </div>
    </div>
@endif
 <!-- Back to Projects Button -->
 <div class="mb-3">
            <a href="{{ route('projects.index') }}" class="inline-block bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 text-sm">
                ← Back to Projects
            </a>
        </div>
<div class="w-full max-w-6xl max-h-[90vh] overflow-y-auto mx-auto bg-white p-8 shadow-lg rounded-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Update Project Status</h2>

    <form action="{{ route('projects.status.update', $project->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Project Status --}}
        <div>
            <label class="block text-gray-700 font-semibold mb-1">Project Status</label>
            <select name="project_status" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400">
            <option value="Complete" {{ trim(strtolower($project->project_status)) == 'complete' ? 'selected' : '' }}>Complete</option>
            <option value="Hold" {{ trim(strtolower($project->project_status)) == 'hold' ? 'selected' : '' }}>Hold</option>
            <option value="Paused" {{ trim(strtolower($project->project_status)) == 'paused' ? 'selected' : '' }}>Paused</option>
            <option value="Working" {{ trim(strtolower($project->project_status)) == 'working' ? 'selected' : '' }}>Working</option>
           <option value="Issues" {{ trim(strtolower($project->project_status)) == 'issues' ? 'selected' : '' }}>Issues</option>
           <option value="Temp Hold" {{ trim(strtolower($project->project_status)) == 'temp hold' ? 'selected' : '' }}>Temp Hold</option>
            <!-- <option value="Closed" {{ trim(strtolower($project->project_status)) == 'closed' ? 'selected' : '' }}>Closed</option> -->


</select>

        </div>

        {{-- Status Date --}}
        <div>
            <label class="block text-gray-700 font-semibold mb-1">Select Date</label>
            <input type="date" required name="status_date" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400" 
       value="{{ $project->status_date ? \Carbon\Carbon::parse($project->status_date)->format('Y-m-d') : '' }}">

        </div>

        {{-- Reason Description --}}
        <div>
            <label class="block text-gray-700 font-semibold mb-1">Reason Description</label>
            <textarea name="reason_description" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400" rows="4">{{ $project->reason_description }}</textarea>
        </div>

        {{-- Can Client Rehire --}}
        <div>
            <label class="block text-gray-700 font-semibold mb-1">Can Client Rehire?</label>
            <select name="can_client_rehire" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400">
                <option value="">Select Status</option>
                <option value="Yes" {{ $project->can_client_rehire == 'Yes' ? 'selected' : '' }}>Yes</option>
                <option value="No" {{ $project->can_client_rehire == 'No' ? 'selected' : '' }}>No</option>
            </select>
        </div>

        {{-- Rehire Date --}}
        <div>
            <label class="block text-gray-700 font-semibold mb-1">Rehire Date</label>
           
            <input type="date" name="rehire_date" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400" 
       value="{{ $project->rehire_date ? \Carbon\Carbon::parse($project->rehire_date)->format('Y-m-d') : '' }}">

        </div>

        {{-- Submit Button --}}
        <div class="text-right">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 transition text-white font-semibold px-6 py-3 rounded-lg shadow-sm">
                💾 Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
