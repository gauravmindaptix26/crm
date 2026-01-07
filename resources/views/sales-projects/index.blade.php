@extends('layouts.dashboard')

@section('content')
<div class="max-w-xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-xl font-semibold mb-4">Add Attachments for Sales Project: <span class="font-bold">{{ $sales_project->name_or_url }}</span></h2>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sales-projects.attachments.store', $sales_project->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label class="block mb-2 font-medium">Select Files (Multiple allowed)</label>
        <input type="file" name="attachments[]" multiple class="border border-gray-300 p-2 rounded w-full mb-4" required>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Upload
        </button>

        <a href="{{ route('sales-projects.index') }}" class="ml-4 text-gray-600 hover:underline">
            Cancel
        </a>
    </form>
</div>
@endsection
