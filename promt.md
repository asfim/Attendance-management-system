# Biometric Device Integration Guide

এই নথিতে project-এর ZKTeco biometric attendance integration, Python bridge, Laravel API এবং বিকল্প sync পদ্ধতিগুলো ব্যাখ্যা করা হয়েছে। এখানে বর্ণনা করা আচরণ repository-র বর্তমান code অনুযায়ী; কোনো endpoint বা setup পরিবর্তন করা হয়নি।

## সংক্ষেপে: কোন API এবং library ব্যবহার হয়েছে?

Python script-এ hardware-এর জন্য আলাদা কোনো REST API ব্যবহার করা হয়নি:

1. `pyzk` Python library (`from zk import ZK`) ZKTeco device-এর সঙ্গে তার LAN IP ও TCP port দিয়ে যোগাযোগ করে।
2. Device থেকে পাওয়া user ID ও punch time Python `requests` library দিয়ে Laravel-এর HTTPS JSON endpoint-এ পাঠানো হয়:
   `POST https://iqraislamimodelmadrasha.com/api/v1/biometric-sync`
3. Laravel endpoint attendance record তৈরি করে এবং biometric log সংরক্ষণ করে।

অর্থাৎ data path:

```text
ZKTeco device
   -- local network / ZKTeco TCP -->
Python bridge (pithon.py)
   -- HTTPS JSON POST -->
Laravel biometric-sync endpoint
   --> student/staff lookup
   --> attendance + biometric device log
```

## Project-এ থাকা তিনটি integration path

| Path | Device থেকে data কীভাবে আসে | Laravel-এ কোথায় যায় | কখন ব্যবহার করবেন |
|---|---|---|---|
| Python live-capture bridge | `pyzk` দিয়ে device-এ LAN connection; `live_capture()` থেকে punch | `POST /api/v1/biometric-sync` | Python machine device network-এ থাকলে, event পেলেই server-এ পাঠাতে |
| Laravel scheduled/direct sync | Laravel-এর ZKTeco service সরাসরি device-এর IP/port-এ সংযোগ করে | `AttendanceSyncService` থেকে attendance processor | Laravel server নিজেই device network-এ পৌঁছাতে পারলে |
| ZKTeco ADMS push | Device নিজে HTTP request করে | `/iclock/cdata` routes | Device ADMS/Push mode configure করা থাকলে |

একই device/punch-এর জন্য একাধিক path একসঙ্গে চালালে একই punch একাধিকবার process হতে পারে। Python JSON endpoint-এ বর্তমান code-এ duplicate unique-key check নেই; তাই একটি device-এর জন্য একটি ingestion path বেছে নেওয়া উচিত।

## Python bridge

Source file: [`pithon.py`](./pithon.py)

### Dependencies

Script-এ `requests` এবং `zk` import করা হয়েছে। `zk` module-টি `pyzk` package থেকে আসে। Python 3 environment-এ:

```powershell
py -m pip install requests pyzk
```

এই repository-তে Python `requirements.txt` নেই। চাইলে dependency pin করার জন্য আলাদা requirements file যোগ করা যেতে পারে.

### Device configuration

`pithon.py`-এর `DEVICES` তালিকায় প্রতিটি device-এর:

- `ip`: device-এর LAN IP address
- `port`: ZKTeco TCP port; device/network configuration-এর সঙ্গে মিলতে হবে
- `sn`: application log-এ ব্যবহৃত device serial/name

বর্তমান script-এ একাধিক device entry আছে। নিজের network-এ প্রতিটি device-এর জন্য সঠিক IP, port ও serial বসাতে হবে। Python চালানো computer থেকে device IP/port-এ network access এবং Laravel URL-এ outbound HTTPS access থাকতে হবে।

### Script কীভাবে কাজ করে

1. `__main__` block প্রতিটি configured device-এর জন্য আলাদা daemon thread চালু করে।
2. প্রতিটি thread `ZK(ip, port, timeout=10)` তৈরি করে এবং `connect()` করে।
3. `conn.live_capture()` থেকে punch event এলে script user ID ও timestamp নেয়।
4. `send_to_server()` user ID-কে string-এ রূপান্তর করে JSON payload তৈরি করে।
5. `requests.post()` Laravel endpoint-এ payload পাঠায়। HTTP status `200` হলে success, অন্য status হলে response body-সহ error print করে।
6. Connection বা capture exception হলে বর্তমান code 10 সেকেন্ড অপেক্ষা করে পুনরায় connect করার চেষ্টা করে।
7. `Ctrl+C` দিলে main loop বন্ধ হয়।

Python-to-Laravel payload-এর বর্তমান রূপ:

```json
{
  "card_uid": "1001",
  "timestamp": "2026-10-18 08:30:00",
  "device_sn": "DEVICE_SERIAL"
}
```

এখানে `card_uid` হলো device-এ নিবন্ধিত user ID—সাধারণত fingerprint/card owner-এর ID, fingerprint image নয়। ওই ID Laravel-এর student/staff profile-এর পরিচিত ID-র সঙ্গে মেলাতে হবে।

### চালানো

Project root-এ:

```powershell
py pithon.py
```

File name-টি repository-তে `pithon.py` (একটি `y`-সহ); তাই command-এও ওই নাম ব্যবহার করতে হবে।

### Python script-এর বর্তমান সীমাবদ্ধতা

- Device IP/port, serial এবং Laravel URL script-এ hard-coded; environment-specific configuration করতে হলে source পরিবর্তন প্রয়োজন।
- HTTP request-এ timeout বা retry policy দেওয়া নেই। Device capture reconnect করলেও Laravel-এ ব্যর্থ HTTP POST-এর জন্য event queue বা resend mechanism নেই।
- বর্তমান `verify=False` HTTPS certificate verification বন্ধ করে। Production-এ এটি সরিয়ে certificate verification চালু রাখতে হবে।
- Request-এ কোনো API token পাঠানো হয় না। Laravel controller-এও token যাচাই দেখা যায় না; endpoint internet-এ উন্মুক্ত রাখার আগে authentication বা network-level access restriction যোগ করা উচিত।
- Thread-গুলো daemon thread; process বন্ধ হলে তারা আলাদা করে graceful shutdown/cleanup নিশ্চিত করে না।

## Laravel JSON endpoint contract

Route: `routes/web.php`-এ `POST /api/v1/biometric-sync`  
Controller: `app/Http/Controllers/Api/BiometricSyncController.php`  
CSRF configuration: `bootstrap/app.php`-এ `api/*` CSRF exception হিসেবে আছে।

### Request fields

| Field | Required | Validation / অর্থ |
|---|---:|---|
| `card_uid` | হ্যাঁ | String; profile lookup-এর ID |
| `timestamp` | না | Date/time; না দিলে server-এর current time |
| `type` | না | `student`, `employee`, `staff`, `teacher`, অথবা `auto`; default `auto` |
| `device_sn` | না | Device serial/name; না দিলে `DEFAULT_DEVICE` |
| `punch_state` | না | `check_in`, `check_out`, অথবা `auto` |

উদাহরণ:

```powershell
$body = @{
    card_uid  = "1001"
    timestamp = "2026-10-18 08:30:00"
    device_sn = "DEVICE_SERIAL"
} | ConvertTo-Json

Invoke-RestMethod `
    -Method Post `
    -Uri "https://iqraislamimodelmadrasha.com/api/v1/biometric-sync" `
    -ContentType "application/json" `
    -Body $body
```

### Profile matching ও attendance behavior

- `student` বা `auto` হলে student profile-এর `biometric_id`, `admission_no`, `roll_no` দিয়ে lookup হয়।
- `employee`, `staff`, `teacher` বা `auto` হলে staff profile-এর `biometric_id`, `phone`, `national_id` দিয়ে lookup হয়।
- `auto` mode-এ আগে student, পরে staff lookup হয়।
- ঐ দিনের প্রথম punch entry/check-in হিসেবে সংরক্ষিত হয়। Student shift থাকলে shift start-এর ১৫ মিনিট পর থেকে late ধরা হয়; অন্য ক্ষেত্রে default cutoff `09:15:00`। পরের punch-টি exit/check-out হিসেবে update হয়।
- সফল request-এর response-এ `success`, `message`, `record` (name, type, status, entry/exit time, timestamp) ফেরে।
- Matching profile না থাকলে failed biometric log তৈরি করে HTTP `404` ফেরায়।
- Validation error হলে Laravel validation response (সাধারণত `422`) ফেরায়; internal processing error হলে controller HTTP `500` ফেরায়।
- Controller-এ duplicate punch idempotency guard নেই। Retry করলে duplicate event attendance-এর পরবর্তী punch হিসেবে গণনা হয়ে exit time update করতে পারে।
- `punch_state` input validation-এ ընդուն হয়; attendance action-টি controller-এর first-punch/subsequent-punch logic দিয়ে নির্ধারিত হয়।

## Laravel direct device sync

Laravel application-এ Composer dependency হিসেবে `0mithun/php-zkteco` (`composer.json`) আছে। `app/Services/ZKTecoService.php`-এ `Mithun\PhpZkteco\Libs\ZKTeco` দিয়ে device-এ সরাসরি সংযোগ করে `getAttendances()` থেকে record আনা হয়।

Scheduled path:

1. `routes/console.php`-এ `zkteco:sync` command প্রতি ৫ মিনিটে schedule করা।
2. `app/Console/Commands/SyncZKTecoDevices.php` active device-গুলো database থেকে নেয়।
3. `app/Services/AttendanceSyncService.php` প্রতিটি device-এর log নেয়, unique key দিয়ে duplicate বাদ দেয়, student/staff profile খোঁজে, এবং daily attendance processor-এ punch পাঠায়।
4. Device status এবং `device_sync_logs`-এ sync summary রাখা হয়। একই device-এর overlapping sync ঠেকাতে cache lock ব্যবহৃত হয়।

Scheduler চালু রেখে manual sync:

```powershell
php artisan zkteco:sync
```

Windows-এর `start-scheduler.bat` `php artisan schedule:work` চালায়। Laravel scheduler-এর নিয়মিত execution-এর জন্য ওই process চালু রাখতে হবে।

Laravel admin screen-এ device record-এ IP, port, serial number, active flag ও communication key সংরক্ষণ করা হয়। Device port সাধারণত `4370`, তবে actual device configuration-ই অনুসরণ করতে হবে। Laravel server থেকে device-এর LAN IP/port reachable না হলে direct sync কাজ করবে না।

## ZKTeco ADMS push routes

`routes/web.php`-এ নিচের routes রয়েছে:

- `GET /iclock/cdata`: handshake/heartbeat হিসেবে plain-text `OK`
- `POST /iclock/cdata`: tab-separated punch payload নেয়; `SN` query parameter-কে device serial হিসেবে নেয়
- `GET /iclock/getrequest`: plain-text `OK`

`POST /iclock/cdata`-এ expected line format ZKTeco-style tab-separated data (`USER_ID`, `TIMESTAMP`, এরপর optional columns)। Controller profile lookup করে attendance/log লেখে। Device-কে ADMS/Push mode-এ configure করে এই route-গুলোতে পৌঁছানোর মতো network access দিতে হয়।

## Troubleshooting checklist

1. Python machine ও ZKTeco device একই reachable network-এ আছে কি না যাচাই করুন।
2. Configured IP/port device configuration-এর সঙ্গে মেলে কি না দেখুন।
3. Device-এ punch-এর user ID Laravel profile-এর `biometric_id` বা supported lookup field-এ মেলে কি না যাচাই করুন।
4. Python process-এ `connect`, capture, HTTP status/error output দেখুন।
5. Laravel `biometric_device_logs`, `attendances` ও application log দেখুন।
6. Python bridge, Laravel scheduled sync, `biometric:listen` এবং ADMS একই device-এর জন্য একই সময়ে চালু আছে কি না পরীক্ষা করুন।
7. Timezone mismatch থাকলে device time, Python host time ও Laravel application timezone মিলিয়ে নিন।

## Related source files

- [`pithon.py`](./pithon.py) — Python live-capture bridge
- [`routes/web.php`](./routes/web.php) — JSON ও ADMS routes
- [`app/Http/Controllers/Api/BiometricSyncController.php`](./app/Http/Controllers/Api/BiometricSyncController.php) — JSON/ADMS request handling
- [`app/Services/ZKTecoService.php`](./app/Services/ZKTecoService.php) — Laravel-to-device connection
- [`app/Services/AttendanceSyncService.php`](./app/Services/AttendanceSyncService.php) — direct sync processing
- [`app/Console/Commands/SyncZKTecoDevices.php`](./app/Console/Commands/SyncZKTecoDevices.php) — scheduled sync command
- [`routes/console.php`](./routes/console.php) — scheduler frequency
- [`bootstrap/app.php`](./bootstrap/app.php) — CSRF exception configuration
