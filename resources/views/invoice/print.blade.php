<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->series }}-{{ $invoice->invoice_no }} - Omkaar Logistics</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            color: #000;
        }

        @page {
            size: 297mm 210mm;
            margin: 0 !important;
        }

        body {
            background-color: #f4f6f8;
            font-size: 13.5px;
            padding: 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .invoice-container {
            width: 287mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            position: relative;
        }

        /* Non-GST Party Bill Container - Full A4 Landscape Height with perfect borders */
        .standard-bill-container {
            border: 1.2px solid #000;
            padding: 5px 7px;
            background: #fff;
            box-sizing: border-box;
            width: 100%;
            height: 200mm;
            min-height: 200mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Floating Action Bar */
        .no-print-bar {
            width: 287mm;
            max-width: 100%;
            margin: 0 auto 8px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print-action {
            background: #0f3460;
            color: #fff;
            padding: 6px 16px;
            border: none;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            text-decoration: none;
        }

        .btn-print-action:hover {
            background: #162447;
        }

        .btn-download-action {
            background: #27ae60;
            color: #fff;
            padding: 6px 16px;
            border: none;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
            border-radius: 3px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            text-decoration: none;
        }

        .btn-download-action:hover {
            background: #219653;
        }

        .btn-back-action {
            background: #e2e8f0;
            color: #1e293b;
            padding: 6px 12px;
            border: 1px solid #cbd5e1;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
            border-radius: 3px;
            text-decoration: none;
        }

        /* ========================================================= */
        /* STANDARD (NON-GST) PARTY BILL STYLES                      */
        /* ========================================================= */
        .top-header-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
            gap: 10px;
        }

        .company-bill-to-left {
            flex: 1;
        }

        .company-name-title {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 0.5px;
            line-height: 1.15;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .company-sub-details {
            font-size: 14px;
            line-height: 1.45;
            margin-bottom: 4px;
        }

        .bill-to-section {
            font-size: 13.5px;
            line-height: 1.45;
            margin-top: 4px;
        }

        .bill-to-title {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 2px;
        }

        .bill-to-party-name {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .meta-box-table {
            border-collapse: collapse;
            border: 1.2px solid #000;
            width: 380px;
            font-size: 13.5px;
        }

        .meta-box-table th,
        .meta-box-table td {
            border: 1.2px solid #000;
            padding: 6px 6px;
            text-align: center;
        }

        .meta-box-table th {
            font-weight: bold;
            background: #fff;
            text-transform: uppercase;
            font-size: 13.5px;
        }

        .meta-box-table td {
            font-weight: bold;
            font-size: 14.5px;
        }

        .bill-table-block {
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .invoice-main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #000;
            font-size: 12.5px;
            table-layout: fixed;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .invoice-main-table thead {
            display: table-header-group;
        }

        .invoice-main-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .invoice-main-table th {
            border: 1px solid #000;
            padding: 6px 3px !important;
            text-align: center;
            font-weight: bold;
            background: #fff;
            text-transform: uppercase;
            font-size: 12px;
            line-height: 1.2;
            white-space: normal !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        .invoice-main-table td {
            border: 1px solid #000;
            padding: 6px 6px;
            font-size: 12.5px;
            line-height: 1.35;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .cell-nowrap { white-space: nowrap !important; }
        .cell-wrap { white-space: normal !important; word-wrap: break-word !important; overflow-wrap: break-word !important; }

        .amount-words-bar {
            border: 1.2px solid #000;
            border-top: none;
            padding: 7px 10px;
            font-size: 13.5px;
            font-weight: bold;
            text-transform: uppercase;
            background: #fff;
            box-sizing: border-box;
            width: 100%;
        }

        /* Bottom Summary Grid - Full 4-sided black border with clear top closing line */
        .bottom-summary-grid {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            border: 1.2px solid #000;
            margin-top: auto;
            margin-bottom: 0;
            page-break-inside: avoid;
            box-sizing: border-box;
            width: 100%;
        }

        .bottom-left-notes {
            flex: 1;
            padding: 8px 10px;
            font-size: 13px;
            line-height: 1.45;
            border-right: 1.2px solid #000;
        }

        .bank-details-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 2px;
            margin-bottom: 2px;
            font-size: 14px;
        }

        .notes-list {
            margin-top: 3px;
            font-size: 12px;
            line-height: 1.4;
        }

        .bottom-right-totals {
            width: 360px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .totals-sub-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .totals-sub-table td {
            border-bottom: 1.2px solid #000;
            padding: 5px 6px;
        }

        .totals-sub-table td:first-child {
            border-right: 1.2px solid #000;
            font-weight: bold;
        }

        .totals-sub-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .signature-box {
            padding: 24px 10px 8px 10px;
            text-align: right;
            font-size: 13.5px;
            font-weight: bold;
        }

        /* ========================================================= */
        /* GST BILL DEDICATED LAYOUT STYLES (MATCHING USER SCREENSHOTS) */
        /* ========================================================= */
        .gst-page-1-wrapper {
            border: 1.2px solid #000;
            padding: 5px 7px;
            background: #fff;
            box-sizing: border-box;
            width: 100%;
            height: 200mm;
            min-height: 200mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .gst-company-header {
            margin-bottom: 10px;
        }

        .gst-company-title {
            font-family: "Times New Roman", Times, serif, Arial;
            font-size: 38px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            line-height: 1.15;
            margin-bottom: 4px;
        }

        .gst-company-sub {
            font-size: 14.5px;
            line-height: 1.5;
        }

        .gst-content-box {
            border: 1.2px solid #000;
            width: 100%;
            box-sizing: border-box;
            margin-top: auto;
            flex: none;
        }

        .gst-billto-meta-container {
            display: flex;
            border-bottom: 1.2px solid #000;
        }

        .gst-billto-left {
            flex: 1;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.5;
            border-right: 1.2px solid #000;
        }

        .gst-billto-head {
            font-weight: bold;
            font-size: 15.5px;
            margin-bottom: 4px;
        }

        .gst-billto-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .gst-meta-right {
            width: 390px;
        }

        .gst-meta-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .gst-meta-table th,
        .gst-meta-table td {
            border-bottom: 1.2px solid #000;
            border-right: 1.2px solid #000;
            padding: 7px 6px;
            text-align: center;
        }

        .gst-meta-table th:last-child,
        .gst-meta-table td:last-child {
            border-right: none;
        }

        .gst-meta-table th {
            font-weight: bold;
            background: #fff;
            text-transform: uppercase;
            font-size: 14px;
        }

        .gst-meta-table td {
            font-weight: bold;
            font-size: 15px;
        }

        /* Description & Tax Breakdown Table */
        .gst-tax-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .gst-tax-table th {
            border-bottom: 1.2px solid #000;
            border-right: 1.2px solid #000;
            padding: 7px 8px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            background: #fff;
            font-size: 14.5px;
        }

        .gst-tax-table th:last-child {
            border-right: none;
        }

        .gst-tax-table td {
            border-right: 1.2px solid #000;
            padding: 6px 8px;
            font-size: 14px;
        }

        .gst-tax-table td:last-child {
            border-right: none;
        }

        .gst-desc-cell {
            padding: 12px 12px !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 15.5px;
            vertical-align: top;
        }

        .gst-hsn-cell {
            text-align: center;
            font-weight: bold;
            vertical-align: top;
            padding-top: 12px !important;
            font-size: 15.5px;
        }

        .gst-amt-top-cell {
            text-align: right;
            font-weight: bold;
            vertical-align: top;
            padding-top: 12px !important;
            font-size: 15.5px;
        }

        .gst-tax-row td {
            border-top: 1.2px solid #000;
            font-size: 14px;
        }

        .gst-total-row td {
            border-top: 1.2px solid #000;
            border-bottom: 1.2px solid #000;
            font-weight: bold;
            font-size: 16.5px;
            padding: 8px 8px;
        }

        /* Bottom Bank Details */
        .gst-bank-bottom-row {
            display: flex;
            align-items: stretch;
            min-height: 125px;
        }

        .gst-bank-bottom-left {
            width: 660px;
            padding: 10px 12px;
            font-size: 13.5px;
            line-height: 1.5;
            border-right: 1.2px solid #000;
        }

        .gst-bank-bottom-right {
            flex: 1;
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            padding: 10px 14px;
        }

        /* Page 2 Annexure Styles */
        .gst-annexure-wrapper {
            background: #fff;
            width: 100%;
            box-sizing: border-box;
            min-height: 200mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .transportation-header-box {
            border: 1.2px solid #000;
            text-align: center;
            padding: 7px 0;
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            background: #fff;
        }

        .annexure-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #000;
            border-top: none;
            font-size: 12.5px;
            table-layout: fixed;
            box-sizing: border-box;
            page-break-inside: auto;
        }

        .annexure-table thead {
            display: table-header-group;
        }

        .annexure-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .annexure-table th {
            border: 1px solid #000;
            padding: 5px 2px !important;
            text-align: center;
            font-weight: bold;
            background: #fff;
            text-transform: uppercase;
            font-size: 11.5px;
            line-height: 1.2;
            white-space: normal !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        .annexure-table td {
            border: 1px solid #000;
            padding: 4px 4px;
            font-size: 12px;
            line-height: 1.25;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .annexure-total-row td {
            font-weight: bold;
            border-top: 1.2px solid #000;
            border-bottom: 1.2px solid #000;
            font-size: 13.5px;
            padding: 5px 6px;
        }

        .annexure-words-bar {
            border: 1.2px solid #000;
            border-top: none;
            padding: 5px 8px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            box-sizing: border-box;
            width: 100%;
            page-break-after: avoid;
            break-after: avoid;
        }

        .annexure-bottom-section {
            border: 1.2px solid #000;
            margin-top: auto;
            margin-bottom: 0;
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            box-sizing: border-box;
            width: 100%;
        }

        .annexure-notes-box {
            flex: 1;
            padding: 6px 10px;
            font-size: 11px;
            line-height: 1.35;
            border-right: 1.2px solid #000;
        }

        .annexure-sig-box {
            width: 320px;
            padding: 14px 10px 6px 10px;
            text-align: right;
            font-size: 13px;
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
            break-after: page;
            height: 0;
            margin: 0;
            padding: 0;
        }

        @media screen {
            .page-break {
                margin: 20px 0;
                border-top: 2px dashed #999;
                position: relative;
            }
            .page-break::after {
                content: "— PAGE BREAK —";
                position: absolute;
                top: -9px;
                left: 50%;
                transform: translateX(-50%);
                background: #f4f6f8;
                padding: 0 10px;
                font-size: 9px;
                color: #666;
                font-weight: bold;
            }
            .gst-annexure-wrapper {
                border: 1.2px solid #000;
                padding: 5px 7px;
            }
        }

        @media print {
            @page {
                size: 297mm 210mm;
                margin: 0 !important;
            }
            html, body {
                width: 297mm !important;
                height: auto !important;
                min-height: 100% !important;
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .invoice-container {
                width: 287mm !important;
                max-width: 287mm !important;
                height: auto !important;
                min-height: auto !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 auto !important;
            }
            .gst-page-1-wrapper {
                border: 1.2px solid #000 !important;
                padding: 4px 6px !important;
                width: 287mm !important;
                max-width: 287mm !important;
                height: 200mm !important;
                min-height: 200mm !important;
                margin: 5mm auto !important;
                box-sizing: border-box !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .gst-content-box {
                margin-top: auto !important;
                flex: none !important;
            }
            .gst-annexure-wrapper {
                display: block !important;
                border: none !important;
                padding: 4mm 0 0 0 !important;
                width: 287mm !important;
                margin: 0 auto !important;
                height: auto !important;
                min-height: auto !important;
                box-sizing: border-box !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            .annexure-table {
                width: 100% !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            .annexure-table thead {
                display: table-header-group !important;
            }
            .annexure-table tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .annexure-bottom-section {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin-top: 6px !important;
            }
            .standard-bill-container {
                border: 1.2px solid #000 !important;
                padding: 4px 6px !important;
                width: 287mm !important;
                max-width: 287mm !important;
                min-height: 200mm !important;
                height: auto !important;
                margin: 5mm auto !important;
                box-sizing: border-box !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            .page-break {
                page-break-after: always !important;
                break-after: page !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
            }
            .page-break::after {
                content: "" !important;
                display: none !important;
            }
        }
    </style>
</head>
<body>

    @php
    if (!function_exists('convertNumberToIndianWords')) {
        function convertNumberToIndianWords(float $number)
        {
            $decimal = round($number - ($no = floor($number)), 2) * 100;
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
            return 'RUPEES: ' . ($Rupees ? trim($Rupees) : 'ZERO') . $paise . ' ONLY.';
        }
    }

    // Format Working Month (e.g. Jul-2026)
    $workingMonth = '';
    if (!empty($invoice->for_month)) {
        if (preg_match('/^([A-Za-z]{3})[\/\-]?(\d{4})$/', $invoice->for_month, $m)) {
            $workingMonth = ucfirst(strtolower($m[1])) . '-' . $m[2];
        } elseif (preg_match('/^(\d{4})-(\d{2})$/', $invoice->for_month, $m)) {
            try {
                $workingMonth = \Carbon\Carbon::createFromFormat('Y-m', $invoice->for_month)->format('M-Y');
            } catch (\Exception $e) {
                $workingMonth = $invoice->for_month;
            }
        } else {
            $workingMonth = str_replace('/', '-', $invoice->for_month);
        }
    } elseif ($invoice->invoice_date) {
        $workingMonth = $invoice->invoice_date->format('M-Y');
    }

    // Format Invoice No Display (e.g. GSTOML2627045)
    $displayInvoiceNo = $invoice->formatted_invoice_no;

    // Check if any bilties in this invoice have a PARTY CN NO
    $hasPartyCnNo = false;
    foreach ($invoice->items as $item) {
        if (!empty(trim($item->cn_no ?? ''))) {
            $hasPartyCnNo = true;
            break;
        }
    }

    // State Code & State Name Resolution
    $stateCodes = [
        '01' => 'Jammu and Kashmir', '02' => 'Himachal Pradesh', '03' => 'Punjab',
        '04' => 'Chandigarh', '05' => 'Uttarakhand', '06' => 'Haryana',
        '07' => 'Delhi', '08' => 'Rajasthan', '09' => 'Uttar Pradesh',
        '10' => 'Bihar', '11' => 'Sikkim', '12' => 'Arunachal Pradesh',
        '13' => 'Nagaland', '14' => 'Manipur', '15' => 'Mizoram',
        '16' => 'Tripura', '17' => 'Meghalaya', '18' => 'Assam',
        '19' => 'West Bengal', '20' => 'Jharkhand', '21' => 'Odisha',
        '22' => 'Chhattisgarh', '23' => 'Madhya Pradesh', '24' => 'Gujarat',
        '26' => 'Dadra and Nagar Haveli and Daman and Diu', '27' => 'Maharashtra',
        '28' => 'Andhra Pradesh', '29' => 'Karnataka', '30' => 'Goa',
        '31' => 'Lakshadweep', '32' => 'Kerala', '33' => 'Tamil Nadu',
        '34' => 'Puducherry', '35' => 'Andaman and Nicobar Islands',
        '36' => 'Telangana', '37' => 'Andhra Pradesh', '38' => 'Ladakh',
    ];

    $gstNo = trim($invoice->account->gst_no ?? '');
    $stateCode = '';
    $stateName = '';

    if (strlen($gstNo) >= 2 && is_numeric(substr($gstNo, 0, 2))) {
        $stateCode = substr($gstNo, 0, 2);
    } elseif (!empty($invoice->account->state_code)) {
        $stateCode = str_pad($invoice->account->state_code, 2, '0', STR_PAD_LEFT);
    } elseif (!empty($invoice->account->stateRelation->code)) {
        $stateCode = str_pad($invoice->account->stateRelation->code, 2, '0', STR_PAD_LEFT);
    } else {
        $stateCode = '18';
    }

    if (!empty($invoice->account->stateRelation->name)) {
        $stateName = ucwords(strtolower($invoice->account->stateRelation->name));
    } elseif (isset($stateCodes[$stateCode])) {
        $stateName = $stateCodes[$stateCode];
    } else {
        $stateName = 'Assam';
    }

    // GST Calculations
    $taxableAmount = (float)$invoice->bill_amount;
    $gstPercent = $invoice->gst_percent > 0 ? (float)$invoice->gst_percent : 18.0;
    $isIgst = (bool)$invoice->is_igst;

    if ($invoice->gst_amount > 0) {
        $totalGstLiability = (float)$invoice->gst_amount;
    } else {
        $totalGstLiability = round($taxableAmount * ($gstPercent / 100), 2);
    }

    $cgstRate = $gstPercent / 2;
    $sgstRate = $gstPercent / 2;
    $cgstAmt = round($totalGstLiability / 2, 2);
    $sgstAmt = round($totalGstLiability / 2, 2);
    $igstAmt = $totalGstLiability;

    $grandTotal = $invoice->total_amount > 0 ? (float)$invoice->total_amount : ($taxableAmount + $totalGstLiability);
    @endphp

    @if(!isset($isPdf))
    <!-- Action Buttons for Browser View -->
    <div class="no-print-bar">
        <a href="{{ route('invoice.create') }}" class="btn-back-action">← Back to Create Invoice</a>
        <div style="display: flex; gap: 8px; align-items: center;">
            <a href="{{ route('invoice.pdf', ['id' => $invoice->id, 'download' => 1]) }}" class="btn-download-action">📥 Download Invoice</a>
            <button class="btn-print-action" onclick="window.print()">🖨 Print Invoice</button>
        </div>
    </div>
    @endif

    <div class="invoice-container">

        @if($invoice->is_gst_bill)
            {{-- ========================================================================= --}}
            {{-- GST BILL FORMAT (PAGE 1: SUMMARY + PAGE 2+: TRANSPORTATION DETAILS)      --}}
            {{-- ========================================================================= --}}

            <!-- PAGE 1: TAX INVOICE SUMMARY -->
            <div class="gst-page-1-wrapper">
                <!-- Top Header -->
                <div class="gst-company-header">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <img src="{{ asset('assets/logo.jpg') }}" alt="Logo" style="width: 155px; height: auto; object-fit: contain; max-height: 105px;" onerror="this.style.display='none'">
                            <div>
                                <div class="gst-company-title">OMKAAR LOGISTICS</div>
                                <div class="gst-company-sub">
                                    LALUNGGAON, NEAR NPS SCHOLL, KAMRUP METROPOLITAN, ASSAM, 781040<br>
                                    <strong>GST :</strong> 18AAHFO6045J1ZY &nbsp;&nbsp; <strong>PAN :</strong> AAHFO6045J<br>
                                    <strong>E-mail :</strong> omkaar.lagistics@gmail.com
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Outer Content Box -->
                    <div class="gst-content-box">
                        <!-- Bill To & Meta Section -->
                        <div class="gst-billto-meta-container">
                            <!-- Left: Bill To -->
                            <div class="gst-billto-left">
                                <div class="gst-billto-head">BILL TO</div>
                                <div class="gst-billto-name">{{ $invoice->account_name }}</div>
                                @if($invoice->account && $invoice->account->address)
                                    <div>{{ $invoice->account->address }}</div>
                                @endif
                                <div>
                                    <strong>GSTIN :</strong> {{ $invoice->account->gst_no ?? '' }}
                                    &nbsp;&nbsp;
                                    <strong>PHONE :</strong> {{ $invoice->account->mobile ?: ($invoice->account->phone_o ?? '') }}
                                </div>
                                <div style="margin-top: 1px;">
                                    <strong>State Name :</strong> {{ $stateName }}
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <strong>State Code :</strong> {{ $stateCode }}
                                </div>
                            </div>

                            <!-- Right: Meta Box -->
                            <div class="gst-meta-right">
                                <table class="gst-meta-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 32%;">WORKING</th>
                                            <th style="width: 38%;">INVOICE NO</th>
                                            <th style="width: 30%;">INVOICE DATE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $workingMonth }}</td>
                                            <td>{{ $displayInvoiceNo }}</td>
                                            <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d-M-Y') : '' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Description & Tax Liability Grid Table -->
                        <table class="gst-tax-table">
                            <thead>
                                <tr>
                                    <th style="width: 60%;">DESCRIPTION</th>
                                    <th style="width: 20%;">HSN/SAC CODE</th>
                                    <th style="width: 20%;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Main Description Row -->
                                <tr class="main-desc-row">
                                    <td class="gst-desc-cell" style="height: 110px;">
                                        SUPPLY OF LOGISTICS SERVICES {{ $workingMonth }}
                                    </td>
                                    <td class="gst-hsn-cell">
                                        996812
                                    </td>
                                    <td class="gst-amt-top-cell">
                                        {{ number_format($taxableAmount, 2) }}
                                    </td>
                                </tr>

                                <!-- Tax Breakdown Rows -->
                                <tr class="gst-tax-row">
                                    <td rowspan="4" style="border-top: 1.2px solid #000; border-right: 1.2px solid #000;"></td>
                                    <td style="text-align: center; font-weight: bold;">CGST {{ number_format($cgstRate, 0) }}%</td>
                                    <td style="text-align: right; font-weight: bold;">
                                        {{ !$isIgst ? number_format($cgstAmt, 2) : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: bold; border-top: 1px solid #000;">SGST {{ number_format($sgstRate, 0) }}%</td>
                                    <td style="text-align: right; font-weight: bold; border-top: 1px solid #000;">
                                        {{ !$isIgst ? number_format($sgstAmt, 2) : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: bold; border-top: 1px solid #000;">IGST {{ number_format($gstPercent, 0) }}%</td>
                                    <td style="text-align: right; font-weight: bold; border-top: 1px solid #000;">
                                        {{ $isIgst ? number_format($igstAmt, 2) : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-weight: bold; border-top: 1px solid #000;">TOTAL GST LIABILITY</td>
                                    <td style="text-align: right; font-weight: bold; border-top: 1px solid #000;">
                                        {{ number_format($totalGstLiability, 2) }}
                                    </td>
                                </tr>

                                <!-- Total Invoice Amount Row -->
                                <tr class="gst-total-row">
                                    <td colspan="2" style="text-align: center; font-weight: bold; letter-spacing: 0.5px;">
                                        TOTAL INVOICE AMOUNT
                                    </td>
                                    <td style="text-align: right; font-weight: bold;">
                                        {{ number_format($grandTotal, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Bottom Bank Details Section -->
                        <div class="gst-bank-bottom-row">
                            <div class="gst-bank-bottom-left">
                                <div><strong>PAN :</strong> AAHFO6045J</div>
                                <div style="font-weight: bold; font-size: 14.5px; text-decoration: underline; margin-top: 2px; margin-bottom: 2px;">Bank Detail's</div>
                                <div style="font-weight: bold; font-size: 14.5px;">Omkaar Logistics</div>
                                <div><strong>Account No :</strong> 5202870649</div>
                                <div><strong>IFSC Code :</strong> CBIN0283591</div>
                                <div><strong>Bank Name :</strong> Central Bank of India</div>
                                <div><strong>Branch :</strong> Lalganesh</div>
                            </div>
                            <div class="gst-bank-bottom-right">
                                <div style="text-align: right; font-size: 13.5px; font-weight: bold;">
                                    <div style="font-weight: normal; font-size: 13.5px;">For</div>
                                    <div style="margin-top: 2px; font-size: 16px;">Omkaar Logistics</div>
                                    <div style="margin-top: 6px; font-size: 11px; font-weight: normal; font-style: italic; color: #444;">
                                        (This is a System generated Bill)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Page Break between Summary and Annexure -->
            <div class="page-break"></div>

            <!-- PAGE 2+: TRANSPORTATION DETAILS ANNEXURE -->
            <div class="gst-annexure-wrapper">
                <div>
                    <!-- Annexure Header Box -->
                    <div class="transportation-header-box">
                        TRANSPORTATION DETAILS
                    </div>

                    <!-- Main Items Table -->
                    <table class="annexure-table">
                        <thead>
                            <tr>
                                <th style="width: {{ $hasPartyCnNo ? '6%' : '6.8%' }};">DATE</th>
                                <th style="width: {{ $hasPartyCnNo ? '4.5%' : '4.8%' }};">C.N NO.</th>
                                @if($hasPartyCnNo)
                                    <th style="width: 5.5%;">PARTY CN NO</th>
                                @endif
                                <th style="width: {{ $hasPartyCnNo ? '3%' : '3.2%' }};">PKT</th>
                                <th style="width: {{ $hasPartyCnNo ? '7.5%' : '7.5%' }};">FROM</th>
                                <th style="width: {{ $hasPartyCnNo ? '11%' : '11.2%' }};">DESTINATION</th>
                                <th style="width: {{ $hasPartyCnNo ? '12%' : '13.5%' }};">CONSIGNEE</th>
                                <th style="width: {{ $hasPartyCnNo ? '7%' : '8%' }};">ITEM GOODS</th>
                                <th style="width: {{ $hasPartyCnNo ? '9.5%' : '9.5%' }};">INVOICE No.</th>
                                <th style="width: 5%;">WGT</th>
                                <th style="width: {{ $hasPartyCnNo ? '5.5%' : '6%' }};">RT/KG/CB</th>
                                <th style="width: 4.5%;">STAT. CHG</th>
                                <th style="width: 5.5%;">FR.AMT</th>
                                <th style="width: {{ $hasPartyCnNo ? '5.5%' : '6%' }};">UNLOADING</th>
                                <th style="width: {{ $hasPartyCnNo ? '8%' : '8.5%' }};">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="text-center cell-nowrap">{{ $item->bilty_date ? $item->bilty_date->format('j-m-Y') : '' }}</td>
                                    <td class="text-center font-bold cell-nowrap">{{ $item->bilty_no }}</td>
                                    @if($hasPartyCnNo)
                                        <td class="text-center cell-nowrap">{{ $item->cn_no ?? '' }}</td>
                                    @endif
                                    <td class="text-center cell-nowrap">{{ $item->packages }}</td>
                                    <td class="text-left cell-wrap" style="text-transform: uppercase;">{{ $item->from_location }}</td>
                                    <td class="text-left cell-wrap" style="text-transform: uppercase;">{{ $item->to_location }}</td>
                                    <td class="text-left cell-wrap" style="text-transform: uppercase;">{{ $item->consignee_name }}</td>
                                    <td class="text-left cell-wrap" style="text-transform: uppercase;">{{ $item->item_description }}</td>
                                    <td class="text-center cell-wrap">{{ $item->invoice_no_ref }}</td>
                                    <td class="text-center cell-nowrap">
                                        @if(strtoupper(trim($item->weight_type ?? '')) === 'FIXED' || strtoupper(trim($invoice->unit_filter ?? '')) === 'FIXED')
                                            FIXED
                                        @elseif(strtoupper(trim($item->weight_type ?? '')) === 'KG' && $item->weight > 0)
                                            {{ number_format($item->weight, 2) }}
                                        @elseif($item->weight > 0)
                                            {{ number_format($item->weight, 2) }}
                                        @else
                                            {{ $item->weight_type ?: 'FIXED' }}
                                        @endif
                                    </td>
                                    <td class="text-center cell-nowrap">{{ number_format($item->rate, 2) }}</td>
                                    <td class="text-center cell-nowrap">{{ $item->st_charge > 0 ? number_format($item->st_charge, 0) : '0' }}</td>
                                    <td class="text-right cell-nowrap">{{ number_format($item->freight_amount, 2) }}</td>
                                    <td class="text-right cell-nowrap">{{ number_format($item->unload_amount, 2) }}</td>
                                    <td class="text-right font-bold cell-nowrap" style="white-space: nowrap !important;">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                            <!-- Annexure Total Row -->
                            <tr class="annexure-total-row">
                                <td colspan="{{ $hasPartyCnNo ? 11 : 10 }}" style="border-right: none;"></td>
                                <td colspan="2" class="text-center font-bold" style="border-left: 1px solid #000;">Total</td>
                                <td class="text-right"></td>
                                <td class="text-right font-bold cell-nowrap" style="white-space: nowrap !important;">{{ number_format($taxableAmount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Amount in Words Bar -->
                    <div class="annexure-words-bar">
                        {{ convertNumberToIndianWords((float)$taxableAmount) }}
                    </div>
                </div>

                <!-- Notes and Signature Section -->
                <div class="annexure-bottom-section">
                    <div class="annexure-notes-box">
                        <div style="font-weight: bold; font-size: 13.5px; margin-bottom: 3px;">NOTE</div>
                        <div>1. Please pay as per the due date given in this LOGISTICS SERVICES INVOICE.</div>
                        <div>2. Please pay through NEFT/RTGS in favour OMKAAR LOGISTICS</div>
                        <div>3. Kindly e-mail payment advice on <strong>omkaar.lagistics@gmail.com</strong></div>
                        <div>4 Request you to please pay on time to avoid disruption in services.</div>
                        <div>5. TDS to be deducted as per the provision of section 194C.</div>
                        <div>6. Please mail TDS certificate at <strong>omkaar.lagistics@gmail.com</strong></div>
                    </div>
                    <div class="annexure-sig-box">
                        <div style="font-weight: normal; font-size: 13.5px;">For</div>
                        <div style="margin-top: 2px; font-size: 16px; font-weight: bold;">Omkaar Logistics</div>
                        <div style="margin-top: 6px; font-size: 11px; font-weight: normal; font-style: italic; color: #444;">
                            (This is a System generated Bill)
                        </div>
                    </div>
                </div>
            </div>

        @else
            {{-- ========================================================================= --}}
            {{-- STANDARD (NON-GST) PARTY BILL FORMAT                                      --}}
            {{-- ========================================================================= --}}
            <div class="standard-bill-container">
                <!-- Top Header & Meta Table -->
                <div class="top-header-grid">
                    <div class="company-bill-to-left">
                        <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 4px;">
                            <img src="{{ asset('assets/logo.jpg') }}" alt="Omkaar Logistics Logo" style="width: 140px; height: auto; object-fit: contain; max-height: 95px;" onerror="this.style.display='none'">
                            <div>
                                <div class="company-name-title">OMKAAR LOGISTICS</div>
                                <div class="company-sub-details">
                                    LALUNGGAON, NEAR NPS SCHOOL, KAMRUP METROPOLITAN, ASSAM, 781040<br>
                                    <strong>GST :</strong> 18AAHFO6045J1ZY &nbsp;&nbsp; <strong>PAN :</strong> AAHFO6045J<br>
                                    <strong>E-mail :</strong> omkaar.lagistics@gmail.com
                                </div>
                            </div>
                        </div>

                        <div class="bill-to-section">
                            <div class="bill-to-title">BILL TO</div>
                            <div class="bill-to-party-name">{{ $invoice->account_name }}</div>
                            @if($invoice->account && $invoice->account->address)
                                <div>{{ $invoice->account->address }}</div>
                            @endif
                            <div>
                                <strong>GSTIN :</strong> {{ $invoice->account->gst_no ?? '' }}
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <strong>PHONE :</strong> {{ $invoice->account->mobile ?: ($invoice->account->phone_o ?? '') }}
                            </div>
                        </div>
                    </div>

                    <!-- Meta Box Table -->
                    <div>
                        <table class="meta-box-table">
                            <thead>
                                <tr>
                                    <th style="width: 32%;">WORKING</th>
                                    <th style="width: 38%;">INVOICE NO</th>
                                    <th style="width: 30%;">INVOICE DATE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $workingMonth }}</td>
                                    <td>{{ $displayInvoiceNo }}</td>
                                    <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d-M-Y') : '' }}</td>
                                </tr>
                                <tr>
                                    <td>HSN/SAC CODE</td>
                                    <td colspan="2" style="text-align: center; letter-spacing: 1px;">996791</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Middle Items & Words Block -->
                <div class="bill-table-block">
                    <table class="invoice-main-table">
                        <thead>
                            <tr>
                                <th style="width: {{ $hasPartyCnNo ? '6%' : '6.8%' }};">DATE</th>
                                <th style="width: {{ $hasPartyCnNo ? '4.5%' : '4.8%' }};">C.N NO.</th>
                                @if($hasPartyCnNo)
                                    <th style="width: 5.5%;">PARTY CN NO</th>
                                @endif
                                <th style="width: {{ $hasPartyCnNo ? '3%' : '3.2%' }};">PKT</th>
                                <th style="width: {{ $hasPartyCnNo ? '7.5%' : '7.5%' }};">FROM</th>
                                <th style="width: {{ $hasPartyCnNo ? '11%' : '11.2%' }};">DESTINATION</th>
                                <th style="width: {{ $hasPartyCnNo ? '12%' : '13.5%' }};">CONSIGNEE</th>
                                <th style="width: {{ $hasPartyCnNo ? '7%' : '8%' }};">ITEM GOODS</th>
                                <th style="width: {{ $hasPartyCnNo ? '9.5%' : '9.5%' }};">INVOICE No.</th>
                                <th style="width: 5%;">WGT</th>
                                <th style="width: {{ $hasPartyCnNo ? '5.5%' : '6%' }};">RT/KG/CB</th>
                                <th style="width: 4.5%;">STAT. CHG</th>
                                <th style="width: 5.5%;">FR.AMT</th>
                                <th style="width: {{ $hasPartyCnNo ? '5.5%' : '6%' }};">UNLOADING</th>
                                <th style="width: {{ $hasPartyCnNo ? '8%' : '8.5%' }};">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="text-center cell-nowrap">{{ $item->bilty_date ? $item->bilty_date->format('d-m-Y') : '' }}</td>
                                    <td class="text-center font-bold cell-nowrap">{{ $item->bilty_no }}</td>
                                    @if($hasPartyCnNo)
                                        <td class="text-center cell-nowrap">{{ $item->cn_no ?? '' }}</td>
                                    @endif
                                    <td class="text-center cell-nowrap">{{ $item->packages }}</td>
                                    <td class="text-left cell-wrap" style="text-transform: uppercase;">{{ $item->from_location }}</td>
                                    <td class="text-left cell-wrap" style="text-transform: uppercase;">{{ $item->to_location }}</td>
                                    <td class="text-left cell-wrap" style="text-transform: uppercase;">{{ $item->consignee_name }}</td>
                                    <td class="text-left cell-wrap" style="text-transform: uppercase;">{{ $item->item_description }}</td>
                                    <td class="text-center cell-wrap">{{ $item->invoice_no_ref }}</td>
                                    <td class="text-center cell-nowrap">
                                        @if(strtoupper(trim($item->weight_type ?? '')) === 'FIXED' || strtoupper(trim($invoice->unit_filter ?? '')) === 'FIXED')
                                            FIXED
                                        @elseif(strtoupper(trim($item->weight_type ?? '')) === 'KG' && $item->weight > 0)
                                            {{ number_format($item->weight, 2) }}
                                        @elseif($item->weight > 0)
                                            {{ number_format($item->weight, 2) }}
                                        @else
                                            {{ $item->weight_type ?: 'FIXED' }}
                                        @endif
                                    </td>
                                    <td class="text-center cell-nowrap">{{ number_format($item->rate, 2) }}</td>
                                    <td class="text-center cell-nowrap">{{ $item->st_charge > 0 ? number_format($item->st_charge, 0) : '0' }}</td>
                                    <td class="text-right cell-nowrap">{{ number_format($item->freight_amount, 2) }}</td>
                                    <td class="text-right cell-nowrap">{{ number_format($item->unload_amount, 2) }}</td>
                                    <td class="text-right font-bold cell-nowrap" style="white-space: nowrap !important;">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Amount In Words Bar -->
                    <div class="amount-words-bar">
                        {{ convertNumberToIndianWords((float)$invoice->total_amount) }}
                    </div>
                </div>

                <!-- Bottom Summary & Bank Details Grid (Anchored to Bottom of A5 Paper) -->
                <div class="bottom-summary-grid">
                    <!-- Left Side: PAN, Bank Details & Notes -->
                    <div class="bottom-left-notes">
                        <div><strong>PAN :</strong> AAHFO6045J</div>
                        <div class="bank-details-title">Bank Detail's</div>
                        <div><strong>Omkaar Logistics</strong></div>
                        <div><strong>Account No :</strong> 5202870649</div>
                        <div><strong>IFSC Code :</strong> CBIN0283591</div>
                        <div><strong>Bank Name :</strong> Central Bank of India</div>
                        <div><strong>Branch :</strong> Lalganesh</div>

                        <div style="margin-top: 2px; font-weight: bold; text-decoration: underline;">NOTE</div>
                        <div class="notes-list">
                            1. Please pay as per the due date given in this LOGISTICS SERVICES INVOICE<br>
                            2. Please pay through NEFT/RTGS in favour OMKAAR LOGISTICS<br>
                            3. Kindly e-mail payment advice on omkaar.lagistics@gmail.com<br>
                            4. Request you to please pay on time to avoid disruption in services.<br>
                            5. TDS to be deducted as per the provision of section 194C.<br>
                            6. Please mail TDS certificate at omkaar.lagistics@gmail.com
                        </div>
                    </div>

                    <!-- Right Side: Totals Table & Signature -->
                    <div class="bottom-right-totals">
                        <table class="totals-sub-table">
                            <tr>
                                <td>Total Bill Value</td>
                                <td>{{ number_format($invoice->bill_amount, 2) }}</td>
                            </tr>
                            @if($invoice->gst_percent > 0)
                                @if($invoice->is_igst)
                                    <tr>
                                        <td>IGST ({{ number_format($invoice->gst_percent, 2) }}%)</td>
                                        <td>{{ number_format($invoice->gst_amount, 2) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td>CGST ({{ number_format($invoice->gst_percent / 2, 2) }}%)</td>
                                        <td>{{ number_format($invoice->gst_amount / 2, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>SGST ({{ number_format($invoice->gst_percent / 2, 2) }}%)</td>
                                        <td>{{ number_format($invoice->gst_amount / 2, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td>Reverse Charge</td>
                                    <td>No</td>
                                </tr>
                            @else
                                <tr>
                                    <td>GST Not Charged</td>
                                    <td>0.00</td>
                                </tr>
                                <tr>
                                    <td>Reverse Charge</td>
                                    <td>Yes</td>
                                </tr>
                            @endif
                            <tr style="font-size: 15.5px; font-weight: bold;">
                                <td>Grand Total</td>
                                <td>{{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        </table>

                        <div class="signature-box">
                            <div style="font-weight: normal; font-size: 13.5px;">For</div>
                            <div style="margin-top: 2px; font-size: 16px; font-weight: bold;">Omkaar Logistics</div>
                            <div style="margin-top: 6px; font-size: 11px; font-weight: normal; font-style: italic; color: #444;">
                                (This is a System generated Bill)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

</body>
</html>
