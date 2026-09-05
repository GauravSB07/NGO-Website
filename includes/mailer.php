<?php

/* =========================================================
   SEVARTHA FOUNDATION
   STANDALONE SMTP MAILER & NOTIFICATION ENGINE
   Pure PHP SMTP Client (Gmail / Custom SMTP)
   Supports SSL/TLS, AUTH LOGIN, HTML & PDF Attachments
========================================================= */

class SevarthaMailer
{
    private $host;
    private $port;
    private $secure; // 'tls', 'ssl', or 'none'
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    private $timeout = 25;
    private $lastError = '';

    public function __construct(array $config = [])
    {
        $this->host = $config['smtp_host'] ?? 'smtp.gmail.com';
        $this->port = (int) ($config['smtp_port'] ?? 587);
        $this->secure = strtolower($config['smtp_secure'] ?? 'tls');
        $this->username = trim($config['smtp_username'] ?? '');
        $this->password = trim($config['smtp_password'] ?? '');
        $this->fromEmail = trim($config['from_email'] ?? $this->username);
        $this->fromName = trim($config['from_name'] ?? 'Sevartha Foundation');
    }

    /**
     * Load settings from file or database without modifying any schema
     */
    public static function loadSettings($conn = null): self
    {
        $configFile = __DIR__ . '/../config/email_config.php';
        if (file_exists($configFile)) {
            $config = include $configFile;
            if (is_array($config)) {
                return new self($config);
            }
        }

        // If a database table exists, read from it safely without altering anything
        if ($conn) {
            $query = @mysqli_query($conn, "SELECT * FROM email_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
            if ($query && ($row = mysqli_fetch_assoc($query))) {
                return new self($row);
            }
        }

        return new self();
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function isConfigured(): bool
    {
        return !empty($this->username) && !empty($this->password);
    }

    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $attachmentBytes = null,
        ?string $attachmentFilename = null
    ): bool {
        if (!$this->isConfigured()) {
            $this->lastError = 'SMTP username and password are not configured. Please set them in Email Settings.';
            return false;
        }

        $remoteHost = ($this->secure === 'ssl') ? 'ssl://' . $this->host : $this->host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client(
            $remoteHost . ':' . $this->port,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            $this->lastError = "Could not connect to SMTP server ({$this->host}:{$this->port}): $errstr ($errno)";
            return false;
        }

        stream_set_timeout($socket, $this->timeout);

        try {
            $this->readResponse($socket, [220]);

            // EHLO
            $clientHost = gethostname() ?: 'localhost';
            $this->sendCommand($socket, "EHLO $clientHost", [250]);

            // STARTTLS if port 587
            if ($this->secure === 'tls') {
                $this->sendCommand($socket, "STARTTLS", [220]);
                $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$crypto) {
                    throw new Exception("STARTTLS cryptographic negotiation failed.");
                }
                $this->sendCommand($socket, "EHLO $clientHost", [250]);
            }

            // AUTH LOGIN
            $this->sendCommand($socket, "AUTH LOGIN", [334]);
            $this->sendCommand($socket, base64_encode($this->username), [334]);
            $this->sendCommand($socket, base64_encode($this->password), [235]);

            // MAIL FROM & RCPT TO
            $sender = !empty($this->fromEmail) ? $this->fromEmail : $this->username;
            $this->sendCommand($socket, "MAIL FROM:<{$sender}>", [250]);
            $this->sendCommand($socket, "RCPT TO:<{$toEmail}>", [250, 251]);

            // DATA
            $this->sendCommand($socket, "DATA", [354]);

            // Construct MIME message
            $mimeData = $this->buildMimeMessage($toEmail, $toName, $subject, $htmlBody, $attachmentBytes, $attachmentFilename);
            $this->sendCommand($socket, $mimeData . "\r\n.", [250]);

            // QUIT
            $this->sendCommand($socket, "QUIT", [221]);
            fclose($socket);
            return true;

        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            @fclose($socket);
            return false;
        }
    }

    private function sendCommand($socket, string $command, array $expectedCodes): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->readResponse($socket, $expectedCodes);
    }

    private function readResponse($socket, array $expectedCodes): string
    {
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 1024);
            if ($line === false) break;
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new Exception("SMTP Error [$code]: " . trim($response));
        }

        return $response;
    }

    private function buildMimeMessage(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $attachmentBytes,
        ?string $attachmentFilename
    ): string {
        $boundaryMixed = '=_mix_' . md5(microtime(true) . rand(1000, 9999));
        $boundaryAlt = '=_alt_' . md5(microtime(true) . rand(1000, 9999));

        $sender = !empty($this->fromEmail) ? $this->fromEmail : $this->username;
        $fromEncoded = "=?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$sender}>";
        $toEncoded = "=?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>";
        $subjectEncoded = "=?UTF-8?B?" . base64_encode($subject) . "?=";

        $headers = [];
        $headers[] = "Date: " . date('r');
        $headers[] = "From: {$fromEncoded}";
        $headers[] = "To: {$toEncoded}";
        $headers[] = "Subject: {$subjectEncoded}";
        $headers[] = "Reply-To: {$sender}";
        $headers[] = "X-Mailer: SevarthaFoundationMailer/2.0";
        $headers[] = "MIME-Version: 1.0";

        if (!empty($attachmentBytes) && !empty($attachmentFilename)) {
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundaryMixed}\"";

            $body = "--{$boundaryMixed}\r\n";
            $body .= "Content-Type: multipart/alternative; boundary=\"{$boundaryAlt}\"\r\n\r\n";

            // Plain text version
            $plainText = strip_tags(str_replace(['<br>', '<p>', '</div>'], "\r\n", $htmlBody));
            $body .= "--{$boundaryAlt}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($plainText)) . "\r\n";

            // HTML version
            $body .= "--{$boundaryAlt}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";
            $body .= "--{$boundaryAlt}--\r\n\r\n";

            // PDF Attachment
            $body .= "--{$boundaryMixed}\r\n";
            $body .= "Content-Type: application/pdf; name=\"{$attachmentFilename}\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"{$attachmentFilename}\"\r\n\r\n";
            $body .= chunk_split(base64_encode($attachmentBytes)) . "\r\n";
            $body .= "--{$boundaryMixed}--";

        } else {
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundaryAlt}\"";

            $plainText = strip_tags(str_replace(['<br>', '<p>', '</div>'], "\r\n", $htmlBody));
            $body = "--{$boundaryAlt}\r\n";
            $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($plainText)) . "\r\n";

            $body .= "--{$boundaryAlt}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";
            $body .= "--{$boundaryAlt}--";
        }

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    public function sendDonationVerification(
        array $donation,
        string $certificateNumber,
        string $pdfBytes,
        string $webCertificateUrl
    ): bool {
        $donorName = $donation['donor_name'];
        $donorEmail = $donation['donor_email'];
        $amount = number_format((float) $donation['donation_amount']);
        $purpose = $donation['donation_purpose'];
        $txId = $donation['transaction_id'];
        $date = date('d F Y');

        $subject = "Official Certificate of Appreciation & Heartfelt Gratitude | Sevartha Foundation";

        $html = "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<style>
    body { margin:0; padding:0; background:#fbf9f5; font-family:Arial, Helvetica, sans-serif; color:#1a1a1a; line-height:1.6; }
    .wrapper { max-width:620px; margin:24px auto; background:#ffffff; border:1px solid #d0c8b6; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(84,82,71,0.08); }
    .header { background:#545247; color:#ffffff; padding:36px 30px; text-align:center; position:relative; }
    .header h1 { font-family:Georgia, serif; margin:0 0 6px; font-size:28px; font-weight:normal; letter-spacing:-0.5px; }
    .header p { margin:0; font-size:12px; letter-spacing:2px; text-transform:uppercase; color:#d0c8b6; }
    .content { padding:36px 32px; }
    .greeting { font-size:18px; font-weight:bold; color:#1a1a1a; margin-bottom:16px; }
    .lead { font-size:15px; color:#545247; line-height:1.7; margin-bottom:24px; }
    .cert-box { background:#fbf9f5; border:1px solid #d0c8b6; border-radius:12px; padding:24px; margin:26px 0; text-align:center; }
    .cert-kicker { font-size:10px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#545247; margin-bottom:8px; display:block; }
    .cert-title { font-family:Georgia, serif; font-size:22px; font-weight:bold; color:#1a1a1a; margin:0 0 10px; }
    .cert-number { display:inline-block; font-family:monospace; font-size:13px; font-weight:bold; background:#eee9df; padding:4px 12px; border-radius:6px; color:#545247; margin-bottom:14px; }
    .summary-table { width:100%; border-collapse:collapse; margin:16px 0; font-size:13px; text-align:left; }
    .summary-table td { padding:8px 0; border-bottom:1px solid #e5ded0; }
    .summary-table td.label { color:#68665e; width:40%; }
    .summary-table td.value { font-weight:bold; color:#1a1a1a; text-align:right; }
    .btn-wrap { text-align:center; margin:30px 0 10px; }
    .btn { display:inline-block; background:#545247; color:#ffffff !important; text-decoration:none; padding:15px 32px; border-radius:10px; font-size:14px; font-weight:bold; letter-spacing:0.5px; box-shadow:0 4px 14px rgba(84,82,71,0.25); }
    .footer { background:#f6f2e8; border-top:1px solid #e5ded0; padding:24px 30px; font-size:12px; color:#68665e; text-align:center; line-height:1.7; }
    .footer strong { color:#1a1a1a; }
</style>
</head>
<body>
<div class='wrapper'>
    <div class='header'>
        <p>SEVARTHA FOUNDATION</p>
        <h1>Certificate of Appreciation</h1>
    </div>

    <div class='content'>
        <div class='greeting'>Dear {$donorName},</div>

        <p class='lead'>
            On behalf of the entire team, trustees, and the communities served by <strong>Sevartha Foundation</strong>, please accept our heartfelt gratitude for your generous philanthropic contribution of <strong>₹{$amount}</strong>.
        </p>

        <p class='lead'>
            Your verified support towards <strong>{$purpose}</strong> directly enables us to provide education, healthcare, essential nutrition, and dignity to vulnerable individuals across India. Every contribution creates lasting change.
        </p>

        <div class='cert-box'>
            <span class='cert-kicker'>OFFICIAL PHILANTHROPIC RECOGNITION</span>
            <div class='cert-title'>Certificate of Appreciation</div>
            <div class='cert-number'>ID: {$certificateNumber}</div>

            <table class='summary-table'>
                <tr><td class='label'>Donor Name</td><td class='value'>{$donorName}</td></tr>
                <tr><td class='label'>Contributed Amount</td><td class='value'>₹{$amount} INR</td></tr>
                <tr><td class='label'>Supported Purpose</td><td class='value'>{$purpose}</td></tr>
                <tr><td class='label'>Transaction UTR / ID</td><td class='value' style='font-family:monospace;'>{$txId}</td></tr>
                <tr><td class='label'>Verification Date</td><td class='value'>{$date}</td></tr>
            </table>

            <p style='margin:12px 0 0; font-size:12px; color:#68665e;'>
                Your official printable certificate is attached to this email as a PDF document.
            </p>
        </div>

        <div class='btn-wrap'>
            <a href='{$webCertificateUrl}' class='btn' target='_blank'>View &amp; Download Digital Certificate</a>
        </div>

        <p style='font-size:13px; color:#68665e; margin-top:28px; line-height:1.7;'>
            Warm regards,<br>
            <strong>Board of Trustees</strong><br>
            Sevartha Foundation<br>
            <em>\"Small acts of kindness. Lasting possibilities.\"</em>
        </p>
    </div>

    <div class='footer'>
        <strong>Sevartha Foundation</strong> • Registered Charitable Trust<br>
        This email confirms receipt and official verification of your contribution. Please retain this email and attached certificate for your records.<br>
        For inquiries or assistance, connect with us at contact@sevartha.org.
    </div>
</div>
</body>
</html>
";

        $filename = "Sevartha_Foundation_Certificate_" . preg_replace('/[^A-Za-z0-9_-]/', '_', $certificateNumber) . ".pdf";

        return $this->send($donorEmail, $donorName, $subject, $html, $pdfBytes, $filename);
    }
}
