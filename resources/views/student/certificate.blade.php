<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admas University Clearance Certificate</title>
    <style>
        @page { margin: 24px; }
        body {
            font-family: DejaVu Serif, Georgia, serif;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .outer-frame {
            border: 3.5px solid #1b365d;
            padding: 4px;
        }
        .inner-frame {
            border: 1.2px solid #1b365d;
            padding: 26px 45px 22px;
            text-align: center;
        }
        .logo-box {
            margin-bottom: 6px;
        }
        .univ-title {
            font-size: 27px;
            font-weight: bold;
            color: #1b365d;
            letter-spacing: 1px;
            margin: 2px 0 4px;
        }
        .cert-title {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.6px;
            margin: 0 0 22px;
            color: #111;
        }
        .intro {
            font-size: 15px;
            margin-bottom: 18px;
            color: #222;
        }
        .student-name {
            font-size: 26px;
            font-weight: bold;
            display: inline-block;
            border-bottom: 1px solid #777;
            padding: 0 44px 5px;
            margin-bottom: 16px;
            color: #000;
        }
        .details {
            font-size: 14.5px;
            line-height: 1.65;
            margin-bottom: 34px;
            color: #111;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin-top: 10px;
        }
        .footer-table td {
            vertical-align: bottom;
            padding: 0;
        }
        .sig-line {
            border-top: 1px solid #555;
            width: 210px;
            margin: 0 auto 6px;
        }
        .qr-img {
            border: 1px solid #333;
            padding: 2px;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/admas-logo.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : 'data:image/svg+xml;base64,' . base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" width="84" height="84" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="46" fill="#29abe2" stroke="#1b365d" stroke-width="2"/>
                    <circle cx="50" cy="50" r="32" fill="#ffffff"/>
                    <path d="M22 50 A28 28 0 0 1 78 50" fill="#29abe2"/>
                    <line x1="50" y1="22" x2="50" y2="50" stroke="#ffffff" stroke-width="2"/>
                    <line x1="32" y1="30" x2="50" y2="50" stroke="#ffffff" stroke-width="2"/>
                    <line x1="68" y1="30" x2="50" y2="50" stroke="#ffffff" stroke-width="2"/>
                    <line x1="24" y1="42" x2="50" y2="50" stroke="#ffffff" stroke-width="2"/>
                    <line x1="76" y1="42" x2="50" y2="50" stroke="#ffffff" stroke-width="2"/>
                    <path d="M28 54 Q50 62 72 54 L72 68 Q50 76 28 68 Z" fill="#e6f4fb" stroke="#1b365d" stroke-width="1.5"/>
                    <line x1="50" y1="56" x2="50" y2="72" stroke="#1b365d" stroke-width="1.5"/>
                    <text x="50" y="15" font-size="7.5" font-family="sans-serif" font-weight="bold" fill="#ffffff" text-anchor="middle">አድማስ ዩኒቨርሲቲ</text>
                    <text x="50" y="91" font-size="7" font-family="sans-serif" font-weight="bold" fill="#ffffff" text-anchor="middle">ADMAS UNIVERSITY</text>
                </svg>
            ');
    @endphp

    <div class="outer-frame">
        <div class="inner-frame">
            <div class="logo-box">
                <img src="{{ $logoSrc }}" width="78" height="78" alt="Admas University Logo">
            </div>

            <div class="univ-title">ADMAS UNIVERSITY</div>
            <div class="cert-title">CLEARANCE CERTIFICATE</div>

            <div class="intro">This is to certify that</div>
            <div class="student-name">{{ $clearance->user->name }}</div>

            <div class="details">
                ID No. <strong>{{ $clearance->user->student_id }}</strong> &nbsp;|&nbsp;
                Department of <strong>{{ $clearance->user->department }}</strong> &nbsp;|&nbsp;
                Batch <strong>{{ $clearance->user->batch ?? '2024' }}</strong><br>
                has completed the clearance process and has no outstanding obligation to the university.
            </div>

            <table class="footer-table">
                <tr>
                    <td style="text-align: left; width: 32%;">
    Date: {{ $clearance->issued_at?->format('d F Y') ?? now()->format('d F Y') }}<br>
    Serial: {{ $clearance->certificate_serial }}<br>
    Verification Code: <strong>{{ $clearance->verification_code }}</strong>
</td>
                    <td style="text-align: center; width: 36%;">
                        <div class="sig-line"></div>
                        Registrar (electronic signature)
                    </td>
                    <td style="text-align: right; width: 32%;">
                        <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="82" height="82" class="qr-img" alt="QR Code"><br>
                        <div style="width: 88px; text-align: center; display: inline-block; font-size: 10.5px;">Scan to verify</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>