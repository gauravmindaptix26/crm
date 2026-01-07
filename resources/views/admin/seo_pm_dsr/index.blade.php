{{-- resources/views/admin/seo_pm_dsr/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'SEO PM • Daily Status Reports')

@push('styles')
<style>
    :root { --brand: #0e9b8e; --brand-light: #ccfbf1; }

    .dsr-card { background: linear-gradient(145deg, #ffffff, #f8fdfc); border-radius: 2rem; overflow: hidden;
        box-shadow: 0 20px 50px rgba(14,155,142,0.12); border: 1px solid rgba(14,155,142,0.15); transition: all 0.4s; }
    .dsr-card:hover { transform: translateY(-12px); box-shadow: 0 30px 70px rgba(14,155,142,0.2); }

    .header-gradient { background: linear-gradient(135deg, #0e9b8e, #14b8a6); padding: 2.5rem 2rem; color: white; }

    .pm-avatar { width: 100px; height: 100px; background: white; color: var(--brand); border-radius: 50%;
        font-size: 2.5rem; font-weight: 900; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2); border: 6px solid #f0fdfa; }

    .rating-ring { width: 120px; height: 120px;
        background: conic-gradient(var(--brand) calc(var(--rating)*36deg), #e0f2fe 0);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 2.8rem; font-weight: 900; color: var(--brand); box-shadow: 0 15px 40px rgba(14,155,142,0.4);
        border: 8px solid white; }

    .section { padding: 2.5rem; background: #fafafa; border-top: 1px solid #e2e8f0; }

    /* BOLDER & MORE PROMINENT SECTION TITLES — EXACTLY WHAT YOU WANTED */
    .section-title {
        font-size: 1.8rem !important;
        font-weight: 900 !important;
        color: #0d6e65 !important;
        margin-bottom: 1.8rem !important;
        padding-bottom: 1rem !important;
        border-bottom: 6px solid var(--brand-light) !important;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 1.5px !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1.5rem; }
    .metric { background: white; padding: 1.5rem; border-radius: 1.5rem; text-align: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08); border: 2px solid #f1f5f9; }
    .metric .value { font-size: 3.2rem; font-weight: 900; }
    .metric .label { font-size: 0.9rem; color: #64748b; margin-top: 0.5rem; font-weight: 600; }

    .yes-no { padding: 0.8rem 1.8rem; border-radius: 9999px; font-weight: 800; font-size: 1rem;
        text-transform: uppercase; letter-spacing: 1px; }
    .yes { background: #d1fae5; color: #065f46; border: 2px solid #10b981; }
    .no { background: #fee2e2; color: #991b1b; border: 2px solid #ef4444; }

    .tag { display: inline-block; background: var(--brand-light); color: #0f766e; padding: 0.5rem 1rem;
        border-radius: 9999px; font-size: 0.85rem; font-weight: 600; margin: 0.3rem; border: 1px solid var(--brand); }

    .note-box { background: #ecfdf5; border-left: 6px solid #10b981; padding: 1.5rem; border-radius: 1rem;
        margin-top: 1rem; font-style: italic; color: #065f46; box-shadow: 0 8px 20px rgba(16,185,129,0.1);
        line-height: 1.7; font-size: 0.95rem; }

    .opportunity-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.8rem; margin-top: 1rem; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-teal-50 via-cyan-50 to-emerald-50 py-16 px-4">
    <div class="max-w-7xl mx-auto">

        <!-- Header & Filters — unchanged -->
        <div class="text-center mb-16">
            <h1 class="text-7xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-teal-600 via-emerald-600 to-cyan-600 mb-6">
                DSR Dashboard
            </h1>
            <p class="text-3xl font-bold text-gray-800">SEO Project Managers • Daily Performance</p>
            <div class="mt-8 h-2 w-96 bg-gradient-to-r from-teal-500 via-emerald-500 to-cyan-500 rounded-full mx-auto"></div>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl p-10 mb-16 border-t-8 border-t-teal-600">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-8">
                <div>
                    <label class="block text-lg font-bold text-gray-700 mb-3">Project Manager</label>
                    <select name="pm_id" class="w-full px-6 py-5 rounded-2xl bg-gradient-to-r from-teal-50 to-emerald-50 border-2 border-teal-200 focus:border-teal-600 text-lg font-medium">
                        <option value="">All PMs</option>
                        @foreach($projectManagers as $pm)
                            <option value="{{ $pm->id }}" {{ request('pm_id') == $pm->id ? 'selected' : '' }}>{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-lg font-bold text-gray-700 mb-3">From</label><input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-6 py-5 rounded-2xl border-2 border-teal-200 focus:border-teal-600"></div>
                <div><label class="block text-lg font-bold text-gray-700 mb-3">To</label><input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-6 py-5 rounded-2xl border-2 border-teal-200 focus:border-teal-600"></div>
                <div><label class="block text-lg font-bold text-gray-700 mb-3">Rating</label>
                    <select name="rating" class="w-full px-6 py-5 rounded-2xl bg-gradient-to-r from-teal-50 to-emerald-50 border-2 border-teal-200 text-lg">
                        <option value="">Any Rating</option>
                        @for($i=10;$i>=1;$i--)
                            <option value="{{ $i }}" {{ request('rating')==$i ? 'selected' : '' }}>{{ $i }}/10</option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full bg-gradient-to-r from-teal-600 to-emerald-600 text-white font-bold py-5 rounded-2xl shadow-2xl hover:shadow-teal-600/50 transform hover:scale-105 transition text-xl">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Reports -->
        <div class="space-y-20">
            @forelse($dsrs as $dsr)
                @php
                    $date = \Carbon\Carbon::parse($dsr->report_date);
                    $ratingPercent = $dsr->rating * 10;
                @endphp

                <div class="dsr-card">
                    <!-- Header -->
                    <div class="header-gradient">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-10">
                            <div class="flex items-center gap-8">
                                <div class="pm-avatar">{{ Str::upper(Str::substr($dsr->pm->name, 0, 2)) }}</div>
                                <div>
                                    <h2 class="text-5xl md:text-6xl font-black">
                                        {{ $dsr->pm->name }} <span class="text-3xl opacity-80">PM</span>
                                    </h2>
                                    <p class="text-2xl font-medium opacity-90 mt-2">(Project Manager)</p>
                                    <p class="text-xl opacity-85 mt-3">{{ $date->format('l, j F Y') }} • {{ $date->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="rating-ring" style="--rating: {{ $ratingPercent }}">
                                <div>{{ $dsr->rating }}<small class="text-2xl">/10</small></div>
                            </div>
                        </div>
                    </div>

                    <!-- THREE COLUMNS WITH PERFECT SPACING (gap-8 + gap-12 on large screens) -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12 p-6 lg:p-8">

                        <!-- Column 1 -->
                        <div class="section bg-gradient-to-b from-teal-50 to-white space-y-12">
                            <div>
                                <h3 class="section-title font-black">Remarketing & Upsell</h3>
                                <div class="space-y-6 text-lg">
                                    <div class="flex justify-between items-center"><span>Follow Paused Clients</span><span class="yes-no {{ $dsr->follow_paused_clients ? 'yes' : 'no' }}">Yes</span></div>
                                    <div class="flex justify-between items-center"><span>Follow Closed Clients</span><span class="yes-no {{ $dsr->follow_closed_clients ? 'yes' : 'no' }}">Yes</span></div>
                                    @if($dsr->follow_closed_detail)<div class="note-box text-sm">{{ $dsr->follow_closed_detail }}</div>@endif
                                    <div class="flex justify-between items-center"><span>Upsell Approached</span><span class="yes-no {{ $dsr->upsell_clients ? 'yes' : 'no' }}">Yes</span></div>
                                    <div class="flex justify-between items-center"><span>Referral Asked</span><span class="yes-no {{ $dsr->referral_client ? 'yes' : 'no' }}">Yes</span></div>
                                </div>
                            </div>
                            <div>
                                <h3 class="section-title font-black">Case Study & Reviews</h3>
                                <div class="space-y-6 text-lg">
                                    <div class="flex justify-between items-center"><span>Case Study Updated</span><span class="yes-no {{ $dsr->updated_case_study ? 'yes' : 'no' }}">Yes</span></div>
                                    @if($dsr->case_study_description)<div class="note-box text-sm">{{ $dsr->case_study_description }}</div>@endif
                                    <div class="flex justify-between items-center"><span>Review Collected</span><span class="yes-no {{ $dsr->collected_review ? 'yes' : 'no' }}">Yes</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="section bg-gradient-to-b from-emerald-50 to-white space-y-12">
                            <div>
                                <h3 class="section-title font-black">Invoices & Payments</h3>
                                <div class="metric-grid mt-8">
                                    <div class="metric"><div class="value text-blue-600">{{ $dsr->invoices_sent }}</div><div class="label">Sent</div></div>
                                    <div class="metric"><div class="value text-orange-600">{{ $dsr->invoices_pending }}</div><div class="label">Pending</div></div>
                                    <div class="metric"><div class="value text-purple-600">{{ $dsr->payment_followups }}</div><div class="label">Follow-ups</div></div>
                                </div>
                                @if($dsr->payment_notes)<div class="note-box mt-6">{{ $dsr->payment_notes }}</div>@endif
                            </div>
                            <div>
                                <h3 class="section-title font-black">Happy Things Today</h3>
                                <div class="metric-grid mt-8">
                                    <div class="metric"><div class="value text-red-600">{{ $dsr->paused_today }}</div><div class="label">Paused</div></div>
                                    <div class="metric"><div class="value text-green-600">{{ $dsr->restarted_today }}</div><div class="label">Restarted</div></div>
                                    <div class="metric"><div class="value text-teal-600">{{ $dsr->closed_today }}</div><div class="label">Won</div></div>
                                </div>
                                @if($dsr->happy_things_notes)<div class="note-box mt-6">{{ $dsr->happy_things_notes }}</div>@endif
                            </div>
                        </div>

                        <!-- Column 3 -->
                        <div class="section bg-gradient-to-b from-cyan-50 to-white space-y-12">
                            <div>
                                <h3 class="section-title font-black">Daily Production</h3>
                                <div class="space-y-8 mt-8">
                                    <div class="metric"><div class="value text-teal-600">{{ $dsr->meetings_completed }}</div><div class="label">Meetings Completed</div></div>
                                    <div class="metric"><div class="value text-emerald-600">{{ $dsr->client_queries_resolved ?: '—' }}</div><div class="label">Queries Resolved</div></div>
                                </div>
                                @if($dsr->additional_tasks)<div class="note-box mt-6">{{ $dsr->additional_tasks }}</div>@endif
                            </div>

                            <div>
                                <h3 class="section-title font-black">Company Recommended Tasks</h3>
                                <div class="flex flex-wrap gap-3">
                                    @if($dsr->checked_teammate_dsr)<span class="tag">Teammate DSR Checked & Rated</span>@endif
                                    @if($dsr->audited_project)<span class="tag">Project Audited & Suggestions Given</span>@endif
                                </div>
                                @if($dsr->daily_tasks_description)<div class="note-box text-sm mt-4">{{ $dsr->daily_tasks_description }}</div>@endif
                            </div>

                            <div>
                                <h3 class="section-title font-black">SEO Discovery (Weekly)</h3>
                                <div class="flex flex-wrap gap-3">
                                    @if($dsr->seo_discovery_post)<span class="tag">Discovery Post Shared</span>@endif
                                    @if($dsr->weekly_team_session)<span class="tag">Team Session Conducted</span>@endif
                                    @if($dsr->seo_video_shared)<span class="tag">SEO Video Shared</span>@endif
                                </div>
                            </div>

                            <div>
                                <h3 class="section-title font-black">Opportunities Approached</h3>
                                <div class="opportunity-grid">
                                    @if($dsr->pr_placements)<div class="tag">PR Placements</div>@endif
                                    @if($dsr->guest_post_backlinking)<div class="tag">Guest Post Backlinking</div>@endif
                                    @if($dsr->website_redesign)<div class="tag">Website Redesign</div>@endif
                                    @if($dsr->blog_writing_seo)<div class="tag">Blog Writing SEO</div>@endif
                                    @if($dsr->virtual_assistant)<div class="tag">Virtual Assistant</div>@endif
                                    @if($dsr->full_web_development)<div class="tag">Full Web Development</div>@endif
                                    @if($dsr->crm_setup)<div class="tag">CRM Setup</div>@endif
                                    @if($dsr->google_ads)<div class="tag">Google Ads</div>@endif
                                    @if($dsr->social_ads)<div class="tag">Social Ads</div>@endif
                                    @if($dsr->logo_redesign)<div class="tag">Logo Redesign</div>@endif
                                    @if($dsr->podcast_outreach)<div class="tag">Podcast Outreach</div>@endif
                                    @if($dsr->video_testimonial)<div class="tag">Video Testimonial</div>@endif
                                    @if($dsr->google_reviews_service)<div class="tag">Google Reviews Service</div>@endif
                                </div>
                                @if($dsr->opportunity_description)
                                    <div class="note-box mt-6 text-sm"><strong>Details:</strong> {{ $dsr->opportunity_description }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="dsr-card text-center py-32">
                    <h3 class="text-6xl font-bold text-gray-400 mb-6">No Reports Found</h3>
                    <p class="text-2xl text-gray-500">Try changing the filters above</p>
                </div>
            @endforelse
        </div>

        <div class="mt-20 flex justify-center">
            {{ $dsrs->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection