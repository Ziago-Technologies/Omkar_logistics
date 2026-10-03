<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Receipt #{{ $receipt->series }}-{{ $receipt->receipt_no }} - Omkaar Logistics</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background-color: #7b8089;
            color: #000;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        /* ── Top Crystal Reports Toolbar ── */
        .cr-toolbar {
            background: linear-gradient(180deg, #f5f5f5 0%, #e2e2e2 100%);
            border-bottom: 1px solid #999;
            padding: 5px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            user-select: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .cr-tools-left {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cr-btn {
            background: #ffffff;
            border: 1px solid #7f9db9;
            padding: 3px 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 25px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 3px;
            color: #000;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .cr-btn:hover {
            background: #e6f0ff;
            border-color: #0055ff;
        }

        .cr-btn-print {
            background: #0044cc;
            color: #fff;
            border-color: #003399;
            font-weight: bold;
        }
        .cr-btn-print:hover {
            background: #0033aa;
            color: #fff;
        }

        .cr-divider {
            width: 1px;
            height: 18px;
            background: #b4b4b4;
            margin: 0 4px;
        }

        .cr-tools-right {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 11px;
            color: #333;
        }

        .cr-close-btn {
            background: #fff;
            border: 1px solid #999;
            padding: 2px 12px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            border-radius: 3px;
        }

        .cr-close-btn:hover {
            background: #fee;
            border-color: #c00;
            color: #c00;
        }

        /* ── Main Report Area ── */
        .cr-main-area {
            display: flex;
            flex: 1;
            overflow: auto;
        }

        /* Left Side Navigation */
        .cr-tree-nav {
            width: 140px;
            background: #ffffff;
            border-right: 1px solid #999;
            display: flex;
            flex-direction: column;
        }

        .cr-tab-header {
            background: #e4e2de;
            border-bottom: 1px solid #999;
            padding: 5px 8px;
            font-size: 11px;
            font-weight: bold;
            color: #000;
        }

        .cr-tree-body {
            flex: 1;
            background: #fff;
            padding: 10px;
            font-size: 11px;
            color: #333;
        }

        /* Right Canvas Area */
        .cr-canvas-area {
            flex: 1;
            background: #7b8089;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            overflow-y: auto;
        }

        /* Printable Sheet (A5 Landscape: 210mm x 148mm) */
        .report-sheet {
            background: #ffffff;
            width: 204mm;
            max-width: 204mm;
            height: 142mm;
            max-height: 142mm;
            padding: 3.5mm;
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
            box-sizing: border-box;
            position: relative;
            background-color: #ffffff;
            overflow: hidden;
            page-break-inside: avoid;
        }

        /* Outer Double/Solid Border Box */
        .report-box {
            border: 1.8px solid #000000;
            padding: 5px 7px 5px 7px;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Header Layout Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #000;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .logo-col {
            width: 110px;
            text-align: left;
        }

        .logo-col img {
            width: 95px;
            height: auto;
            max-height: 48px;
            object-fit: contain;
            display: block;
        }

        .company-middle {
            text-align: center;
            padding: 0 4px;
        }

        .company-name {
            font-size: 20px;
            font-weight: 900;
            color: #8b0000;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            line-height: 1.05;
            margin-bottom: 1px;
        }

        .company-tagline {
            font-size: 9px;
            font-weight: bold;
            color: #003087;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 1px;
        }

        .company-address {
            font-size: 9.5px;
            color: #111;
            line-height: 1.2;
            font-weight: 500;
        }

        .company-tax {
            font-size: 9.5px;
            font-weight: bold;
            color: #000;
            margin-top: 1px;
        }

        .logo-right {
            width: 110px;
            text-align: right;
        }

        .logo-right img {
            width: 95px;
            height: auto;
            max-height: 48px;
            object-fit: contain;
            display: block;
            margin-left: auto;
        }

        /* Title Badge */
        .badge-container {
            text-align: center;
            margin: 1px 0 4px 0;
        }

        .voucher-title-badge {
            background: #8b0000;
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
            text-align: center;
            letter-spacing: 1px;
            padding: 2px 14px;
            border-radius: 2px;
            display: inline-block;
            text-transform: uppercase;
        }

        /* Meta details row (Receipt No, Date, Time, Status) */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 4px;
            background: #f8fafc;
            border: 1px solid #94a3b8;
        }

        .meta-table td {
            padding: 3px 5px;
            vertical-align: middle;
            border: none;
        }

        .meta-label {
            font-weight: bold;
            color: #1e293b;
        }

        .meta-val {
            font-weight: 700;
            color: #000;
        }

        /* Customer / Payer Details Box */
        .party-box {
            border: 1px solid #94a3b8;
            padding: 3px 6px;
            background: #ffffff;
            margin-bottom: 4px;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .party-name {
            font-size: 12px;
            font-weight: 900;
            color: #000;
            text-transform: uppercase;
        }

        /* Items / Invoice Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 4px;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 2.5px 4px;
        }

        .data-table th {
            background: #e2e8f0;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .data-table td {
            font-weight: 600;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .total-row td {
            background: #fdf2f2;
            font-weight: bold;
        }

        .highlight-paid {
            background: #ffffcc;
            font-weight: 900;
            color: #0044cc;
            font-size: 10.5px;
        }

        /* Instrument Info Table */
        .instrument-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 4px;
            background: #fafafa;
            border: 1px solid #cbd5e1;
        }

        .instrument-table td {
            padding: 2.5px 5px;
            vertical-align: top;
            border: none;
        }

        .instrument-table .lbl {
            font-weight: bold;
            color: #334155;
            width: 85px;
        }

        /* Amount in Words Box */
        .words-bar {
            border: 1px solid #000;
            background: #f1f5f9;
            padding: 3px 6px;
            font-size: 9.5px;
            font-weight: bold;
            color: #000;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        /* Signature Row */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .signature-table td {
            vertical-align: bottom;
            font-size: 9.5px;
            font-weight: bold;
            color: #000;
            border: none;
            padding: 0 4px;
        }

        .sign-line {
            border-top: 1px dashed #000;
            padding-top: 2px;
            display: inline-block;
            min-width: 120px;
        }

        /* ── Bottom Status Bar ── */
        .cr-status-bar {
            background: #e4e2de;
            border-top: 1px solid #999;
            padding: 3px 15px;
            font-size: 11px;
            display: flex;
            justify-content: space-between;
            color: #333;
            user-select: none;
        }

        @media print {
            @page {
                size: A5 landscape;
                margin: 3mm !important;
            }
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                height: 100% !important;
                width: 100% !important;
            }
            .cr-toolbar, .cr-tree-nav, .cr-status-bar {
                display: none !important;
            }
            .cr-main-area {
                overflow: visible !important;
                display: block !important;
            }
            .cr-canvas-area {
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
                display: block !important;
                overflow: visible !important;
            }
            .report-sheet {
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                height: 100% !important;
                max-height: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
            }
            .report-box {
                border: 1.5px solid #000 !important;
                padding: 4px 6px !important;
                height: 138mm !important;
                box-sizing: border-box !important;
            }
            .voucher-title-badge {
                background: #8b0000 !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .data-table th {
                background: #e2e8f0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .total-row td {
                background: #fdf2f2 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .highlight-paid {
                background: #ffffcc !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .words-bar {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .meta-table {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

@php
if (!function_exists('amountToWords')) {
    function amountToWords($amount) {
        $number = round($amount, 2);
        $no = floor($number);
        $decimal = round(($number - $no) * 100);
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '', 1 => 'ONE', 2 => 'TWO',
            3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE', 6 => 'SIX',
            7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE',
            10 => 'TEN', 11 => 'ELEVEN', 12 => 'TWELVE',
            13 => 'THIRTEEN', 14 => 'FOURTEEN', 15 => 'FIFTEEN',
            16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN',
            19 => 'NINETEEN', 20 => 'TWENTY', 30 => 'THIRTY',
            40 => 'FORTY', 50 => 'FIFTY', 60 => 'SIXTY',
            70 => 'SEVENTY', 80 => 'EIGHTY', 90 => 'NINETY'
        );
        $digits = array('', 'HUNDRED', 'THOUSAND', 'LAKH', 'CRORE');
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? '' : '';
                $hundred = ($counter == 1 && $str[0]) ? ' AND ' : null;
                $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? " AND " . ($words[$decimal / 10 * 10] . " " . $words[$decimal % 10]) . ' PAISE' : '';
        return 'RUPEES ' . ($Rupees ? trim($Rupees) : 'ZERO') . $paise . ' ONLY';
    }
}

$netReceived = (float)($receipt->receipt_amount);
@endphp

    <!-- Top Crystal Reports Toolbar -->
    <div class="cr-toolbar">
        <div class="cr-tools-left">
            <button class="cr-btn cr-btn-print" onclick="window.print();" title="Print (A5)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Print Receipt (A5)
            </button>

            <div class="cr-divider"></div>

            <button class="cr-btn" title="First Page" onclick="window.scrollTo(0,0);">&#124;&#9664;</button>
            <button class="cr-btn" title="Previous Page" onclick="window.scrollTo(0,0);">&#9664;</button>
            <button class="cr-btn" title="Next Page" onclick="window.scrollTo(0,0);">&#9654;</button>
            <button class="cr-btn" title="Last Page" onclick="window.scrollTo(0,0);">&#9654;&#124;</button>

            <div class="cr-divider"></div>

            <span style="font-size: 11px; font-weight: 600;">Page 1 / 1 (A5 Landscape)</span>
        </div>

        <div class="cr-tools-right">
            <span style="font-weight: bold; color: #444;">OMKAAR LOGISTICS ERP</span>
            <button class="cr-close-btn" onclick="window.close();">Close</button>
        </div>
    </div>

    <!-- Main Report Body Area -->
    <div class="cr-main-area">
        <!-- Left Side Tree -->
        <div class="cr-tree-nav">
            <div class="cr-tab-header">Main Report</div>
            <div class="cr-tree-body">
                <div style="font-weight: bold; color: #0044cc;">✔ Money Receipt</div>
                <div style="margin-top: 6px; color: #555;">Series: <strong>{{ $receipt->series }}</strong></div>
                <div style="margin-top: 3px; color: #555;">No: <strong>#{{ $receipt->receipt_no }}</strong></div>
                <div style="margin-top: 3px; color: #555;">Amount: <strong>₹{{ number_format($netReceived, 2) }}</strong></div>
            </div>
        </div>

        <!-- Right Canvas Area -->
        <div class="cr-canvas-area">
            <div class="report-sheet">
                <div class="report-box">

                    <div>
                        <!-- Header Table with Logo and Company Branding -->
                        <table class="header-table">
                            <tr>
                                <td class="logo-col">
                                    <img src="{{ asset('assets/logo.jpg') }}" alt="Omkaar Logistics" onerror="this.style.display='none'">
                                </td>
                                <td class="company-middle">
                                    <div class="company-name">OMKAAR LOGISTICS</div>
                                    <div class="company-tagline">FLEET OWNERS &bull; TRANSPORT CONTRACTORS</div>
                                    <div class="company-address">
                                        Head Office: Lokhra Lalunggaon Near NPS School, Guwahati - 781040 (Assam)<br>
                                        Phone: +91 98640-82153, 97335-35513 &bull; Email: omkaar.lagistics@gmail.com
                                    </div>
                                    <div class="company-tax">
                                        GSTIN: 18AAHFO6045J1ZY &nbsp;|&nbsp; PAN: AAHFO6045J
                                    </div>
                                </td>
                                <td class="logo-right">
                                    <img src="{{ asset('assets/logo.jpg') }}" alt="Omkaar Logistics" onerror="this.style.display='none'">
                                </td>
                            </tr>
                        </table>

                        <!-- Title Badge -->
                        <div class="badge-container">
                            <div class="voucher-title-badge">MONEY RECEIPT / CREDIT VOUCHER</div>
                        </div>

                        <!-- Meta Details -->
                        <table class="meta-table">
                            <tr>
                                <td style="width: 28%;"><span class="meta-label">RECEIPT NO :</span> <span class="meta-val" style="color:#8b0000; font-size:11px;">{{ $receipt->series }}-{{ $receipt->receipt_no }}</span> @if($receipt->voucher_no)<span style="font-size:9px; color:#666;">(Voucher: {{ $receipt->voucher_no }})</span>@endif</td>
                                <td style="width: 24%;"><span class="meta-label">DATE :</span> <span class="meta-val">{{ !empty($receipt->receipt_date) ? ($receipt->receipt_date instanceof \Carbon\Carbon ? $receipt->receipt_date->format('d-m-Y') : \Carbon\Carbon::parse($receipt->receipt_date)->format('d-m-Y')) : date('d-m-Y') }}</span></td>
                                <td style="width: 24%;"><span class="meta-label">TIME :</span> <span class="meta-val">{{ !empty($receipt->receipt_time) ? \Carbon\Carbon::parse($receipt->receipt_time)->format('h:i:s A') : date('h:i:s A') }}</span></td>
                                <td style="width: 24%; text-align: right;"><span class="meta-label">STATUS :</span> <span class="meta-val" style="text-transform:uppercase; color:{{ $receipt->status === 'cancelled' ? '#c00' : '#008000' }};">{{ strtoupper($receipt->status ?? 'CONFIRMED') }}</span></td>
                            </tr>
                        </table>

                        <!-- Customer / Payer Details Box -->
                        <div class="party-box">
                            <div>
                                <span style="font-size:9.5px; color:#555; text-transform:uppercase; font-weight:bold;">RECEIVED WITH THANKS FROM:</span><br>
                                <span class="party-name">{{ $receipt->account_name }}</span>
                            </div>
                            <div style="text-align: right; font-size: 9.5px;">
                                @if($receipt->account && $receipt->account->under_group)
                                    <div><strong>Under Group:</strong> {{ $receipt->account->under_group }}</div>
                                @endif
                                @if($receipt->mobile || ($receipt->account && $receipt->account->mobile))
                                    <div><strong>Contact:</strong> {{ $receipt->mobile ?: $receipt->account->mobile }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Table of Invoices Settled -->
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 32px;">Sr.</th>
                                    <th style="width: 45px;">Series</th>
                                    <th style="width: 70px;">Inv No</th>
                                    <th style="width: 65px;">Bill Amt (₹)</th>
                                    <th style="width: 65px;">Old Paid (₹)</th>
                                    <th style="width: 65px;">Due Amt (₹)</th>
                                    <th style="width: 55px;">Disc (₹)</th>
                                    <th style="width: 55px;">TDS (₹)</th>
                                    <th style="width: 75px;">Recd Amt (₹)</th>
                                    <th style="width: 65px;">Balance (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sr = 1; @endphp
                                @forelse ($receipt->items as $item)
                                    <tr>
                                        <td class="text-center">{{ $sr++ }}</td>
                                        <td class="text-center">{{ $item->series }}</td>
                                        <td class="text-center font-bold">{{ $item->invoice_no }}</td>
                                        <td class="text-right">{{ number_format($item->bill_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->old_paid, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->due_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->tds, 2) }}</td>
                                        <td class="text-right highlight-paid">{{ number_format($item->paid_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->balance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td class="text-center">{{ $receipt->series }}</td>
                                        <td class="text-center font-bold">On Account</td>
                                        <td class="text-right">{{ number_format($receipt->bill_amount > 0 ? $receipt->bill_amount : $receipt->receipt_amount, 2) }}</td>
                                        <td class="text-right">0.00</td>
                                        <td class="text-right">{{ number_format($receipt->due_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($receipt->discount_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($receipt->tds_amount, 2) }}</td>
                                        <td class="text-right highlight-paid">{{ number_format($receipt->receipt_amount, 2) }}</td>
                                        <td class="text-right">{{ number_format($receipt->balance_amount, 2) }}</td>
                                    </tr>
                                @endforelse
                                <tr class="total-row">
                                    <td colspan="3" class="text-center" style="font-weight:900;">TOTAL :</td>
                                    <td class="text-right">₹{{ number_format($receipt->bill_amount > 0 ? $receipt->bill_amount : $receipt->receipt_amount, 2) }}</td>
                                    <td class="text-right">-</td>
                                    <td class="text-right">₹{{ number_format($receipt->due_amount, 2) }}</td>
                                    <td class="text-right">₹{{ number_format($receipt->discount_amount, 2) }}</td>
                                    <td class="text-right">₹{{ number_format($receipt->tds_amount, 2) }}</td>
                                    <td class="text-right highlight-paid" style="font-size:11px;">₹{{ number_format($receipt->receipt_amount, 2) }}</td>
                                    <td class="text-right">₹{{ number_format($receipt->balance_amount, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Payment Mode & Instrument Details -->
                        <table class="instrument-table">
                            <tr>
                                <td class="lbl">PAY MODE :</td>
                                <td style="width: 38%;"><strong>{{ $receipt->pay_mode ?: 'BANK TRANSFER' }}</strong></td>
                                <td class="lbl">DEPOSITED IN :</td>
                                <td><strong>{{ $receipt->bank_name ?: ($receipt->bank ? $receipt->bank->ledger_name : 'OMKAAR CBI CD / CASH') }}</strong></td>
                            </tr>
                            <tr>
                                <td class="lbl">CHQ / REF NO :</td>
                                <td>{{ $receipt->cheque_no ?: 'N/A' }}</td>
                                <td class="lbl">CHQ DATE :</td>
                                <td>{{ !empty($receipt->cheque_date) ? ($receipt->cheque_date instanceof \Carbon\Carbon ? $receipt->cheque_date->format('d-m-Y') : \Carbon\Carbon::parse($receipt->cheque_date)->format('d-m-Y')) : 'N/A' }}</td>
                            </tr>
                            @if($receipt->remark)
                            <tr>
                                <td class="lbl">REMARKS :</td>
                                <td colspan="3" style="color: #000; font-weight: 600;">{{ $receipt->remark }}</td>
                            </tr>
                            @endif
                        </table>

                        <!-- Amount in Words -->
                        <div class="words-bar">
                            <span style="color:#555;">AMOUNT IN WORDS:</span> {{ amountToWords($netReceived) }}
                        </div>
                    </div>

                    <!-- Signatures -->
                    <table class="signature-table">
                        <tr>
                            <td style="width: 33%; text-align: left;">
                                <div class="sign-line">Customer / Depositor's Signature</div>
                            </td>
                            <td style="width: 34%; text-align: center;">
                                <div style="color: #555; font-size: 9px; margin-bottom: 2px;">Prepared By: {{ $receipt->user->username ?? 'Cashier' }}</div>
                                <div class="sign-line">Accountant / Cashier</div>
                            </td>
                            <td style="width: 33%; text-align: right;">
                                <div style="color: #8b0000; font-size: 9px; margin-bottom: 2px; font-weight:bold;">For OMKAAR LOGISTICS</div>
                                <div class="sign-line">Authorised Signatory</div>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Status Bar -->
    <div class="cr-status-bar">
        <span>Document: Money Receipt #{{ $receipt->series }}-{{ $receipt->receipt_no }}</span>
        <span>Paper Size: A5 (Landscape)</span>
        <span>Page: 1 of 1</span>
    </div>

</body>
</html>
