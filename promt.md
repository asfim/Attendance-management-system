# Office Biometric Device Integration Guide (অফিস অ্যাটেনডেন্স ও বায়োমেট্রিক ইন্টিগ্রেশন গাইড)

এই নথিতে **Attendify Office Attendance & Workforce Management System**-এর ZKTeco বায়োমেট্রিক হার্ডওয়্যার ইন্টিগ্রেশন, Python রিয়েল-টাইম ব্রিজ স্ক্রিপ্ট, Laravel REST API এন্ডপয়েন্ট এবং শিফট পলিসি ম্যানেজমেন্ট বিস্তারিতভাবে ব্যাখ্যা করা হয়েছে। 

সফটওয়্যারের সকল উপাদান এখন সম্পূর্ণভাবে **কর্পোরেট ও অফিস এইচআর (Office HR & Workforce)** পরিচালনার উপযোগী করে সাজানো।

---

## ১. আর্কিটেকচার ও ডেটা ফ্লো ওভারভিউ

বায়োমেট্রিক ডিভাইস (ফিঙ্গারপ্রিন্ট, আরএফআইডি কার্ড পাঞ্চ বা ফেস রিকগনিশন) থেকে সিস্টেমে ডেটা আসার প্রধান ডেটা-পাথ:

```text
[ ZKTeco Biometric Device (Office Gate / Entrance) ]
         │
         │ (LAN / TCP Port 4370 - pyzk Protocol)
         ▼
[ Python Real-time Bridge (pithon.py) ]
         │
         │ (HTTP / HTTPS JSON POST)
         ▼
[ Laravel Office API: POST /api/v1/biometric-sync ]
         │
         ├─► Employee Lookup (StaffProfile: biometric_id / employee_id / phone)
         ├─► Shift Evaluation (Grace Time, Late Coming, Early Exit, Overtime, Hours)
         ├─► Attendance Record Created / Updated (Check-In / Check-Out)
         └─► Audit Logging (biometric_device_logs)
```

---

## ২. সিস্টেমে থাকা ৩টি ইন্টিগ্রেশন পদ্ধতি

| পদ্ধতি | ডেটা ক্যাপচারের নিয়ম | Laravel এন্ডপয়েন্ট / সার্ভিস | কখন ব্যবহার করবেন |
|---|---|---|---|
| **১. Python Live-Capture Bridge** | লোকাল নেটওয়ার্কে `pithon.py` স্ক্রিপ্ট `live_capture()` ইভেন্ট শুনে সাথে সাথে সার্ভারে পুশ করে। | `POST /api/v1/biometric-sync` | রিয়েল-টাইম (পাঞ্চ করার সাথে সাথে ১ সেকেন্ডে) এন্ট্রি/এক্সিট ট্র্যাকিং এর জন্য **(সর্বাধিক প্রস্তাবিত)**। |
| **২. Laravel Scheduled Direct Sync** | লারাভেল শিডিউলার ডিভাইসগুলোর আইপিতে সরাসরি কানেক্ট করে মেমোরি লগ রিড করে। | `php artisan zkteco:sync` (`AttendanceSyncService`) | ডিভাইস সরাসরি সার্ভার নেটওয়ার্কে থাকলে প্রতি ৫ মিনিট পর পর ব্যাকগ্রাউন্ড সিঙ্ক করার জন্য। |
| **৩. ZKTeco ADMS Cloud Push** | ডিভাইসের নিজস্ব ফার্মওয়্যার সরাসরি ওয়েব সার্ভারে HTTP POST করে। | `GET/POST /iclock/cdata` | ডিভাইসে ADMS / Cloud Server Push ফার্মওয়্যার সক্রিয় থাকলে। |

> **সতর্কতা:** একটি ডিভাইসের জন্য যেকোনো একটি পদ্ধতি সক্রিয় রাখা উচিত, যাতে একই পাঞ্চ একাধিকবার প্রসেস না হয়।

---

## ৩. Python Live Bridge (`pithon.py`)

সোর্স কোড ফাইল: [`pithon.py`](./pithon.py)

### ডিপেন্ডেন্সি ইন্সটলেশন
পাইথন ৩ পরিবেশে নিচের প্যাকেজগুলো ইন্সটল থাকতে হবে:
```powershell
py -m pip install requests pyzk
```

### কনফিগারেশন
`pithon.py` ফাইলে আপনার অফিসের বায়োমেট্রিক ডিভাইসের আইপি এবং পোর্ট বসিয়ে নিন:
```python
# API Endpoint (প্রোডাকশনে আপনার ডোমেইন দিন)
API_URL = os.getenv("ATTENDANCE_API_URL", "http://127.0.0.1:8000/api/v1/biometric-sync")

# ডিভাইসের তালিকা
DEVICES = [
    {"ip": "192.168.10.201", "port": 4370, "sn": "OFFICE_MAIN_ENTRY"},
    {"ip": "192.168.10.202", "port": 4371, "sn": "OFFICE_BRANCH_ENTRY"}
]
```

### স্ক্রিপ্ট যেভাবে কাজ করে
1. প্রতিটি ডিভাইসের জন্য ব্যাকগ্রাউন্ডে একটি করে ডেমন থ্রেড (`live_capture_device`) চালু হয়।
2. ডিভাইসে কোনো এমপ্লয়ি আঙুল বা কার্ড ছোঁয়ালে `live_capture()` সাথে সাথে পাঞ্চ ইভেন্ট পায়।
3. এমপ্লয়ির User ID এবং পাঞ্চের সময় নিয়ে নিচের ফরম্যাটে JSON তৈরি করে সার্ভারে পাঠানো হয়:
   ```json
   {
     "card_uid": "EMP-0005",
     "timestamp": "2026-10-18 08:58:30",
     "device_sn": "OFFICE_MAIN_ENTRY",
     "type": "employee"
   }
   ```
4. সার্ভার রেসপন্স `200 OK` আসলে কনসোলে সফল বার্তা প্রিন্ট হয়। সংযোগ বিচ্ছিন্ন হলে স্ক্রিপ্ট প্রতি ১০ সেকেন্ড পর পুনরায় কানেক্ট করার চেষ্টা করে।

### চালু করার কমান্ড
```powershell
py pithon.py
```

---

## ৪. Laravel Office API Contract

- **রুট:** `POST /api/v1/biometric-sync`
- **কন্ট্রোলার:** [`app/Http/Controllers/Api/BiometricSyncController.php`](./app/Http/Controllers/Api/BiometricSyncController.php)
- **CSRF এক্সেপশন:** `bootstrap/app.php`-এ `api/*` CSRF মুক্ত করা আছে।

### রিকোয়েস্ট ফিল্ডস

| ফিল্ড | রিকোয়ার্ড | বর্ণনা |
|---|:---:|---|
| `card_uid` | হ্যাঁ | বায়োমেট্রিক ডিভাইসে থাকা User ID, এমপ্লয়ি কোড, কার্ড আইডি বা ফোন নম্বর |
| `timestamp` | না | পাঞ্চের নির্দিষ্ট সময় (`YYYY-MM-DD HH:MM:SS`); না দিলে সার্ভারের বর্তমান সময় নিবে |
| `type` | না | `employee`, `staff`, `admin`, বা `auto` (ডিফল্ট: `employee`) |
| `device_sn` | না | ডিভাইসের নাম/সিরিয়াল নম্বর (লগ সংরক্ষণের জন্য) |
| `punch_state` | না | `check_in`, `check_out`, অথবা `auto` (ডিফল্ট: `auto`) |

### টেস্টিং রিকোয়েস্ট (PowerShell উদাহরণ):
```powershell
$body = @{
    card_uid  = "EMP-0005"
    timestamp = "2026-10-18 08:55:00"
    device_sn = "OFFICE_MAIN_ENTRY"
    type      = "employee"
} | ConvertTo-Json

Invoke-RestMethod `
    -Method Post `
    -Uri "http://127.0.0.1:8000/api/v1/biometric-sync" `
    -ContentType "application/json" `
    -Body $body
```

### সফল রেসপন্স (`200 OK`):
```json
{
  "success": true,
  "message": "Biometric attendance recorded successfully for Kazi Nazrul Islam.",
  "record": {
    "name": "Kazi Nazrul Islam",
    "employee_id": "EMP-0005",
    "user_type": "employee",
    "status": "present",
    "entry_time": "08:55:00",
    "exit_time": null,
    "working_hours": 0,
    "timestamp": "2026-10-18T08:55:00+06:00"
  }
}
```

---

## ৫. অফিস অ্যাটেনডেন্স ক্যালকুলেশন পলিসি

লারাভেল কন্ট্রোলার প্রতিটি পাঞ্চ প্রসেস করার সময় নিচের অফিস রুলস প্রয়োগ করে:

1. **এমপ্লয়ি ম্যাচিং:**
   - পাঠানো `card_uid` দিয়ে ডাটাবেজের `staff_profiles` টেবিলের `biometric_id`, `employee_id`, `phone`, বা `national_id` ফিল্ডে সার্চ করা হয়।
   - এমপ্লয়ির সাথে সংযুক্ত ব্রাঞ্চ (`branch_id`) এবং ডিপার্টমেন্ট (`department_id`) স্বয়ংক্রিয়ভাবে অ্যাটেনডেন্স রেকর্ডে যুক্ত হয়।

2. **চেক-ইন ও লেইট কাউন্ট (প্রথম পাঞ্চ):**
   - দিনের প্রথম পাঞ্চকে **Check-In** হিসেবে গণ্য করা হয়।
   - এমপ্লয়ির শিফট টাইম (যেমন: সকাল ০৯:০০) এবং গ্রেস টাইম (যেমন: ১৫ মিনিট) মিলিয়ে লেট থ্রেশহোল্ড (০৯:১৫) নির্ধারণ করা হয়।
   - নির্ধারিত সময়ের পর পাঞ্চ করলে স্ট্যাটাস `late` হবে এবং কত মিনিট লেট (`late_minutes`) তা স্বয়ংক্রিয়ভাবে হিসাব হয়ে পে-রোলে জমা হবে।
   - প্রথম পাঞ্চে `is_missing_punch = true` থাকে, যাতে দিনশেষে বের হওয়ার সময় পাঞ্চ মিস হলে সিস্টেমে মিসিং পাঞ্চ হিসেবে চিহ্নিত হয়।

3. **চেক-আউট, আর্লি লিভ ও ওভারটাইম (পরবর্তী পাঞ্চ):**
   - দিনের দ্বিতীয় বা শেষ পাঞ্চকে **Check-Out** হিসেবে গণ্য করা হয়।
   - শিফট শেষ হওয়ার সময়ের পূর্বে বের হলে `early_leave_minutes` হিসাব হয় (৩০ মিনিটের বেশি আগে বের হলে স্ট্যাটাস `early_leave` হবে)।
   - শিফট শেষ হওয়ার পরে বের হলে অতিরিক্ত সময় `overtime_minutes` হিসেবে হিসাব হয়।
   - মোট কর্মঘণ্টা (`working_hours = CheckOut - CheckIn`) দশমিক আকারে স্বয়ংক্রিয়ভাবে নির্ণয় হয়।
   - চেক-আউট সম্পন্ন হলে `is_missing_punch = false` হয়ে যায়।

---

## ৬. সরাসরি ডিভাইস সিঙ্ক (`zkteco:sync`)

সার্ভার এবং ZKTeco ডিভাইস যদি একই লোকাল নেটওয়ার্কে থাকে, তবে কোনো মধ্যবর্তী পাইথন স্ক্রিপ্ট ছাড়াই লারাভেল সরাসরি ডিভাইস থেকে লগ সিঙ্ক করতে পারে:

- **কমান্ড:**
  ```powershell
  php artisan zkteco:sync
  ```
- **কনফিগারেশন:** 
  ওয়েব প্যানেলের **Biometric Devices** (`/admin/attendance-suite/devices`) মেনু থেকে প্রতিটি ডিভাইসের আইপি এবং পোর্ট যোগ করুন।
- **স্বয়ংক্রিয় ব্যাকগ্রাউন্ড শিডিউল:**
  `routes/console.php`-এ প্রতি ৫ মিনিটে কমান্ডটি রান করার জন্য শিডিউল করা আছে। লোকাল সার্ভারে শিডিউলার চালু রাখতে চালান:
  ```powershell
  php artisan schedule:work
  ```

---

## ৭. ZKTeco ADMS Cloud Push রুট

ডিভাইসে যদি ADMS ক্লাউড ফার্মওয়্যার থাকে, তবে ডিভাইসের মেনুতে সার্ভারের আইপি এবং পোর্ট দিলে ডিভাইস নিজেই নিচের রুটে ডেটা পুশ করবে:

- `GET /iclock/cdata` : কানেকশন হ্যান্ডশেক (রিটার্ন: `OK`)
- `POST /iclock/cdata` : রিয়েল-টাইম পাঞ্চ লগ রিসিভার
- `GET /iclock/getrequest` : ডিভাইস কমান্ড পুলিং

কন্ট্রোলার প্রতিটি পাঞ্চ পার্স করে এমপ্লয়ি প্রোফাইলের সাথে মিলিয়ে চেক-ইন/আউট তৈরি করে।

---

## ৮. ট্রাবলশুটিং চেকলিস্ট

| সমস্যা | সমাধান |
|---|---|
| **ডিভাইসে কানেক্ট হচ্ছে না (Connection refused / timeout)** | ১. ডিভাইস এবং পিসি একই আইপি সাবনেটে (যেমন `192.168.10.x`) আছে কি না পিং (`ping 192.168.10.201`) করে দেখুন।<br>২. ডিভাইসের কমিউনিকেশন মেনুতে পোর্ট `4370` ঠিক আছে কি না নিশ্চিত করুন।<br>৩. উইন্ডোজ ফায়ারওয়ালে TCP Port 4370 এলাউ করা আছে কি না যাচাই করুন। |
| **সার্ভার ৪MD৪ (No employee profile found)** | ১. ডিভাইসে নিবন্ধিত User ID এবং অ্যাডমিন প্যানেলে এমপ্লয়ির **Biometric ID / Employee ID** হুবহু এক হতে হবে।<br>২. অ্যাডমিন প্যানেলের Staff Profiles মেনুতে গিয়ে এমপ্লয়ি প্রোফাইলে ডিভাইস আইডি বসিয়ে দিন। |
| **পাঞ্চ দেখাচ্ছে কিন্তু লেট বা ওভারটাইম কাউন্ট হচ্ছে না** | এমপ্লয়ির শিফট বরাদ্দ করা আছে কি না তা **Attendance Suite -> Shifts** থেকে যাচাই করুন। |
| **অডিট লগ দেখতে চান** | অ্যাডমিন প্যানেলের **Attendance Suite -> Attendance History** (`/admin/attendance-suite/attendance/history`) থেকে সম্পূর্ণ লাইভ হিস্টোরি দেখা ও ডাউনলোড করা যাবে। |

---

## সংশ্লিষ্ট সোর্স ফাইলসমূহ

- [`pithon.py`](./pithon.py) — পাইথন রিয়েল-টাইম লাইভ ব্রিজ স্ক্রিপ্ট
- [`routes/web.php`](./routes/web.php) — বায়োমেট্রিক ও ADMS রুট
- [`app/Http/Controllers/Api/BiometricSyncController.php`](./app/Http/Controllers/Api/BiometricSyncController.php) — অফিস অ্যাটেনডেন্স JSON ও ADMS কন্ট্রোলার
- [`app/Services/AttendanceSyncService.php`](./app/Services/AttendanceSyncService.php) — ডাইরেক্ট ডিভাইস সিঙ্ক সার্ভিস
- [`app/Services/AttendanceProcessingService.php`](./app/Services/AttendanceProcessingService.php) — শিফট, লেইট, ওভারটাইম ও কর্মঘণ্টা ক্যালকুলেটর
- [`app/Console/Commands/SyncZKTecoDevices.php`](./app/Console/Commands/SyncZKTecoDevices.php) — কনসোল সিঙ্ক কমান্ড
- [`bootstrap/app.php`](./bootstrap/app.php) — API CSRF ব্যতিক্রম কনফিগারেশন
