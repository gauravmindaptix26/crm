<aside class="w-full lg:w-[28%] 2xl:w-[20%] bg-white text-black h-screen p-4 flex flex-col shadow-xl transition-all duration-300 sticky top-0 h-full">





    <!-- <aside class="w-[20%] bg-white text-black h-screen p-4 flex flex-col shadow-xl transition-all duration-300 sticky top-16"> -->
    <!-- Logo / Title -->
    <!-- <h2 class="text-2xl font-bold mb-6 text-center text-blue-400 animate-pulse text-emerald-500 font-[500]">SEO DISCOVERY</h2> -->

    <div class="main-custom flex flex-col h-[90vh] justify-between">
    <h2 class="text-[23px] bg-black px-[30px] py-2.5 rounded-[10px] text-center text-emerald-500 font-[500] animate-pulse mb-5 duration-[1500ms]">
  SEO DISCOVERY
</h2>

        <nav class="flex-1 space-y-1 overflow-y-auto">
              
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
                class="flex items-center py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                  <span class="side-war-img flex items-center justify-center">
                    <span class="side-war-img"> <img src="{{ asset('storage/images/dashboard-new.png') }}"> </span>
                    <span class="ml-3 text-md font-medium">Dashboard</span> </a></span>

            <!-- Profile -->
            <a href="{{ route('profile.edit') }}"
                class="flex items-center py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                 <span class="side-war-img"> <img src="{{ asset('storage/images/admin.png') }}"> </span>
                <span class="ml-3 text-md font-medium">Profile</span>
            </a>

            <!-- HR Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center w-full py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                    <span class="side-war-img"> <img src="{{ asset('storage/images/hr.png') }}"> </span>
                     <span class="ml-3 text-md font-medium">HR Section</span>
                    <span class="transform transition-transform duration-300" :class="open ? 'rotate-180' : ''">▼</span>
                </button>
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="mt-2 rounded-lg shadow-lg overflow-hidden">
                    <a href="{{ route('users.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">👥 User Management</a>
                    <a href="{{ route('candidates.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Candidates Management</a>
                    <a href="{{ route('support-tickets.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Support Ticket</a>
                    <a href="{{ route('admin.send.email.form') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Send Email Notification</a>
                    <a href="{{ route('departments.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📋 Department Management</a>
                </div>
            </div>

            <!-- Guest Post Department -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center w-full py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                       <span class="side-war-img"> <img src="{{ asset('storage/images/guest.png') }}"> </span>
                    <span class="ml-3 text-md font-medium">Guest Post</span>
                  
                    <span class="transform transition-transform duration-300" :class="open ? 'rotate-180' : ''">▼</span>
                </button>
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="mt-2 rounded-lg shadow-lg overflow-hidden">
                    <a href="{{ route('guest-posts.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Guest Post</a>
                    <a href="{{ route('gigs.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">💼 Gigs</a>
                    <a href="{{ route('link-building.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">🔗 Link Building</a>
                </div>
            </div>

            @unless(auth()->user()->hasRole('HR'))
            <!-- Production Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center w-full py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                    
                     <span class="side-war-img"> <img src="{{ asset('storage/images/produc.png') }}"> </span>
                    <span class="ml-3 text-md font-medium">Production Department</span>
                    <span class="transform transition-transform duration-300" :class="open ? 'rotate-180' : ''">▼</span>
                </button>
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="mt-2 rounded-lg shadow-lg overflow-hidden">
                    <a href="{{ route('projects.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Project Management</a>
                    <a href="{{ route('project-tasks.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Project Task</a>
                    <a href="{{ route('projects.closed') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Closed Project</a>
                    <a href="{{ route('projects.paused') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Paused Projects</a>
                    <a href="{{ route('projects.pending.invoices') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Pending Invoices</a>
                    <a href="{{ route('countries.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">🌍 All Countries</a>
                    <a href="{{ route('project-directories.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📂 Project Categories</a>
                    <a href="{{ route('task-phases.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Task Phase</a>
                    <a href="{{ route('projects.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📝 Hired Projects</a>
                </div>
            </div>
            @endunless

            <!-- Payment Details -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center w-full py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">

                    <span class="side-war-img"> <img src="{{ asset('storage/images/money.png') }}"> </span>
                    <span class="ml-3 text-md font-medium">Payment Details</span>
                    <span class="transform transition-transform duration-300" :class="open ? 'rotate-180' : ''">▼</span>
                </button>
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="mt-2 rounded-lg shadow-lg overflow-hidden">
                    <a href="{{ route('payment_accounts.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📋 Payment Account</a>
                    <a href="{{ route('payment.details') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">👥 Payment Details</a>
                </div>
            </div>

            <!-- Sales Department -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center w-full py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                     <span class="side-war-img"> <img src="{{ asset('storage/images/sales.png') }}"> </span>
                     <span class="ml-3 text-md font-medium">Sales Department</span>
                    <span class="transform transition-transform duration-300" :class="open ? 'rotate-180' : ''">▼</span>
                </button>
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="mt-2 rounded-lg shadow-lg overflow-hidden">
                    <a href="{{ route('projects.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Hired Projects</a>
                    <a href="{{ route('project-portfolios.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Portfolio Project</a>
                    <a href="{{ route('all-portfolios.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Portfolio Management</a>
                    <a href="{{ route('sales-projects.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Sales Team Project</a>
                    <a href="{{ route('sales-leads.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Sales Lead Management</a>
                    <a href="{{ route('all-data-entries.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📂 All Data entry</a>
                    <a href="{{ route('hired-from.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Hired From Management</a>
                </div>
            </div>



  <!-- Website Submission -->
  <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center w-full py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                     <span class="side-war-img"> <img src="{{ asset('storage/images/sales.png') }}"> </span>
                     <span class="ml-3 text-md font-medium">Website Submission</span>
                    <span class="transform transition-transform duration-300" :class="open ? 'rotate-180' : ''">▼</span>
                </button>
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="mt-2 rounded-lg shadow-lg overflow-hidden">
                    <a href="{{ route('submission_categories.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">Website Categories</a>
                    <a href="{{ route('submission_sites.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">Add Websites</a>
                    <!-- <a href="{{ route('all-portfolios.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Portfolio Management</a>
                    <a href="{{ route('sales-projects.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Sales Team Project</a>
                    <a href="{{ route('sales-leads.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Sales Lead Management</a>
                    <a href="{{ route('all-data-entries.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📂 All Data entry</a>
                    <a href="{{ route('hired-from.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">📊 Hired From Management</a> -->
                </div>
            </div>




            <!-- Daily Updates -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="flex items-center w-full py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                     <span class="side-war-img"> <img src="{{ asset('storage/images/update.png') }}"> </span>
                    <span class="ml-3 text-md font-medium">Daily Updates</span>
                    <span class="transform transition-transform duration-300" :class="open ? 'rotate-180' : ''">▼</span>
                </button>
                <div x-show="open" @click.away="open = false"
                    x-transition:enter="transition ease-out duration-300 transform opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200 transform opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="mt-2 rounded-lg shadow-lg overflow-hidden">
                    <!-- ADMIN ONLY: Show Both View DSR Buttons -->
        @if(auth()->user()->hasRole('Admin'))
            <a href="{{ route('admin.dsr.reports') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">
                👁️ View SEO DSR Reports
            </a>
            <a href="{{ route('admin.web.dev.dsr.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">
                👁️ View Web Dev DSR Reports
            </a>
        @endif
<!-- Add Daily DSR Button - NOW MATCHES OTHER LINKS STYLE -->
@if(auth()->user()->department && auth()->user()->hasRole('Project Manager'))
    @php
        $deptName = auth()->user()->department->name ?? '';
    @endphp

    @if($deptName === 'SEO')
        <a href="{{ route('seo.pm.dsr.dashboard') }}" 
           class="block py-3 px-5 transition duration-200 hover:bg-teal-600">
            ➕ Add Daily DSR (PM SEO)
        </a>
    @elseif($deptName === 'Web Development')
        <a href="{{ route('web.dev.pm.dsr.daily') }}" 
           class="block py-3 px-5 transition duration-200 hover:bg-teal-600">
            ➕ Add DSR (PM Designing)
        </a>
    @endif
@endif
<!-- Hide "Add DSR" button from Admin AND Project Manager -->
@if(!auth()->user()->hasRole('Admin') && !auth()->user()->hasRole('Project Manager'))
    <a href="{{ route('dsr.create') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">➕ Add DSR</a>
@endif              <a href="{{ route('team.dsr.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">👥 Team DSR</a>
                    <a href="{{ route('all-rnds.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">👥 R & D Details</a>
                    <a href="{{ route('tasks.index') }}" class="block py-3 px-5 transition duration-200 hover:bg-teal-600">👥 Task Management</a>
                </div>
            </div>

            <!-- All Niche -->
            <a href="{{ route('niches.index') }}"
                class="flex items-center py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                 <span class="side-war-img"> <img src="{{ asset('storage/images/all.png') }}"> </span>
                <span class="ml-3 text-md font-medium">All Niche</span>
            </a>

          <!-- Email Template -->
@unless(auth()->user()->hasRole('Employee'))
    <a href="{{ route('email-templates.index') }}"
       class="flex items-center py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
        <span class="side-war-img"> <img src="{{ asset('storage/images/email.png') }}"> </span>
        <span class="ml-3 text-md font-medium">Email Templates</span>
    </a>
@endunless

          <!-- Project Audit -->
@unless(auth()->user()->hasRole('Employee'))
    <a href="{{ route('projects.audit') }}"
       class="flex items-center py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
        <span class="side-war-img">
            <img src="{{ asset('storage/images/email.png') }}">
        </span>
        <span class="ml-3 text-md font-medium">Project Audit</span>
    </a>
@endunless


            <!-- Assigned Projects -->
            @if(auth()->user() && auth()->user()->hasAnyRole(['Employee', 'Team Lead']))
            <a href="{{ route('my.assigned.projects') }}"
                class="flex items-center py-3 px-4 rounded-lg transition-all duration-300 hover:bg-gradient-to-r from-teal-400 to-gray-400 hover:shadow-lg">
                🎫 <span class="ml-3 text-md font-medium">Assigned Projects</span>
            </a>
            @endif
        </nav>

        <!-- Logout -->
        <div class="bottom-[10px] left-[15px] right-0 w-full lg:w-[220px] 2xl:w-[300px] mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
              <button type="submit" class="flex items-center py-3 px-4 w-full text-center bg-teal-600 rounded-lg transition-all duration-300 hover:bg-teal-600 hover:shadow-lg" style="
    position: absolute;
    width: 100%;
    max-width: 300px;
     top: auto;
    bottom: 55px;
">
                     <span class="side-war-img"> <img src="{{ asset('storage/images/logout.png') }}"> </span> <span class="ml-3 text-md  text-white font-medium">Logout</span>
                </button>






            </form>
        </div>
    </div>
</aside>