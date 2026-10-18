import os
import time
import requests
import threading
from zk import ZK

# Office Attendance Sync API Endpoint
# Change to your live office server domain in production or set ATTENDANCE_API_URL env variable
API_URL = os.getenv("ATTENDANCE_API_URL", "http://127.0.0.1:8000/api/v1/biometric-sync")

# Configure your office ZKTeco biometric devices here
DEVICES = [
    {"ip": "192.168.10.201", "port": 4370, "sn": "OFFICE_MAIN_ENTRY"},
    {"ip": "192.168.10.202", "port": 4371, "sn": "OFFICE_BRANCH_ENTRY"}
]

def send_to_server(device, user_id, timestamp):
    payload = {
        "card_uid": str(user_id),
        "timestamp": timestamp.strftime('%Y-%m-%d %H:%M:%S'),
        "device_sn": device['sn'],
        "type": "employee"
    }
    headers = {
        'User-Agent': 'Attendify-BiometricBridge/1.0 (Windows; Office)'
    }
    
    try:
        response = requests.post(API_URL, json=payload, headers=headers, verify=False, timeout=10)
        if response.status_code == 200:
            print(f"[{device['ip']}] Success! Server: {response.text.strip()}")
        else:
            print(f"[{device['ip']}] Failed to sync. Error: {response.status_code} - {response.text.strip()}")
    except Exception as e:
        print(f"[{device['ip']}] Server connection error: {e}")

def live_capture_device(device):
    zk = ZK(device['ip'], port=device['port'], timeout=10)
    conn = None
    
    while True:
        try:
            print(f"[{device['ip']}] Connecting for Live Capture...")
            conn = zk.connect()
            print(f"[{device['ip']}] Connected! Waiting for employee punches...")
            
            # Start live capture
            for attendance in conn.live_capture():
                if attendance is None:
                    continue
                
                print(f"[{device['ip']}] Employee Fingerprint Detected! User ID: {attendance.user_id}")
                # Send data immediately to Attendance server
                send_to_server(device, attendance.user_id, attendance.timestamp)
                
        except Exception as e:
            print(f"[{device['ip']}] Error: {e}. Retrying in 10 seconds...")
            time.sleep(10)
        finally:
            if conn:
                try:
                    conn.disconnect()
                except:
                    pass

if __name__ == "__main__":
    print("=======================================================")
    print("  Attendify Office Suite - Biometric Real-time Bridge  ")
    print(f"  Target Server: {API_URL}")
    print("=======================================================")
    
    threads = []
    # Start a separate daemon thread for each biometric device
    for dev in DEVICES:
        t = threading.Thread(target=live_capture_device, args=(dev,))
        t.daemon = True
        t.start()
        threads.append(t)
        
    try:
        # Keep main program running
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\nStopping Office Biometric Live Sync...")