"""
Auto-fill the admission form on cfschools.net.ng with dummy test data.
"""
import re
import requests

BASE_URL = 'https://cfschools.net.ng'
FORM_URL = BASE_URL + '/admission-form/'
AJAX_URL = BASE_URL + '/wp-admin/admin-ajax.php'

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language': 'en-US,en;q=0.5',
    'Referer': FORM_URL,
    'Origin': BASE_URL,
}

sess = requests.Session()
sess.headers.update({
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
})

# Step 1: Get the form page and extract nonces
print("[1] Fetching form page...")
resp = sess.get(FORM_URL, headers=HEADERS, timeout=15)
html = resp.text

# Extract all nonces
school_nonce = re.search(r'name="school_id"[^>]*data-nonce="([^"]+)"', html)
class_nonce = re.search(r'name="class_id"[^>]*data-nonce="([^"]+)"', html)
form_nonce = re.search(r'name="wlsm-submit-registration"\s+value="([^"]+)"', html)

if not all([school_nonce, class_nonce, form_nonce]):
    print("ERROR: Could not extract nonces")
    exit(1)

school_nonce = school_nonce.group(1)
class_nonce = class_nonce.group(1)
form_nonce = form_nonce.group(1)

print(f"  School nonce: {school_nonce}")
print(f"  Class nonce: {class_nonce}")
print(f"  Form nonce: {form_nonce}")

# Step 2: Fetch classes for a school
print("\n[2] Fetching classes for school_id=2 (LOKOGOMA SECONDARY)...")
ajax_headers = {
    'User-Agent': HEADERS['User-Agent'],
    'Accept': '*/*',
    'Accept-Language': 'en-US,en;q=0.9',
    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    'X-Requested-With': 'XMLHttpRequest',
    'Referer': FORM_URL,
    'Origin': BASE_URL,
}

data = {
    'action': 'wlsm-p-get-school-classes',
    'nonce': school_nonce,
    'school_id': '2',
}
resp = sess.post(AJAX_URL, data=data, headers=ajax_headers, timeout=15)
print(f"  Status: {resp.status_code}")
print(f"  Response: {resp.text[:500]}")

classes = resp.json()
print(f"  Found {len(classes)} classes")
for c in classes[:10]:
    print(f"    ID={c['class']['ID']}: {c['class']['label']}")

# Take the first class
if classes:
    class_id = classes[0]['class']['ID']
    print(f"\n  Using class_id={class_id}")
else:
    print("ERROR: No classes found")
    exit(1)

# Step 3: Fetch sections for the class
print(f"\n[3] Fetching sections for school_id=2, class_id={class_id}...")
data = {
    'action': 'wlsm-p-get-class-sections',
    'nonce': class_nonce,
    'school_id': '2',
    'class_id': class_id,
}
resp = sess.post(AJAX_URL, data=data, headers=ajax_headers, timeout=15)
print(f"  Status: {resp.status_code}")
print(f"  Response: {resp.text[:500]}")

try:
    sections = resp.json()
    print(f"  Found {len(sections)} sections")
    for s in sections[:10]:
        print(f"    ID={s['ID']}: {s['label']}")
    section_id = sections[0]['ID']
    print(f"  Using section_id={section_id}")
except Exception as e:
    print(f"  Could not parse sections: {e}")
    print("  Trying to extract from HTML...")
    # Some responses might be HTML
    section_id = '1'

# Step 4: Submit the form
print("\n[4] Submitting form with dummy data...")

import random
import string

def random_str(prefix='test', length=6):
    return prefix + ''.join(random.choices(string.ascii_lowercase + string.digits, k=length))

username = random_str('stu_')
parent_username = random_str('par_')

form_data = {
    'wlsm-submit-registration': form_nonce,
    'action': 'wlsm-p-submit-registration',
    'school_id': '2',
    'name': 'Test Student John',
    'gender': 'male',
    'dob': '15/06/2012',
    'religion': 'Christianity',
    'caste': '',
    'blood_group': 'O+',
    'address': '123 Test Street, Wuse District',
    'phone': '08012345678',
    'email': f'{username}@example.com',
    'city': 'Abuja',
    'state': 'FCT',
    'country': 'Nigeria',
    'id_number': '',
    'class_id': str(class_id),
    'section_id': str(section_id),
    'father_name': 'Father John Doe',
    'father_phone': '08012345679',
    'father_occupation': 'Civil Engineer',
    'mother_name': 'Mother Jane Doe',
    'mother_phone': '08012345680',
    'mother_occupation': 'Teacher',
    'username': username,
    'login_email': f'{username}@example.com',
    'password': 'TestPass123!',
    'allow_parent_login': '1',
    'parent_username': parent_username,
    'parent_login_email': f'{parent_username}@example.com',
    'parent_password': 'ParentPass123!',
    'survey': 'google',
}

resp = sess.post(AJAX_URL, data=form_data, headers=ajax_headers, timeout=30)
print(f"  Status: {resp.status_code}")
print(f"  Response: {resp.text[:1000]}")

try:
    result = resp.json()
    if result.get('success'):
        print("\n✓ SUCCESS: Form submitted successfully!")
    else:
        print(f"\n✗ FAILED: {result}")
except Exception as e:
    print(f"\n  Response (raw): {resp.text[:1000]}")
