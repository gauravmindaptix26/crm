@extends('layouts.dashboard')

@section('title', 'SEO PM DSR Dashboard')

@push('styles')
<style>
    body {
        background: #eef2f7 !important;
        font-family: 'Inter', sans-serif;
        color: #1e293b;
    }

    /* Glass Card */
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,0.45);
        padding: 2.8rem;
        box-shadow: 0 12px 28px rgba(0,0,0,0.08);
        transition: 0.35s ease;
    }
    .glass-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 18px 35px rgba(0,0,0,0.15);
    }

    /* Colored Icon Circles */
    .icon-badge {
        height: 85px;
        width: 85px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2.6rem;
        color: #fff;
        margin: 0 auto 1.4rem;
        box-shadow: 0 6px 14px rgba(0,0,0,0.15);
    }

    /* Progress Bar */
    .progress-container {
        background: #e2e8f0;
        height: 12px;
        border-radius: 6px;
        margin-top: 1.3rem;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        transition: width 1.1s ease;
        border-radius: 6px;
    }

    /* Action Buttons */
    .action-btn {
        background: white;
        padding: 2.4rem;
        border-radius: 20px;
        text-align: center;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        transition: 0.25s ease;
        color: #1e293b;
    }
    .action-btn:hover {
        transform: translateY(-12px);
        box-shadow: 0 16px 30px rgba(0,0,0,0.10);
    }

    .action-title {
        font-size: 1.9rem;
        font-weight: 800;
        margin-bottom: 0.7rem;
    }

    .small-text {
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
    }

</style>
@endpush

@section('content')
<div class="py-16 px-6">
{{-- TOAST MESSAGES – TOP CENTER, AUTO DISAPPEAR --}}
<div class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 space-y-4">
    @if(session('success'))
        <div id="successToast" class="toast bg-gradient-to-r from-green-500 to-emerald-600 text-white px-8 py-6 rounded-2xl shadow-2xl flex items-center gap-4">
            <div class="text-5xl">Success</div>
            <div>
                <h3 class="text-2xl font-bold">{{ session('success') }}</h3>
                <p class="text-sm opacity-90">Keep up the amazing work!</p>
            </div>
        </div>

        <script>
            setTimeout(() => {
                const toast = document.getElementById('successToast');
                if (toast) {
                    toast.style.transition = 'all 0.6s ease-out';
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-20px)';
                    setTimeout(() => toast.remove(), 600);
                }
            }, 4000);
        </script>
    @endif

    @if(session('error'))
        <div id="errorToast" class="toast bg-gradient-to-r from-red-500 to-rose-600 text-white px-8 py-6 rounded-2xl shadow-2xl flex items-center gap-4">
            <div class="text-5xl">Warning</div>
            <div>
                <h3 class="text-2xl font-bold">{{ session('error') }}</h3>
            </div>
        </div>

        <script>
            setTimeout(() => {
                const toast = document.getElementById('errorToast');
                if (toast) {
                    toast.style.transition = 'all 0.6s ease-out';
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-20px)';
                    setTimeout(() => toast.remove(), 600);
                }
            }, 5000);
        </script>
    @endif
</div>

    <!-- Header -->
    <div class="text-center mb-20">
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">SEO PM DSR HUB</h1>
        <div class="w-56 h-1.5 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 mx-auto my-4 rounded-full"></div>
        <p class="text-lg text-slate-600">{{ now()->format('l, j F Y') }}</p>
        <p class="text-xl text-slate-600 mt-2">Submit Reports • Track Performance • Stay Consistent</p>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-20">

        <!-- DAILY -->
        <div class="glass-card text-center">
            <div class="icon-badge" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                📄
            </div>
            <h3 class="text-2xl font-bold mb-1">Daily Report</h3>
            <p class="{{ $dailySubmitted ? 'text-green-600' : 'text-amber-600' }} font-semibold text-lg">
                {{ $dailySubmitted ? 'Completed Today' : 'Pending' }}
            </p>

            <div class="progress-container mt-4">
                <div class="progress-fill"
                     style="width: {{ $dailySubmitted ? '100%' : '30%' }};
                     background: linear-gradient(90deg,#10b981,#34d399);">
                </div>
            </div>
        </div>

        <!-- WEEKLY -->
        <div class="glass-card text-center">
            <div class="icon-badge" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                📆
            </div>
            <h3 class="text-2xl font-bold mb-1">Weekly Report</h3>
            <p class="{{ $weeklySubmitted ? 'text-green-600' : 'text-blue-600' }} font-semibold text-lg">
                {{ $weeklySubmitted ? 'Submitted' : 'Due Soon' }}
            </p>

            <div class="progress-container mt-4">
                <div class="progress-fill"
                     style="width: {{ $weeklySubmitted ? '100%' : '60%' }};
                     background: linear-gradient(90deg,#6366f1,#8b5cf6);">
                </div>
            </div>
        </div>

        <!-- MONTHLY -->
        <div class="glass-card text-center">
            <div class="icon-badge" style="background: linear-gradient(135deg, #ec4899, #f43f5e);">
                🏆
            </div>
            <h3 class="text-2xl font-bold mb-1">Monthly Report</h3>
            <p class="{{ $monthlySubmitted ? 'text-green-600' : 'text-rose-600' }} font-semibold text-lg">
                {{ $monthlySubmitted ? 'Submitted' : 'Pending' }}
            </p>

            <div class="progress-container mt-4">
                <div class="progress-fill"
                     style="width: {{ $monthlySubmitted ? '100%' : '25%' }};
                     background: linear-gradient(90deg,#fb7185,#f43f5e);">
                </div>
            </div>
        </div>

    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-14 mb-24">

        <!-- DAILY LINK -->
        <a href="{{ route('seo.pm.dsr.daily') }}" class="action-btn">
            <div class="icon-badge" style="background: linear-gradient(135deg,#0ea5e9,#38bdf8);">
                📝
            </div>
            <div class="action-title">Daily Report</div>
            <p class="small-text">Submit today’s work update</p>
        </a>

        <!-- WEEKLY LINK -->
        <a href="{{ route('seo.pm.dsr.weekly') }}" class="action-btn">
            <div class="icon-badge" style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
                📅
            </div>
            <div class="action-title">Weekly Report</div>
            <p class="small-text">Discovery • Case Study • SEO Tasks</p>
        </a>

        <!-- MONTHLY LINK -->
        <a href="{{ route('seo.pm.dsr.monthly') }}" class="action-btn">
            <div class="icon-badge" style="background: linear-gradient(135deg,#ec4899,#f43f5e);">
                📊
            </div>
            <div class="action-title">Monthly Report</div>
            <p class="small-text">Opportunities & Bonus Tracking</p>
        </a>

    </div>

    <div class="text-center mt-20">
        <h2 class="text-3xl font-bold text-slate-800 mb-1">Stay Consistent. Stay Ahead.</h2>
        <p class="text-lg text-slate-500">Your discipline builds your success.</p>
    </div>

</div>
@endsection