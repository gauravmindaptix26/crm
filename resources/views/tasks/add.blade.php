@extends('layouts.dashboard')

@section('title', 'Submit Message for Task')

@section('content')
<div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-2xl font-semibold mb-6">Submit Message for Task: {{ $task->name }}</h2>

    <form method="POST" action="{{ route('task.submitMessage', $task->id) }}">
    @csrf
    <div class="mb-4">
        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
        <textarea name="message" id="message" rows="4" required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
    </div>

    <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        Submit Message
    </button>
</form>

</div>
@endsection
