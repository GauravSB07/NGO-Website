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
     * Get path to the customizable certificate template JSON file
     */
    public static function getTemplatePath(): string
    {
        return __DIR__ . '/../config/certificate_template.json';
    }

    /**
     * Default professional template configuration
     */
    public static function getDefaultTemplate(): array
    {
        return [
            'org_name' => 'SEVARTHA FOUNDATION',
            'org_sub' => 'A REGISTERED PUBLIC CHARITABLE TRUST  |  DEDICATED TO HUMANITARIAN SERVICE',
            'org_motto' => 'Small acts of kindness. Lasting possibilities.',
            'kicker' => 'OFFICIAL PHILANTHROPIC RECOGNITION',
            'cert_title' => 'CERTIFICATE OF APPRECIATION',
            'presentation_text' => 'This certificate is gratefully presented in recognition of',
            'citation_line1' => 'for their generous philanthropic contribution of Rs. {amount} towards {purpose},',
            'citation_line2' => 'enabling essential educational, medical, and social care for vulnerable communities.',
            'citation_line3' => 'Their commitment to service brings meaningful, lasting transformation to society.',
            'citation_paragraph' => 'in sincere recognition of your generous contribution of ₹{amount} in support of {purpose}, providing vital education, healthcare, and human dignity to vulnerable individuals across India.',
            'citation_sub_paragraph' => 'Your compassion and dedication to service create enduring hope and opportunity.',
            'seal_text_top' => 'SEVARTHA',
            'seal_text_mid' => 'FOUNDATION',
            'seal_text_bot' => '* OFFICIAL SEAL *',
            'signatory1_title' => 'Managing Trustee',
            'signatory1_org' => 'Sevartha Foundation',
            'signatory2_title' => 'Date of Issue',
            'signatory2_subtitle' => '{date}',
            'footer_authenticity' => 'Digitally Verified by Sevartha Foundation Trust',
            'color_theme' => 'classic_gold' // 'classic_gold' | 'regal_olive' | 'royal_bronze'
        ];
    }

    /**
     * Load current certificate template from storage or fallback to defaults
     */
    public static function loadTemplate(): array
    {
        $defaults = self::getDefaultTemplate();
        $path = self::getTemplatePath();
        if (file_exists($path)) {
            $content = @file_get_contents($path);
            if ($content) {
                $json = @json_decode($content, true);
                if (is_array($json)) {
                    return array_merge($defaults, $json);
                }
            }
        }
        return $defaults;
    }

    /**
     * Save certificate template configuration
     */
    public static function saveTemplate(array $template): bool
    {
        $path = self::getTemplatePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $clean = array_merge(self::getDefaultTemplate(), $template);
        return (bool) @file_put_contents(
            $path,
            json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    /**
     * Replace dynamic variables inside template strings
     */
    public static function replacePlaceholders(string $text, array $donation): string
    {
        $id = (int) ($donation['id'] ?? 0);
        $donorName = $donation['donor_name'] ?? 'Generous Donor';
        $amount = number_format((float) ($donation['donation_amount'] ?? 0));
        $purpose = $donation['donation_purpose'] ?? 'General Charitable Operations';
        $certNumber = self::getCertificateNumber($id);
        $date = date('d F Y');
        $txId = $donation['transaction_id'] ?? 'Verified';

        $replacements = [
            '{donor_name}' => $donorName,
            '{amount}' => $amount,
            '{purpose}' => $purpose,
            '{cert_id}' => $certNumber,
            '{date}' => $date,
            '{transaction_id}' => $txId,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

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
    public static function generatePdf(array $donation, ?array $customTemplate = null): string
    {
        $tmpl = $customTemplate ?? self::loadTemplate();

        $id = (int) ($donation['id'] ?? 0);
        $donorName = self::sanitizePdfText($donation['donor_name'] ?? 'Generous Donor');
        $certNumber = self::getCertificateNumber($id);

        // Process template fields with placeholders
        $orgTitle = self::sanitizePdfText(self::replacePlaceholders($tmpl['org_name'], $donation));
        $orgSub = self::sanitizePdfText(self::replacePlaceholders($tmpl['org_sub'], $donation));
        $certTitle = self::sanitizePdfText(self::replacePlaceholders($tmpl['cert_title'], $donation));
        $presentText = self::sanitizePdfText(self::replacePlaceholders($tmpl['presentation_text'], $donation));

        $line1 = self::sanitizePdfText(self::replacePlaceholders($tmpl['citation_line1'], $donation));
        $line2 = self::sanitizePdfText(self::replacePlaceholders($tmpl['citation_line2'], $donation));
        $line3 = self::sanitizePdfText(self::replacePlaceholders($tmpl['citation_line3'], $donation));

        $sig1Title = self::sanitizePdfText(self::replacePlaceholders($tmpl['signatory1_title'], $donation));
        $sig1Org = self::sanitizePdfText(self::replacePlaceholders($tmpl['signatory1_org'], $donation));
        $sig2Title = self::sanitizePdfText(self::replacePlaceholders($tmpl['signatory2_title'], $donation));
        $sig2Subtitle = self::sanitizePdfText(self::replacePlaceholders($tmpl['signatory2_subtitle'], $donation));

        $sealTop = self::sanitizePdfText($tmpl['seal_text_top']);
        $sealMid = self::sanitizePdfText($tmpl['seal_text_mid']);
        $sealBot = self::sanitizePdfText($tmpl['seal_text_bot']);

        $footerNotice = self::sanitizePdfText(self::replacePlaceholders($tmpl['footer_authenticity'], $donation));

        // Color palettes
        $theme = $tmpl['color_theme'] ?? 'classic_gold';
        $borderPrimary = "0.74 0.70 0.63";   // Gold/sand
        $borderSecondary = "0.33 0.32 0.28"; // Olive drab
        $titleColor = "0.33 0.32 0.28";      // Olive

        if ($theme === 'regal_olive') {
            $borderPrimary = "0.45 0.43 0.35";
            $borderSecondary = "0.25 0.24 0.20";
            $titleColor = "0.25 0.24 0.20";
        } elseif ($theme === 'royal_bronze') {
            $borderPrimary = "0.76 0.58 0.38";
            $borderSecondary = "0.35 0.25 0.20";
            $titleColor = "0.38 0.26 0.18";
        }

        // Page dimensions in PDF points (72 points = 1 inch)
        // A4 Landscape: 842 pt width x 595 pt height
        $w = 842;
        $h = 595;

        // Content stream buffer
        $s = "";

        // 1. Background Tint (Ivory warm paper: #FBF9F5 => 0.984, 0.976, 0.961)
        $s .= "0.984 0.976 0.961 rg\n";
        $s .= "0 0 {$w} {$h} re f\n";

        // 2. Outer Ornamental Border
        $s .= "{$borderPrimary} RG\n";
        $s .= "3.0 w\n";
        $s .= "24 24 " . ($w - 48) . " " . ($h - 48) . " re S\n";

        // Inner Fine Border
        $s .= "{$borderSecondary} RG\n";
        $s .= "1.2 w\n";
        $s .= "30 30 " . ($w - 60) . " " . ($h - 60) . " re S\n";

        // Decorative Corner Inset Squares
        $corners = [
            [26, 26],
            [$w - 34, 26],
            [26, $h - 34],
            [$w - 34, $h - 34]
        ];
        $s .= "{$borderPrimary} rg\n";
        foreach ($corners as $c) {
            $s .= "{$c[0]} {$c[1]} 8 8 re f\n";
        }

        // 3. Header: Organization Name & Subtitle
        $s .= "BT\n";
        $s .= "/F2 26 Tf\n"; // Times-Bold
        $s .= "0.20 0.20 0.20 rg\n";
        $s .= self::centerText($orgTitle, 26 * 0.58, $w, 520);
        $s .= "ET\n";

        if ($orgSub !== '') {
            $s .= "BT\n";
            $s .= "/F4 8 Tf\n"; // Helvetica
            $s .= "0.45 0.43 0.38 rg\n";
            $s .= self::centerText($orgSub, 8 * 0.52, $w, 502);
            $s .= "ET\n";
        }

        // Thin Accent Divider Line
        $s .= "{$borderPrimary} RG\n";
        $s .= "0.8 w\n";
        $s .= "220 488 402 0 re S\n";

        // 4. Main Award Title
        $s .= "BT\n";
        $s .= "/F2 28 Tf\n"; // Times-Bold
        $s .= "{$titleColor} rg\n";
        $s .= self::centerText($certTitle, 28 * 0.58, $w, 448);
        $s .= "ET\n";

        // Presentation phrase
        if ($presentText !== '') {
            $s .= "BT\n";
            $s .= "/F3 12 Tf\n"; // Times-Italic
            $s .= "0.40 0.40 0.40 rg\n";
            $s .= self::centerText($presentText, 12 * 0.45, $w, 418);
            $s .= "ET\n";
        }

        // 5. Recipient / Donor Name (Prominent & Underlined)
        $s .= "BT\n";
        $s .= "/F2 24 Tf\n"; // Times-Bold
        $s .= "0.10 0.10 0.10 rg\n";
        $s .= self::centerText($donorName, 24 * 0.58, $w, 372);
        $s .= "ET\n";

        // Name Underline Accent
        $nameLen = strlen($donorName) * 14;
        $nameStartX = max(180, ($w - $nameLen) / 2);
        $s .= "{$borderPrimary} RG\n";
        $s .= "1.2 w\n";
        $s .= "{$nameStartX} 364 " . min(480, $nameLen) . " 0 re S\n";

        // 6. Citation Statement Lines
        $s .= "BT\n";
        $s .= "/F1 12 Tf\n"; // Times-Roman
        $s .= "0.30 0.30 0.30 rg\n";
        $currentY = 332;
        if ($line1 !== '') {
            $s .= self::centerText($line1, 12 * 0.45, $w, $currentY);
            $currentY -= 20;
        }
        if ($line2 !== '') {
            $s .= self::centerText($line2, 12 * 0.45, $w, $currentY);
            $currentY -= 20;
        }
        if ($line3 !== '') {
            $s .= self::centerText($line3, 12 * 0.45, $w, $currentY);
        }
        $s .= "ET\n";

        // 7. Official Seal Graphic (Medallion on left)
        $sealX = 140;
        $sealY = 160;
        // Outer accent circle
        $s .= "{$borderPrimary} RG\n";
        $s .= "2.0 w\n";
        $s .= self::drawCircle($sealX, $sealY, 44);
        $s .= "S\n";
        // Inner dashed secondary circle
        $s .= "{$borderSecondary} RG\n";
        $s .= "1.0 w\n";
        $s .= "[3 3] 0 d\n";
        $s .= self::drawCircle($sealX, $sealY, 38);
        $s .= "S\n";
        $s .= "[] 0 d\n"; // reset dash

        // Seal Text inside
        if ($sealTop !== '') {
            $s .= "BT\n";
            $s .= "/F5 7 Tf\n";
            $s .= "{$borderSecondary} rg\n";
            $s .= self::centerTextAbsolute($sealTop, 7 * 0.52, $sealX, $sealY + 10);
            $s .= "ET\n";
        }
        if ($sealMid !== '') {
            $s .= "BT\n";
            $s .= "/F4 6 Tf\n";
            $s .= "{$borderSecondary} rg\n";
            $s .= self::centerTextAbsolute($sealMid, 6 * 0.52, $sealX, $sealY - 2);
            $s .= "ET\n";
        }
        if ($sealBot !== '') {
            $s .= "BT\n";
            $s .= "/F5 6 Tf\n";
            $s .= "{$borderPrimary} rg\n";
            $s .= self::centerTextAbsolute($sealBot, 6 * 0.52, $sealX, $sealY - 14);
            $s .= "ET\n";
        }

        // 8. Signature Blocks
        // Left Signature: Signatory 1
        $s .= "{$borderSecondary} RG\n";
        $s .= "1.0 w\n";
        $s .= "340 148 160 0 re S\n";
        $s .= "BT\n";
        $s .= "/F5 10 Tf\n";
        $s .= "0.20 0.20 0.20 rg\n";
        $s .= "360 132 Td (" . $sig1Title . ") Tj\n";
        $s .= "ET\n";
        if ($sig1Org !== '') {
            $s .= "BT\n";
            $s .= "/F4 8 Tf\n";
            $s .= "0.45 0.45 0.45 rg\n";
            $s .= "360 120 Td (" . $sig1Org . ") Tj\n";
            $s .= "ET\n";
        }

        // Right Signature: Signatory 2
        $s .= "580 148 160 0 re S\n";
        $s .= "BT\n";
        $s .= "/F5 10 Tf\n";
        $s .= "0.20 0.20 0.20 rg\n";
        $s .= "600 132 Td (" . $sig2Title . ") Tj\n";
        $s .= "ET\n";
        if ($sig2Subtitle !== '') {
            $s .= "BT\n";
            $s .= "/F4 8 Tf\n";
            $s .= "0.45 0.45 0.45 rg\n";
            $s .= "600 120 Td (" . $sig2Subtitle . ") Tj\n";
            $s .= "ET\n";
        }

        // 9. Footer: Certificate ID & Authenticity Notice
        $s .= "BT\n";
        $s .= "/F5 8 Tf\n";
        $s .= "0.40 0.40 0.40 rg\n";
        $s .= "42 42 Td (Certificate ID: {$certNumber}) Tj\n";
        $s .= "ET\n";

        if ($footerNotice !== '') {
            $s .= "BT\n";
            $s .= "/F4 8 Tf\n";
            $s .= "0.50 0.50 0.50 rg\n";
            $s .= "520 42 Td ({$footerNotice}) Tj\n";
            $s .= "ET\n";
        }

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

    private static function centerTextAbsolute(string $text, float $charWidth, float $centerX, float $y): string
    {
        $halfWidth = (strlen($text) * $charWidth) / 2;
        $x = $centerX - $halfWidth;
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
    public static function renderHtmlCertificate(array $donation, bool $showActions = true, ?array $customTemplate = null): string
    {
        $tmpl = $customTemplate ?? self::loadTemplate();

        $id = (int) ($donation['id'] ?? 0);
        $donorName = htmlspecialchars($donation['donor_name'] ?? 'Generous Donor', ENT_QUOTES, 'UTF-8');
        $certNumber = self::getCertificateNumber($id);
        $txId = htmlspecialchars($donation['transaction_id'] ?? 'Verified', ENT_QUOTES, 'UTF-8');

        // Process fields with placeholders
        $orgName = htmlspecialchars(self::replacePlaceholders($tmpl['org_name'], $donation), ENT_QUOTES, 'UTF-8');
        $orgSub = htmlspecialchars(self::replacePlaceholders($tmpl['org_sub'], $donation), ENT_QUOTES, 'UTF-8');
        $orgMotto = htmlspecialchars(self::replacePlaceholders($tmpl['org_motto'] ?? '', $donation), ENT_QUOTES, 'UTF-8');
        $kicker = htmlspecialchars(self::replacePlaceholders($tmpl['kicker'] ?? 'OFFICIAL PHILANTHROPIC RECOGNITION', $donation), ENT_QUOTES, 'UTF-8');
        $certTitle = htmlspecialchars(self::replacePlaceholders($tmpl['cert_title'], $donation), ENT_QUOTES, 'UTF-8');
        $presentText = htmlspecialchars(self::replacePlaceholders($tmpl['presentation_text'], $donation), ENT_QUOTES, 'UTF-8');

        $paragraph = self::replacePlaceholders($tmpl['citation_paragraph'], $donation);
        $subParagraph = self::replacePlaceholders($tmpl['citation_sub_paragraph'] ?? '', $donation);

        $sealTop = htmlspecialchars($tmpl['seal_text_top'], ENT_QUOTES, 'UTF-8');
        $sealMid = htmlspecialchars($tmpl['seal_text_mid'], ENT_QUOTES, 'UTF-8');
        $sealBot = htmlspecialchars($tmpl['seal_text_bot'], ENT_QUOTES, 'UTF-8');

        $sig1Title = htmlspecialchars(self::replacePlaceholders($tmpl['signatory1_title'], $donation), ENT_QUOTES, 'UTF-8');
        $sig1Org = htmlspecialchars(self::replacePlaceholders($tmpl['signatory1_org'], $donation), ENT_QUOTES, 'UTF-8');
        $sig2Title = htmlspecialchars(self::replacePlaceholders($tmpl['signatory2_title'], $donation), ENT_QUOTES, 'UTF-8');
        $sig2Subtitle = htmlspecialchars(self::replacePlaceholders($tmpl['signatory2_subtitle'], $donation), ENT_QUOTES, 'UTF-8');

        $footerNotice = htmlspecialchars(self::replacePlaceholders($tmpl['footer_authenticity'], $donation), ENT_QUOTES, 'UTF-8');

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
                            " . ($orgSub !== '' ? "<span class='cert-org-eyebrow'>{$orgSub}</span>" : "") . "
                            <h2 class='cert-org-name'>{$orgName}</h2>
                            " . ($orgMotto !== '' ? "<p class='cert-org-motto'>{$orgMotto}</p>" : "") . "
                        </div>
                    </div>

                    <div class='cert-divider'></div>

                    <!-- Title -->
                    <div class='cert-title-section'>
                        " . ($kicker !== '' ? "<span class='cert-award-kicker'>{$kicker}</span>" : "") . "
                        <h1 class='cert-award-title'>{$certTitle}</h1>
                        <p class='cert-award-to'>{$presentText}</p>
                    </div>

                    <!-- Recipient -->
                    <div class='cert-recipient'>
                        <span class='cert-donor-name'>{$donorName}</span>
                    </div>

                    <!-- Citation -->
                    <div class='cert-citation'>
                        <p>{$paragraph}</p>
                        " . ($subParagraph !== '' ? "<p class='cert-citation-sub'>{$subParagraph}</p>" : "") . "
                    </div>

                    <!-- Seal & Signatures -->
                    <div class='cert-footer'>
                        <!-- Official Seal -->
                        <div class='cert-seal-wrap'>
                            <div class='cert-seal'>
                                <i class='fa-solid fa-stamp'></i>
                                <span>{$sealTop}<br>{$sealMid}<br><b>{$sealBot}</b></span>
                            </div>
                        </div>

                        <!-- Signatory 1 -->
                        <div class='cert-sign-col'>
                            <div class='cert-sign-line'></div>
                            <span class='cert-sign-name'>{$sig1Title}</span>
                            <span class='cert-sign-org'>{$sig1Org}</span>
                        </div>

                        <!-- Signatory 2 -->
                        <div class='cert-sign-col'>
                            <div class='cert-sign-line'></div>
                            <span class='cert-sign-name'>{$sig2Title}</span>
                            <span class='cert-sign-org'>{$sig2Subtitle}</span>
                        </div>
                    </div>

                    <!-- Bottom Bar -->
                    <div class='cert-bottom-meta'>
                        <span>Certificate ID: <strong>{$certNumber}</strong></span>
                        <span>UTR / Transaction: <code style='font-family: monospace;'>{$txId}</code></span>
                        <span>{$footerNotice}</span>
                    </div>
                </div>
            </div>
        </div>
        ";
    }
}
