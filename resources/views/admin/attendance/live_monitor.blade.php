@extends('layouts.app')

@section('title', 'Live Biometric Attendance Monitor Kiosk')

@section('content')
<style>
/* ─── Live Kiosk Monitor Theme (Balanced Sizing) ──────────────── */
.kiosk-wrapper {
    background: radial-gradient(circle at top left, #0f172a, #020617) !important;
    color: #ffffff !important;
    padding: 1.75rem 2rem;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}
.kiosk-wrapper .text-white {
    color: #ffffff !important;
}
.kiosk-card {
    background: rgba(30, 41, 59, 0.85);
    backdrop-filter: blur(16px);
    border: 1.5px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 2rem 2.25rem;
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
}
.kiosk-card.punch-pulse {
    animation: punchGlow 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes punchGlow {
    0% { transform: scale(0.98); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
    50% { transform: scale(1.01); box-shadow: 0 0 35px 10px rgba(34, 197, 94, 0.5); }
    100% { transform: scale(1); box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5); }
}
.student-photo-frame {
    width: 280px;
    height: 280px;
    border-radius: 9px;
    border: 5px solid #3b82f6;
    box-shadow: 0 0 30px rgba(59, 130, 246, 0.55);
    overflow: hidden;
    margin: 0 auto 1.5rem auto;
    background: #1e293b;
    transition: border-color 0.3s, box-shadow 0.3s;
}
.student-photo-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.live-clock-badge {
    background: #0f172a;
    border: 1.5px solid #38bdf8;
    padding: 0.5rem 1.6rem;
    border-radius: 50px;
    font-family: monospace;
    font-size: 1.5rem;
    font-weight: 800;
    color: #38bdf8;
    box-shadow: 0 0 16px rgba(56, 189, 248, 0.35);
}
.recent-punch-item {
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 0.85rem 1.1rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}
.recent-punch-item:hover {
    background: rgba(30, 41, 59, 0.95);
}
.badge-class-tag {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff !important;
    font-size: 1.15rem;
    padding: 0.5rem 1.4rem;
    border-radius: 50px;
    font-weight: 750;
}
.badge-roll-tag {
    background: rgba(255, 255, 255, 0.15);
    color: #f1f5f9 !important;
    font-size: 1.05rem;
    padding: 0.45rem 1.15rem;
    border-radius: 50px;
    font-weight: 650;
}

/* ─── Glowing Live Tag Badge ───────────────────────────────────── */
.badge-live-tag {
    background: #dc2626 !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    border-radius: 50px !important;
    font-size: 1rem !important;
    letter-spacing: 0.04em !important;
    padding: 0.5rem 1.4rem !important;
    box-shadow: 0 0 20px rgba(220, 38, 38, 0.65) !important;
    display: inline-flex !important;
    align-items: center !important;
}

/* ─── Glowing Status Badge Styling ───────────────────────────── */
.badge-status-glow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 2.25rem;
    border-radius: 50px;
    font-size: 1.4rem;
    font-weight: 850;
    color: #ffffff !important;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    box-shadow: 0 0 32px rgba(34, 197, 94, 0.65);
    transition: all 0.3s ease;
}
.badge-status-glow.success {
    background: linear-gradient(135deg, #16a34a, #15803d) !important;
    border: 2px solid #4ade80 !important;
}
.badge-status-glow.exit {
    background: linear-gradient(135deg, #ea580c, #c2410c) !important;
    border: 2px solid #fb923c !important;
    box-shadow: 0 0 25px rgba(234, 88, 12, 0.6);
}
.badge-status-glow.waiting {
    background: rgba(30, 41, 59, 0.95) !important;
    border: 2px solid rgba(255, 255, 255, 0.25) !important;
    color: #cbd5e1 !important;
    box-shadow: none !important;
}

/* Light theme overrides for the kiosk display */
[data-bs-theme="light"] .kiosk-wrapper {
    background: radial-gradient(circle at top left, #eff6ff, #f8fafc) !important;
    color: #0f172a !important;
    border: 1px solid #dbeafe;
}
[data-bs-theme="light"] .kiosk-wrapper .text-white,
[data-bs-theme="light"] .kiosk-card .text-white,
[data-bs-theme="light"] .recent-punch-card .text-white {
    color: #0f172a !important;
}
[data-bs-theme="light"] .kiosk-card,
[data-bs-theme="light"] .recent-punch-card {
    background: rgba(255, 255, 255, 0.96) !important;
    border-color: #cbd5e1;
    box-shadow: 0 16px 36px -18px rgba(15, 23, 42, 0.35);
}
[data-bs-theme="light"] .student-photo-frame {
    background: #e2e8f0;
    box-shadow: 0 0 24px rgba(37, 99, 235, 0.22);
}
[data-bs-theme="light"] .live-clock-badge {
    background: #ffffff;
    border-color: #0284c7;
    color: #0369a1;
    box-shadow: 0 0 14px rgba(14, 165, 233, 0.2);
}
[data-bs-theme="light"] .recent-punch-item {
    background: #f8fafc;
    border-color: #dbe3ee;
}
[data-bs-theme="light"] .recent-punch-item:hover {
    background: #eff6ff;
}
[data-bs-theme="light"] .badge-roll-tag {
    background: #e2e8f0;
    color: #334155 !important;
}
[data-bs-theme="light"] .badge-status-glow.waiting {
    background: #e2e8f0 !important;
    border-color: #94a3b8 !important;
    color: #475569 !important;
}
[data-bs-theme="light"] .kiosk-wrapper .bg-black.bg-opacity-40 {
    background-color: #f1f5f9 !important;
}
[data-bs-theme="light"] .kiosk-wrapper .border-white {
    border-color: #cbd5e1 !important;
}
[data-bs-theme="light"] .kiosk-wrapper .border-top {
    border-color: #cbd5e1 !important;
}
</style>

<div class="kiosk-wrapper d-flex flex-column justify-content-between">
    <!-- Header Controls -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge-live-tag me-2">
                <span class="spinner-grow spinner-grow-sm text-white me-2" role="status"></span> LIVE GATE MONITOR DISPLAY
            </span>
            <span class="text-white fw-semibold small">Real-Time Biometric Gate Punch Kiosk</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="live-clock-badge" id="live-clock">00:00:00 AM</div>
            <a href="{{ route('admin.attendance.biometric-logs') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">
                <i class="bi bi-journal-text me-1"></i> View All Logs
            </a>
            <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow" onclick="toggleFullScreen()">
                <i class="bi bi-fullscreen me-1"></i> Full Screen Mode
            </button>
        </div>
    </div>

    <!-- Main Live Punch Card Area -->
    <div class="row align-items-center g-3 my-auto">
        <!-- Main Display Kiosk -->
        <div class="col-lg-8">
            <div class="kiosk-card text-center" id="main-kiosk-card">
                <!-- Status Header -->
                <div class="mb-3" id="punch-status-container">
                    <span class="badge-status-glow success" id="punch-status-badge">
                        <i class="bi bi-check-circle-fill me-2"></i> PUNCH IN SUCCESSFUL
                    </span>
                </div>

                <!-- Student Photo -->
                <div class="student-photo-frame" id="photo-frame">
                    <img id="kiosk-photo" src="{{ asset('images/default-avatar.png') }}" alt="Student Photo">
                </div>

                <!-- Student Name -->
                <h2 class="fs-2 fw-bold text-white mb-2" id="kiosk-name">Waiting for Fingerprint Punch...</h2>
                
                <!-- Class & Section Badges -->
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3" id="kiosk-class-container">
                    <span class="badge-class-tag" id="kiosk-class-section">CLASS — SECTION</span>
                    <span class="badge-roll-tag" id="kiosk-roll">ROLL: #—</span>
                    <span class="badge-roll-tag" id="kiosk-admission">ADM: —</span>
                </div>

                <!-- Time & State Info -->
                <div class="d-inline-flex align-items-center gap-3 px-3 py-1.5 rounded-pill bg-black bg-opacity-40 border border-white border-opacity-20 mt-1">
                    <div class="fs-5 text-info fw-bold" id="kiosk-punch-time"><i class="bi bi-clock-fill me-1"></i> --:--:-- --</div>
                    <div class="border-end border-white border-opacity-30" style="height: 20px;"></div>
                    <div class="fs-5 text-warning fw-bold text-uppercase" id="kiosk-punch-state"><i class="bi bi-door-open-fill me-1"></i> ENTRY</div>
                    <div class="fs-5 text-danger fw-bold d-none bg-white bg-opacity-10 px-3 py-1 rounded-pill" id="kiosk-late-min"></div>
                </div>
            </div>
        </div>

        <!-- Right Side: Recent Punches Feed -->
        <div class="col-lg-4">
            <div class="card recent-punch-card border border-white border-opacity-15 rounded-4 p-3 shadow-lg">
                <div class="d-flex align-items-center justify-content-between mb-2 border-bottom border-white border-opacity-15 pb-2">
                    <h6 class="fw-bold mb-0 text-white"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Gate Punches</h6>
                    <span class="badge bg-primary rounded-pill px-2 py-0.5 small">Live Stream</span>
                </div>

                <div id="recent-punches-list">
                    <div class="text-center py-4 small" style="color: rgba(255,255,255,0.6);">
                        <i class="bi bi-fingerprint fs-3 d-block mb-1" style="color: rgba(255,255,255,0.4);"></i>
                        No punch activity yet.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Note (Removed) -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let lastPunchId = null;
    const defaultAvatar = "{{ asset('images/default-avatar.png') }}";

    // 1. Clock Update
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        document.getElementById('live-clock').textContent = timeStr;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // 2. Web Audio Sound Chime Generator (Synthesizer Chime for Live Punch)
    function playChimeSound() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
            osc.frequency.exponentialRampToValueAtTime(880, audioCtx.currentTime + 0.15); // A5
            gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.4);
        } catch (e) {
            console.log('Audio playback blocked or unavailable.');
        }
    }

    // 3. Poll Live Punch Feed Endpoint
    function pollLiveFeed() {
        fetch("{{ route('admin.attendance.live-feed') }}")
            .then(res => res.json())
            .then(data => {
                if (data.has_punch && data.punch) {
                    const punch = data.punch;

                    // Check if it's a NEW punch log that hasn't been displayed yet
                    if (lastPunchId !== punch.id) {
                        lastPunchId = punch.id;
                        updateKioskDisplay(punch);
                        playChimeSound();
                    }
                }

                if (data.recent) {
                    updateRecentList(data.recent);
                }
            })
            .catch(err => console.error('Live feed error:', err));
    }

    // 4. Update Main Kiosk Display Card
    function updateKioskDisplay(p) {
        const kioskCard = document.getElementById('main-kiosk-card');
        const photo = document.getElementById('kiosk-photo');
        const photoFrame = document.getElementById('photo-frame');
        const name = document.getElementById('kiosk-name');
        const classSec = document.getElementById('kiosk-class-section');
        const roll = document.getElementById('kiosk-roll');
        const admission = document.getElementById('kiosk-admission');
        const punchTime = document.getElementById('kiosk-punch-time');
        const punchState = document.getElementById('kiosk-punch-state');
        const statusBadge = document.getElementById('punch-status-badge');

        // Pulse Animation
        kioskCard.classList.remove('punch-pulse');
        void kioskCard.offsetWidth; // trigger reflow
        kioskCard.classList.add('punch-pulse');

        // Set Details
        name.textContent = p.name;
        photo.src = p.photo_url || defaultAvatar;

        if (p.user_type === 'student') {
            classSec.textContent = (p.class_name ? 'CLASS ' + p.class_name : '') + (p.section_name ? ' — SECTION ' + p.section_name : 'STUDENT');
            roll.textContent = p.roll_no ? 'ROLL: #' + p.roll_no : 'STUDENT';
            admission.textContent = p.admission_no ? 'ADM: ' + p.admission_no : '';
            classSec.style.display = 'inline-block';
            roll.style.display = 'inline-block';
            admission.style.display = p.admission_no ? 'inline-block' : 'none';
        } else {
            classSec.textContent = p.designation ? p.designation.toUpperCase() : 'TEACHER / STAFF';
            roll.textContent = p.department ? 'DEPT: ' + p.department : 'STAFF';
            admission.style.display = 'none';
        }

        punchTime.innerHTML = `<i class="bi bi-clock-fill me-1"></i> ${p.punch_time}`;
        
        const lateMinEl = document.getElementById('kiosk-late-min');
        if (p.punch_state === 'check_in' && p.late_minutes && p.late_minutes > 0) {
            let lateText = '';
            if (p.late_minutes > 59) {
                let hours = Math.floor(p.late_minutes / 60);
                let mins = p.late_minutes % 60;
                lateText = hours + ' HR ' + (mins > 0 ? mins + ' MIN' : '');
            } else {
                lateText = p.late_minutes + ' MIN';
            }
            lateMinEl.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> LATE: ${lateText}`;
            lateMinEl.classList.remove('d-none');
        } else {
            lateMinEl.classList.add('d-none');
        }

        // Update High-Contrast Status Badge
        if (p.punch_state === 'check_out') {
            punchState.innerHTML = `<i class="bi bi-box-arrow-right me-1 text-danger"></i> EXIT`;
            statusBadge.innerHTML = `<i class="bi bi-box-arrow-right me-2"></i> PUNCH OUT SUCCESSFUL`;
            statusBadge.className = 'badge-status-glow exit';
            photoFrame.style.borderColor = '#ea580c';
            photoFrame.style.boxShadow = '0 0 30px rgba(234, 88, 12, 0.7)';
        } else if (p.punch_state === 'check_in') {
            punchState.innerHTML = `<i class="bi bi-box-arrow-in-right me-1 text-success"></i> ENTRY`;
            statusBadge.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> PUNCH IN SUCCESSFUL`;
            statusBadge.className = 'badge-status-glow success';
            photoFrame.style.borderColor = '#16a34a';
            photoFrame.style.boxShadow = '0 0 30px rgba(34, 197, 94, 0.7)';
        } else {
            punchState.innerHTML = `<i class="bi bi-files me-1 text-secondary"></i> DUPLICATE`;
            statusBadge.innerHTML = `<i class="bi bi-info-circle-fill me-2"></i> ALREADY PUNCHED`;
            statusBadge.className = 'badge-status-glow waiting';
            photoFrame.style.borderColor = '#94a3b8';
            photoFrame.style.boxShadow = '0 0 30px rgba(148, 163, 184, 0.7)';
        }
    }

    // 5. Update Recent Ticker List
    function updateRecentList(items) {
        const list = document.getElementById('recent-punches-list');
        if (!items || items.length === 0) return;

        let html = '';
        items.forEach(item => {
            const roleBadge = item.user_type === 'student' ? 'Student' : 'Staff';
            let stateColor = 'text-secondary';
            let stateText = 'Duplicate';
            
            if (item.state === 'check_out') {
                stateColor = 'text-danger';
                stateText = 'Exit';
            } else if (item.state === 'check_in') {
                stateColor = 'text-success';
                stateText = 'Entry';
            }

            html += `
                <div class="recent-punch-item">
                    <div>
                        <div class="fw-bold text-white small">${item.name}</div>
                        <span class="badge bg-secondary bg-opacity-40 text-white px-2 py-0 small">${roleBadge}</span>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-info small">${item.time}</div>
                        <span class="small ${stateColor} fw-bold">${stateText}</span>
                    </div>
                </div>
            `;
        });
        list.innerHTML = html;
    }

    // Poll every 1.5 seconds
    setInterval(pollLiveFeed, 1500);
    pollLiveFeed();
});

function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}
</script>
@endsection
