@extends('layouts.dashboard')

@section('content')
<div class="max-w-3xl mx-auto bg-white shadow-md rounded-xl p-6 mt-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
        Upload Attachments for: <span class="text-blue-600">{{ $project->name_or_url }}</span>
    </h2>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('projects.attachments.store', $project->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Attachments</label>
            <input 
                type="file" 
                name="attachments[]" 
                multiple 
                required 
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-400"
            >
            <p class="text-xs text-gray-500 mt-1">Upload multiple files (Max 10MB each).</p>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('projects.index') }}" class="mr-4 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
        </div>
    </form>
</div>
@endsection
