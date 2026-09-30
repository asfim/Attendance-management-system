@extends('frontend.layouts.app')
@section('content')

<!-- ============ EVENT HEADER ============ -->
<section class="pt-40 pb-12 px-6" style="background:var(--cream-2);">
  <div class="max-w-4xl mx-auto reveal">
    <a href="{{ route('events') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[var(--slate)] hover:text-[var(--navy-deep)] transition mb-8">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Back to Events
    </a>
    
    <div class="flex flex-wrap items-center gap-3 mb-6">
      <span class="px-3 py-1 rounded-full text-[10px] font-bold tracking-widest uppercase bg-purple-50 text-purple-600 border border-purple-100 flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span> Event
      </span>
      @if($event->start_date)
      <span class="text-sm text-[var(--slate)] font-mono flex items-center gap-1.5">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        {{ $event->start_date->format('d F Y, h:i A') }}
      </span>
      @endif
      @if($event->location)
      <span class="text-sm text-[var(--slate)] font-mono flex items-center gap-1.5">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        {{ $event->location }}
      </span>
      @endif
    </div>
    
    <h1 class="font-display text-3xl md:text-5xl text-[var(--navy-deep)] leading-tight mb-6">{{ $event->title }}</h1>
    
    @if($event->image_path)
    <div class="w-full h-64 md:h-96 rounded-2xl overflow-hidden mb-8 shadow-sm border border-black/5">
        <img src="{{ asset($event->image_path) }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
    </div>
    @endif
  </div>
</section>

<!-- ============ EVENT BODY ============ -->
<section class="pb-24 px-6 bg-[var(--cream-2)]">
  <div class="max-w-4xl mx-auto bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-black/5 reveal lift">
    <div class="prose prose-lg max-w-none text-[var(--slate)]" style="font-size: 1.1rem; line-height: 1.8;">
      {!! nl2br(e($event->description)) !!}
    </div>
  </div>

  <!-- Navigation between events -->
  <div class="max-w-4xl mx-auto mt-8 flex justify-between gap-4">
    @php
      $prev = \App\Models\Event::where('id', '<', $event->id)->latest('id')->first();
      $next = \App\Models\Event::where('id', '>', $event->id)->oldest('id')->first();
    @endphp
    @if($prev)
    <a href="{{ route('event.details', $prev->id) }}" class="flex-1 flex items-center gap-3 p-4 bg-white border border-black/10 rounded-2xl hover:border-[var(--navy-deep)] hover:shadow-md transition group">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--slate)] group-hover:text-[var(--navy-deep)]"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      <div class="overflow-hidden">
        <p class="text-xs text-[var(--slate)]">Previous Event</p>
        <p class="text-sm font-semibold text-[var(--navy-deep)] truncate">{{ $prev->title }}</p>
      </div>
    </a>
    @else
    <div class="flex-1"></div>
    @endif
    
    @if($next)
    <a href="{{ route('event.details', $next->id) }}" class="flex-1 flex items-center justify-end gap-3 p-4 bg-white border border-black/10 rounded-2xl hover:border-[var(--navy-deep)] hover:shadow-md transition group text-right">
      <div class="overflow-hidden">
        <p class="text-xs text-[var(--slate)]">Next Event</p>
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
