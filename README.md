# dcbprotect
This project demonstrates the integration of Evina DCBProtect on a public demo website using a PHP backend and JavaScript frontend.

Demo Website:
https://demo.setllartech.com/evina/

The demo site contains four flows:

Hosted Page Integration

Redirect Integration

Transparent Integration

Manual Script Integration

Table of Contents

Deployment

Integration Flows

1. Hosted Page Integration

2. Redirect Integration

3. Transparent Integration

4. Manual Script Integration

Fraud and Authentic Testing

Difficulties and Solutions

Deployment
1. Requirements

PHP 7.4+ with cURL enabled

HTTPS-enabled server (Evina APIs work only over HTTPS)

Modern browser (Chrome, Edge, Firefox)

Evina API Key

Grant list to avoid CSP issues:

https://notify.dcbprotect.com

https://notify.clfldcbprotect.com

wss://ws.dcbprotect.com:8080

2. Setup

APIs were initially tested using Postman.

Code was developed and deployed on Hostinger Web Hosting and GitHub.

Integration Flows
1. Hosted Page Integration
Description

Evina anti-fraud JavaScript is injected before DOMContentLoaded directly into the partner’s webpage.

Step-by-Step Flow

End user requests the partner page.

Partner backend calls Evina /script API with:

unique transaction ID (ti)

targeted element selector (te)

Evina returns a JS snippet that must be injected before DOMContentLoaded.

User clicks the protected element.

Backend calls Evina /check API using the same ti.

Evina returns fraud code (ft).

Partner allows access (code 1000) or shows an error.

Implementation Notes

Never expose API key in frontend.

Encode all request parameters before sending to Evina API.

Script must be injected before DOMContentLoaded.

Example payload:

$payload = [
    "ti" => $uuid,
    "te" => "#cta_btn",
    "ts" => time(),
    "country" => "AE"
];

2. Redirect Integration
Description

Evina handles the fraud verification and then redirects automatically to success or failure URLs.

Step-by-Step Flow

End user requests page.

Backend calls /script API with success (ru) and failure (rfu) URLs.

A blank HTML page is hosted and Evina script is injected.

Evina validates user and redirects accordingly.

Implementation Notes

API key must remain backend-only.

Always encode parameters.

Script must be injected before DOMContentLoaded.

Payload example:

$payload = [
    "ti"  => $uuid,
    "te"  => "#cta_btn",
    "ts"  => time(),
    "ru"  => "https://demo.setllartech.com/evina/pages/success.html",
    "rfu" => "https://demo.setllartech.com/evina/pages/failure.html",
    "country" => "AE"
];

3. Transparent Integration
Description

A multi-party flow where the Content Provider loads the script, but the Partner performs the fraud check.

Step-by-Step Flow

End user requests partner page.

Content Provider calls /script API with:

ti, te

permission list (pl) for partner accounts

Evina returns script → injected before DOMContentLoaded.

User is redirected to partner page.

User clicks protected element.

Partner backend calls /check API with:

ti

owner (account that generated script)

Evina responds with fraud code.

Partner allows or blocks access.

Implementation Notes

Secondary API key of muhammad_so used – cannot perform /check, gives:
check-is-not-allowed-for-this-account

ti and owner must be passed to partner page.

Missing owner returns unknown-transaction-id.

Example payloads:

$getScript_payload = [
    "ti"  => $uuid,
    "te"  => "#cta_btn",
    "ts"  => time(),
    "pl"  => '["muhammad"]',
    "country" => "AE"
];

$checkTranx_payload = [
    "ti"     => $uuid,
    "ts"     => time(),
    "owner"  => "muhammad_so",
    "country" => "AE"
];

4. Manual Script Flow
Description

HTML loads first, and Evina JS is added after DOMContentLoaded.

Step-by-Step Flow

User loads partner page.

HTML loads fully.

Backend calls /script API.

Evina script is injected after DOMContentLoaded.

Additional events triggered manually.

User clicks protected element.

Backend calls /check API.

Partner allows or denies access.

Implementation Notes

Script normally must run before DOMContentLoaded, so manual events must fire:

var ev = new Event('DCBProtectRun');
document.dispatchEvent(ev);


Example /script payload:

$payload = [
    "ti" => $uuid,
    "te" => "#cta_btn",
    "ts" => time(),
    "country" => "AE"
];

Fraud and Authentic Testing
Authentic Session Example

Fraud Code 1000 → No fraud detected

Alert: Secure User – No fraud detected

Fraud Scenarios

VPN / Proxy IP

Fraud Code 2593 → Remote Control Fraud – Device remotely controlled

Incorrect te selector

Fraud Code 2802 → Bypass Fraud – Evina script bypassed
