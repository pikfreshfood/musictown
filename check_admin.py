import requests

urls = [
    'https://cfschools.net.ng/wp-admin',
    'https://cfschools.net.ng/wp-login.php',
    'https://cfschools.net.ng/admin',
    'https://cfschools.net.ng/login',
    'https://cfschools.net.ng/login-2',
    'https://cfschools.net.ng/administrator',
    'https://cfschools.net.ng/wp-admin/index.php',
]

for url in urls:
    try:
        resp = requests.get(url, headers={'User-Agent': 'Mozilla/5.0'}, timeout=10, allow_redirects=True)
        title = ''
        if '<title>' in resp.text:
            title = resp.text.split('<title>')[1].split('</title>')[0]
        print(f'{url}: status={resp.status_code}, final_url={resp.url}, title={title[:80]}')
    except Exception as e:
        print(f'{url}: ERROR {e}')
