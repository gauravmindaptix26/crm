@extends('layouts.frontend')

@section('title', 'Website Submission Categories')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10">

    <!-- Page Header -->
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-blue-700 mb-3">
            Website Submission Categories
        </h1>
        <p class="text-gray-600 text-lg">
            Explore our curated list of submission sites or search for websites (one per line, e.g., canva.com\ndiigo.com\npadlet.com) to boost your visibility.
        </p>
    </div>

    <!-- Search -->
    <div class="mb-8 flex justify-center">
        <form method="GET" action="{{ route('submissions.index') }}" 
              class="flex flex-col md:flex-row gap-3 w-full md:w-3/4 lg:w-2/3">
            <textarea name="search" 
                      rows="3" 
                      placeholder="🔍 Enter multiple websites (one per line):
e.g., 
canva.com
diigo.com
padlet.com" 
                      class="w-full h-32 border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-primarydark focus:border-transparent resize-none text-lg">{{ $search ?? '' }}</textarea>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow">
                    Search
                </button>
                <a href="{{ route('submissions.index') }}" 
                   class="px-6 py-4 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    @if($search)
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-blue-700 mb-4">
                Search Results for your query
            </h2>
            @if($groupedSites->isNotEmpty())
                @foreach($groupedSites as $group)
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-blue-600 mb-3 border-b border-blue-200 pb-2">
                            {{ $group['category']->name }} ({{ $group['sites']->count() }} sites)
                        </h3>
                        <div class="bg-white border border-gray-200 rounded-lg shadow overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead class="bg-gray-100 text-gray-700 font-semibold">
                                    <tr>
                                        <th class="border px-4 py-3">Website</th>
                                        <th class="border px-4 py-3">Sub-Category</th>
                                        <th class="border px-4 py-3">Country</th>
                                        <th class="border px-4 py-3">Submission Type</th>
                                        <th class="border px-4 py-3">Moz DA</th>
                                        <th class="border px-4 py-3">Spam Score</th>
                                        <th class="border px-4 py-3">Traffic</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($group['sites'] as $site)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="border px-4 py-3 font-medium text-blue-600">
                                                @if($site->submissionCategory)
                                                    <a href="{{ route('submissions.show', $site->submissionCategory->slug) }}" 
                                                       class="hover:underline">
                                                        {{ $site->website_name }}
                                                    </a>
                                                @else
                                                    {{ $site->website_name }}
                                                @endif
                                            </td>
                                            <td class="border px-4 py-3 text-gray-600">
                                                {{ $site->category ?? 'N/A' }}
                                            </td>
                                            <td class="border px-4 py-3 text-gray-600">
                                                {{ $site->country ?? 'N/A' }}
                                            </td>
                                            <td class="border px-4 py-3 text-gray-600">
                                                {{ $site->submission_type ?? 'N/A' }}
                                            </td>
                                            <td class="border px-4 py-3 text-gray-600">
                                                {{ $site->moz_da ?? 'N/A' }}
                                            </td>
                                            <td class="border px-4 py-3 text-gray-600">
                                                {{ $site->spam_score ?? 'N/A' }}
                                            </td>
                                            <td class="border px-4 py-3 text-gray-600">
                                                {{ $site->traffic ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-center text-gray-600">No websites found for your search.</p>
            @endif
        </div>
    @else
        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($categories as $cat)
                <div class="bg-gradient-to-br from-white to-blue-50 border border-gray-200 rounded-2xl shadow hover:shadow-lg transition p-6">
                    <h2 class="text-2xl font-bold text-blue-700 mb-3">
                        <a href="{{ route('submissions.show', $cat->slug) }}" 
                           class="hover:text-blue-900 transition">
                            {{ $cat->name }}
                        </a>
                    </h2>
                    <p class="text-gray-600 mb-4 line-clamp-3">
                        {{ $cat->description }}
                    </p>
                    <p class="text-sm font-medium text-gray-500">
                        📌 {{ $cat->sites_count }} sites available
                    </p>
                </div>
            @empty
                <p class="col-span-3 text-center text-gray-600">No categories found.</p>
            @endforelse
        </div>
    @endif

</div>
@endsection