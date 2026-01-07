@extends('layouts.frontend')

@section('title', $category->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Page Header -->
    <div class="mb-10 text-center">
        <h1 class="text-4xl font-extrabold text-blue-700 mb-3">{{ $category->name }}</h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
            {{ $category->description ?? 'Explore our curated list of websites in this category to boost your visibility and SEO.' }}
        </p>
    </div>

    <!-- Sites Table -->
    <div class="mb-12 bg-white shadow-md rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-blue-50 text-blue-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Website</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Register Page</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Sub-Category</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Country</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Moz DA</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Traffic</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Spam Score</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Submission Type</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Report</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($category->sites as $site)
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-4 font-medium text-blue-600">
                                <a href="{{ $site->website_url ?? '#' }}" target="_blank" class="hover:underline">
                                    {{ $site->website_name }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                @if($site->register_url)
                                    <a href="{{ $site->register_url }}" target="_blank" class="text-green-600 font-semibold hover:underline">Submit</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $site->category ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $site->country ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $site->moz_da ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $site->traffic ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $site->spam_score ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $site->submission_type ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($site->report_url)
                                    <a href="{{ $site->report_url }}" target="_blank" class="text-red-600 font-semibold hover:underline">Report</a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                No sites found in this category.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-blue-700 mb-6 text-center">Frequently Asked Questions</h2>
        <div class="space-y-4 max-w-3xl mx-auto">
            <details class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <summary class="px-6 py-4 font-semibold text-gray-800 cursor-pointer hover:bg-gray-50">
                    What is an article submission website?
                </summary>
                <div class="px-6 py-4 text-gray-600 border-t border-gray-200">
                    An article submission website is a platform where authors can submit their work for publication. These sites increase traffic to users' websites and enable them to share their insights and gain SEO benefits through backlinks.
                </div>
            </details>
            <details class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <summary class="px-6 py-4 font-semibold text-gray-800 cursor-pointer hover:bg-gray-50">
                    What is an article submission in SEO?
                </summary>
                <div class="px-6 py-4 text-gray-600 border-t border-gray-200">
                    Article submission in SEO involves submitting articles to enhance online visibility and improve search engine rankings. By strategically using keywords, backlinks, and high-quality content, businesses can drive more traffic and build authority in their field.
                </div>
            </details>
            <details class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <summary class="px-6 py-4 font-semibold text-gray-800 cursor-pointer hover:bg-gray-50">
                    Which is the best article submission site?
                </summary>
                <div class="px-6 py-4 text-gray-600 border-t border-gray-200">
                    The best article submission sites depend on your niche and audience. Platforms like this one are popular due to their ease of use and large community engagement. Choose a site based on your content goals and target audience for maximum reach and impact.
                </div>
            </details>
            <details class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <summary class="px-6 py-4 font-semibold text-gray-800 cursor-pointer hover:bg-gray-50">
                    How to do an article submission?
                </summary>
                <div class="px-6 py-4 text-gray-600 border-t border-gray-200">
                    Write high-quality content that adheres to the selected site's guidelines. Sign up for the site, fill out the required information, and upload your article. Optimize your submission with relevant keywords and links before publishing.
                </div>
            </details>
            <details class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <summary class="px-6 py-4 font-semibold text-gray-800 cursor-pointer hover:bg-gray-50">
                    Which platform provides the best article submission site?
                </summary>
                <div class="px-6 py-4 text-gray-600 border-t border-gray-200">
                    This platform is a top choice for article submissions due to its wide reach and user-friendly interface, making it ideal for enhancing your content marketing strategy.
                </div>
            </details>
            <details class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <summary class="px-6 py-4 font-semibold text-gray-800 cursor-pointer hover:bg-gray-50">
                    What are the other best website submission types to boost traffic?
                </summary>
                <div class="px-6 py-4 text-gray-600 border-t border-gray-200">
                    Other effective submission types include directory submissions, social bookmarking, blog commenting, guest posting, and infographic submissions. Each can drive traffic and improve SEO when done strategically.
                </div>
            </details>
        </div>
    </div>

    <!-- Back Link -->
    <div class="text-center">
        <a href="{{ route('submissions.index') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition">
            ← Back to Categories
        </a>
    </div>

</div>
@endsection