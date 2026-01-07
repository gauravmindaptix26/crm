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

    <!-- Modal -->
    <div id="geoTargetModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3 relative">
            <button onclick="closeModal()" class="absolute top-3 right-3 text-xl text-gray-500 hover:text-black">&times;</button>
            <h2 class="text-xl font-bold mb-4" id="modalTitle">Add Geo Target</h2>
            <form id="geoTargetForm">
                @csrf
                <input type="hidden" id="geoTargetId">
                <div>

                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" name="title" class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mt-3">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" class="w-full px-3 py-2 border rounded"></textarea>
                </div>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function openModal() {
        document.getElementById("geoTargetModal").classList.remove("hidden");
        document.getElementById("modalTitle").innerText = "Add Geo Target";
        document.getElementById("geoTargetId").value = "";
        document.getElementById("title").value = "";
        document.getElementById("description").value = "";
    }

    function closeModal() {
    let modal = document.getElementById("geoTargetModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex"); // Hide modal properly
}

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("geoTargetForm").addEventListener("submit", function (e) {
        e.preventDefault();

        let geoTargetId = document.getElementById("geoTargetId").value;
        let title = document.getElementById("title").value;
        let description = document.getElementById("description").value;
        let countryId = new URLSearchParams(window.location.search).get("country_id"); // Get country_id from URL

        if (!countryId) {
            alert("Country ID is missing in the URL.");
            return;
        }

        let url = geoTargetId ? `geo-targets/${geoTargetId}` : `geo-targets?country_id=${countryId}`;
        let method = geoTargetId ? "PUT" : "POST";

        fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ title, description })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal();
                location.reload(); // Refresh to reflect new records
            } else {
                alert("Failed to save Geo Target.");
            }
        })
        .catch(error => console.error("Error:", error));
    });
});

    function editGeoTarget(id) {
    fetch(`geo-targets/${id}/edit`, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("Fetched Data:", data); // Debugging Log

        // Populate modal fields
        document.getElementById("geoTargetId").value = data.id;
        document.getElementById("title").value = data.title;
        document.getElementById("description").value = data.description;
        document.getElementById("modalTitle").innerText = "Edit Geo Target";

        // Open modal
        let modal = document.getElementById("geoTargetModal");
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    })
    .catch(error => {
        console.error("Error fetching data:", error);
        alert("Failed to fetch data. Check console for details.");
    });
}



    function deleteGeoTarget(id) {
        if (!confirm("Are you sure you want to delete this Geo Target?")) return;

        fetch(`geo-targets/${id}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`geoTarget-${id}`).remove();
            } else {
                alert("Failed to delete Geo Target.");
            }
        })
        .catch(error => console.error("Error deleting data:", error));
    }
</script>

<script>
    function loadGeoTargets(countryId) {
    fetch(`geo-targets?country_id=${countryId}`, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        }
    })
    .then(response => response.json())
    .then(data => {
        let tbody = document.querySelector("tbody");
        tbody.innerHTML = ""; // Clear existing rows

        data.geoTargets.data.forEach((geoTarget, index) => {
            let newRow = `
                <tr id="geoTarget-${geoTarget.id}">
                    <td class="border px-4 py-2">${index + 1}</td>
                    <td class="border px-4 py-2">${geoTarget.title}</td>
                    <td class="border px-4 py-2">${geoTarget.description || 'N/A'}</td>
                    <td class="border px-4 py-2">
                        On: ${new Date(geoTarget.created_at).toLocaleDateString()}<br>
                        By: ${geoTarget.creator ? geoTarget.creator.name : 'Unknown'}
                    </td>
                    <td class="border px-4 py-2">
                        <button onclick="editGeoTarget(${geoTarget.id})" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                        <button onclick="deleteGeoTarget(${geoTarget.id})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML("beforeend", newRow);
        });

        // Update pagination dynamically
        document.querySelector(".pagination-links").innerHTML = data.geoTargets.links;
    })
    .catch(error => console.error("Error:", error));
}


    </script>
@endsection
