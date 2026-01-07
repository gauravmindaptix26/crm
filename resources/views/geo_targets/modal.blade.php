@extends('layouts.dashboard')

@section('title', 'Geo Targets')

@section('content')
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between mb-4">
            <h2 class="text-xl font-bold">Geo Targets</h2>
            <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded">Add Geo Target</button>
        </div>
        
        <table class="w-full border-collapse border border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2">S/N</th>
                    <th class="border px-4 py-2">Title</th>
                    <th class="border px-4 py-2">Description</th>
                    <th class="border px-4 py-2">Added On / By</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($geoTargets as $geoTarget)
                    <tr id="geoTarget-{{ $geoTarget->id }}">
                        <td class="border px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="border px-4 py-2">{{ $geoTarget->title }}</td>
                        <td class="border px-4 py-2">{{ $geoTarget->description }}</td>
                        <td class="border px-4 py-2">
                            On: {{ $geoTarget->created_at->format('d-m-Y') }}<br>
                            By: {{ $geoTarget->creator->name ?? 'Unknown' }}
                        </td>
                        <td class="border px-4 py-2">
                            <button onclick="editGeoTarget({{ $geoTarget->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                            <button onclick="deleteGeoTarget({{ $geoTarget->id }})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $geoTargets->links() }}
        </div>
    </div>

@endsection

@section('scripts')
<script src="{{ asset('js/geo_targets.js') }}"></script>
@endsection
