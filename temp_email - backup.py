import requests
import re
import random
import string
import sys
import json
import os
from datetime import datetime

from PyQt6.QtCore import Qt, QTimer, QThread, pyqtSignal
from PyQt6.QtGui import QColor
from PyQt6.QtWidgets import (
    QApplication, QMainWindow, QWidget, QVBoxLayout, QHBoxLayout,
    QLabel, QLineEdit, QPushButton, QListWidget, QListWidgetItem,
    QSplitter, QStatusBar, QFrame, QDialog, QFormLayout,
    QDialogButtonBox, QMessageBox
)
from PyQt6.QtWebEngineWidgets import QWebEngineView

API = "https://api.mail.tm"
HEADERS = {"Accept": "application/ld+json"}
DATA_FILE = os.path.join(os.path.dirname(__file__), "accounts.json")

APP_STYLE = """
QMainWindow, QWidget { background-color: #1e1e2e; color: #cdd6f4; font-family: 'Segoe UI', sans-serif; }
QLineEdit {
    background-color: #313244; color: #cdd6f4; border: 1px solid #45475a; border-radius: 6px;
    padding: 6px 10px; font-size: 13px;
}
QPushButton {
    background-color: #89b4fa; color: #1e1e2e; border: none; border-radius: 6px;
    padding: 6px 14px; font-size: 12px; font-weight: 600; min-width: 70px;
}
QPushButton:hover { background-color: #74c7ec; }
QPushButton:disabled { background-color: #45475a; color: #6c7086; }
QListWidget {
    background-color: #181825; color: #cdd6f4; border: 1px solid #313244; border-radius: 6px; padding: 4px; font-size: 12px;
}
QListWidget::item { padding: 8px 10px; border-radius: 4px; margin: 1px 0px; }
QListWidget::item:selected { background-color: #313244; color: #89b4fa; }
QStatusBar { background-color: #11111b; color: #6c7086; border-top: 1px solid #313244; font-size: 12px; }
QFrame#header { background-color: #181825; border-bottom: 1px solid #313244; padding: 8px 16px; }
QFrame#accounts { background-color: #181825; border-right: 1px solid #313244; }
QDialog { background-color: #1e1e2e; color: #cdd6f4; }
"""


class ApiWorker(QThread):
    done = pyqtSignal(object)

    def __init__(self, method, path, data=None, token=None):
        super().__init__()
        self.method = method
        self.path = path
        self.data = data
        self.token = token

    def run(self):
        try:
            headers = {**HEADERS}
            if self.data is not None:
                headers["Content-Type"] = "application/json"
            if self.token:
                headers["Authorization"] = f"Bearer {self.token}"
            r = requests.request(self.method, f"{API}{self.path}", headers=headers, json=self.data, timeout=15)
            self.done.emit(r.json())
        except Exception as e:
            self.done.emit({"error": str(e)})


class LoginDialog(QDialog):
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setWindowTitle("Login")
        self.setFixedSize(420, 200)
        self.setStyleSheet(APP_STYLE)
        self.result_data = None

        layout = QVBoxLayout(self)
        layout.setSpacing(12)

        QLabel("Enter existing mail.tm credentials:")
        form = QFormLayout()
        self.email_input = QLineEdit()
        self.email_input.setPlaceholderText("you@web-library.net")
        self.pw_input = QLineEdit()
        self.pw_input.setPlaceholderText("password")
        self.pw_input.setEchoMode(QLineEdit.EchoMode.Password)
        form.addRow("Email:", self.email_input)
        form.addRow("Password:", self.pw_input)
        layout.addLayout(form)

        btn_box = QDialogButtonBox(QDialogButtonBox.StandardButton.Ok | QDialogButtonBox.StandardButton.Cancel)
        btn_box.accepted.connect(self._accept)
        btn_box.rejected.connect(self.reject)
        layout.addWidget(btn_box)

    def _accept(self):
        email = self.email_input.text().strip()
        pw = self.pw_input.text().strip()
        if not email or not pw:
            QMessageBox.warning(self, "Error", "Email and password required")
            return
        self.result_data = (email, pw)
        self.accept()


class TempEmailApp(QMainWindow):
    def __init__(self):
        super().__init__()
        self.token = None
        self.email = None
        self.password = None
        self.messages = {}
        self.seen_ids = set()
        self.message_ids = []
        self.accounts = []
        self._workers = []
        self._load_accounts()

        self.setWindowTitle("Temp Email")
        self.setMinimumSize(1000, 600)
        self.resize(1200, 700)
        self.setStyleSheet(APP_STYLE)
        self._build_ui()
        self.show()

        # Start with first account or create new
        QTimer.singleShot(100, self._auto_start)

    def _load_accounts(self):
        try:
            if os.path.exists(DATA_FILE):
                with open(DATA_FILE) as f:
                    self.accounts = json.load(f)
        except:
            self.accounts = []

    def _save_accounts(self):
        with open(DATA_FILE, "w") as f:
            json.dump(self.accounts, f, indent=2)

    def _build_ui(self):
        central = QWidget()
        self.setCentralWidget(central)
        layout = QVBoxLayout(central)
        layout.setContentsMargins(0, 0, 0, 0)
        layout.setSpacing(0)

        # HEADER
        header = QFrame()
        header.setObjectName("header")
        hh = QHBoxLayout(header)
        hh.setContentsMargins(16, 8, 16, 8)

        title = QLabel("Temp Email")
        title.setStyleSheet("font-size: 18px; font-weight: bold; color: #89b4fa;")
        hh.addWidget(title)
        hh.addSpacing(16)

        hh.addWidget(QLabel("Email:"))
        self.email_input = QLineEdit()
        self.email_input.setReadOnly(True)
        hh.addWidget(self.email_input, 1)

        hh.addWidget(QLabel("Pass:"))
        self.pw_input = QLineEdit()
        self.pw_input.setReadOnly(True)
        self.pw_input.setMaximumWidth(120)
        hh.addWidget(self.pw_input)

        self.copy_btn = QPushButton("Copy")
        self.copy_btn.clicked.connect(lambda: self._copy())
        hh.addWidget(self.copy_btn)

        self.new_btn = QPushButton("New")
        self.new_btn.clicked.connect(self._create_new)
        hh.addWidget(self.new_btn)

        self.login_btn = QPushButton("Login")
        self.login_btn.setStyleSheet("background-color: #a6e3a1; color: #1e1e2e; border: none; border-radius: 6px; padding: 6px 14px; font-size: 12px; font-weight: 600;")
        self.login_btn.clicked.connect(self._show_login)
        hh.addWidget(self.login_btn)

        layout.addWidget(header)

        # OTP BANNER
        self.otp_banner = QLabel("")
        self.otp_banner.setStyleSheet("background-color: #f38ba8; color: #1e1e2e; font-size: 16px; font-weight: bold; padding: 10px; text-align: center;")
        self.otp_banner.setAlignment(Qt.AlignmentFlag.AlignCenter)
        self.otp_banner.setVisible(False)
        self.otp_banner.setFixedHeight(40)
        layout.addWidget(self.otp_banner)

        # MAIN CONTENT
        outer = QSplitter(Qt.Orientation.Horizontal)

        # ACCOUNTS PANEL
        acct_panel = QFrame()
        acct_panel.setObjectName("accounts")
        acct_panel.setFixedWidth(200)
        av = QVBoxLayout(acct_panel)
        av.setContentsMargins(8, 8, 8, 8)

        ah = QHBoxLayout()
        ah.addWidget(QLabel("Accounts"))
        ah.addStretch()
        self.acct_count = QLabel("0")
        self.acct_count.setStyleSheet("color: #6c7086; font-size: 11px;")
        ah.addWidget(self.acct_count)
        av.addLayout(ah)

        self.acct_list = QListWidget()
        self.acct_list.currentRowChanged.connect(self._switch_account)
        av.addWidget(self.acct_list)

        del_btn = QPushButton("Delete")
        del_btn.setStyleSheet("background-color: #f38ba8; color: #1e1e2e;")
        del_btn.clicked.connect(self._delete_account)
        av.addWidget(del_btn)

        outer.addWidget(acct_panel)

        # INBOX
        left = QWidget()
        lv = QVBoxLayout(left)
        lv.setContentsMargins(8, 8, 4, 8)
        lh = QHBoxLayout()
        lh.addWidget(QLabel("Inbox"))
        lh.addStretch()
        self.count_lbl = QLabel("0")
        self.count_lbl.setStyleSheet("color: #6c7086;")
        lh.addWidget(self.count_lbl)
        lv.addLayout(lh)

        self.inbox = QListWidget()
        self.inbox.currentRowChanged.connect(self._show_msg)
        lv.addWidget(self.inbox)

        bh = QHBoxLayout()
        self.refresh_btn = QPushButton("Refresh")
        self.refresh_btn.clicked.connect(self._manual_refresh)
        bh.addWidget(self.refresh_btn)
        self.poll_btn = QPushButton("Stop Poll")
        self.poll_btn.setEnabled(False)
        self.poll_btn.clicked.connect(self._toggle_poll)
        bh.addWidget(self.poll_btn)
        lv.addLayout(bh)
        outer.addWidget(left)

        # MESSAGE VIEW
        right = QWidget()
        rv = QVBoxLayout(right)
        rv.setContentsMargins(4, 8, 8, 8)
        self.msg_title = QLabel("Select a message")
        self.msg_title.setStyleSheet("font-size: 13px; font-weight: 600; color: #a6adc8; padding-bottom: 4px;")
        rv.addWidget(self.msg_title)
        self.web = QWebEngineView()
        self.web.setHtml("<html><body style='background:#1e1e2e;color:#6c7086;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;'><p>Select a message</p></body></html>")
        rv.addWidget(self.web)
        outer.addWidget(right)

        outer.setSizes([200, 350, 650])
        layout.addWidget(outer, 1)

        # STATUS BAR
        self.sb = QStatusBar()
        self.sb_lbl = QLabel("Ready")
        self.sb.addPermanentWidget(self.sb_lbl)
        self.setStatusBar(self.sb)

        # Polling timer
        self.poll_timer = QTimer()
        self.poll_timer.timeout.connect(self._do_poll)

    def _auto_start(self):
        if self.accounts:
            self._switch_to(0)
        else:
            self._create_new()

    def _set_status(self, text):
        self.sb_lbl.setText(text)

    def _api(self, method, path, data=None, callback=None):
        w = ApiWorker(method, path, data, self.token)
        w.done.connect(callback if callback else self._default_callback)
        w.finished.connect(lambda: self._workers.remove(w) if w in self._workers else None)
        self._workers.append(w)
        w.start()

    def _default_callback(self, result):
        pass

    def _create_new(self):
        self.login_btn.setEnabled(False)
        self._set_status("Creating mailbox...")
        self._api("GET", "/domains", callback=self._on_domains)

    def _on_domains(self, data):
        if "error" in data or "hydra:member" not in data:
            self._set_status("Failed: domains")
            self.new_btn.setEnabled(True)
            self.login_btn.setEnabled(True)
            return
        domain = data["hydra:member"][0]["domain"]
        local = "".join(random.choices(string.ascii_lowercase + string.digits, k=10))
        pw = "Temp" + "".join(random.choices(string.digits, k=5)) + "!"
        addr = f"{local}@{domain}"
        self._tmp_addr = addr
        self._tmp_pw = pw
        self._api("POST", "/accounts", {"address": addr, "password": pw}, callback=self._on_account_created)

    def _on_account_created(self, data):
        if "error" in data:
            self._set_status("Failed: create")
            self.new_btn.setEnabled(True)
            self.login_btn.setEnabled(True)
            return
        self._api("POST", "/token", {"address": self._tmp_addr, "password": self._tmp_pw}, callback=self._on_token)

    def _on_token(self, data):
        if "error" in data or "token" not in data:
            self._set_status("Failed: token")
            self.new_btn.setEnabled(True)
            self.login_btn.setEnabled(True)
            return
        self.token = data["token"]
        self.email = self._tmp_addr
        self.password = self._tmp_pw

        # Save
        self.accounts.insert(0, {"email": self.email, "password": self.password, "created": datetime.now().isoformat()})
        self._save_accounts()

        self._activate_account()
        self._set_status(f"Created: {self.email}")

    def _show_login(self):
        dlg = LoginDialog(self)
        if dlg.exec() != QDialog.DialogCode.Accepted:
            return
        email, pw = dlg.result_data
        self._set_status("Logging in...")
        self.login_btn.setEnabled(False)
        self.new_btn.setEnabled(False)
        self._api("POST", "/token", {"address": email, "password": pw}, callback=lambda d: self._on_login(d, email, pw))

    def _on_login(self, data, email, pw):
        self.login_btn.setEnabled(True)
        self.new_btn.setEnabled(True)
        if "error" in data or "token" not in data:
            QMessageBox.warning(self, "Error", "Invalid email or password")
            self._set_status("Login failed")
            return
        self.token = data["token"]
        self.email = email
        self.password = pw

        for a in self.accounts:
            if a["email"] == email:
                self.accounts.remove(a)
                break
        self.accounts.insert(0, {"email": email, "password": pw, "created": datetime.now().isoformat()})
        self._save_accounts()

        self._activate_account()
        self._set_status(f"Logged in: {email}")

    def _activate_account(self):
        self.seen_ids.clear()
        self.messages.clear()
        self.message_ids.clear()
        self.email_input.setText(self.email or "")
        self.pw_input.setText(self.password or "")
        self.inbox.clear()
        self.web.setHtml("<html><body style='background:#1e1e2e;color:#6c7086;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;'><p>No messages</p></body></html>")
        self.msg_title.setText("Select a message")
        self.otp_banner.setVisible(False)
        self.count_lbl.setText("0")
        self.new_btn.setEnabled(True)
        self.login_btn.setEnabled(True)
        self._refresh_accounts_list()
        self._start_polling()

    def _refresh_accounts_list(self):
        self.acct_list.blockSignals(True)
        self.acct_list.clear()
        active_idx = -1
        for i, a in enumerate(self.accounts):
            item = QListWidgetItem(a["email"])
            if a["email"] == self.email:
                item.setForeground(QColor("#89b4fa"))
                f = item.font()
                f.setBold(True)
                item.setFont(f)
                active_idx = i
            self.acct_list.addItem(item)
        self.acct_list.setCurrentRow(active_idx)
        self.acct_list.blockSignals(False)
        self.acct_count.setText(str(len(self.accounts)))

    def _switch_account(self, row):
        if row < 0 or row >= len(self.accounts):
            return
        acct = self.accounts[row]
        if acct["email"] == self.email:
            return
        self._set_status(f"Switching...")
        self.acct_list.setCurrentRow(-1)  # Prevent double-trigger
        self._api("POST", "/token", {"address": acct["email"], "password": acct["password"]},
                  callback=lambda d: self._on_switch(d, acct))

    def _on_switch(self, data, acct):
        if "error" in data or "token" not in data:
            self._set_status(f"Switch failed for {acct['email']}")
            self._refresh_accounts_list()
            return
        self.token = data["token"]
        self.email = acct["email"]
        self.password = acct["password"]
        self._activate_account()
        self._set_status(f"Active: {self.email}")

    def _delete_account(self):
        row = self.acct_list.currentRow()
        if row < 0 or row >= len(self.accounts):
            return
        acct = self.accounts[row]
        confirm = QMessageBox.question(self, "Delete", f"Delete {acct['email']}?",
                                       QMessageBox.StandardButton.Yes | QMessageBox.StandardButton.No)
        if confirm == QMessageBox.StandardButton.Yes:
            self.accounts.pop(row)
            self._save_accounts()
            self._refresh_accounts_list()
            if acct["email"] == self.email:
                self.email = None
                self.password = None
                self.token = None
                self.email_input.setText("")
                self.pw_input.setText("")
                self.inbox.clear()
                self.count_lbl.setText("0")
                self.otp_banner.setVisible(False)
                self._stop_polling()
                self._set_status("Account deleted")
                # Switch to first available
                if self.accounts:
                    self._switch_to(0)

    def _switch_to(self, index):
        if index < len(self.accounts):
            acct = self.accounts[index]
            self._api("POST", "/token", {"address": acct["email"], "password": acct["password"]},
                      callback=lambda d: self._on_switch(d, acct))

    def _copy(self):
        QApplication.clipboard().setText(self.email_input.text())
        self._set_status("Copied!")

    def _start_polling(self):
        self.poll_timer.start(5000)
        self.poll_btn.setEnabled(True)
        self.poll_btn.setText("Stop Poll")
        self._do_poll()

    def _stop_polling(self):
        self.poll_timer.stop()
        self.poll_btn.setEnabled(False)
        self.poll_btn.setText("Stopped")

    def _toggle_poll(self):
        if self.poll_timer.isActive():
            self.poll_timer.stop()
            self.poll_btn.setText("Start Poll")
            self._set_status("Polling stopped")
        else:
            self.poll_timer.start(5000)
            self.poll_btn.setText("Stop Poll")
            self._do_poll()

    def _manual_refresh(self):
        self._set_status("Refreshing...")
        self._do_poll()

    def _do_poll(self):
        if not self.token:
            return
        self._api("GET", "/messages", callback=self._on_messages)

    def _on_messages(self, data):
        if "error" in data:
            return
        msgs = data.get("hydra:member", [])
        if not msgs:
            return
        for msg in reversed(msgs):
            mid = msg.get("id")
            if mid in self.seen_ids:
                continue
            self.seen_ids.add(mid)
            self.messages[mid] = msg
            self.message_ids.insert(0, mid)

            f = msg.get("from", {})
            sender = f.get("name", "") or f.get("address", "Unknown")
            subject = msg.get("subject", "(No Subject)")
            intro = msg.get("intro", "") or ""
            combined = intro + " " + subject
            m = re.search(r'(?:code|otp|is|verification)\s*:?\s*(\d{4,8})', combined, re.IGNORECASE)
            if not m:
                m = re.search(r'\b(\d{6})\b', combined)
            otp = m.group(1) if m else ""

            display = f"{sender}\n{subject}"
            if otp:
                display += f"\n>>> OTP: {otp}"

            item = QListWidgetItem(display)
            if otp:
                item.setForeground(QColor("#f38ba8"))
                f = item.font()
                f.setBold(True)
                item.setFont(f)
                self.otp_banner.setText(f">>>  OTP Code: {otp}  <<<")
                self.otp_banner.setVisible(True)
                self._set_status(f">>> OTP: {otp}")
            else:
                self._set_status(f"New: {subject}")

            self.inbox.insertItem(0, item)

        self.count_lbl.setText(str(len(self.message_ids)))

    def _show_msg(self, row):
        if row < 0 or row >= len(self.message_ids):
            return
        mid = self.message_ids[row]
        msg = self.messages.get(mid)
        if not msg:
            return

        self._api("GET", msg["@id"], callback=self._on_full_msg)

    def _on_full_msg(self, full):
        f = full.get("from", {})
        sender = f.get("name", "") or f.get("address", "")
        subject = full.get("subject", "")
        date = full.get("createdAt", "")
        html_raw = full.get("html", "")
        text_raw = full.get("text", [])

        def _join(v):
            if isinstance(v, list):
                return " ".join(str(x) for x in v)
            return str(v or "")

        html_str = _join(html_raw)
        text_str = _join(text_raw)

        self.msg_title.setText(f"{sender} — {subject}")

        if html_str and not html_str.isspace():
            body = html_str
        elif text_str and not text_str.isspace():
            body = f"<pre style='white-space:pre-wrap;color:#cdd6f4;'>{text_str}</pre>"
        else:
            body = "<p style='color:#6c7086;'>(No body content)</p>"

        full_text = html_str + " " + text_str + " " + (subject or "")
        m = re.search(r'(?:code|otp|is|verification)\s*:?\s*(\d{4,8})', full_text, re.IGNORECASE)
        if not m:
            m = re.search(r'\b(\d{6})\b', full_text)
        otp_html = ""
        if m:
            otp_html = f"<div style='background:#f38ba8;color:#1e1e2e;padding:10px 16px;border-radius:8px;font-size:22px;font-weight:bold;text-align:center;margin-bottom:12px;'>OTP: {m.group(1)}</div>"

        self.web.setHtml(f"""
        <html><body style="background:#1e1e2e;color:#cdd6f4;font-family:sans-serif;padding:16px;margin:0;">
            {otp_html}
            <div style="color:#a6adc8;font-size:12px;margin-bottom:12px;">
                From: {sender} | {date}
            </div>
            <div style="line-height:1.6;">{body}</div>
        </body></html>
        """)

    def closeEvent(self, event):
        self.poll_timer.stop()
        event.accept()


if __name__ == "__main__":
    import traceback
    try:
        app = QApplication(sys.argv)
        win = TempEmailApp()
        sys.exit(app.exec())
    except Exception:
        with open("crash_log.txt", "w") as f:
            traceback.print_exc(file=f)
        raise
