@extends('layouts.dashboard')

@section('content')
<div class="container mx-auto px-4 py-10">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-6xl mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">📧 Send Email to All Employees</h2>

        <form action="{{ route('admin.send.email.submit') }}" method="POST">
            @csrf

            {{-- Success / Error Messages --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded shadow">{{ session('success') }}</div>
            @elseif (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded shadow">{{ session('error') }}</div>
            @endif

            {{-- Email Subject --}}
            <div class="mb-6">
                <label for="email_subject" class="block text-lg font-medium text-gray-700 mb-2">Email Subject *</label>
                <input type="text" name="email_subject" id="email_subject"
                    class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Enter email subject">
            </div>

            {{-- Email Body --}}
            <div class="mb-6">
                <label for="email_content" class="block text-lg font-medium text-gray-700 mb-2">Email Content *</label>
                <textarea name="email_content" id="email_content" rows="10"
                    class="w-full border border-gray-300 px-4 py-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Write your email message here..."></textarea>
            </div>

            {{-- Submit Button --}}
            <div class="text-right">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-lg py-3 px-6 rounded-xl transition-all duration-200 shadow-md">
                    🚀 Send To All Employees
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
