import time
import requests
import threading
from zk import ZK

API_URL = "https://iqraislamimodelmadrasha.com/api/v1/biometric-sync"

DEVICES = [
    {"ip": "192.168.10.201", "port": 4370, "sn": "K40_DEVICE_1"},
    {"ip": "192.168.10.202", "port": 4371, "sn": "K40_DEVICE_2"}
]

def send_to_server(device, user_id, timestamp):
    payload = {
        "card_uid": str(user_id),
        "timestamp": timestamp.strftime('%Y-%m-%d %H:%M:%S'),
        "device_sn": device['sn']
    }
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
    }
    
    try:
        response = requests.post(API_URL, json=payload, headers=headers, verify=False)
        if response.status_code == 200:
            print(f"[{device['ip']}] Success! Server: {response.text.strip()}")
        else:
            print(f"[{device['ip']}] Failed to sync. Error: {response.status_code} - {response.text}")
    except Exception as e:
        print(f"[{device['ip']}] Server connection error: {e}")

def live_capture_device(device):
    zk = ZK(device['ip'], port=device['port'], timeout=10)
    conn = None
    
    while True:
        try:
            print(f"[{device['ip']}] Connecting for Live Capture...")
            conn = zk.connect()
            print(f"[{device['ip']}] Connected! Waiting for fingers...")
            
            # Start live capture
            for attendance in conn.live_capture():
                if attendance is None:
                    continue
                
                print(f"[{device['ip']}] Fingerprint Detected! User: {attendance.user_id}")
                # Send data immediately to live server
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
    print("--- Starting Live ZKTeco Sync (Real-time) ---")
    
    threads = []
    # Start a separate thread for each device
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
        print("\nStopping Live Sync...")
 