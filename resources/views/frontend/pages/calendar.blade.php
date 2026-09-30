@extends('frontend.layouts.app')

@section('content')

@php
  $calYear  = (int)($settings['cms_calendar_year'] ?? date('Y'));
  $semester = $settings['cms_calendar_semester'] ?? 'Fall Semester';

  $legends = json_decode($settings['cms_calendar_legends'] ?? '[]', true);
  if(empty($legends)) {
      $legends = [
          ['key' => 'academic', 'label' => 'Academic Event', 'icon' => 'bi-book', 'bg_color' => '#E1EFFE', 'text_color' => '#1E429F'],
          ['key' => 'exam', 'label' => 'Examination', 'icon' => 'bi-pen', 'bg_color' => '#FDE8E8', 'text_color' => '#9B1C1C'],
          ['key' => 'holiday', 'label' => 'Holiday', 'icon' => 'bi-gift', 'bg_color' => '#FDF6B2', 'text_color' => '#723B13'],
          ['key' => 'school', 'label' => 'School Event', 'icon' => 'bi-building', 'bg_color' => '#DEF7EC', 'text_color' => '#03543F'],
          ['key' => 'parents', 'label' => 'Parents Meeting', 'icon' => 'bi-people', 'bg_color' => '#E0E7FF', 'text_color' => '#4338CA'],
          ['key' => 'sports', 'label' => 'Sports / Cultural', 'icon' => 'bi-trophy', 'bg_color' => '#FEECDC', 'text_color' => '#8A2C0D'],
      ];
  }
  $legendIcons = [];
  foreach($legends as $leg) {
      $legendIcons[$leg['key']] = $leg['icon'];
  }

  $rawEvents = json_decode($settings['cms_calendar_events'] ?? '[]', true);
  if(empty($rawEvents)){
    $rawEvents = [
      ['date'=>'Sep 01', 'day'=>'Tuesday', 'title'=>'Fall Semester Begins', 'desc'=>'Orientation for new students.', 'type'=>'school'],
      ['date'=>'Oct 15', 'day'=>'Thursday', 'title'=>'Mid-Term Examinations', 'desc'=>'First term assessments.', 'type'=>'exam'],
    ];
  }

  // Inject Exam Schedules from database (grouped by date)
  if(isset($examSchedules) && $examSchedules->count() > 0) {
      $groupedExams = [];
      foreach($examSchedules as $schedule) {
          $dateKey = $schedule->exam_date->format('Y-m-d');
          if(!isset($groupedExams[$dateKey])) {
              $groupedExams[$dateKey] = [
                  'exam_date' => $schedule->exam_date,
                  'exam_name' => $schedule->examType ? $schedule->examType->name : 'Exams',
                  'details' => []
              ];
          }
          $className = $schedule->schoolClass ? $schedule->schoolClass->name : 'Unknown';
          $subjectName = $schedule->subject ? $schedule->subject->name : 'Unknown';
          $timeStr = \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($schedule->end_time)->format('h:i A');
          $groupedExams[$dateKey]['details'][] = "• Class {$className}: {$subjectName} ({$timeStr})";
      }

      foreach($groupedExams as $dateKey => $data) {
          $rawEvents[] = [
              'date'  => $data['exam_date']->format('M d'),
              'day'   => $data['exam_date']->format('l'),
              'title' => $data['exam_name'],
              'desc'  => implode("\n", $data['details']),
              'type'  => 'exam',
              'is_grouped' => true
          ];
      }
  }

  $upcomingEvents = [];
  $today = now()->startOfDay();

  // Normalize dates and filter upcoming events
  foreach($rawEvents as $k => $v) {
      $rawDate = $v['date'];
      $parsedDate = null;
      try {
          $dateString = strlen($rawDate) <= 6 ? $rawDate . ' ' . $calYear : $rawDate;
          $parsedDate = \Carbon\Carbon::parse($dateString);
      } catch (\Exception $e) {
          // If unparseable, skip
          continue;
      }

      // Reformat date for JS consistency (e.g. "Aug 29")
      $rawEvents[$k]['date'] = $parsedDate->format('M d');

      // Check if event is upcoming (today or in the future)
      if ($parsedDate->gte($today)) {
          $upcomingEvents[] = [
              'date'  => $parsedDate->format('M d'),
              'sort_date' => $parsedDate->timestamp,
              'day'   => $v['day'] ?? '',
              'title' => $v['title'],
              'desc'  => $v['desc'] ?? '',
              'type'  => $v['type'] ?? 'academic'
          ];
      }
  }

  // Sort upcoming events chronologically
  usort($upcomingEvents, function($a, $b) {
      return $a['sort_date'] <=> $b['sort_date'];
  });
  
  // Take top 5
  $upcomingEvents = array_slice($upcomingEvents, 0, 5);
@endphp

<style>
  :root {
    --bg-card: #FFFFFF;
    
    /* Event Colors */
    @foreach($legends as $leg)
    --ev-{{ $leg['key'] }}-bg: {{ $leg['bg_color'] }}; --ev-{{ $leg['key'] }}-text: {{ $leg['text_color'] }};
    @endforeach
  }

  .cal-hero {
      background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
      padding: 120px 0 60px;
      position: relative;
      overflow: hidden;
  }
  .cal-hero::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: radial-gradient(circle at center, transparent 0%, var(--navy-deep) 100%);
      opacity: 0.7;
  }
  
  .cal-sidebar-card {
      background: #fff;
      border-radius: 1.5rem;
      border: 1px solid rgba(0,0,0,0.05);
      box-shadow: 0 10px 30px rgba(0,0,0,0.02);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      position: relative;
  }
  
  .legend-item {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 12px;
  }
  .icon-box {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
  }

  @foreach($legends as $leg)
  .type-{{ $leg['key'] }} { background-color: var(--ev-{{ $leg['key'] }}-bg); color: var(--ev-{{ $leg['key'] }}-text); }
  @endforeach

  /* Calendar Grid */
  .calendar-wrapper {
      background: #fff;
      border-radius: 1.5rem;
      border: 1px solid rgba(0,0,0,0.05);
      box-shadow: 0 10px 30px rgba(0,0,0,0.02);
      padding: 1.5rem;
  }
  .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, minmax(0, 1fr));
      gap: 10px;
  }
  .day-header {
      text-align: center;
      font-weight: 700;
      color: var(--navy-deep);
      padding: 10px 0;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 1px;
  }
  .day-cell {
      background-color: var(--cream);
      border: 1px solid rgba(0,0,0,0.03);
      border-radius: 12px;
      padding: 8px;
      min-height: 120px;
      transition: all 0.3s ease;
      position: relative;
  }
  .day-cell:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      transform: translateY(-2px);
      border-color: var(--accent);
  }
  .day-cell.empty {
      background-color: transparent;
      border: none;
      box-shadow: none;
  }
  .day-number {
      font-size: 1rem;
      font-weight: 700;
      color: var(--navy-deep);
      margin-bottom: 8px;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: #fff;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }
  .day-cell.today {
      background-color: rgba(249, 168, 37, 0.1);
      border-color: var(--accent);
  }
  .day-cell.today .day-number {
      background-color: var(--accent);
      color: #fff;
  }
  
  .event-pill {
      font-size: 0.75rem;
      padding: 4px 8px;
      border-radius: 6px;
      margin-bottom: 5px;
      font-weight: 600;
      line-height: 1.2;
      cursor: pointer;
      transition: opacity 0.2s;
  }
  .event-pill:hover {
      opacity: 0.8;
  }

  .upcoming-item {
      background: var(--cream);
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 15px;
      border: 1px solid rgba(0,0,0,0.03);
      transition: all 0.3s;
      cursor: pointer;
      display: flex;
      gap: 15px;
      align-items: flex-start;
  }
  .upcoming-item:hover {
      background: #fff;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      border-color: var(--primary);
  }
</style>

<!-- Hero Section -->
<div class="cal-hero text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">{{ $settings['cms_calendar_subtitle'] ?? 'Academic Session ' . $calYear }}</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ $settings['cms_calendar_title'] ?? 'Academic Calendar' }}</h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            {{ $settings['cms_calendar_desc'] ?? 'Stay updated with important dates, examination schedules, holidays, and school events throughout the academic year.' }}
        </p>
    </div>
</div>

<section class="section py-5 bg-light min-vh-100">
    <div class="container py-4">
        <div class="row g-4">
            
            <!-- Left Sidebar (Legend) -->
            <div class="col-lg-3 order-2 order-lg-1">
                <div class="cal-sidebar-card sticky-top" style="top: 100px;">
                    <h5 class="fw-bold mb-4" style="color: var(--navy-deep);"><i class="bi bi-info-circle text-warning me-2"></i>Event Legend</h5>
                    <div class="legend-list">
                        @foreach($legends as $leg)
                        <div class="legend-item">
                            <div class="icon-box type-{{ $leg['key'] }}"><i class="bi {{ $leg['icon'] }}"></i></div>
                            <span class="fw-semibold text-secondary">{{ $leg['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Center Calendar -->
            <div class="col-lg-6 order-1 order-lg-2">
                <div class="calendar-wrapper">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <button id="prevMonth" class="btn btn-outline-secondary rounded-circle" style="width:45px;height:45px;"><i class="bi bi-chevron-left"></i></button>
                        <h3 id="monthYearDisplay" class="fw-bold mb-0" style="color: var(--navy-deep); font-family: 'Playfair Display', serif;">January {{ $calYear }}</h3>
                        <button id="nextMonth" class="btn btn-outline-secondary rounded-circle" style="width:45px;height:45px;"><i class="bi bi-chevron-right"></i></button>
                    </div>

                    <div class="calendar-grid mb-2">
                        <div class="day-header text-danger">Sun</div>
                        <div class="day-header">Mon</div>
                        <div class="day-header">Tue</div>
                        <div class="day-header">Wed</div>
                        <div class="day-header">Thu</div>
                        <div class="day-header">Fri</div>
                        <div class="day-header text-danger">Sat</div>
                    </div>
                    <div class="calendar-grid" id="calendarDays">
                        <!-- JS Will Render Days Here -->
                    </div>
                </div>
            </div>

            <!-- Right Sidebar (Upcoming Events) -->
            <div class="col-lg-3 order-3">
                <div class="cal-sidebar-card">
                    <h5 class="fw-bold mb-4" style="color: var(--navy-deep);"><i class="bi bi-calendar-event text-warning me-2"></i>Upcoming Events</h5>
                    <div class="upcoming-events-list">
                        @forelse($upcomingEvents as $ev)
                            @php
                                $descText = !empty($ev['desc']) ? htmlspecialchars($ev['desc'], ENT_QUOTES) : '';
                                $titleText = htmlspecialchars($ev['title'], ENT_QUOTES);
                                $iconClass = $legendIcons[$ev['type']] ?? 'bi-book';
                            @endphp
                            <div class="upcoming-item" onclick="showEventModal(this)" data-title="{{ $titleText }}" data-date="{{ $ev['date'] }}" data-desc="{{ $descText }}">
                                <div class="icon-box type-{{ $ev['type'] }}"><i class="bi {{ $iconClass }}"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">{{ $ev['title'] }}</h6>
                                    <small class="text-primary fw-bold">{{ $ev['date'] }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted p-3">No upcoming events.</div>
                        @endforelse
                    </div>

                    @if(!empty($settings['cms_calendar_pdf']))
                        <a href="{{ $settings['cms_calendar_pdf'] }}" target="_blank" class="btn btn-primary w-full w-100 rounded-pill mt-3 py-2 fw-bold">
                            <i class="bi bi-file-earmark-pdf me-2"></i> Download PDF
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 1.5rem; border: none; overflow: hidden;">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%); border: none;">
        <h5 class="modal-title fw-bold" id="modalTitle" style="font-family: 'Playfair Display', serif;">Event Title</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 bg-light">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-calendar3 text-warning me-2 fs-4"></i>
            <h6 class="mb-0 fw-bold text-primary" id="modalDate">Date</h6>
        </div>
        <p class="text-muted mb-0" id="modalDesc" style="white-space: pre-line;"></p>
      </div>
      <div class="modal-footer border-0 bg-light pt-0">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rawEvents = {!! json_encode($rawEvents) !!};
    const calYear = {{ $calYear }};
    
    // Parse events into a map: "M-D" -> array of events
    const eventMap = {};
    const monthNamesShort = { "Jan":0, "Feb":1, "Mar":2, "Apr":3, "May":4, "Jun":5, "Jul":6, "Aug":7, "Sep":8, "Oct":9, "Nov":10, "Dec":11 };
    
    rawEvents.forEach(ev => {
        const parts = ev.date.trim().split(/\s+/); 
        if(parts.length >= 2) {
            const rawMonth = parts[0].substring(0, 3);
            const mStr = rawMonth.charAt(0).toUpperCase() + rawMonth.substring(1).toLowerCase();
            const m = monthNamesShort[mStr];
            const d = parseInt(parts[1], 10);
            if(m !== undefined && !isNaN(d)) {
                const key = m + '-' + d;
                if(!eventMap[key]) eventMap[key] = [];
                eventMap[key].push(ev);
            }
        }
    });

    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    let currentDate = new Date();
    let currentMonth = currentDate.getFullYear() === calYear ? currentDate.getMonth() : 0;
    
    const calendarDays = document.getElementById('calendarDays');
    const monthYearDisplay = document.getElementById('monthYearDisplay');
    
    function renderCalendar(month, year) {
        calendarDays.innerHTML = '';
        monthYearDisplay.textContent = monthNames[month] + ' ' + year;
        
        const firstDay = new Date(year, month, 1).getDay(); // 0 (Sun) to 6 (Sat)
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        const today = new Date();
        const isCurrentMonthYear = today.getMonth() === month && today.getFullYear() === year;
        const currentDay = today.getDate();

        // Fill leading empty cells
        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'day-cell empty';
            calendarDays.appendChild(emptyCell);
        }
        
        // Fill days
        for (let d = 1; d <= daysInMonth; d++) {
            const cell = document.createElement('div');
            let cellClasses = 'day-cell';
            
            if(isCurrentMonthYear && d === currentDay) {
                cellClasses += ' today';
            }
            
            cell.className = cellClasses;
            
            let html = `<div class="day-number">${d}</div>`;
            
            const key = month + '-' + d;
            if(eventMap[key]) {
                html += `<div class="mt-2">`;
                eventMap[key].forEach(ev => {
                    const descText = ev.desc ? ev.desc.replace(/"/g, '&quot;') : '';
                    html += `<div class="event-pill type-${ev.type}" data-title="${ev.title.replace(/"/g, '&quot;')}" data-desc="${descText}" data-date="${ev.date}" onclick="showEventModal(this)">${ev.title}</div>`;
                });
                html += `</div>`;
            }
            
            cell.innerHTML = html;
            calendarDays.appendChild(cell);
        }
    }
    
    document.getElementById('prevMonth').addEventListener('click', () => {
        currentMonth--;
        if(currentMonth < 0) { currentMonth = 11; }
        renderCalendar(currentMonth, calYear);
    });
    
    document.getElementById('nextMonth').addEventListener('click', () => {
        currentMonth++;
        if(currentMonth > 11) { currentMonth = 0; }
        renderCalendar(currentMonth, calYear);
    });
    
    renderCalendar(currentMonth, calYear);
});

function showEventModal(el) {
    const title = el.getAttribute('data-title');
    const desc = el.getAttribute('data-desc');
    const date = el.getAttribute('data-date');
    
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalDate').textContent = date;
    document.getElementById('modalDesc').innerHTML = desc ? desc.replace(/\n/g, '<br>') : 'No additional details available.';
    
    var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
    myModal.show();
}
</script>

@endsection
