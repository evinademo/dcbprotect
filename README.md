**Evina DCBProtect Integration Demo**
This project demonstrates the integration of Evina DCBProtect on a demo public hosted website using PHP backend and JavaScript frontend.

**Demo Website URL**: https://demo.setllartech.com/evina/

The demo site contains four flows:
1.	Hosted Page Integration
2.	Redirect Integration
3.	Transparent Integration
4.	Manual Script Integration
   
**Table of Contents**
•	Deployment
•	Integration Flows
   1.	Hosted Page Integration
   2.	Redirect Integration
   3.	Transparent Integration
   4.	Manual Script Integration
•	Fraud and Authentic Testing
•	Difficulties and Solutions

**Deployment**
1.	Requirements
o	PHP 7.4 or higher with cURL enabled
o	HTTPS-enabled server (Evina APIs only work over HTTPS)
o	Modern web browser (Chrome, Edge, Firefox)
o	Evina API Key (provided by Evina)
o	Grant list to avoid any CSP violation:
-	https://notify.dcbprotect.com
-	https://notify.clfldcbprotect.com
-	wss://ws.dcbprotect.com:8080

**Integration Flows**
**1. Hosted Page Integration**
**Description:**
In this flow, the Evina anti-fraud JS is injected directly into the partner’s web page before DOMContentLoaded.

**Step-by-Step Flow:**
1.	End user requests the partner page.
2.	Partner backend calls Evina /script API with a unique transaction ID (ti) and targeted element selector (te), 
3.	Evina returns a JavaScript snippet that must be injected before DOMContentLoaded.
4.	User clicks the protected element (button).
5.	Partner backend calls Evina /check API to validate the transaction using the same ti.
6.	Evina responds with a fraud code (ft).
7.	Partner either allows access (code 1000) or shows a fraud/error alert.
   
**Implementation Notes:**
•	Backend ensures API key is never exposed in frontend.
•	Make sure all the request parameters are encoded before calling Evina API.
•	JS injection must happen before DOMContentLoaded to track all clicks.
•	Example payload for /script API:
$payload = [
    "ti" => $uuid,
    "te" => "#cta_btn",
    "ts" => time(),
    "country" => "AE"
];

**2. Redirection Integration**
**Description:**
In this flow, Evina redirects the end user to an success or failure page based of fraud result. Redirection flow with no extra checks was implemented.

**Step-by-Step Flow:**
1.	End user requests the partner page.
2.	Partner backend calls Evina /script API with success (ru) and failure (rfu) URLs.
3.	Partner hosts a blank HTML page and JS is injected before DOMContentLoaded.
4.	After validation, Evina redirects user automatically to either success or failure URL based of the fraud result.
   
**Implementation Notes:**
•	Backend ensures API key is never exposed in frontend.
•	Make sure all the request parameters are encoded before calling Evina API.
•	Blank HTML page is hosted by partner.
•	JS injection must happen before DOMContentLoaded in the blank HTML page.
•	Evina redirects the end user to success (ru) and failure (rfu) URLs.
•	Payload example:
$payload = [
    "ti"  => $uuid,
    "te"  => "#cta_btn",
    "ts"  => time(),
    "ru"  => "https://demo.setllartech.com/evina/pages/success.html",
    "rfu" => "https://demo.setllartech.com/evina/pages /failure.html",
    "country" => "AE"
];

**3. Transparent Integration**
**Description:**
In this integration, different parties like Content Provider & Partner are involved in the end to end flow. Content Provider loads the script for Evina to gather user intel and grants permission for partner to check the transaction status.

**Step-by-Step Flow:**
1.	End user requests the partner page.
2.	Content Provider backend calls Evina /script API with a unique transaction ID (ti) and targeted element selector (te), permission list (pl) used to grant check right to other DCBprotect usernames for the transaction.
3.	Evina returns a JavaScript snippet that must be injected before DOMContentLoaded.
4.	User is redirected from Content Provider to the Partner page.
5.	On partner page, user clicks the protected element (button).
6.	Partner backend calls Evina /check API to validate the transaction using the same unique transaction ID (ti) & owner (owner) - username of the account who generated the script.
7.	Evina responds with a fraud code (ft).
8.	Partner either allows access (code 1000) or shows a fraud/error alert.

**Implementation Notes:**
•	Secondary API key of username “muhammad_so” was used to call /script API. Secondary API key don’t have /check permission and error message "check-is-not-allowed-for-this-account" will be returned.
•	Transaction ID & Owner valued is required to be passed to partner page to check the transaction. If Owner value is missing "unknown-transaction-id" error message will be returned to partner.
•	Make sure all the request parameters are encoded before calling Evina API.
•	JS injection must happen before DOMContentLoaded in the blank HTML page.
•	Payload example:
$getScript_payload = [
    "ti"  => $uuid,
    "te"  => "#cta_btn",
    "ts"  => time(),
    "pl"  => " ["muhammad"]",
    "country" => "AE"
];

$checkTranx_payload = [
    "ti"  => $uuid,
    "ts"  => time(),
    "owner"  => "muhammad_so",
    "country" => "AE"
];

**4. Manual Script Flow**
**Description:**
In this flow, the HTML page is loaded first and then Evina JS script is added to the HTML page after DOMContentLoaded event is triggered.

**Step-by-Step Flow:**
1.	End user requests the partner page.
2.	HTML page is loaded on end user browser.
3.	Partner backend calls Evina /script API with a unique transaction ID (ti) and targeted element selector (te), 
4.	Evina returns a JavaScript snippet is injected in HTML page and additional events are triggered.
5.	Once Evina JS script is loaded, user clicks the protected element (button).
6.	Partner backend calls Evina /check API to validate the transaction using the same ti.
7.	Evina responds with a fraud code (ft).
8.	Partner either allows access (code 1000) or shows a fraud/error alert.
   
**Implementation Notes:**
•	JS injection must happen before DOMContentLoaded to track all clicks.
•	These two lines of JavaScript must be implemented right after DCBprotect script injection. As DOMContentLoaded event should have already been fired before, it needs to be manually launched using the below code.
    var ev = new Event('DCBProtectRun');
    document.dispatchEvent(ev);
•	Example payload for /script API:
$payload = [
    "ti" => $uuid,
    "te" => "#cta_btn",
    "ts" => time(),
    "country" => "AE"
];

**Fraud and Authentic Testing**

**Authentic Session Example:**
•	Fraud code 1000: No fraud detected.
•	JS alert: Secure User – No fraud detected.
**Fraud Scenarios:**
•	Tried proxy IP/VPN to simulated the failure case. Below error was triggered with use of VPN.
Fraud Code: 2593 - Remote Control Fraud – Device remotely controlled.
•	Incorrect HTML transaction element (te) value in API request.
Fraud Code: 2802 - Bypass Fraud – Evina script bypassed.
