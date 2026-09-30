@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-calendar-days me-2"></i> Calendar Page Settings</h5>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('admin.cms.calendar.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Header Section</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Subtitle (Eyebrow)</label>
                            <input type="text" name="cms_calendar_subtitle" class="form-control" value="{{ $settings['cms_calendar_subtitle'] ?? 'Yearly Schedule' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Main Title</label>
                            <input type="text" name="cms_calendar_title" class="form-control" value="{{ $settings['cms_calendar_title'] ?? 'Academic Calendar' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Description</label>
                            <textarea name="cms_calendar_desc" class="form-control" rows="3">{{ $settings['cms_calendar_desc'] ?? 'Stay updated with all important dates, examination schedules, and holidays for the current academic session.' }}</textarea>
                        </div>
                        
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Semester Settings</h6></div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Semester Name</label>
                            <input type="text" name="cms_calendar_semester" class="form-control" value="{{ $settings['cms_calendar_semester'] ?? 'Fall Semester' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Semester Year</label>
                            <input type="text" name="cms_calendar_year" class="form-control" value="{{ $settings['cms_calendar_year'] ?? '2026' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">PDF Calendar File</label>
                            <input type="file" name="cms_calendar_pdf" class="form-control" accept=".pdf">
                            @if(isset($settings['cms_calendar_pdf']))
                                <div class="mt-2"><a href="{{ $settings['cms_calendar_pdf'] }}" target="_blank" class="text-primary"><i class="fa-solid fa-file-pdf me-1"></i> View Current PDF</a></div>
                            @endif
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-3">
                                <h6 class="text-secondary fw-semibold mb-0">Event Legend Settings</h6>
                                <button type="button" class="btn btn-sm btn-info text-white" id="addLegendBtn"><i class="fa-solid fa-plus me-1"></i> Add Legend Type</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="row g-3" id="legendsContainer">
                                @php
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
                                @endphp
                                @foreach($legends as $index => $leg)
                                    <div class="col-md-6 legend-item-box" data-index="{{ $index }}">
                                        <div class="p-3 border rounded position-relative">
                                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-legend-btn" aria-label="Close"></button>
                                            <input type="hidden" name="legends[{{ $index }}][delete]" class="delete-legend-input" value="0">
                                            
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label class="form-label text-secondary small mb-1">Type Key</label>
                                                    <input type="text" name="legends[{{ $index }}][key]" class="form-control form-control-sm legend-key-input" value="{{ $leg['key'] }}" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label text-secondary small mb-1">Label Name</label>
                                                    <input type="text" name="legends[{{ $index }}][label]" class="form-control form-control-sm legend-label-input" value="{{ $leg['label'] }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label text-secondary small mb-1">Icon Class</label>
                                                    <input type="text" name="legends[{{ $index }}][icon]" class="form-control form-control-sm" value="{{ $leg['icon'] }}" required placeholder="bi-star">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-secondary small mb-1">Background Color</label>
                                                    <input type="color" name="legends[{{ $index }}][bg_color]" class="form-control form-control-color w-100 form-control-sm" value="{{ $leg['bg_color'] }}" title="Choose Background Color">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label text-secondary small mb-1">Text Color</label>
                                                    <input type="color" name="legends[{{ $index }}][text_color]" class="form-control form-control-color w-100 form-control-sm" value="{{ $leg['text_color'] }}" title="Choose Text Color">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-3">
                                <h6 class="text-secondary fw-semibold mb-0">Calendar Events</h6>
                                <button type="button" class="btn btn-sm btn-success" id="addEventBtn"><i class="fa-solid fa-plus me-1"></i> Add Event</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="row g-3" id="eventsContainer">
                                @php
                                    $events = json_decode($settings['cms_calendar_events'] ?? '[]', true);
                                    if(empty($events)) {
                                        $events = [
                                            ['date' => 'Sep 01', 'day' => 'Tuesday', 'title' => 'Fall Semester Begins', 'desc' => 'Orientation for new students and regular classes commence.', 'type' => 'school'],
                                            ['date' => 'Oct 15', 'day' => 'Thursday', 'title' => 'Mid-Term Examinations', 'desc' => 'First term assessments across all departments.', 'type' => 'exam'],
                                            ['date' => 'Nov 20', 'day' => 'Friday', 'title' => 'Annual Sports Day', 'desc' => 'Inter-house sports competitions and athletics.', 'type' => 'sports'],
                                            ['date' => 'Dec 18', 'day' => 'Friday', 'title' => 'Winter Break Begins', 'desc' => 'School closes for the winter holidays.', 'type' => 'holiday']
                                        ];
                                    }
                                @endphp
                                @foreach($events as $index => $item)
                                    <div class="col-md-6 event-item" data-index="{{ $index }}">
                                        <div class="p-3 border rounded position-relative">
                                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-event-btn" aria-label="Close"></button>
                                            <input type="hidden" name="events[{{ $index }}][delete]" class="delete-input" value="0">
                                            
                                            <div class="row g-2">
                                                @php
                                                    $rawDate = $item['date'] ?? '';
                                                    $formattedDate = '';
                                                    if (!empty($rawDate)) {
                                                        try {
                                                            // Handle both old "Sep 01" and new "YYYY-MM-DD" formats
                                                            $dateString = strlen($rawDate) <= 6 ? $rawDate . ' ' . ($settings['cms_calendar_year'] ?? date('Y')) : $rawDate;
                                                            $formattedDate = \Carbon\Carbon::parse($dateString)->format('Y-m-d');
                                                        } catch (\Exception $e) {
                                                            $formattedDate = '';
                                                        }
                                                    }
                                                @endphp
                                                <div class="col-md-4">
                                                    <label class="form-label text-secondary small mb-1">Event Date</label>
                                                    <input type="date" name="events[{{ $index }}][date]" class="form-control form-control-sm" value="{{ $formattedDate }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-secondary small mb-1">Day (Optional)</label>
                                                    <input type="text" name="events[{{ $index }}][day]" class="form-control form-control-sm" value="{{ $item['day'] ?? '' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label text-secondary small mb-1">Event Type</label>
                                                    <select name="events[{{ $index }}][type]" class="form-select form-select-sm event-type-select" required>
                                                        @foreach($legends as $legOpt)
                                                            <option value="{{ $legOpt['key'] }}" {{ (isset($item['type']) && $item['type'] == $legOpt['key']) ? 'selected' : '' }}>{{ $legOpt['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label text-secondary small mb-1">Event Title</label>
                                                    <input type="text" name="events[{{ $index }}][title]" class="form-control form-control-sm" value="{{ $item['title'] }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label text-secondary small mb-1">Event Description</label>
                                                    <textarea name="events[{{ $index }}][desc]" class="form-control form-control-sm" rows="2">{{ $item['desc'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save Calendar Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('eventsContainer');
    const addBtn = document.getElementById('addEventBtn');
    let itemIndex = {{ count($events) > 0 ? max(array_keys($events)) + 1 : 0 }};

    // Legends Management
    const legendsContainer = document.getElementById('legendsContainer');
    const addLegendBtn = document.getElementById('addLegendBtn');
    let legendIndex = {{ count($legends) > 0 ? max(array_keys($legends)) + 1 : 0 }};

    addLegendBtn.addEventListener('click', function() {
        const html = `
            <div class="col-md-6 legend-item-box" data-index="${legendIndex}">
                <div class="p-3 border rounded position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-legend-btn" aria-label="Close"></button>
                    <input type="hidden" name="legends[${legendIndex}][delete]" class="delete-legend-input" value="0">
                    
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small mb-1">Type Key</label>
                            <input type="text" name="legends[${legendIndex}][key]" class="form-control form-control-sm legend-key-input" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-secondary small mb-1">Label Name</label>
                            <input type="text" name="legends[${legendIndex}][label]" class="form-control form-control-sm legend-label-input" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary small mb-1">Icon Class</label>
                            <input type="text" name="legends[${legendIndex}][icon]" class="form-control form-control-sm" required placeholder="bi-star">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small mb-1">Background Color</label>
                            <input type="color" name="legends[${legendIndex}][bg_color]" class="form-control form-control-color w-100 form-control-sm" value="#E1EFFE">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small mb-1">Text Color</label>
                            <input type="color" name="legends[${legendIndex}][text_color]" class="form-control form-control-color w-100 form-control-sm" value="#1E429F">
                        </div>
                    </div>
                </div>
            </div>
        `;
        legendsContainer.insertAdjacentHTML('beforeend', html);
        legendIndex++;
    });

    legendsContainer.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-legend-btn')) {
            const item = e.target.closest('.legend-item-box');
            item.style.display = 'none';
            item.querySelector('.delete-legend-input').value = '1';
            updateAllEventTypeDropdowns();
        }
    });

    legendsContainer.addEventListener('input', function(e) {
        if(e.target.classList.contains('legend-key-input') || e.target.classList.contains('legend-label-input')) {
            updateAllEventTypeDropdowns();
        }
    });

    function getActiveLegends() {
        const legends = [];
        document.querySelectorAll('.legend-item-box').forEach(box => {
            if(box.querySelector('.delete-legend-input').value !== '1') {
                const key = box.querySelector('.legend-key-input').value.trim();
                const label = box.querySelector('.legend-label-input').value.trim();
                if(key && label) {
                    legends.push({key, label});
                }
            }
        });
        return legends;
    }

    function updateAllEventTypeDropdowns() {
        const legends = getActiveLegends();
        document.querySelectorAll('.event-type-select').forEach(select => {
            const currentVal = select.value;
            select.innerHTML = '';
            legends.forEach(leg => {
                const opt = document.createElement('option');
                opt.value = leg.key;
                opt.textContent = leg.label;
                if(leg.key === currentVal) opt.selected = true;
                select.appendChild(opt);
            });
        });
    }

    addBtn.addEventListener('click', function() {
        let legendOptions = '';
        getActiveLegends().forEach(leg => {
            legendOptions += `<option value="${leg.key}">${leg.label}</option>`;
        });

        const html = `
            <div class="col-md-6 event-item" data-index="${itemIndex}">
                <div class="p-3 border rounded position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-event-btn" aria-label="Close"></button>
                    <input type="hidden" name="events[${itemIndex}][delete]" class="delete-input" value="0">
                    
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small mb-1">Event Date</label>
                            <input type="date" name="events[${itemIndex}][date]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small mb-1">Day (Optional)</label>
                            <input type="text" name="events[${itemIndex}][day]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small mb-1">Event Type</label>
                            <select name="events[${itemIndex}][type]" class="form-select form-select-sm event-type-select" required>
                                ${legendOptions}
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary small mb-1">Event Title</label>
                            <input type="text" name="events[${itemIndex}][title]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary small mb-1">Event Description</label>
                            <textarea name="events[${itemIndex}][desc]" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    });

    container.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-event-btn')) {
            const item = e.target.closest('.event-item');
            item.style.display = 'none';
            item.querySelector('.delete-input').value = '1';
        }
    });
});
</script>
@endsection
