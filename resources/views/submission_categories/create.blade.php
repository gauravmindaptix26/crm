@extends('layouts.admin') {{-- assuming you have an admin layout --}}

@section('content')
<div class="container mx-auto p-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-4">Add New Submission Category</h2>

        {{-- Show validation errors --}}
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul class="list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('submission_categories.store') }}" method="POST">
            @csrf

            {{-- Name --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                <input type="text" name="name" id="name"
                    value="{{ old('name') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="e.g. Classifieds Submission Sites" required>
            </div>

            {{-- Slug --}}
            <div class="mb-4">
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug (URL)</label>
                <input type="text" name="slug" id="slug"
                    value="{{ old('slug') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="classifieds-submission-sites" required>
                <p class="text-xs text-gray-500">This will be used in the URL (example: /website-submission/classifieds-submission-sites)</p>
            </div>

            {{-- Description --}}
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Write a short description about this submission category">{{ old('description') }}</textarea>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <a href="{{ route('submission_categories.index') }}" 
                   class="px-4 py-2 mr-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">Cancel</a>

                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Save Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
