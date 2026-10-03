<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CN_{{ $bilty->series ? str_replace(['/', '\\', '-'], '_', $bilty->series) . '_' : '' }}{{ $bilty->bilty_no }}</title>
    <style>
        * {
            box-sizing: border-box !important;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
        }

        body {
            background-color: #ffffff !important;
            font-size: 11px;
            padding: 0 !important;
            margin: 0 !important;
            font-weight: bold;
        }

        @page {
            size: A5 landscape;
            margin: 0 !important;
        }

        .receipt-container {
            width: 200mm;
            max-width: 200mm;
            height: 138mm;
            max-height: 138mm;
            position: absolute;
            top: 5mm;
            left: 5mm;
            border: 1.5px solid #000000;
            border-radius: 0;
            padding: 4px 6px;
            background: #fff;
            box-sizing: border-box;
            font-weight: bold;
        }

        /* Header Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
            font-weight: bold;
        }

        .header-table .logo-col {
            width: 135px;
        }

        .header-table .logo-col img {
            width: 132px;
            height: auto;
            display: block;
        }

        .header-table .logo-text-middle {
            text-align: center;
            line-height: 1.15;
            font-weight: bold;
        }

        .logo-text-middle .sub-header {
            font-size: 14px;
            letter-spacing: 0.8px;
            font-weight: normal;
        }

        .logo-text-middle h1 {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 0.5px;
            line-height: 1.05;
            margin: 1px 0;
        }

        .logo-text-middle .address-details {
            font-size: 14px;
            font-weight: normal;
            line-height: 1.2;
        }

        /* Meta Table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 5px;
            font-size: 11px;
            font-weight: bold;
        }

        .meta-table td {
            border: none;
            padding: 0;
            vertical-align: bottom;
            font-weight: bold;
        }

        .meta-left div {
            margin-bottom: 2px;
            font-size: 12px;
            font-weight: bold;
        }

        .meta-left strong {
            font-weight: bold;
        }

        .cn-pill-box {
            border: 1.5px solid #000;
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            text-align: center;
            min-width: 145px;
        }

        .meta-date {
            margin-top: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Locations Table */
        .locations-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .locations-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
            font-weight: bold;
        }

        .loc-pill {
            border: 1.5px solid #000;
            border-radius: 4px;
            padding: 3.5px 12px;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
            box-sizing: border-box;
            width: 175px;
            min-width: 175px;
            text-align: center;
        }

        .freight-mode-text {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        /* Parties Table */
        .parties-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .parties-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            font-weight: bold;
        }

        .party-box {
            border: 1.5px solid #000;
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 11px;
            line-height: 1.3;
            box-sizing: border-box;
            font-weight: bold;
        }

        .party-line {
            margin-bottom: 1.5px;
            font-weight: bold;
        }

        .party-line strong {
            display: inline-block;
            width: 72px;
            font-weight: bold;
        }

        /* Table & Charges Section */
        .table-section {
            width: 100%;
            margin-top: 4px;
        }

        /* Consignment Items Table */
        .items-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1.5px solid #000;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 0;
        }

        .items-table th {
            border-right: 1px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 3px 2px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            background-color: #fff;
        }

        .items-table th:last-child {
            border-right: none;
        }

        .items-table td {
            border-right: 1px solid #000;
            border-bottom: none;
            padding: 6px 4px;
            font-size: 11px;
            text-align: center;
            vertical-align: middle;
            font-weight: normal !important;
        }

        .items-table td:last-child {
            border-right: none;
        }

        /* Bottom Sticky Footer Block */
        .bottom-sticky-block {
            position: absolute;
            bottom: 5px;
            left: 7px;
            right: 7px;
            font-weight: bold;
        }

        .signature-box {
            border: none;
            padding: 2px 0;
            box-sizing: border-box;
            background: transparent;
        }

        .signature-inner-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            font-weight: bold;
        }

        .signature-inner-table td {
            border: none;
            padding: 0;
            font-size: 11px;
            line-height: 1.35;
            font-weight: bold;
        }

        .signature-inner-table .seal-sign {
            font-weight: bold;
            font-size: 12px;
            white-space: nowrap;
        }

        .signature-inner-table .receipt-ack {
            font-weight: bold;
            font-size: 12px;
            white-space: nowrap;
        }

        .signature-inner-table .booking-incharge {
            font-weight: bold;
            font-size: 12px;
            white-space: nowrap;
        }

        .signature-inner-table .terms-footer {
            font-size: 9px;
            font-weight: bold;
            font-style: italic;
            margin-top: 2px;
            white-space: nowrap;
        }

        /* Charges Table */
        .charges-table {
            width: 185px;
            margin-left: auto;
            margin-top: 5px;
            border-collapse: separate;
            border-spacing: 0;
            border: 1.5px solid #000;
            border-radius: 5px;
            overflow: hidden;
            font-weight: bold;
            background: #fff;
        }

        .charges-table td {
            padding: 1.5px 6px;
            font-size: 11px;
            border-bottom: none;
            font-weight: bold;
        }

        .charges-table td:first-child {
            font-weight: bold;
            width: 90px;
            white-space: nowrap;
            border-right: 1.5px solid #000;
        }

        .charges-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .btn-print {
            background: #0f3460;
            color: white;
            border: none;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            top: -40px;
            right: 0;
            display: block;
            z-index: 1000;
        }

        .btn-back-link {
            background: #4e5d6c;
            color: white;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            position: absolute;
            top: -40px;
            left: 0;
            z-index: 1000;
        }

        .print-wrapper {
            position: relative;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        @media screen {
            body {
                padding: 45px 15px 15px 15px !important;
                background-color: #eef2f6 !important;
            }

            .receipt-container {
                position: relative;
                top: auto;
                left: auto;
                max-width: 200mm;
                margin: 0 auto;
                background: #fff;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
        }

        @media print {
            @page {
                size: A5 landscape;
                margin: 0 !important;
            }

            html, body {
                width: 100%;
                height: 100%;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .receipt-container {
                position: absolute !important;
                top: 5mm !important;
                left: 5mm !important;
                width: 200mm !important;
                max-width: 200mm !important;
                height: 138mm !important;
                max-height: 138mm !important;
                margin: 0 !important;
            }

            .btn-print,
            .btn-back-link {
                display: none !important;
            }

            .print-wrapper {
                position: static !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    </style>
</head>

<body>

    <div class="print-wrapper">
        @if(!isset($isPdf))
            <a href="{{ route('bilty.create') }}" class="btn-back-link">⬅ Back to Entry Form</a>
            <div style="position: absolute; top: -40px; right: 0; display: flex; gap: 8px;">
                <a href="{{ route('bilty.pdf', ['id' => $bilty->id, 'download' => 1]) }}" class="btn-print" style="position: static; background: #c0392b; text-decoration: none; display: inline-block;">📥 Download PDF</a>
                <button class="btn-print" style="position: static;" onclick="window.print()">🖨 Print Bill (A5)</button>
            </div>
        @endif

        <div class="receipt-container">

            <!-- Company Header -->
            <table class="header-table">
                <tr>
                    <td class="logo-col" style="text-align: left;">
                        <img src="{{ asset('assets/logo.jpg') }}" alt="Omkaar Logistics">
                    </td>
                    <td class="logo-text-middle">
                        <div class="sub-header">CONSIGNMENT NOTE</div>
                        <h1>OMKAAR LOGISTICS</h1>
                        <div class="address-details">
                            <div>Head office: Lokhra Lalunggaon Near NPS School, GHY-40</div>
                            <div>Mangaldoi, Kharupetia, Tangla, Dhekiajuli, Basimari,</div>
                            <div>Udalguri, Rangia, Nalbari, Patshala</div>
                            <div>📞 98640-82153, 97335-35513</div>
                            <div>✉ omkaar.lagistics@gmail.com</div>
                            <div>GSTIN: 18AAHFO6045J1ZY</div>
                        </div>
                    </td>
                    <td class="logo-col" style="text-align: right;">
                        <img src="{{ asset('assets/logo.jpg') }}" alt="Omkaar Logistics" style="margin-left: auto;">
                    </td>
                </tr>
            </table>

            <!-- Meta Rows -->
            <table class="meta-table">
                <tr>
                    <td class="meta-left" style="text-align: left;">
                        <div><strong>E-WayBill No:</strong> {{ $bilty->eway_bill_no ?? '' }}</div>
                        <div><strong>C.N.No:</strong> {{ $bilty->cn_no ?? '' }}</div>
                        <div><strong>Party Name:</strong> {{ $bilty->billingParty ? $bilty->billingParty->ledger_name : ($bilty->consignor ? $bilty->consignor->ledger_name : '') }}</div>
                    </td>
                    <td style="text-align: right;">
                        <div class="cn-pill-box"><strong>C.N. No.:</strong> {{ $bilty->series ? $bilty->series . '/' : '' }}{{ $bilty->bilty_no }}</div>
                        <div class="meta-date"><strong>Date:</strong> &nbsp; &nbsp; {{ $bilty->invoice_date ? $bilty->invoice_date->format('d/m/Y') : '' }}</div>
                    </td>
                </tr>
            </table>

            <!-- Locations Table -->
            <table class="locations-table">
                <tr>
                    <td style="width: 35%; text-align: left;">
                        <div class="loc-pill">
                            <strong>From:</strong> &nbsp; {{ $bilty->fromLocation ? $bilty->fromLocation->name : ($bilty->fromCity ? $bilty->fromCity->name : '') }}
                        </div>
                    </td>
                    <td style="width: 30%; text-align: center;">
                        <div class="freight-mode-text">
                            Freight Mode: {{ $bilty->billing_type }}
                        </div>
                    </td>
                    <td style="width: 35%; text-align: right;">
                        <div class="loc-pill" style="margin-left: auto; text-align: left;">
                            <strong>To:</strong> &nbsp; {{ $bilty->toLocation ? $bilty->toLocation->name : ($bilty->toCity ? $bilty->toCity->name : '') }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Parties Table -->
            <table class="parties-table">
                <tr>
                    <td style="width: 49%;">
                        <div class="party-box">
                            <div class="party-line"><strong>Consignor:</strong> {{ $bilty->consignor ? $bilty->consignor->ledger_name : '' }}</div>
                            <div class="party-line"><strong>Mobile No:</strong> {{ $bilty->consignor ? ($bilty->consignor->mobile ?: ($bilty->consignor->phone_o ?: $bilty->consignor->phone_r)) : '' }}</div>
                            <div class="party-line"><strong>GSTIN No:</strong> {{ $bilty->consignor ? $bilty->consignor->gst_no : '' }}</div>
                            <div class="party-line"><strong>Address:</strong> {{ $bilty->consignor ? $bilty->consignor->address : '' }}</div>
                        </div>
                    </td>
                    <td style="width: 2%;"></td>
                    <td style="width: 49%;">
                        <div class="party-box">
                            <div class="party-line"><strong>Consignee:</strong> {{ $bilty->consignee ? $bilty->consignee->ledger_name : '' }}</div>
                            <div class="party-line"><strong>Mobile No:</strong> {{ $bilty->consignee ? ($bilty->consignee->mobile ?: ($bilty->consignee->phone_o ?: $bilty->consignee->phone_r)) : '' }}</div>
                            <div class="party-line"><strong>GSTIN No:</strong> {{ $bilty->consignee ? $bilty->consignee->gst_no : '' }}</div>
                            <div class="party-line"><strong>Address:</strong> {{ $bilty->consignee ? $bilty->consignee->address : '' }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Table & Charges Section -->
            @php
                $hasFixedUnit = $bilty->items->contains(function($i) {
                    $mUnit = \App\Models\MeasurementUnit::where('unit_code', $i->unit)->first();
                    $isFixed = $mUnit ? ($mUnit->unit_type === 'fixed') : (strtoupper($i->unit ?? '') !== 'KG');
                    return $isFixed || floatval($i->weight_val ?? 0) > 0;
                });

                $pkgLabels = $bilty->items->map(function($i) {
                    $uCode = strtoupper($i->unit ?? '');
                    $mUnitObj = \App\Models\MeasurementUnit::where('unit_code', $uCode)->first();
                    if ($mUnitObj && $mUnitObj->package_label && $mUnitObj->package_label !== 'NoOfPkgs') {
                        return str_replace('NoOf', '', $mUnitObj->package_label);
                    }
                    if (str_contains($uCode, 'BOX')) return 'Boxes';
                    if (str_contains($uCode, 'CASE')) return 'Cases';
                    if (str_contains($uCode, 'PCS') || str_contains($uCode, 'PIECE')) return 'Pcs';
                    if (str_contains($uCode, 'BAG')) return 'Bags';
                    if (str_contains($uCode, 'DRUM')) return 'Drums';
                    if (str_contains($uCode, 'CARTON')) return 'Cartons';
                    return 'Packages';
                })->unique();

                $uniqueLabel = ($pkgLabels->count() === 1) ? $pkgLabels->first() : 'Packages';
                $pkgHeaderLabel = 'No Of<br>' . $uniqueLabel;
            @endphp
            <div class="table-section">
                <!-- Consignment Items Table -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th width="{{ $hasFixedUnit ? '10%' : '10%' }}">{!! $pkgHeaderLabel !!}</th>
                            <th width="{{ $hasFixedUnit ? '10%' : '12%' }}">Packing</th>
                            <th width="{{ $hasFixedUnit ? '26%' : '33%' }}">Discription</th>
                            <th width="{{ $hasFixedUnit ? '13%' : '14%' }}">Invoice No.</th>
                            <th width="{{ $hasFixedUnit ? '10%' : '11%' }}">Invoice<br>Value</th>
                            <th width="{{ $hasFixedUnit ? '7%' : '8%' }}">Unit</th>
                            @if($hasFixedUnit)
                                <th width="8%">Weight</th>
                            @endif
                            <th width="{{ $hasFixedUnit ? '10%' : '8%' }}">Weight / Fixed</th>
                            <th width="6%">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bilty->items as $item)
                            <tr>
                                <td>{{ $item->no_of_pkgs }}</td>
                                <td>{{ $item->packing }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->invoice_no ?? '' }}</td>
                                <td>{{ $item->invoice_value > 0 ? number_format($item->invoice_value, 2) : '' }}</td>
                                <td>{{ $item->unit ?? '' }}</td>
                                @if($hasFixedUnit)
                                    <td>{{ floatval($item->weight_val ?? 0) > 0 ? number_format($item->weight_val, 2) : '' }}</td>
                                @endif
                                <td>
                                    @if (($item->unit ?? '') === 'KG')
                                        {{ floatval($item->qty ?? 0) > 0 ? number_format($item->qty, 2) : (floatval($item->weight_val ?? 0) > 0 ? number_format($item->weight_val, 2) : '') }}
                                    @else
                                        {{ floatval($item->qty ?? 0) > 0 ? number_format($item->qty, 2) : '' }}
                                    @endif
                                </td>
                                <td>{{ number_format($item->rate, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @php
                    $calculatedGross = floatval($bilty->gross_amount ?? 0);
                    if ($calculatedGross <= 0) {
                        $calculatedGross = 0;
                        foreach ($bilty->items as $item) {
                            $q = floatval($item->qty ?? 0);
                            if ($q <= 0) {
                                $q = floatval($item->weight_val ?? 0);
                            }
                            if ($q <= 0) {
                                $q = 1;
                            }
                            $calculatedGross += ($q * floatval($item->rate ?? 0));
                        }
                    }
                @endphp
                <!-- Charges Table (Right-aligned immediately below package data table) -->
                <table class="charges-table">
                    <tr>
                        <td>Total</td>
                        <td>{{ number_format($calculatedGross, 2) }}</td>
                    </tr>
                    <tr>
                        <td>ST</td>
                        <td>{{ number_format($bilty->st_charge, 2) }}</td>
                    </tr>
                    <tr>
                        <td>RC</td>
                        <td>{{ number_format($bilty->rc_charge, 2) }}</td>
                    </tr>
                    <tr>
                        <td>SC</td>
                        <td>{{ number_format($bilty->sc_charge, 2) }}</td>
                    </tr>
                    <tr>
                        <td>DD</td>
                        <td>{{ number_format($bilty->dd_charge, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Grade Total</td>
                        <td>{{ number_format($bilty->net_amount, 2) }}</td>
                    </tr>
                </table>

            </div>

            <!-- Bottom Sticky Footer Block (Signatures & Terms) -->
            <div class="bottom-sticky-block">
                <div class="signature-box">
                    <table class="signature-inner-table">
                        <tr>
                            <td style="text-align: left; width: 20%; vertical-align: bottom; padding-left: 10px;">
                                <div style="display: inline-block; text-align: center;">
                                    <div class="seal-sign">Customer Seal &amp; Sign</div>
                                </div>
                            </td>
                            <td style="text-align: right; width: 80%; vertical-align: bottom; padding-right: 220px;">
                                <div style="display: inline-block; text-align: center;">
                                    <div style="font-size: 11px; font-weight: bold; color: #000; text-transform: uppercase;">
                                        {{ $bilty->user ? ($bilty->user->name ?: $bilty->user->username) : 'System' }}
                                    </div>
                                    <div class="booking-incharge" style="font-size: 10px; font-weight: bold; color: #444; margin-top: 2px;">
                                        (This is a computer generated C.N. — Signature is not required)
                                    </div>
                                    <div class="terms-footer">* We are not responsible for Brokage /Leakage &amp; Damage of any goods</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>

</html>
