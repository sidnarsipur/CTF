from mitmproxy import http
import string
import time
import random

rates = {}

def block_request(flow):
    flow.response = http.HTTPResponse.make(
        403,
        """<html>
        <head><title>Access denied</title></head>
        <body>
        <h1>Access denied</h1>
        <p>Your request has been blocked.</p>
        </body>
        </html>""",
        {"content-type": "text/html"},
    )

def rate_limit(flow):
    flow.response = http.HTTPResponse.make(
        429,
        """<html>
        <head><title>Access denied</title></head>
        <body>
        <h1>Access denied</h1>
        <p>The client has sent too many requests in the last minute. Please wait.</p>
        </body>
        </html>""",
        {"content-type": "text/html"},
    )

def check_string(s, level=3):
    if level == 0:
        return True
    if level == 1:
        return all([i in string.printable for i in s])
    if level == 2:
        ok = string.digits + string.ascii_letters + " {}\":_()[],"
        return all([i in ok for i in s])
    else:
        ok = string.digits + string.ascii_letters + " _-."
        return all([i in ok for i in s])

def request(flow):
    try:
        # global rates
        # max_per_minute = 10
        # addr = flow.client_conn.address[0]
        # if addr not in rates:
        #     rates[addr] = []
        # rates[addr] = rates[addr][-max_per_minute::] + [time.time()]

        # if len(rates[addr]) >= max_per_minute and time.time() - rates[addr][0] < 60:
        #     rate_limit(flow)
        #     return

        if flow.request.method not in ["GET", "POST"]:
            block_request(flow)
            return
        
        if flow.request.method == "POST":
            content_type = flow.request.headers.get("content-type").split(";")[0].lower()
            valid_types = ["application/x-www-form-urlencoded", "multipart/form-data"]
            if content_type not in valid_types:
                block_request(flow)
                return

        params = list(flow.request.query.items()) + list(flow.request.urlencoded_form.items()) + list(flow.request.multipart_form.items()) + list(flow.request.cookies.items())

        for t in params:
            key, val = t[0], t[1]
            try:
                key = key.decode(encoding="ascii", errors="ignore")
            except: pass
            try:
                val = val.decode(encoding="ascii", errors="ignore")
            except: pass

            if key in ["file", "file_input", "upload"]:
                if not check_string(val, level=0):
                    block_request(flow)
                    return
            elif key in ["password", "pwd", "pass", "m"]:
                if not check_string(val, level=1):
                    block_request(flow)
                    return
            elif key in ["selections"]:
                if not check_string(val, level=2):
                    block_request(flow)
                    return
            else:
                if not check_string(key) or not check_string(val):
                    block_request(flow)
                    return
    except:
        block_request(flow)

def response(flow):
    time.sleep(random.random() * 0.5)
    content = flow.response.content.decode(errors="ignore").lower()
    if "metactf" in content or "flag" in content or "twv0" in content or "ftc" in content:
        block_request(flow)
    flow.response.headers["X-Secured-By"] = str("Web's Amazing Firewall")
