<?php

/* =========================================================
   SEVARTHA FOUNDATION
   OFFICIAL CERTIFICATE GENERATION ENGINE
   Pure-PHP Vector PDF Generator & High-Definition Web Certificate
   Zero External Dependencies • A4 Landscape • Print-Ready
========================================================= */

class SevarthaCertificate
{
    /**
     * Generate unique, deterministic certificate number based on donation ID
     */
    public static function getCertificateNumber(int $donationId): string
    {
        $year = date('Y');
        return "SF-CERT-{$year}-" . str_pad($donationId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate secure verification token for public certificate viewing
     */
    public static function getSecurityToken(int $donationId, string $email): string
    {
        return substr(hash('sha256', "sevartha_{$donationId}_{$email}_trust"), 0, 16);
    }

    /**
     * Generate pure-PHP Vector PDF Certificate (A4 Landscape: 842 x 595 points)
     */
    public static function generatePdf(array $donation): string
    {
        $id = (int) $donation['id'];
        $donorName = self::sanitizePdfText($donation['donor_name']);
        $amount = number_format((float) $donation['donation_amount']);
        $purpose = self::sanitizePdfText($donation['donation_purpose']);
        $certNumber = self::getCertificateNumber($id);
        $date = date('d F Y');

        // Page dimensions in PDF points (72 points = 1 inch)
        // A4 Landscape: 842 pt width x 595 pt height
        $w = 842;
        $h = 595;

        // Content stream buffer
        $s = "";

        // 1. Background Tint (Ivory warm paper: #FBF9F5 => 0.984, 0.976, 0.961)
        $s .= "0.984 0.976 0.961 rg\n";
        $s .= "0 0 {$w} {$h} re f\n";

        // 2. Outer Ornamental Border in Gold/Sand (#BDB4A1 => 0.74, 0.70, 0.63)
        $s .= "0.74 0.70 0.63 RG\n";
        $s .= "3.0 w\n";
        $s .= "24 24 " . ($w - 48) . " " . ($h - 48) . " re S\n";

        // Inner Fine Border in Olive Drab (#545247 => 0.33, 0.32, 0.28)
        $s .= "0.33 0.32 0.28 RG\n";
        $s .= "1.2 w\n";
        $s .= "30 30 " . ($w - 60) . " " . ($h - 60) . " re S\n";

        // Decorative Corner Inset Squares
        $corners = [
            [26, 26],
            [$w - 34, 26],
            [26, $h - 34],
            [$w - 34, $h - 34]
        ];
        $s .= "0.74 0.70 0.63 rg\n";
        foreach ($corners as $c) {
            $s .= "{$c[0]} {$c[1]} 8 8 re f\n";
        }

        // 3. Header: Organization Name & Subtitle
        // "SEVARTHA FOUNDATION"
        $s .= "BT\n";
        $s .= "/F2 26 Tf\n"; // Times-Bold
        $s .= "0.20 0.20 0.20 rg\n";
        $orgTitle = "SEVARTHA FOUNDATION";
        $s .= self::centerText($orgTitle, 26 * 0.58, $w, 520);
        $s .= "ET\n";

        // "A REGISTERED PUBLIC CHARITABLE TRUST"
        $s .= "BT\n";
        $s .= "/F4 8 Tf\n"; // Helvetica
        $s .= "0.45 0.43 0.38 rg\n";
        $orgSub = "A REGISTERED PUBLIC CHARITABLE TRUST  |  DEDICATED TO HUMANITARIAN SERVICE";
        $s .= self::centerText($orgSub, 8 * 0.52, $w, 502);
        $s .= "ET\n";

        // Thin Gold Divider Line
        $s .= "0.74 0.70 0.63 RG\n";
        $s .= "0.8 w\n";
        $s .= "220 488 402 0 re S\n";

        // 4. Main Award Title: "CERTIFICATE OF APPRECIATION"
        $s .= "BT\n";
        $s .= "/F2 28 Tf\n"; // Times-Bold
        $s .= "0.33 0.32 0.28 rg\n"; // Olive
        $certTitle = "CERTIFICATE OF APPRECIATION";
        $s .= self::centerText($certTitle, 28 * 0.58, $w, 448);
        $s .= "ET\n";

        // Presentation phrase
        $s .= "BT\n";
        $s .= "/F3 12 Tf\n"; // Times-Italic
        $s .= "0.40 0.40 0.40 rg\n";
        $presentText = "This certificate is gratefully presented in recognition of";
        $s .= self::centerText($presentText, 12 * 0.45, $w, 418);
        $s .= "ET\n";

        // 5. Recipient / Donor Name (Prominent & Underlined)
        $s .= "BT\n";
        $s .= "/F2 24 Tf\n"; // Times-Bold
        $s .= "0.10 0.10 0.10 rg\n";
        $s .= self::centerText($donorName, 24 * 0.58, $w, 372);
        $s .= "ET\n";

        // Name Underline Accent
        $nameLen = strlen($donorName) * 14;
        $nameStartX = max(180, ($w - $nameLen) / 2);
        $s .= "0.74 0.70 0.63 RG\n";
        $s .= "1.2 w\n";
        $s .= "{$nameStartX} 364 " . min(480, $nameLen) . " 0 re S\n";

        // 6. Citation Statement
        $line1 = "for their generous philanthropic contribution of Rs. {$amount} towards {$purpose},";
        $line2 = "enabling essential educational, medical, and social care for vulnerable communities.";
        $line3 = "Their commitment to service brings meaningful, lasting transformation to society.";

        $s .= "BT\n";
        $s .= "/F1 12 Tf\n"; // Times-Roman
        $s .= "0.30 0.30 0.30 rg\n";
        $s .= self::centerText($line1, 12 * 0.45, $w, 332);
        $s .= self::centerText($line2, 12 * 0.45, $w, 312);
        $s .= self::centerText($line3, 12 * 0.45, $w, 292);
        $s .= "ET\n";

        // 7. Official Seal Graphic (Medallion on left)
        $sealX = 140;
        $sealY = 160;
        // Outer gold circle
        $s .= "0.74 0.70 0.63 RG\n";
        $s .= "2.0 w\n";
        $s .= self::drawCircle($sealX, $sealY, 44);
        $s .= "S\n";
        // Inner dashed olive circle
        $s .= "0.33 0.32 0.28 RG\n";
        $s .= "1.0 w\n";
        $s .= "[3 3] 0 d\n"; // dashed
        $s .= self::drawCircle($sealX, $sealY, 38);
        $s .= "S\n";
        $s .= "[] 0 d\n"; // reset dash
        // Seal Text inside
        $s .= "BT\n";
        $s .= "/F5 7 Tf\n"; // Helvetica-Bold
        $s .= "0.33 0.32 0.28 rg\n";
        $s .= ($sealX - 25) . " " . ($sealY + 10) . " Td (SEVARTHA) Tj\n";
        $s .= "ET\n";
        $s .= "BT\n";
        $s .= "/F4 6 Tf\n";
        $s .= "0.33 0.32 0.28 rg\n";
        $s .= ($sealX - 22) . " " . ($sealY - 2) . " Td (FOUNDATION) Tj\n";
        $s .= "ET\n";
        $s .= "BT\n";
        $s .= "/F5 6 Tf\n";
        $s .= "0.74 0.70 0.63 rg\n";
        $s .= ($sealX - 24) . " " . ($sealY - 14) . " Td (* OFFICIAL SEAL *) Tj\n";
        $s .= "ET\n";

        // 8. Signature Blocks
        // Left Signature: Authorized Trustee
        $s .= "0.33 0.32 0.28 RG\n";
        $s .= "1.0 w\n";
        $s .= "340 148 160 0 re S\n";
        $s .= "BT\n";
        $s .= "/F5 10 Tf\n";
        $s .= "0.20 0.20 0.20 rg\n";
        $s .= "385 132 Td (Managing Trustee) Tj\n";
        $s .= "ET\n";
        $s .= "BT\n";
        $s .= "/F4 8 Tf\n";
        $s .= "0.45 0.45 0.45 rg\n";
        $s .= "380 120 Td (Sevartha Foundation) Tj\n";
        $s .= "ET\n";

        // Right Signature: Verification Officer / Date
        $s .= "580 148 160 0 re S\n";
        $s .= "BT\n";
        $s .= "/F5 10 Tf\n";
        $s .= "0.20 0.20 0.20 rg\n";
        $s .= "630 132 Td (Date of Issue) Tj\n";
        $s .= "ET\n";
        $s .= "BT\n";
        $s .= "/F4 8 Tf\n";
        $s .= "0.45 0.45 0.45 rg\n";
        $s .= "625 120 Td ({$date}) Tj\n";
        $s .= "ET\n";

        // 9. Footer: Certificate ID & Authenticity Notice
        $s .= "BT\n";
        $s .= "/F5 8 Tf\n";
        $s .= "0.40 0.40 0.40 rg\n";
        $s .= "42 42 Td (Certificate ID: {$certNumber}) Tj\n";
        $s .= "ET\n";

        $s .= "BT\n";
        $s .= "/F4 8 Tf\n";
        $s .= "0.50 0.50 0.50 rg\n";
        $s .= "540 42 Td (Digitally Verified by Sevartha Foundation Trust) Tj\n";
        $s .= "ET\n";

        // Assemble full PDF 1.4 syntax
        return self::buildPdfDocument($w, $h, $s);
    }

    private static function sanitizePdfText(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private static function centerText(string $text, float $charWidth, float $pageWidth, float $y): string
    {
        $estimatedWidth = strlen($text) * $charWidth;
        $x = max(30, ($pageWidth - $estimatedWidth) / 2);
        return sprintf("%.1f %.1f Td (%s) Tj\n", $x, $y, $text);
    }

    private static function drawCircle(float $cx, float $cy, float $r): string
    {
        // 4-bezier cubic approximation of a circle
        $k = 0.5522847498 * $r;
        $out = sprintf("%.1f %.1f m\n", $cx, $cy + $r);
        $out .= sprintf("%.1f %.1f %.1f %.1f %.1f %.1f c\n", $cx + $k, $cy + $r, $cx + $r, $cy + $k, $cx + $r, $cy);
        $out .= sprintf("%.1f %.1f %.1f %.1f %.1f %.1f c\n", $cx + $r, $cy - $k, $cx + $k, $cy - $r, $cx, $cy - $r);
        $out .= sprintf("%.1f %.1f %.1f %.1f %.1f %.1f c\n", $cx - $k, $cy - $r, $cx - $r, $cy - $k, $cx - $r, $cy);
        $out .= sprintf("%.1f %.1f %.1f %.1f %.1f %.1f c\n", $cx - $r, $cy + $k, $cx - $k, $cy + $r, $cx, $cy + $r);
        return $out;
    }

    private static function buildPdfDocument(int $w, int $h, string $streamContent): string
    {
        $objects = [];
        $offsets = [];

        // 1 0 obj: Catalog
        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // 2 0 obj: Pages
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

        // 3 0 obj: Page
        $objects[3] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$w} {$h}] /Resources 4 0 R /Contents 5 0 R >>\nendobj\n";

        // 4 0 obj: Font Resources
        $objects[4] = "4 0 obj\n<< /Font <<
            /F1 << /Type /Font /Subtype /Type1 /BaseFont /Times-Roman >>
            /F2 << /Type /Font /Subtype /Type1 /BaseFont /Times-Bold >>
            /F3 << /Type /Font /Subtype /Type1 /BaseFont /Times-Italic >>
            /F4 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
            /F5 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>
        >> >>\nendobj\n";

        // 5 0 obj: Content Stream
        $len = strlen($streamContent);
        $objects[5] = "5 0 obj\n<< /Length {$len} >>\nstream\n{$streamContent}\nendstream\nendobj\n";

        // Assemble document
        $pdf = "%PDF-1.4\n";
        for ($i = 1; $i <= 5; $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $objects[$i];
        }

        $xrefStart = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefStart}\n%%EOF";

        return $pdf;
    }

    /**
     * Render high-definition Web Certificate view (for browser display & printing)
     */
    public static function renderHtmlCertificate(array $donation, bool $showActions = true): string
    {
        $id = (int) $donation['id'];
        $donorName = htmlspecialchars($donation['donor_name'], ENT_QUOTES, 'UTF-8');
        $amount = number_format((float) $donation['donation_amount']);
        $purpose = htmlspecialchars($donation['donation_purpose'], ENT_QUOTES, 'UTF-8');
        $certNumber = self::getCertificateNumber($id);
        $date = date('d F Y');
        $txId = htmlspecialchars($donation['transaction_id'] ?? 'Verified', ENT_QUOTES, 'UTF-8');

        return "
        <div class='certificate-outer'>
            <div class='certificate-frame'>
                <div class='cert-corner top-left'></div>
                <div class='cert-corner top-right'></div>
                <div class='cert-corner bottom-left'></div>
                <div class='cert-corner bottom-right'></div>

                <div class='certificate-inner'>
                    <!-- Header -->
                    <div class='cert-header'>
                        <div class='cert-brand'>
                            <span class='cert-org-eyebrow'>Registered Public Charitable Trust</span>
                            <h2 class='cert-org-name'>SEVARTHA FOUNDATION</h2>
                            <p class='cert-org-motto'>Small acts of kindness. Lasting possibilities.</p>
                        </div>
                    </div>

                    <div class='cert-divider'></div>

                    <!-- Title -->
                    <div class='cert-title-section'>
                        <span class='cert-award-kicker'>OFFICIAL PHILANTHROPIC RECOGNITION</span>
                        <h1 class='cert-award-title'>Certificate of Appreciation</h1>
                        <p class='cert-award-to'>This is gratefully presented to</p>
                    </div>

                    <!-- Recipient -->
                    <div class='cert-recipient'>
                        <span class='cert-donor-name'>{$donorName}</span>
                    </div>

                    <!-- Citation -->
                    <div class='cert-citation'>
                        <p>
                            in sincere recognition of your generous contribution of <strong>₹{$amount}</strong> in support of <strong>{$purpose}</strong>, providing vital education, healthcare, and human dignity to vulnerable individuals across India.
                        </p>
                        <p class='cert-citation-sub'>
                            Your compassion and dedication to service create enduring hope and opportunity.
                        </p>
                    </div>

                    <!-- Seal & Signatures -->
                    <div class='cert-footer'>
                        <!-- Official Seal -->
                        <div class='cert-seal-wrap'>
                            <div class='cert-seal'>
                                <i class='fa-solid fa-stamp'></i>
                                <span>SEVARTHA<br>FOUNDATION<br><b>OFFICIAL SEAL</b></span>
                            </div>
                        </div>

                        <!-- Managing Trustee -->
                        <div class='cert-sign-col'>
                            <div class='cert-sign-line'></div>
                            <span class='cert-sign-name'>Authorized Trustee</span>
                            <span class='cert-sign-org'>Sevartha Foundation</span>
                        </div>

                        <!-- Date & Verification -->
                        <div class='cert-sign-col'>
                            <div class='cert-sign-line'></div>
                            <span class='cert-sign-name'>Date of Issue</span>
                            <span class='cert-sign-org'>{$date}</span>
                        </div>
                    </div>

                    <!-- Bottom Bar -->
                    <div class='cert-bottom-meta'>
                        <span>Certificate ID: <strong>{$certNumber}</strong></span>
                        <span>UTR / Transaction: <code style='font-family: monospace;'>{$txId}</code></span>
                        <span>Digitally Verified Document</span>
                    </div>
                </div>
            </div>
        </div>
        ";
    }
}
