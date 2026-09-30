@extends('frontend.layouts.app')
@section('content')

<!-- ============ NOTICE HEADER ============ -->
<section class="pt-40 pb-12 px-6" style="background:var(--cream-2);">
  <div class="max-w-4xl mx-auto reveal">
    <a href="{{ route('notice') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[var(--slate)] hover:text-[var(--navy-deep)] transition mb-8">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Back to Notices
    </a>
    
    <div class="flex flex-wrap items-center gap-3 mb-6">
      <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase bg-blue-50 text-blue-600 border border-blue-100 flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> {{ ucfirst($notice->target_audience ?? 'General') }}
      </span>
      <span class="text-sm text-[var(--slate)] font-mono flex items-center gap-1.5">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        {{ optional($notice->published_at ?? $notice->created_at)->format('d F Y') }}
      </span>
      @if($notice->expires_at)
      <span class="text-sm text-red-500 font-mono flex items-center gap-1.5">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Expires: {{ $notice->expires_at->format('d F Y') }}
      </span>
      @endif
    </div>
    
    <h1 class="font-display text-3xl md:text-5xl text-[var(--navy-deep)] leading-tight mb-6">{{ $notice->title }}</h1>
    
    <div class="flex items-center gap-4 py-4 border-y border-black/10">
      <div class="w-10 h-10 rounded-full bg-[var(--navy-deep)] text-white flex items-center justify-center font-bold text-sm">
        AO
      </div>
      <div>
        <p class="text-sm font-semibold text-[var(--navy-deep)]">Academic Office</p>
        <p class="text-xs text-[var(--slate)]">{{ \App\Models\Setting::get('site_name', 'Meridian International School & College') }}</p>
      </div>
      <div class="ml-auto">
        <a href="{{ route('notice.download', $notice->id) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white transition hover:opacity-90 hover:scale-105"
           style="background: var(--navy-deep);">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
          </svg>
          Download PDF
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============ NOTICE BODY ============ -->
<section class="pb-24 px-6 bg-[var(--cream-2)]">
  <div class="max-w-4xl mx-auto bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-black/5 reveal lift">
    <div class="prose prose-lg max-w-none text-[var(--slate)]" style="font-size: 1.1rem; line-height: 1.8;">
      {!! nl2br(e($notice->content)) !!}
    </div>
  </div>

  <!-- Navigation between notices -->
  <div class="max-w-4xl mx-auto mt-8 flex justify-between gap-4">
    @php
      $prev = \App\Models\Notice::where('id', '<', $notice->id)->latest('id')->first();
      $next = \App\Models\Notice::where('id', '>', $notice->id)->oldest('id')->first();
    @endphp
    @if($prev)
    <a href="{{ route('notice.details', $prev->id) }}" class="flex-1 flex items-center gap-3 p-4 bg-white border border-black/10 rounded-2xl hover:border-[var(--navy-deep)] hover:shadow-md transition group">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--slate)] group-hover:text-[var(--navy-deep)]"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      <div class="overflow-hidden">
        <p class="text-xs text-[var(--slate)]">Previous Notice</p>
        <p class="text-sm font-semibold text-[var(--navy-deep)] truncate">{{ $prev->title }}</p>
      </div>
    </a>
    @else
    <div class="flex-1"></div>
    @endif
    
    @if($next)
    <a href="{{ route('notice.details', $next->id) }}" class="flex-1 flex items-center justify-end gap-3 p-4 bg-white border border-black/10 rounded-2xl hover:border-[var(--navy-deep)] hover:shadow-md transition group text-right">
      <div class="overflow-hidden">
        <p class="text-xs text-[var(--slate)]">Next Notice</p>
        <p class="text-sm font-semibold text-[var(--navy-deep)] truncate">{{ $next->title }}</p>
      </div>
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--slate)] group-hover:text-[var(--navy-deep)] shrink-0"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    @else
    <div class="flex-1"></div>
    @endif
  </div>
</section>

@endsection
