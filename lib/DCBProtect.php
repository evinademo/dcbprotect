<?php

class DCBProtect
{
    private $apiMainKey = "uWQTkF0GlI1hDpjpTwLnkDbhojhPsb6iYaDKgAdfkfng5kfkZcrD2pnsVV2O0xd6"; // API key for Hosted Page, Redirect, and /check API integration
    private $apiSecondaryKey = "1ZBx6WzRpMNp5vDDVTn7NPrVkkDvX4y2lQbSqTXYjYtKlGyKvuieePM5BWIbaTkP"; // API key for transparent integration
    private $country = "AE"; // Default AE country
    private $urlScript = "https://api.clfldcbprotect.com/v1/script"; // Cloudflare Get script API URL
    private $urlCheck = "https://api.clfldcbprotect.com/v1/check"; // Cloudflare check transactionAPI URL
    private $pl = ["muhammad"]; //Permission list for transparent integration
    private $type; 
    private $targetElement = "#cta_btn"; //HTML element to check transaction status

    public function __construct($type = "")
    {
        $this->type = $type; // Assigning type of integration
    }

    /**
     * Generic function to Get the dynamic script
     */
    public function getScript()
    {
        $ti = $this->uuid_v4(); // UUID as transaction ID
        $apiKey = $this->apiMainKey; // Default API key of user "muhammad"
        
        // Generic payload for Get script API call
        $payload = [
            "ti" => $ti,
            "ts" => time(),
            "country" => $this->country,
        ];
        
        // Additional paramters for Hosted Page integration
        if ($this->type === "hosted") {
            $payload["te"] = $this->targetElement;
        }
        
        // Additional paramters for Redirect integration
        if ($this->type === "redirect") {
            $payload["ru"] = "https://demo.setllartech.com/evina/pages/success.html"; // Success page URL
            $payload["rfu"] = "https://demo.setllartech.com/evina/pages/failure.html"; // Failure page URL
        }

        // Additional paramters for Transparent integration
        if ($this->type === "transparent") {
            $payload["te"] = $this->targetElement;
            $payload["pl"] = $this->pl; // Assging check privilage to other user 'muhammad'
            $apiKey = $this->apiSecondaryKey; // Using user 'muhammad_so' API key for transparent integration
        }
        
        // Calling Evina Get Script API
        $response = $this->callEvina($apiKey, $this->urlScript, $payload); 
        $json = json_decode($response, true);

        // Returning the response back to backend call
        return [
            "ti" => $ti,
            "script" => $json["s"] ?? "",
        ];
    }

    /**
     * Functino to Check transaction status
     */
    public function checkStatus($ti, $owner = null)
    {
        if (!$ti) {
            return [
                "ti" => $ti,
                "success" => false,
                "message" => "Missing transaction ID",
            ];
        }

        $payload = [
            "ti" => $ti,
            "ts" => time(),
            "country" => $this->country,
        ];
        
        // Passing owner value in transparent integration for partner to check status
        if ($owner) {
            $payload["owner"] = "$owner";
        }

        $response = $this->callEvina( $this->apiMainKey, $this->urlCheck, $payload);
        $json = json_decode($response, true);

        $fraudCode = $json["ft"] ?? null;

        return [
            "success" => true,
            "fraud_code" => $fraudCode,
            "message" => $this->fraudMessage($fraudCode),
        ];
    }

    /**
     * Core cURL function to URL encode and call API
     */
    private function callEvina($apiKey, $endpoint, $payload)
    {
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-EVINA-APIKEY: {$apiKey}",
            "Content-Type: application/x-www-form-urlencoded",
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * Generate UUID v4 for transaction ID
     */
    private function uuid_v4()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf("%s%s-%s-%s-%s-%s%s%s", str_split(bin2hex($data), 4));
    }

    /**
     * Map fraud code to human readable description
     */
    private function fraudMessage($code)
    {
        if (!$code) {
            return "No fraud code returned.";
        }

        $code = (int) $code;
        
        // Mapping specific codes
        $specific = [
            1000 => "Authentic – No fraud detected.",
            2601 => "Blacklisted App detected.",
            2602 => "Blacklisted Domain detected.",
            2603 => "Suspicious App behavior detected.",
            2604 => "Abnormal fraudulent behavior.",
            2605 => "Abnormal fraudulent behavior (updated rule).",
            3101 => "Accidental click – browser issue.",
            3102 => "Accidental click – Pocket click.",
            3103 => "Accidental movement – gen 1.",
            3104 => "Accidental movement – gen 2.",
            3105 => "Accidental click – no movement detected.",
            3106 => "Accidental movement – gen 3.",
            4101 => "Kit Expired – Lifespan exceeded 48 hours.",
            4102 => "Token Expired – Validity exceeded 6 hours.",
            5101 => "Google Bot detected.",
            5102 => "Other Bots/Crawlers detected.",
            5201 => "Impersonator Bot – Mimics human behavior.",
            6110 => "Attempt < 30 seconds.",
            6111 => "Attempt < 1 minute.",
            6112 => "Attempt < 5 minutes.",
            6113 => "Attempt < 1 hour.",
            6114 => "Attempt < 24 hours.",
            6120 => "Multi-service attempt < 30 sec.",
            6121 => "Multi-service attempt < 1 min.",
            6122 => "Multi-service attempt < 5 min.",
            6123 => "Multi-service attempt < 1 hr.",
            6124 => "Multi-service attempt < 24 hr.",
            6201 => "Legal Rule violation.",
            6202 => "Customer-specific rule.",
            6203 => "Customer-specific rule.",
            6204 => "Outdated Android OS – vulnerable device.",
            6205 => "Proxy/VPN detected.",
            6206 => "Multiple attempts (User ID).",
            6207 => "Aggressive campaign – Level 1.",
            6208 => "Aggressive campaign – Level 2.",
            6209 => "Aggressive campaign – Level 3.",
        ];

        if (isset($specific[$code])) {
            return $specific[$code];
        }
        
        // Mapping code ranges
        $ranges = [
            [2100, 2199, "Code Injection – Malicious code injected."],
            [2200, 2299, "Malicious Apps – Hidden automated process."],
            [2300, 2399, "Clickjacking – User click hijacked."],
            [2400, 2499, "Spoofing – SIM/Network identity theft."],
            [2500, 2599, "Remote Control Fraud – Device remotely controlled."],
            [2600, 2699, "Blacklisted – App/domain/behavior flagged."],
            [2700, 2799, "Replay Attack – Reused request/data."],
            [2800, 2899, "Bypass Fraud – Evina script bypassed."],
            [
                3100,
                3199,
                "Unintentional Session – Accidental click or browser bug.",
            ],
            [4100, 4199, "Error – Kit or Token expired."],
            [5100, 5199, "Bots – Crawlers detected."],
            [5200, 5299, "Impersonator Bots – Human-like bots."],
            [6100, 6199, "Ad-hoc rules – Geo/IP limits."],
            [6200, 6299, "Custom rules – Legal or client rules."],
        ];

        foreach ($ranges as $range) {
            if ($code >= $range[0] && $code <= $range[1]) {
                return $range[2];
            }
        }

        return "Unknown fraud code.";
    }
}

?>
