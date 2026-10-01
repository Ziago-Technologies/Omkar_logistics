@extends('layouts.app')

@section('title', 'Invoice / Party Bill - Omkaar Logistics')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Desktop ERP System Style matching screenshot */
    .party-bill-wrapper {
        background: #d4d0c8;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 13px;
        border: 1px solid #7da9d4;
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        margin: 0 auto;
        color: #000;
    }

    /* Red Title Bar */
    .party-bill-header-bar {
        background: #8b0000;
        color: #fff;
        padding: 6px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #5a0000;
        font-weight: 700;
        font-size: 14.5px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .header-left-group {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-left-group label,
    .header-right-group label {
        font-weight: bold;
        color: #fff;
        font-size: 13px;
    }

    .header-left-group input,
    .header-right-group input {
        height: 26px;
        border: 1.5px solid #333;
        font-size: 13px;
        font-weight: bold;
        padding: 2px 6px;
        box-sizing: border-box;
        background: #fff;
        color: #000;
    }

    .header-center-title {
        font-size: 19px;
        font-weight: bold;
        color: #ffff00; /* Bright yellow title like the screenshot */
        text-shadow: 1px 1px 2px #000;
        letter-spacing: 0.5px;
    }

    .header-right-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Top Selection & Filter Panel */
    .party-bill-controls {
        background: #ece9d8;
        padding: 10px 14px;
        border-bottom: 1.5px solid #999;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .controls-row {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .ctrl-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ctrl-group label {
        font-weight: bold;
        color: #000;
        font-size: 13px;
        white-space: nowrap;
    }

    .ctrl-group input[type="text"],
    .ctrl-group select {
        height: 28px;
        border: 1.5px solid #7f9db9;
        font-size: 13px;
        font-weight: 600;
        padding: 2px 6px;
        box-sizing: border-box;
        background: #fff;
        color: #000;
    }

    /* Pale Yellow Account Input Field from Old System */
    .input-account-yellow {
        background-color: #ffffc0 !important;
        border: 1.5px solid #7f9db9 !important;
        font-weight: bold;
        font-size: 13px !important;
        color: #000;
        width: 320px;
    }

    .ctrl-group select {
        min-width: 180px;
    }

    /* Main Table Container */
    .grid-table-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: auto;
        max-height: calc(100vh - 330px);
        min-height: 280px;
        background: #808080; /* Grey backdrop when table has empty space */
        border-top: 1.5px solid #999;
        border-bottom: 2px solid #666;
    }

    .party-bill-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 12.5px;
    }

    .party-bill-table th {
        background: #e4e2de;
        color: #000;
        font-weight: bold;
        padding: 6px 6px;
        border: 1px solid #999;
        text-align: center;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        font-size: 12.5px;
    }

    .party-bill-table td {
        border: 1px solid #bbb;
        padding: 2px 3px !important;
        text-align: center;
        white-space: nowrap;
        color: #000;
        font-size: 12.5px;
        font-weight: 500;
    }

    /* Grid Input fields for inline editing */
    .grid-input,
    .party-bill-table td input,
    .party-bill-table td select {
        min-width: 100% !important;
        width: 100%;
        height: 24px;
        border: 1.5px solid #7f9db9;
        background: #fff;
        color: #000;
        font-size: 12.5px;
        font-weight: 600;
        font-family: inherit;
        padding: 1px 4px;
        box-sizing: border-box !important;
        field-sizing: content;
    }
    .grid-input:focus,
    .party-bill-table td input:focus,
    .party-bill-table td select:focus {
        border-color: #0055ff;
        outline: 1.5px solid #0055ff;
        background: #ffffea;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .font-bold { font-weight: bold; }

    .party-bill-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .party-bill-table tr:hover {
        background-color: #dbeafe !important;
    }

    .party-bill-table tr.selected-row {
        background-color: #bfdbfe !important;
        font-weight: 600;
    }

    .party-bill-table input.row-checkbox {
        cursor: pointer;
        width: 15px;
        height: 15px;
    }

    /* GST Bill Mode: Hide Third Party C.N.No Column */
    .hide-third-party-cn .col-third-party-cn {
        display: none !important;
    }

    /* Table Autocomplete Dropdown Styling */
    .autocomplete-grid-wrap {
        position: relative;
        width: 100%;
    }
    .auto-grid-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 150px;
        max-width: 260px;
        max-height: 180px;
        overflow-y: auto;
        background: #ffffff;
        border: 1.5px solid #0f3460;
        box-shadow: 0 6px 14px rgba(0,0,0,0.25);
        z-index: 99999;
        text-align: left;
        font-size: 12.5px;
        border-radius: 2px;
        display: none;
    }
    .auto-grid-item {
        padding: 5px 8px;
        cursor: pointer;
        color: #000;
        font-weight: 600;
        border-bottom: 1px solid #f0f0f0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .auto-grid-item:hover,
    .auto-grid-item.active {
        background-color: #0f3460;
        color: #ffffff;
    }

    /* Bottom Summary & Actions Panel */
    .party-bill-footer-bar {
        background: #ece9d8;
        padding: 10px 14px;
        border-top: 1.5px solid #fff;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .footer-row-1 {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .footer-amounts-group {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .footer-amounts-group .ctrl-group input {
        height: 26px;
        border: 1.5px solid #7f9db9;
        font-size: 12.5px;
        padding: 2px 6px;
        text-align: right;
        font-weight: bold;
    }

    .total-amt-box {
        background: #fff;
        border: 1.5px solid #7f9db9;
        font-size: 14.5px !important;
        font-weight: 800 !important;
        color: #8b0000 !important;
        width: 130px !important;
        text-align: right;
        padding: 2px 6px;
    }

    .footer-row-2 {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .remark-input-field {
        width: 100%;
        height: 26px;
        border: 1.5px solid #7f9db9;
        font-size: 12.5px;
        font-weight: 600;
        padding: 2px 6px;
    }

    /* Row 3: Lower Action Buttons Row (Matching Desktop ERP) */
    .footer-row-3 {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding-top: 8px;
        border-top: 1.5px solid #d4d0c8;
        flex-wrap: wrap;
    }

    .voucher-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .voucher-group label {
        font-size: 13px;
        font-weight: bold;
        color: #000;
        white-space: nowrap;
    }

    .voucher-group input {
        height: 26px;
        border: 1.5px solid #7f9db9;
        font-size: 13px;
        font-weight: bold;
        padding: 2px 6px;
        background: #fff;
        text-align: center;
    }

    .desktop-btn-bar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        flex: 1;
        margin-left: 20px;
        flex-wrap: wrap;
    }

    .btn-desktop-cyan {
        background: linear-gradient(to bottom, #d6f2fa 0%, #b8e6f5 50%, #9ddcef 100%);
        border: 1.5px solid #5b89a6;
        color: #000;
        font-weight: bold;
        font-size: 12.5px;
        min-width: 88px;
        height: 28px;
        padding: 0 16px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.7), 0 1px 2px rgba(0,0,0,0.15);
        text-decoration: none;
    }

    .btn-desktop-cyan:hover {
        background: linear-gradient(to bottom, #e4f7fc 0%, #ccecf8 50%, #aee3f4 100%);
        border-color: #3b6c8c;
    }

    .btn-desktop-cyan:active {
        background: #90d3e8;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
    }

    /* Select2 Tweaks to match desktop ERP styling */
    .select2-container .select2-selection--single {
        height: 28px !important;
        border: 1.5px solid #7f9db9 !important;
        border-radius: 0px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px !important;
        padding-left: 6px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #000 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 26px !important;
    }

    /* Ensure SweetAlert2 is always on top of EVERYTHING */
    .swal2-container {
        z-index: 99999999 !important;
    }
    .swal2-popup {
        z-index: 99999999 !important;
        font-family: inherit !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid" style="padding: 10px 15px;">
    
    <form action="{{ ($isEdit ?? false) ? route('invoice.update', $existingInvoice->id) : route('invoice.store') }}" method="POST" id="invoiceForm">
        @csrf
        @if($isEdit ?? false)
            @method('PUT')
        @endif
        <input type="hidden" name="status" id="invoice_status" value="{{ old('status', $existingInvoice->status ?? 'finalized') }}">
        <input type="hidden" id="existing_invoice_id" name="existing_invoice_id" value="{{ ($isEdit ?? false) ? $existingInvoice->id : '' }}">

        <div class="party-bill-wrapper">
            <!-- 1. Top Red Header Bar -->
            <div class="party-bill-header-bar">
                <div class="header-left-group">
                    <div class="ctrl-group">
                        <label for="series">SERIES</label>
                        <select name="series" id="series" style="width: 75px; font-weight: 700; background: #fff; border: 1px solid #999; height: 26px; border-radius: 2px;">
                            @if(isset($seriesList) && count($seriesList) > 0)
                                @foreach($seriesList as $s)
                                    @if($s->name !== 'A')
                                        <option value="{{ $s->name }}" {{ old('series', $existingInvoice->series ?? ($series ?? ($defaultSeries ?? '26-27'))) == $s->name ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endif
                                @endforeach
                            @else
                                <option value="{{ $defaultSeries ?? '26-27' }}" selected>{{ $defaultSeries ?? '26-27' }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="ctrl-group">
                        <label for="invoice_no">INVOICE NO.</label>
                        <input type="number" name="invoice_no" id="invoice_no" value="{{ old('invoice_no', $existingInvoice->invoice_no ?? $nextInvoiceNo) }}" style="width: 70px;" required autocomplete="off">
                    </div>
                </div>

                <div class="header-center-title" id="headerTitle">
                    INVOICE
                </div>

                <div class="header-right-group">
                    <div class="ctrl-group">
                        <label for="invoice_date">DATE</label>
                        <input type="date" name="invoice_date" id="invoice_date" value="{{ old('invoice_date', (isset($existingInvoice) && $existingInvoice->invoice_date) ? $existingInvoice->invoice_date->format('Y-m-d') : date('Y-m-d')) }}" style="width: 120px;" required>
                    </div>
                </div>
            </div>

            <!-- 2. Controls & Selection Panel -->
            <div class="party-bill-controls">
                <!-- Row 1: Account, Consignor, Pending Bill Parties -->
                <div class="controls-row">
                    <div class="ctrl-group">
                        <label for="account_name_select" style="min-width: 55px;">ACCOUNT <span id="account_count_badge" style="color: #0f3460; font-weight: normal; font-size: 10px;">({{ count($consignors) }})</span></label>
                        <select name="account_name" id="account_name_select" class="input-account-yellow" required style="width: 320px; height: 22px; border: 1px solid #7f9db9; font-size: 11px; font-weight: bold; background-color: #ffffc0; padding: 1px 3px;">
                            <option value="">-- SELECT ACCOUNT ({{ count($consignors) }}) --</option>
                            @php
                                $currentAccount = old('account_name', $existingInvoice->account_name ?? '');
                            @endphp
                            @if($currentAccount && !in_array($currentAccount, $consignors))
                                <option value="{{ $currentAccount }}" selected>{{ $currentAccount }}</option>
                            @endif
                            @foreach ($consignors as $c)
                                <option value="{{ $c }}" {{ $currentAccount == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ctrl-group">
                        <label for="consignor_select">CONSIGNOR <span id="consignor_count_badge" style="color: #0f3460; font-weight: normal; font-size: 10px;">({{ count($consignors) }})</span></label>
                        <select name="consignor_name" id="consignor_select" style="width: 230px; height: 22px; border: 1px solid #7f9db9; font-size: 11px; background: #fff; padding: 1px 3px;">
                            <option value="">-- ALL CONSIGNORS ({{ count($consignors) }}) --</option>
                            @php
                                $currentConsignor = old('consignor_name', $existingInvoice->consignor_name ?? '');
                            @endphp
                            @if($currentConsignor && !in_array($currentConsignor, $consignors))
                                <option value="{{ $currentConsignor }}" selected>{{ $currentConsignor }}</option>
                            @endif
                            @foreach ($consignors as $c)
                                <option value="{{ $c }}" {{ $currentConsignor == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ctrl-group" style="margin-left: auto;">
                        <label for="pending_party_select">PENDING BILL PARTIES <span id="pending_count_badge" style="color: #8b0000; font-weight: normal; font-size: 10px;">({{ count($pendingParties) }})</span></label>
                        <select id="pending_party_select" style="width: 260px; height: 22px; border: 1px solid #7f9db9; font-size: 11px; font-weight: bold; color: #8b0000; background: #fff; padding: 1px 3px;">
                            <option value="">-- SELECT PENDING PARTY ({{ count($pendingParties) }}) --</option>
                            @foreach ($pendingParties as $p)
                                <option value="{{ $p }}" {{ (old('account_name', $existingInvoice->account_name ?? '') == $p) ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2: For Month, Item, GST Bill, Show Destination -->
                <div class="controls-row">
                    <div class="ctrl-group">
                        <label for="for_month" style="min-width: 55px;">FOR MONTH</label>
                        <select name="for_month" id="for_month" style="width: 110px;" onchange="window.loadMonthParties(this.value, true);">
                            <option value="">-- ALL MONTHS --</option>
                            @php $activeMonth = old('for_month', $existingInvoice->for_month ?? ($selectedMonth ?? date('M/Y'))); @endphp
                            @foreach ($months as $val => $lbl)
                                <option value="{{ $lbl }}" {{ $activeMonth == $lbl ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ctrl-group">
                        <label for="item_filter">ITEM <span id="item_count_badge" style="color: #0f3460; font-weight: normal; font-size: 10px;"></span></label>
                        <select name="item_filter" id="item_filter" style="width: 140px; height: 22px; border: 1px solid #7f9db9; font-size: 11px; background: #fff; padding: 1px 3px;">
                            <option value="">-- ALL ITEMS --</option>
                            @php $activeItem = old('item_filter', $existingInvoice->item_filter ?? ''); @endphp
                            @foreach ($itemDescriptions as $desc)
                                <option value="{{ $desc }}" {{ $activeItem == $desc ? 'selected' : '' }}>{{ $desc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ctrl-group" style="margin-left: 10px;">
                        <input type="checkbox" name="is_gst_bill" id="is_gst_bill" value="1" {{ old('is_gst_bill', $existingInvoice->is_gst_bill ?? false) ? 'checked' : '' }} style="cursor: pointer;">
                        <label for="is_gst_bill" style="cursor: pointer;">GST BILL</label>
                    </div>

                    <div class="ctrl-group" style="margin-left: 20px;">
                        <label for="destination_filter">SHOW DESTINATION <span id="destination_count_badge" style="color: #0f3460; font-weight: normal; font-size: 10px;"></span></label>
                        <select name="destination_filter" id="destination_filter" style="width: 160px; height: 22px; border: 1px solid #7f9db9; font-size: 11px; background: #fff; padding: 1px 3px;">
                            <option value="">-- ALL DESTINATIONS --</option>
                            @php $activeDest = old('destination_filter', $existingInvoice->destination_filter ?? ''); @endphp
                            @foreach ($destinations as $d)
                                <option value="{{ $d }}" {{ $activeDest == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ctrl-group" style="margin-left: auto;">
                        <button type="button" class="btn-action" id="btnFetchBilties" style="height: 22px; font-weight: bold; background: #d0e1fd;">
                            🔍 FETCH PENDING BILTIES
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. Main Data Table (Exact 19 Columns from old system) -->
            <div class="grid-table-container">
                <table class="party-bill-table" id="partyBillTable">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" id="checkAllRows" checked title="Select All / Deselect All"></th>
                            <th width="35">SRNO</th>
                            <th width="75">DATE</th>
                            <th width="65">C.N.NO</th>
                            <th width="45">PKT</th>
                            <th width="180">FROM</th>
                            <th width="130">DESTINATION</th>
                            <th width="140">CONSIGNEE</th>
                            <th width="100">ITEMS</th>
                            <th width="80">INV.NO</th>
                            <th width="65">WGT</th>
                            <th width="65">UNIT CAT.</th>
                            <th width="65">RT/KG/CB</th>
                            <th width="55">ST.CH.</th>
                            <th width="65">FR.AMT</th>
                            <th width="65">UNLOAD RT.</th>
                            <th width="65">UNLOAD AMT.</th>
                            <th width="55">OTH.CH.</th>
                            <th width="65">ODA CHARGE</th>
                            <th width="75">AMT.</th>
                        </tr>
                    </thead>
                    <tbody id="partyBillTableBody">
                        @if(isset($existingInvoice) && $existingInvoice->items && $existingInvoice->items->count() > 0)
                            @foreach($existingInvoice->items as $index => $item)
                                <tr class="selected-row" id="row_{{ $index }}">
                                    <td>
                                        <input type="checkbox" class="row-checkbox" checked onchange="toggleRowSelection(this, {{ $index }})">
                                        <input type="hidden" name="items[{{ $index }}][bilty_id]" value="{{ $item->bilty_id }}">
                                    </td>
                                    <td>{{ $item->sr_no ?: ($index + 1) }}</td>
                                    <td>
                                        <input type="text" class="grid-input text-center" name="items[{{ $index }}][date]" value="{{ $item->bilty_date ? $item->bilty_date->format('d-m-Y') : '' }}" style="width: 72px;">
                                    </td>
                                    <td>
                                        <input type="text" class="grid-input text-center font-bold" name="items[{{ $index }}][bilty_no]" value="{{ $item->bilty_no }}" readonly style="width: 58px; background: #e8e8e8; cursor: not-allowed;" title="C.N. No cannot be changed">
                                    </td>

                                    <td>
                                        <input type="number" class="grid-input text-center" name="items[{{ $index }}][packages]" value="{{ $item->packages }}" style="width: 42px;" oninput="onUnitCatOrRateOrPktChange({{ $index }})">
                                    </td>
                                    <td>
                                        <div class="autocomplete-grid-wrap">
                                            <input type="text" class="grid-input text-left auto-grid-input" name="items[{{ $index }}][from_location]" value="{{ $item->from_location }}" data-original="{{ $item->from_location }}" data-type="location" style="width: 170px;" autocomplete="off">
                                            <div class="auto-grid-dropdown"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="autocomplete-grid-wrap">
                                            <input type="text" class="grid-input text-left auto-grid-input" name="items[{{ $index }}][to_location]" value="{{ $item->to_location }}" data-original="{{ $item->to_location }}" data-type="location" style="width: 120px;" autocomplete="off">
                                            <div class="auto-grid-dropdown"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="autocomplete-grid-wrap">
                                            <input type="text" class="grid-input text-left auto-grid-input" name="items[{{ $index }}][consignee_name]" value="{{ $item->consignee_name }}" data-original="{{ $item->consignee_name }}" data-type="consignee" style="width: 125px;" autocomplete="off">
                                            <div class="auto-grid-dropdown"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="grid-input text-left" name="items[{{ $index }}][item_description]" value="{{ $item->item_description }}" style="width: 90px;">
                                    </td>
                                    <td>
                                        <input type="text" class="grid-input text-center" name="items[{{ $index }}][invoice_no_ref]" value="{{ $item->invoice_no_ref }}" style="width: 72px;">
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" class="grid-input text-right" name="items[{{ $index }}][weight]" id="weight_{{ $index }}" value="{{ $item->weight > 0 ? (float)$item->weight : '' }}" placeholder="0" style="width: 58px;" oninput="onUnitCatOrRateOrPktChange({{ $index }})">
                                    </td>
                                    <td>
                                        @php $wType = strtoupper(trim($item->weight_type ?: 'KG')); @endphp
                                        <select class="grid-input text-center font-bold" name="items[{{ $index }}][weight_type]" id="weight_type_{{ $index }}" style="width: 62px; height: 20px; padding: 0 1px; font-size: 10px;" onchange="onUnitCatChange({{ $index }}, this.value)">
                                            <option value="KG" {{ $wType === 'KG' ? 'selected' : '' }}>KG</option>
                                            <option value="FIXED" {{ $wType === 'FIXED' ? 'selected' : '' }}>FIXED</option>
                                            <option value="CARTON" {{ $wType === 'CARTON' ? 'selected' : '' }}>CARTON</option>
                                            <option value="PKT" {{ $wType === 'PKT' ? 'selected' : '' }}>PKT</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="grid-input text-right" name="items[{{ $index }}][rate]" id="rate_{{ $index }}" value="{{ $item->rate > 0 ? (float)$item->rate : '' }}" placeholder="0" style="width: 58px;" oninput="onRateInput({{ $index }}, this.value)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="grid-input text-right" name="items[{{ $index }}][st_charge]" id="st_charge_{{ $index }}" value="{{ $item->st_charge > 0 ? (float)$item->st_charge : '' }}" placeholder="0" style="width: 48px;" oninput="onStChargeInput({{ $index }}, this.value)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="grid-input text-right font-bold" name="items[{{ $index }}][freight_amount]" id="freight_amount_{{ $index }}" value="{{ $item->freight_amount > 0 ? (float)$item->freight_amount : '' }}" placeholder="0" style="width: 62px;" oninput="recalculateRowTotal({{ $index }})">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="grid-input text-right" name="items[{{ $index }}][unload_rate]" id="unload_rate_{{ $index }}" value="{{ $item->unload_rate > 0 ? (float)$item->unload_rate : '' }}" placeholder="0" style="width: 52px;" oninput="onUnloadRateInput({{ $index }}, this.value)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="grid-input text-right" name="items[{{ $index }}][unload_amount]" id="unload_amount_{{ $index }}" value="{{ $item->unload_amount > 0 ? (float)$item->unload_amount : '' }}" placeholder="0" style="width: 55px;" oninput="updateRowUnloadAmount({{ $index }}, this.value)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="grid-input text-right" name="items[{{ $index }}][other_charges]" id="other_charges_{{ $index }}" value="{{ $item->other_charges > 0 ? (float)$item->other_charges : '' }}" placeholder="0" style="width: 48px;" oninput="recalculateRowTotal({{ $index }})">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="grid-input text-right" name="items[{{ $index }}][oda_charge]" id="oda_charge_{{ $index }}" value="{{ $item->oda_charge > 0 ? (float)$item->oda_charge : '' }}" placeholder="0" style="width: 52px;" oninput="recalculateRowTotal({{ $index }})">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="grid-input text-right font-bold" name="items[{{ $index }}][amount]" id="row_amount_input_{{ $index }}" value="{{ $item->amount > 0 ? (float)$item->amount : '' }}" placeholder="0" style="width: 65px; color: #8b0000;" oninput="onRowAmountManualEdit({{ $index }})">
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="21" style="text-align: center; color: #444; padding: 40px; font-size: 12px;">
                                    Please select an <strong>Account</strong>, <strong>Consignor</strong>, or <strong>Pending Bill Party</strong> above to load pending consignment notes.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- 4. Bottom Totals & Actions Footer -->
            <div class="party-bill-footer-bar">
                <!-- Row 1: Amounts Row -->
                <div class="footer-row-1">
                    <div class="footer-amounts-group">
                        <div class="ctrl-group">
                            <label for="bill_amount">BILL AMT.</label>
                            <input type="text" name="bill_amount" id="bill_amount" value="{{ (isset($existingInvoice) && $existingInvoice->bill_amount > 0) ? (float)$existingInvoice->bill_amount : '0' }}" placeholder="0" readonly style="width: 100px; background: #e8e8e8; text-align: right; font-weight: bold;">
                        </div>

                        <div class="ctrl-group">
                            <label for="gst_percent">GST%</label>
                            <input type="number" name="gst_percent" id="gst_percent" value="{{ (isset($existingInvoice) && $existingInvoice->gst_percent > 0) ? (float)$existingInvoice->gst_percent : ((old('is_gst_bill', $existingInvoice->is_gst_bill ?? false)) ? '18' : '0') }}" placeholder="0" step="0.01" style="width: 55px; text-align: right;">
                        </div>

                        <div class="ctrl-group">
                            <input type="checkbox" name="is_igst" id="is_igst" value="1" {{ old('is_igst', $existingInvoice->is_igst ?? false) ? 'checked' : '' }} style="cursor: pointer;">
                            <label for="is_igst" style="cursor: pointer;">IGST</label>
                        </div>

                        <div class="ctrl-group">
                            <label for="gst_amount">GST AMT.</label>
                            <input type="text" name="gst_amount" id="gst_amount" value="{{ (isset($existingInvoice) && $existingInvoice->gst_amount > 0) ? (float)$existingInvoice->gst_amount : '0' }}" placeholder="0" readonly style="width: 90px; background: #e8e8e8; text-align: right; font-weight: bold;">
                        </div>

                        <div class="ctrl-group">
                            <label for="total_amount">TOTAL AMT.</label>
                            <input type="text" name="total_amount" id="total_amount" value="{{ (isset($existingInvoice) && $existingInvoice->total_amount > 0) ? (float)$existingInvoice->total_amount : '0' }}" placeholder="0" readonly style="width: 110px; font-weight: bold; color: #8b0000; background: #ffffd0; text-align: right;">
                        </div>

                        <!-- Auto-Save Status Indicator placed at right of TOTAL AMT. -->
                        <span id="autosave_status_indicator" style="font-size: 11.5px; font-weight: 600; margin-left: 14px; padding: 3px 12px; border-radius: 14px; display: inline-flex; align-items: center; gap: 5px; text-shadow: none; transition: all 0.3s ease; opacity: 0; pointer-events: none; border: 1px solid transparent; background: #dcfce7; color: #166534;">
                            <span class="autosave-icon" style="font-weight: bold;">✓</span>
                            <span class="autosave-text" style="font-size: 11px;">All changes saved</span>
                        </span>
                    </div>
                </div>

                <!-- Row 2: Remarks Row -->
                <div class="footer-row-2">
                    <div class="ctrl-group" style="width: 100%;">
                        <label for="remark" style="min-width: 55px;">REMARK</label>
                        <input type="text" name="remark" id="remark" placeholder="ENTER INVOICE REMARKS OR NOTES..." value="{{ old('remark', $existingInvoice->remark ?? '') }}" style="width: 100%;">
                    </div>
                </div>

                <!-- Row 3: Lower Action Buttons Row (Matching Desktop ERP) -->
                <div class="footer-row-3">
                    <div class="voucher-group">
                        <label for="voucher_no_display">VOUCHER NO. :</label>
                        <input type="text" id="voucher_no_display" value="{{ $existingInvoice->invoice_no ?? ($nextInvoiceNo ?? 40) }}" readonly style="width: 80px;">
                    </div>

                    <div class="desktop-btn-bar">
                        <button type="button" class="btn-desktop-cyan" onclick="window.location.href='{{ route('invoice.create') }}';" title="New Invoice">
                            NEW
                        </button>
                        <button type="button" class="btn-desktop-cyan" id="btnDraftInvoice" onclick="submitInvoiceWithStatus('draft');" style="background: linear-gradient(to bottom, #fff8db 0%, #fae69e 50%, #f7d768 100%); border-color: #d4a017; font-weight: bold;" title="Save as Draft">
                            DRAFT
                        </button>
                        <button type="button" class="btn-desktop-cyan" id="btnGenerateInvoice" onclick="submitInvoiceWithStatus('finalized');" style="font-weight: bold;" title="Save Invoice">
                            SAVE
                        </button>
                        <button type="button" class="btn-desktop-cyan" id="btnPrintInvoice" onclick="handlePrintBtn();" title="Print Invoice">
                            PRINT
                        </button>
                        <button type="button" class="btn-desktop-cyan" id="btnCancelInvoice" onclick="handleCancelBtn();" @if($isEdit ?? false) style="background: linear-gradient(to bottom, #fee2e2 0%, #fecaca 50%, #fca5a5 100%); border-color: #ef4444;" title="Cancel this Bill" @else title="Reset Form" @endif>
                            CANCEL
                        </button>
                        <button type="button" class="btn-desktop-cyan" id="btnDeleteInvoice" onclick="handleDeleteBtn();" title="Delete Invoice">
                            DELETE
                        </button>
                        <button type="button" class="btn-desktop-cyan" onclick="window.location.href='{{ route('invoice.register') }}';">
                            EXIT
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const monthPartiesMap = @json($monthPartiesMap);
    window.allLocationsList = @json($allLocationsList ?? []);
    window.allConsigneesList = @json($allConsigneesList ?? []);

    // Global function to update Destination dropdown based on selected month and party/account
    window.updateDestinationDropdown = function() {
        const monthSelect = document.getElementById('for_month');
        const monthVal = monthSelect ? monthSelect.value : '';
        const key = (monthVal && monthVal !== '-- All Months --') ? monthVal : 'all';
        const data = monthPartiesMap[key] || monthPartiesMap[monthVal] || monthPartiesMap['all'] || { destinations: [], party_destinations: {} };

        const accountSelect = document.getElementById('account_name_select');
        const accountVal = accountSelect ? accountSelect.value.trim() : '';
        const consignorSelect = document.getElementById('consignor_select');
        const consignorVal = consignorSelect ? consignorSelect.value.trim() : '';

        const selectedParty = accountVal || consignorVal;

        let destList = [];
        if (selectedParty && data.party_destinations && data.party_destinations[selectedParty]) {
            destList = data.party_destinations[selectedParty];
        } else if (!selectedParty) {
            destList = data.destinations || [];
        } else {
            destList = [];
        }

        const destSelect = document.getElementById('destination_filter');
        if (destSelect) {
            const prevVal = destSelect.value;
            const dCount = destList.length;
            let dHtml = `<option value="">-- All Destinations ${dCount > 0 ? '(' + dCount + ')' : ''} --</option>`;
            destList.forEach(function(d) {
                dHtml += `<option value="${d}">${d}</option>`;
            });
            destSelect.innerHTML = dHtml;
            if (prevVal && destList.includes(prevVal)) {
                destSelect.value = prevVal;
            } else {
                destSelect.value = '';
            }
        }

        const dBadge = document.getElementById('destination_count_badge');
        if (dBadge) {
            dBadge.textContent = destList.length > 0 ? `(${destList.length})` : '';
        }
    };

    // Global function to update Item dropdown based on selected month and party/account
    window.updateItemDropdown = function() {
        const monthSelect = document.getElementById('for_month');
        const monthVal = monthSelect ? monthSelect.value : '';
        const key = (monthVal && monthVal !== '-- All Months --') ? monthVal : 'all';
        const data = monthPartiesMap[key] || monthPartiesMap[monthVal] || monthPartiesMap['all'] || { items: [], party_items: {} };

        const accountSelect = document.getElementById('account_name_select');
        const accountVal = accountSelect ? accountSelect.value.trim() : '';
        const consignorSelect = document.getElementById('consignor_select');
        const consignorVal = consignorSelect ? consignorSelect.value.trim() : '';

        const selectedParty = accountVal || consignorVal;

        let itemList = [];
        if (selectedParty && data.party_items && data.party_items[selectedParty]) {
            itemList = data.party_items[selectedParty];
        } else if (!selectedParty) {
            itemList = data.items || [];
        } else {
            itemList = [];
        }

        const itemSelect = document.getElementById('item_filter');
        if (itemSelect) {
            const prevVal = itemSelect.value;
            const iCount = itemList.length;
            let iHtml = `<option value="">-- All Items ${iCount > 0 ? '(' + iCount + ')' : ''} --</option>`;
            itemList.forEach(function(item) {
                iHtml += `<option value="${item}">${item}</option>`;
            });
            itemSelect.innerHTML = iHtml;
            if (prevVal && itemList.includes(prevVal)) {
                itemSelect.value = prevVal;
            } else {
                itemSelect.value = '';
            }
        }

        const iBadge = document.getElementById('item_count_badge');
        if (iBadge) {
            iBadge.textContent = itemList.length > 0 ? `(${itemList.length})` : '';
        }
    };

    // Global function to update Account, Consignor, Pending Parties, Destination, and Item dropdowns
    window.loadMonthParties = function(monthVal, autoFetchAfter = false) {
        const key = (monthVal && monthVal !== '-- All Months --') ? monthVal : 'all';
        const data = monthPartiesMap[key] || monthPartiesMap[monthVal] || monthPartiesMap['all'] || { consignors: [], pending_parties: [], destinations: [], items: [], party_destinations: {}, party_items: {} };

        const accountSelect = document.getElementById('account_name_select');
        const prevAccountVal = accountSelect ? accountSelect.value : '';

        const consignorSelect = document.getElementById('consignor_select');
        const prevConsignorVal = consignorSelect ? consignorSelect.value : '';

        const pendingSelect = document.getElementById('pending_party_select');
        const prevPendingVal = pendingSelect ? pendingSelect.value : '';

        const cCount = (data.consignors && data.consignors.length) ? data.consignors.length : 0;
        const pCount = (data.pending_parties && data.pending_parties.length) ? data.pending_parties.length : 0;

        // 1. Update Account Dropdown (Contains same consignors as Consignor dropdown, changes with month)
        if (accountSelect) {
            let aHtml = `<option value="">-- Select Account (${cCount}) --</option>`;
            if (data.consignors && data.consignors.length > 0) {
                data.consignors.forEach(function(item) {
                    aHtml += `<option value="${item}">${item}</option>`;
                });
            }
            accountSelect.innerHTML = aHtml;
            if (prevAccountVal) {
                if (data.consignors && data.consignors.includes(prevAccountVal)) {
                    accountSelect.value = prevAccountVal;
                } else {
                    const opt = document.createElement('option');
                    opt.value = prevAccountVal;
                    opt.textContent = prevAccountVal;
                    opt.selected = true;
                    accountSelect.appendChild(opt);
                }
            } else {
                accountSelect.value = '';
            }
        }
        const aBadge = document.getElementById('account_count_badge');
        if (aBadge) aBadge.textContent = `(${cCount})`;

        // 2. Update Consignor Dropdown
        if (consignorSelect) {
            let cHtml = `<option value="">-- All Consignors (${cCount}) --</option>`;
            if (data.consignors && data.consignors.length > 0) {
                data.consignors.forEach(function(item) {
                    cHtml += `<option value="${item}">${item}</option>`;
                });
            }
            consignorSelect.innerHTML = cHtml;
            if (prevConsignorVal) {
                if (data.consignors && data.consignors.includes(prevConsignorVal)) {
                    consignorSelect.value = prevConsignorVal;
                } else {
                    const opt = document.createElement('option');
                    opt.value = prevConsignorVal;
                    opt.textContent = prevConsignorVal;
                    opt.selected = true;
                    consignorSelect.appendChild(opt);
                }
            } else {
                consignorSelect.value = '';
            }
        }
        const cBadge = document.getElementById('consignor_count_badge');
        if (cBadge) cBadge.textContent = `(${cCount})`;

        // 3. Update Pending Bill Parties Dropdown
        if (pendingSelect) {
            let pHtml = `<option value="">-- Select Pending Party (${pCount}) --</option>`;
            if (data.pending_parties && data.pending_parties.length > 0) {
                data.pending_parties.forEach(function(item) {
                    pHtml += `<option value="${item}">${item}</option>`;
                });
            }
            pendingSelect.innerHTML = pHtml;
            if (prevPendingVal && data.pending_parties && data.pending_parties.includes(prevPendingVal)) {
                pendingSelect.value = prevPendingVal;
            } else {
                pendingSelect.value = '';
            }
        }
        const pBadge = document.getElementById('pending_count_badge');
        if (pBadge) pBadge.textContent = `(${pCount})`;

        // 4. Update Destination Dropdown
        window.updateDestinationDropdown();

        // 5. Update Item Dropdown
        window.updateItemDropdown();

        if (autoFetchAfter) {
            const accountVal = accountSelect ? accountSelect.value.trim() : '';
            const consignorVal = consignorSelect ? consignorSelect.value.trim() : '';
            if (accountVal || consignorVal) {
                fetchPendingBilties();
            } else {
                const tbody = document.getElementById('partyBillTableBody');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="21" style="text-align: center; color: #444; padding: 40px; font-size: 12px;">
                                Please select an <strong>Account</strong>, <strong>Consignor</strong>, or <strong>Pending Bill Party</strong> above to load pending consignment notes.
                            </td>
                        </tr>
                    `;
                }
                recalculateTotals();
            }
        }
    };

    // Recalculate Grand Totals
    window.recalculateTotals = function() {
        let billAmount = 0;
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        
        checkboxes.forEach(cb => {
            const tr = cb.closest('tr');
            if (tr) {
                const amtInput = tr.querySelector('input[name*="[amount]"]');
                if (amtInput) {
                    billAmount += parseFloat(amtInput.value) || 0;
                }
            }
        });

        const gstPercent = parseFloat(document.getElementById('gst_percent')?.value) || 0;
        const gstAmount = (billAmount * gstPercent) / 100.0;
        const totalAmount = billAmount + gstAmount;

        const billAmtInput = document.getElementById('bill_amount');
        if (billAmtInput) billAmtInput.value = billAmount > 0 ? (Number.isInteger(billAmount) ? billAmount : billAmount.toFixed(2)) : '0';

        const gstAmtInput = document.getElementById('gst_amount');
        if (gstAmtInput) gstAmtInput.value = gstAmount > 0 ? (Number.isInteger(gstAmount) ? gstAmount : gstAmount.toFixed(2)) : '0';

        const totalAmtInput = document.getElementById('total_amount');
        if (totalAmtInput) totalAmtInput.value = totalAmount > 0 ? (Number.isInteger(totalAmount) ? totalAmount : totalAmount.toFixed(2)) : '0';

        if (typeof window.autoFitAllGridInputs === 'function') {
            window.autoFitAllGridInputs();
        }

        if (typeof window.triggerAutoSave === 'function') {
            window.triggerAutoSave(600);
        }
    };
    function recalculateTotals() { window.recalculateTotals(); }

    // Global function to toggle GST Bill behavior (hide Third Party C.N.No & set GST% to 18)
    window.toggleGstBill = function() {
        const gstCheckbox = document.getElementById('is_gst_bill');
        const isChecked = gstCheckbox ? gstCheckbox.checked : false;
        const gstPercentInput = document.getElementById('gst_percent');
        const table = document.getElementById('partyBillTable');
        
        if (isChecked) {
            if (gstPercentInput && (!gstPercentInput.value || gstPercentInput.value === '0.00' || gstPercentInput.value === '0')) {
                gstPercentInput.value = '18';
            }
            if (table) table.classList.add('hide-third-party-cn');
        } else {
            if (gstPercentInput && (gstPercentInput.value === '18.00' || gstPercentInput.value === '18')) {
                gstPercentInput.value = '0';
            }
            if (table) table.classList.remove('hide-third-party-cn');
        }
        window.recalculateTotals();
    };
    function toggleGstBill() { window.toggleGstBill(); }

    // Real-Time Auto-Save Engine (Excel / Google Sheets Style)
    let autoSaveTimer = null;
    let isAutoSaving = false;
    let autoSaveQueued = false;
    let queuedExplicitStatus = null;

    function updateAutoSaveIndicator(state, message = '') {
        const ind = document.getElementById('autosave_status_indicator');
        if (!ind) return;
        
        ind.style.opacity = '1';
        const iconEl = ind.querySelector('.autosave-icon');
        const textEl = ind.querySelector('.autosave-text');

        if (state === 'saving') {
            ind.style.background = '#fef3c7';
            ind.style.color = '#92400e';
            ind.style.borderColor = '#fde68a';
            if (iconEl) iconEl.textContent = '⏳';
            if (textEl) textEl.textContent = message || 'Saving to database...';
        } else if (state === 'saved') {
            ind.style.background = '#dcfce7';
            ind.style.color = '#166534';
            ind.style.borderColor = '#86efac';
            if (iconEl) iconEl.textContent = '✓';
            const timeStr = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            if (textEl) textEl.textContent = message || `All changes saved (${timeStr})`;
            
            setTimeout(() => {
                if (ind && ind.dataset.state === 'saved') {
                    ind.style.opacity = '0.7';
                }
            }, 3000);
        } else if (state === 'error') {
            ind.style.background = '#fee2e2';
            ind.style.color = '#991b1b';
            ind.style.borderColor = '#fca5a5';
            if (iconEl) iconEl.textContent = '⚠️';
            if (textEl) textEl.textContent = message || 'Auto-save failed';
        }
        ind.dataset.state = state;
    }

    window.triggerAutoSave = function(debounceMs = 600, explicitStatus = null) {
        if (explicitStatus) {
            queuedExplicitStatus = explicitStatus;
        }

        if (autoSaveTimer) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = null;
        }

        if (debounceMs <= 0) {
            window.executeAutoSave();
        } else {
            updateAutoSaveIndicator('saving', 'Saving changes...');
            autoSaveTimer = setTimeout(() => {
                window.executeAutoSave();
            }, debounceMs);
        }
    };

    window.executeAutoSave = function(explicitStatusOverride = null, isUserClick = false) {
        const form = document.getElementById('invoiceForm');
        if (!form) return;

        const targetStatus = explicitStatusOverride || queuedExplicitStatus || document.getElementById('invoice_status')?.value || 'draft';
        queuedExplicitStatus = null;

        const accountSelect = document.getElementById('account_name_select');
        const accountName = accountSelect ? accountSelect.value.trim() : '';
        const rows = document.querySelectorAll('#partyBillTableBody tr.selected-row');

        if (!accountName || rows.length === 0) {
            return;
        }

        if (isAutoSaving) {
            autoSaveQueued = true;
            return;
        }

        isAutoSaving = true;
        updateAutoSaveIndicator('saving', 'Saving to database...');

        const statusInput = document.getElementById('invoice_status');
        if (statusInput) statusInput.value = targetStatus;

        const formData = new FormData(form);
        const existingIdInput = document.getElementById('existing_invoice_id');
        const existingId = existingIdInput ? existingIdInput.value : '';

        let targetUrl = form.action;
        if (existingId) {
            formData.set('_method', 'PUT');
            targetUrl = `{{ url('/invoice/update') }}/${existingId}`;
        } else {
            formData.delete('_method');
            targetUrl = `{{ route('invoice.store') }}`;
        }

        fetch(targetUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errData => { throw new Error(errData.message || 'Server error ' + response.status); });
            }
            return response.json();
        })
        .then(data => {
            isAutoSaving = false;
            if (data.success) {
                if (data.invoice_id) {
                    if (existingIdInput) existingIdInput.value = data.invoice_id;
                    form.action = data.update_url || `{{ url('/invoice/update') }}/${data.invoice_id}`;
                    if (data.edit_url && window.location.pathname !== `/invoice/edit/${data.invoice_id}`) {
                        window.history.replaceState({}, '', data.edit_url);
                    }
                }

                const voucherDisplay = document.getElementById('voucher_no_display');
                if (voucherDisplay && data.invoice_no) {
                    voucherDisplay.value = data.invoice_no;
                }

                const generateBtn = document.getElementById('btnGenerateInvoice');
                if (generateBtn) {
                    generateBtn.textContent = 'SAVE';
                    generateBtn.title = 'Save Invoice';
                }

                updateAutoSaveIndicator('saved', `All changes saved`);

                if (isUserClick) {
                    Swal.fire({
                        title: data.status === 'draft' ? 'Saved as Draft!' : 'Invoice Saved!',
                        text: `Invoice #${data.series || ''}-${data.invoice_no || ''} saved successfully!`,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: '🖨 Print Invoice Now',
                        cancelButtonText: 'Continue Editing',
                        confirmButtonColor: '#0f3460',
                        cancelButtonColor: '#64748b'
                    }).then((result) => {
                        if (result.isConfirmed && (data.invoice_id || existingId)) {
                            window.open(`{{ url('/invoice/print') }}/${data.invoice_id || existingId}`, '_blank');
                        }
                    });
                }
            } else {
                updateAutoSaveIndicator('error', data.message || 'Auto-save failed');
            }

            if (autoSaveQueued) {
                autoSaveQueued = false;
                setTimeout(() => window.executeAutoSave(), 200);
            }
        })
        .catch(err => {
            isAutoSaving = false;
            console.error('AutoSave error:', err);
            updateAutoSaveIndicator('error', err.message || 'Auto-save failed');
            if (isUserClick) {
                Swal.fire({
                    title: 'Save Failed',
                    text: err.message || 'An error occurred while saving invoice.',
                    icon: 'error',
                    confirmButtonColor: '#0f3460'
                });
            }
        });
    };

    // Helper: Submit form with specific status ('draft' or 'finalized')
    window.submitInvoiceWithStatus = function(status) {
        const statusInput = document.getElementById('invoice_status');
        if (statusInput) {
            statusInput.value = status;
        }
        window.executeAutoSave(status, true);
    };

    // Global Render Function for Table Rows (Used by Lookup and FetchPendingBilties)
    window.renderTableRows = function(rows) {
        const tbody = document.getElementById('partyBillTableBody');
        if (!tbody) return;

        if (!rows || rows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="21" style="text-align: center; color: #8b0000; padding: 35px; font-weight: bold;">
                        No consignment notes found for the selected criteria.
                    </td>
                </tr>
            `;
            window.recalculateTotals();
            return;
        }

        const fmtVal = (val) => {
            if (val === undefined || val === null || val === '') return '';
            const num = parseFloat(val);
            if (isNaN(num) || num === 0) return '';
            return Number.isInteger(num) ? num : num.toFixed(2).replace(/\.?0+$/, '');
        };
        const fmtWeight = (val) => {
            if (val === undefined || val === null || val === '') return '';
            const num = parseFloat(val);
            if (isNaN(num) || num === 0) return '';
            return Number.isInteger(num) ? num : num.toFixed(3).replace(/\.?0+$/, '');
        };

        let html = '';
        rows.forEach((row, index) => {
            html += `
                <tr class="selected-row" id="row_${index}">
                    <td>
                        <input type="checkbox" class="row-checkbox" checked onchange="toggleRowSelection(this, ${index})">
                        <input type="hidden" name="items[${index}][bilty_id]" value="${row.bilty_id || ''}">
                    </td>
                    <td>${row.sr_no || (index + 1)}</td>
                    <td>
                        <input type="text" class="grid-input text-center" name="items[${index}][date]" value="${row.date || ''}" style="width: 72px;">
                    </td>
                    <td>
                        <input type="text" class="grid-input text-center font-bold" name="items[${index}][bilty_no]" value="${row.bilty_no || ''}" readonly style="width: 58px; background: #e8e8e8; cursor: not-allowed;" title="C.N. No cannot be changed">
                    </td>

                    <td>
                        <input type="number" class="grid-input text-center" name="items[${index}][packages]" value="${row.packages || ''}" placeholder="0" style="width: 42px;" oninput="onUnitCatOrRateOrPktChange(${index})">
                    </td>
                    <td>
                        <div class="autocomplete-grid-wrap">
                            <input type="text" class="grid-input text-left auto-grid-input" name="items[${index}][from_location]" value="${row.from_location || ''}" data-original="${row.from_location || ''}" data-type="location" style="width: 170px;" autocomplete="off">
                            <div class="auto-grid-dropdown"></div>
                        </div>
                    </td>
                    <td>
                        <div class="autocomplete-grid-wrap">
                            <input type="text" class="grid-input text-left auto-grid-input" name="items[${index}][to_location]" value="${row.to_location || ''}" data-original="${row.to_location || ''}" data-type="location" style="width: 120px;" autocomplete="off">
                            <div class="auto-grid-dropdown"></div>
                        </div>
                    </td>
                    <td>
                        <div class="autocomplete-grid-wrap">
                            <input type="text" class="grid-input text-left auto-grid-input" name="items[${index}][consignee_name]" value="${row.consignee_name || ''}" data-original="${row.consignee_name || ''}" data-type="consignee" style="width: 125px;" autocomplete="off">
                            <div class="auto-grid-dropdown"></div>
                        </div>
                    </td>
                    <td>
                        <input type="text" class="grid-input text-left" name="items[${index}][item_description]" value="${row.item_description || ''}" style="width: 90px;">
                    </td>
                    <td>
                        <input type="text" class="grid-input text-center" name="items[${index}][invoice_no_ref]" value="${row.invoice_no_ref || ''}" style="width: 72px;">
                    </td>
                    <td>
                        <input type="number" step="0.001" class="grid-input text-right" name="items[${index}][weight]" id="weight_${index}" value="${fmtWeight(row.weight)}" placeholder="0" style="width: 58px;" oninput="onUnitCatOrRateOrPktChange(${index})">
                    </td>
                    <td>
                        <select class="grid-input text-center font-bold" name="items[${index}][weight_type]" id="weight_type_${index}" style="width: 62px; height: 20px; padding: 0 1px; font-size: 10px;" onchange="onUnitCatChange(${index}, this.value)">
                            <option value="KG" ${(row.weight_type || 'KG') === 'KG' ? 'selected' : ''}>KG</option>
                            <option value="FIXED" ${(row.weight_type || '') === 'FIXED' ? 'selected' : ''}>FIXED</option>
                            <option value="CARTON" ${(row.weight_type || '') === 'CARTON' ? 'selected' : ''}>CARTON</option>
                            <option value="PKT" ${(row.weight_type || '') === 'PKT' ? 'selected' : ''}>PKT</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="grid-input text-right" name="items[${index}][rate]" id="rate_${index}" value="${fmtVal(row.rate)}" placeholder="0" style="width: 58px;" oninput="onRateInput(${index}, this.value)">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="grid-input text-right" name="items[${index}][st_charge]" id="st_charge_${index}" value="${fmtVal(row.st_charge)}" placeholder="0" style="width: 48px;" oninput="onStChargeInput(${index}, this.value)">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="grid-input text-right font-bold" name="items[${index}][freight_amount]" id="freight_amount_${index}" value="${fmtVal(row.freight_amount)}" placeholder="0" style="width: 62px;" oninput="recalculateRowTotal(${index})">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="grid-input text-right" name="items[${index}][unload_rate]" id="unload_rate_${index}" value="${fmtVal(row.unload_rate)}" placeholder="0" style="width: 52px;" oninput="onUnloadRateInput(${index}, this.value)">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="grid-input text-right" name="items[${index}][unload_amount]" id="unload_amount_${index}" value="${fmtVal(row.unload_amount)}" placeholder="0" style="width: 55px;" oninput="updateRowUnloadAmount(${index}, this.value)">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="grid-input text-right" name="items[${index}][other_charges]" id="other_charges_${index}" value="${fmtVal(row.other_charges)}" placeholder="0" style="width: 48px;" oninput="recalculateRowTotal(${index})">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="grid-input text-right" name="items[${index}][oda_charge]" id="oda_charge_${index}" value="${fmtVal(row.oda_charge)}" placeholder="0" style="width: 52px;" oninput="recalculateRowTotal(${index})">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="grid-input text-right font-bold" name="items[${index}][amount]" id="row_amount_input_${index}" value="${fmtVal(row.amount)}" placeholder="0" style="width: 65px; color: #8b0000;" oninput="onRowAmountManualEdit(${index})">
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        window.recalculateTotals();
        if (typeof window.attachGridAutocomplete === 'function') {
            window.attachGridAutocomplete();
        }
        if (typeof window.autoFitAllGridInputs === 'function') {
            setTimeout(window.autoFitAllGridInputs, 50);
        }
    };

    // Dynamic Invoice Lookup (Just like CN Book)
    let invoiceLookupTimeout = null;

    window.handleInvoiceLookup = function(immediate = false) {
        const invoiceNoInput = document.getElementById('invoice_no');
        const seriesInput = document.getElementById('series');
        if (!invoiceNoInput) return;

        const invoiceNo = invoiceNoInput.value.trim();
        const series = seriesInput ? seriesInput.value.trim() : '{{ $defaultSeries ?? '26-27' }}';

        // Keep Voucher No display in sync
        const voucherDisplay = document.getElementById('voucher_no_display');
        if (voucherDisplay) {
            voucherDisplay.value = invoiceNo;
        }

        if (!invoiceNo) {
            window.resetInvoiceFormToNew('', false);
            return;
        }

        clearTimeout(invoiceLookupTimeout);

        const doLookup = () => {
            const params = new URLSearchParams({ series: series });
            fetch(`{{ url('/invoice/lookup') }}/${invoiceNo}?${params.toString()}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Invoice not found');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data && data.found && data.invoice) {
                        window.populateExistingInvoice(data);
                    } else {
                        window.resetInvoiceFormToNew(invoiceNo, immediate);
                    }
                })
                .catch(err => {
                    // Not found in database -> Treat as New Invoice & alert user
                    window.resetInvoiceFormToNew(invoiceNo, immediate);
                });
        };

        if (immediate) {
            doLookup();
        } else {
            invoiceLookupTimeout = setTimeout(doLookup, 200);
        }
    };

    window.populateExistingInvoice = function(data) {
        const inv = data.invoice;
        const form = document.getElementById('invoiceForm');
        if (form) {
            form.action = `{{ url('/invoice/update') }}/${inv.id}`;
            let methodInput = form.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                form.appendChild(methodInput);
            }
            methodInput.value = 'PUT';
        }

        const existingIdInput = document.getElementById('existing_invoice_id');
        if (existingIdInput) {
            existingIdInput.value = inv.id;
        }

        const statusInput = document.getElementById('invoice_status');
        if (statusInput) {
            statusInput.value = inv.status || 'finalized';
        }

        // Action Buttons
        const generateBtn = document.getElementById('btnGenerateInvoice');
        if (generateBtn) {
            generateBtn.textContent = 'SAVE';
            generateBtn.title = 'Save Invoice';
        }
        const cancelBtn = document.getElementById('btnCancelInvoice');
        if (cancelBtn) {
            cancelBtn.title = 'Cancel this Bill';
            cancelBtn.style.background = 'linear-gradient(to bottom, #fee2e2 0%, #fecaca 50%, #fca5a5 100%)';
            cancelBtn.style.borderColor = '#ef4444';
        }

        // Date
        if (inv.invoice_date) {
            const dateInput = document.getElementById('invoice_date');
            if (dateInput) dateInput.value = inv.invoice_date;
        }

        // For Month
        if (inv.for_month) {
            const mSelect = document.getElementById('for_month');
            if (mSelect) mSelect.value = inv.for_month;
        }

        // Account Select
        const accountSelect = document.getElementById('account_name_select');
        if (accountSelect) {
            let hasOpt = false;
            for (let i = 0; i < accountSelect.options.length; i++) {
                if (accountSelect.options[i].value === inv.account_name) {
                    hasOpt = true;
                    break;
                }
            }
            if (!hasOpt && inv.account_name) {
                const opt = document.createElement('option');
                opt.value = inv.account_name;
                opt.textContent = inv.account_name;
                accountSelect.appendChild(opt);
            }
            accountSelect.value = inv.account_name || '';
        }

        // Consignor Select
        const consignorSelect = document.getElementById('consignor_select');
        if (consignorSelect) {
            let hasOpt = false;
            for (let i = 0; i < consignorSelect.options.length; i++) {
                if (consignorSelect.options[i].value === inv.consignor_name) {
                    hasOpt = true;
                    break;
                }
            }
            if (!hasOpt && inv.consignor_name) {
                const opt = document.createElement('option');
                opt.value = inv.consignor_name;
                opt.textContent = inv.consignor_name;
                consignorSelect.appendChild(opt);
            }
            consignorSelect.value = inv.consignor_name || '';
        }

        // Item & Destination Filters
        const itemSelect = document.getElementById('item_filter');
        if (itemSelect) itemSelect.value = inv.item_filter || '';

        const destSelect = document.getElementById('destination_filter');
        if (destSelect) destSelect.value = inv.destination_filter || '';

        // GST Bill
        const gstCb = document.getElementById('is_gst_bill');
        if (gstCb) {
            gstCb.checked = !!inv.is_gst_bill;
            window.toggleGstBill();
        }

        // IGST
        const igstCb = document.getElementById('is_igst');
        if (igstCb) igstCb.checked = !!inv.is_igst;

        // Remarks
        const remarkInput = document.getElementById('remark');
        if (remarkInput) remarkInput.value = inv.remark || '';

        // Amounts
        const gstPInput = document.getElementById('gst_percent');
        if (gstPInput) gstPInput.value = inv.gst_percent || '0';

        // Render Rows
        window.renderTableRows(data.rows || []);
    };

    window.resetInvoiceFormToNew = function(invoiceNo, showAlert = false) {
        const form = document.getElementById('invoiceForm');
        if (form) {
            form.action = `{{ route('invoice.store') }}`;
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
        }

        const existingIdInput = document.getElementById('existing_invoice_id');
        if (existingIdInput) {
            existingIdInput.value = '';
        }

        const statusInput = document.getElementById('invoice_status');
        if (statusInput) {
            statusInput.value = 'finalized';
        }

        // Action Buttons
        const generateBtn = document.getElementById('btnGenerateInvoice');
        if (generateBtn) {
            generateBtn.textContent = 'SAVE';
            generateBtn.title = 'Save Invoice';
        }
        const cancelBtn = document.getElementById('btnCancelInvoice');
        if (cancelBtn) {
            cancelBtn.title = 'Reset Form';
            cancelBtn.style.background = '';
            cancelBtn.style.borderColor = '';
        }

        const voucherDisplay = document.getElementById('voucher_no_display');
        if (voucherDisplay) {
            voucherDisplay.value = invoiceNo || '';
        }

        // Clear Table rows with helpful guidance
        const tbody = document.getElementById('partyBillTableBody');
        if (tbody) {
            if (invoiceNo) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="21" style="text-align: center; color: #9a3412; background: #fff7ed; padding: 35px 20px; font-size: 13px; border-top: 1px solid #fed7aa; border-bottom: 1px solid #fed7aa;">
                            <div style="font-weight: bold; font-size: 14px; margin-bottom: 6px; color: #c2410c;">
                                ℹ️ No bill found on Receipt No. ${invoiceNo}
                            </div>
                            <div style="color: #4b5563; font-size: 12px;">
                                Please select an <strong>Account</strong>, <strong>Consignor</strong>, or <strong>Pending Bill Party</strong> above to generate a new bill for this number.
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="21" style="text-align: center; color: #444; padding: 40px; font-size: 12px;">
                            Please select an <strong>Account</strong>, <strong>Consignor</strong>, or <strong>Pending Bill Party</strong> above to load pending consignment notes.
                        </td>
                    </tr>
                `;
            }
        }

        // Reset Totals
        const billAmt = document.getElementById('bill_amount');
        if (billAmt) billAmt.value = '0';
        const gstAmt = document.getElementById('gst_amount');
        if (gstAmt) gstAmt.value = '0';
        const totalAmt = document.getElementById('total_amount');
        if (totalAmt) totalAmt.value = '0';
        const remarkInput = document.getElementById('remark');
        if (remarkInput) remarkInput.value = '';
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof window.autoFitAllGridInputs === 'function') {
            setTimeout(window.autoFitAllGridInputs, 150);
        }

        // Initial sync of month parties & destinations & items
        const monthSelect = document.getElementById('for_month');
        if (monthSelect) {
            window.loadMonthParties(monthSelect.value, false);
            monthSelect.addEventListener('change', function() {
                window.loadMonthParties(this.value, true);
            });
        }

        // Attach Receipt No / Series lookup listeners with Enter key support
        const invoiceNoInput = document.getElementById('invoice_no');
        if (invoiceNoInput) {
            invoiceNoInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.handleInvoiceLookup(true);
                }
            });
            invoiceNoInput.addEventListener('input', function() { window.handleInvoiceLookup(false); });
            invoiceNoInput.addEventListener('change', function() { window.handleInvoiceLookup(true); });
            invoiceNoInput.addEventListener('blur', function() { window.handleInvoiceLookup(true); });
        }

        const seriesInput = document.getElementById('series');
        if (seriesInput) {
            seriesInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.value = this.value.toUpperCase();
                    window.handleInvoiceLookup(true);
                }
            });
            seriesInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
                window.handleInvoiceLookup(false);
            });
            seriesInput.addEventListener('change', function() { window.handleInvoiceLookup(true); });
        }

        @if($isEdit ?? false)
            window.attachGridAutocomplete();
            window.recalculateTotals();
        @endif

        // When Account changes
        const accountSelect = document.getElementById('account_name_select');
        if (accountSelect) {
            accountSelect.addEventListener('change', function() {
                const val = this.value;
                const consignorSelect = document.getElementById('consignor_select');
                if (consignorSelect) {
                    let hasOption = false;
                    for (let i = 0; i < consignorSelect.options.length; i++) {
                        if (consignorSelect.options[i].value === val) {
                            hasOption = true;
                            break;
                        }
                    }
                    if (hasOption) {
                        consignorSelect.value = val;
                    }
                }
                window.updateDestinationDropdown();
                window.updateItemDropdown();
                if (val) {
                    fetchPendingBilties();
                }
            });
        }

        // When Consignor changes
        const consignorSelect = document.getElementById('consignor_select');
        if (consignorSelect) {
            consignorSelect.addEventListener('change', function() {
                const val = this.value;
                const accountSelect = document.getElementById('account_name_select');
                if (val && accountSelect) {
                    accountSelect.value = val;
                }
                window.updateDestinationDropdown();
                window.updateItemDropdown();
                if (val || (accountSelect && accountSelect.value)) {
                    fetchPendingBilties();
                }
            });
        }

        // When Pending Bill Party changes
        const pendingSelect = document.getElementById('pending_party_select');
        if (pendingSelect) {
            pendingSelect.addEventListener('change', function() {
                const val = this.value;
                if (val) {
                    const accSelect = document.getElementById('account_name_select');
                    if (accSelect) {
                        let exists = false;
                        for (let i = 0; i < accSelect.options.length; i++) {
                            if (accSelect.options[i].value === val) {
                                exists = true;
                                break;
                            }
                        }
                        if (!exists) {
                            const opt = document.createElement('option');
                            opt.value = val;
                            opt.textContent = val;
                            accSelect.appendChild(opt);
                        }
                        accSelect.value = val;
                    }
                    const cSelect = document.getElementById('consignor_select');
                    if (cSelect) {
                        let cExists = false;
                        for (let i = 0; i < cSelect.options.length; i++) {
                            if (cSelect.options[i].value === val) {
                                cExists = true;
                                break;
                            }
                        }
                        if (cExists) {
                            cSelect.value = val;
                        } else {
                            cSelect.value = '';
                        }
                    }
                    window.updateDestinationDropdown();
                    window.updateItemDropdown();
                    fetchPendingBilties();
                }
            });
        }

        // Destination dropdown change filter
        const destinationSelect = document.getElementById('destination_filter');
        if (destinationSelect) {
            destinationSelect.addEventListener('change', function() {
                fetchPendingBilties();
            });
        }

        // Item filter change
        const itemSelect = document.getElementById('item_filter');
        if (itemSelect) {
            itemSelect.addEventListener('change', function() {
                fetchPendingBilties();
            });
        }

        // Search button trigger
        const btnFetch = document.getElementById('btnFetchBilties');
        if (btnFetch) {
            btnFetch.addEventListener('click', function() {
                fetchPendingBilties();
            });
        }

        // GST Bill toggle event
        const gstCheckbox = document.getElementById('is_gst_bill');
        if (gstCheckbox) {
            gstCheckbox.addEventListener('change', window.toggleGstBill);
            window.toggleGstBill();
        }

        const gstPercentInput = document.getElementById('gst_percent');
        if (gstPercentInput) {
            gstPercentInput.addEventListener('input', recalculateTotals);
        }

        const igstCheckbox = document.getElementById('is_igst');
        if (igstCheckbox) {
            igstCheckbox.addEventListener('change', recalculateTotals);
        }

        // Select All Checkbox
        const checkAll = document.getElementById('checkAllRows');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = isChecked;
                    const tr = cb.closest('tr');
                    if (tr) {
                        if (isChecked) tr.classList.add('selected-row');
                        else tr.classList.remove('selected-row');
                    }
                });
                recalculateTotals();
            });
        }

        // Form Submit Validation
        const invoiceForm = document.getElementById('invoiceForm');
        if (invoiceForm) {
            invoiceForm.addEventListener('submit', function(e) {
                const selectedItems = document.querySelectorAll('.row-checkbox:checked');
                if (selectedItems.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Consignment Selected',
                        text: 'Please select at least one consignment note (C.N.) to generate the invoice.',
                        confirmButtonColor: '#0f3460'
                    });
                    return false;
                }

                const accountSelect = document.getElementById('account_name_select');
                const accountName = accountSelect ? accountSelect.value.trim() : '';
                if (!accountName) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Account Required',
                        text: 'Please select an Account for this invoice.',
                        confirmButtonColor: '#0f3460'
                    });
                    return false;
                }
            });
        }

        // Global Table Event Delegation for AutoSave on ANY cell input / select change
        const partyTable = document.getElementById('partyBillTable');
        if (partyTable) {
            partyTable.addEventListener('input', function(e) {
                window.triggerAutoSave(500);
            });
            partyTable.addEventListener('change', function(e) {
                window.triggerAutoSave(0);
            });
        }

        // Header inputs AutoSave
        const autoSaveHeaderIds = ['invoice_date', 'series', 'invoice_no', 'account_name_select', 'consignor_select', 'for_month', 'item_filter', 'destination_filter', 'is_gst_bill', 'is_igst', 'gst_percent', 'remark'];
        autoSaveHeaderIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', () => window.triggerAutoSave(500));
                el.addEventListener('change', () => window.triggerAutoSave(0));
            }
        });

        // Automatic SweetAlert on save with Print Invoice button
        @if(session('print_invoice_id'))
            Swal.fire({
                title: 'Invoice Saved!',
                text: "{{ session('success') }}",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '🖨 Print Invoice Now',
                cancelButtonText: 'Continue',
                confirmButtonColor: '#0f3460',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open("{{ route('invoice.print', session('print_invoice_id')) }}", '_blank');
                }
            });
        @elseif(session('success'))
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonColor: '#0f3460'
            });
        @elseif(session('error'))
            Swal.fire({
                title: 'Error!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonColor: '#0f3460'
            });
        @endif
    });

    // Global function to fetch pending bilties
    function fetchPendingBilties() {
        const accountSelect = document.getElementById('account_name_select');
        const partyName = accountSelect ? accountSelect.value.trim() : '';
        const consignorSelect = document.getElementById('consignor_select');
        const consignorName = consignorSelect ? consignorSelect.value.trim() : '';
        const forMonth = document.getElementById('for_month').value.trim();
        const itemFilter = document.getElementById('item_filter').value.trim();
        const destSelect = document.getElementById('destination_filter');
        const destFilter = destSelect ? destSelect.value.trim() : '';

        if (!partyName && !consignorName) {
            Swal.fire({
                icon: 'info',
                title: 'Select Party or Account',
                text: 'Please select an Account, Consignor, or Pending Bill Party to fetch bilties.',
                confirmButtonColor: '#0f3460'
            });
            return;
        }

        const tbody = document.getElementById('partyBillTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="21" style="text-align:center; padding: 30px; font-weight: bold; color: #0f3460;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" style="animation: spin 1s linear infinite; vertical-align: middle; margin-right: 8px;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:0.75;"></path></svg>
                    Fetching pending consignment notes...
                </td>
            </tr>
        `;

        const params = new URLSearchParams({
            party_name: partyName,
            consignor_name: consignorName,
            for_month: forMonth,
            item_description: itemFilter,
            destination: destFilter
        });

        const existingIdInput = document.getElementById('existing_invoice_id');
        if (existingIdInput && existingIdInput.value) {
            params.append('invoice_id', existingIdInput.value);
        }

        fetch(`{{ route('invoice.pending_bilties', [], false) }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                renderTableRows(data.rows || []);
            })
            .catch(err => {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="21" style="text-align: center; color: #ef4444; padding: 25px;">
                            Error loading bilties: ${err.message}
                        </td>
                    </tr>
                `;
            });
    }

    // Row Selection Toggle
    window.toggleRowSelection = function(checkbox, index) {
        const tr = document.getElementById(`row_${index}`);
        if (tr) {
            if (checkbox.checked) tr.classList.add('selected-row');
            else tr.classList.remove('selected-row');
        }
        recalculateTotals();
    };

    function fmtCalcVal(val) {
        if (val === null || val === undefined || isNaN(val) || val <= 0) return '';
        const num = Math.round((val + Number.EPSILON) * 100) / 100;
        if (Number.isInteger(num)) {
            return num.toLocaleString('fullwide', { useGrouping: false });
        }
        return num.toFixed(2);
    }

    // When Weight, Unit Cat., Rate, or Pkt changes on a single row
    window.onUnitCatOrRateOrPktChange = function(index) {
        const weight = parseFloat(document.getElementById(`weight_${index}`)?.value) || 0;
        const rate = parseFloat(document.getElementById(`rate_${index}`)?.value) || 0;
        const pktsInput = document.querySelector(`input[name="items[${index}][packages]"]`);
        const pkts = parseFloat(pktsInput?.value) || 0;
        const unit = (document.getElementById(`weight_type_${index}`)?.value || 'KG').toUpperCase();
        const unloadRate = parseFloat(document.getElementById(`unload_rate_${index}`)?.value) || 0;

        const frInput = document.getElementById(`freight_amount_${index}`);
        if (frInput) {
            const frAmt = (unit === 'KG') ? (weight * rate) : (pkts * rate);
            frInput.value = fmtCalcVal(frAmt);
        }

        // Auto compute unload amount if unload_rate > 0
        if (unloadRate > 0) {
            const uAmt = (weight * unloadRate);
            const uAmtInput = document.getElementById(`unload_amount_${index}`);
            if (uAmtInput) uAmtInput.value = fmtCalcVal(uAmt);
        }

        recalculateRowTotal(index);
    };

    // Helper: Get trimmed Destination for a given row index
    function getRowDestination(index) {
        const destInput = document.querySelector(`input[name="items[${index}][to_location]"]`);
        return destInput ? destInput.value.trim().toLowerCase() : '';
    }

    // Live Cascading: When Rate (RT/KG/CB) changes on row index, cascade to rows below with SAME DESTINATION
    window.onRateInput = function(index, val) {
        const rows = document.querySelectorAll('#partyBillTableBody tr[id^="row_"]');
        const totalRows = rows.length;
        const sourceDest = getRowDestination(index);

        onUnitCatOrRateOrPktChange(index);

        for (let i = index + 1; i < totalRows; i++) {
            const targetDest = getRowDestination(i);
            if (sourceDest && targetDest && sourceDest === targetDest) {
                const rEl = document.getElementById(`rate_${i}`);
                if (rEl) {
                    rEl.value = val;
                    onUnitCatOrRateOrPktChange(i);
                }
            }
        }
        recalculateTotals();
    };

    // Live Cascading: When St.Ch. (Stationery Charge) changes on row index, cascade to rows below with SAME DESTINATION
    window.onStChargeInput = function(index, val) {
        const rows = document.querySelectorAll('#partyBillTableBody tr[id^="row_"]');
        const totalRows = rows.length;
        const sourceDest = getRowDestination(index);

        recalculateRowTotal(index);

        for (let i = index + 1; i < totalRows; i++) {
            const targetDest = getRowDestination(i);
            if (sourceDest && targetDest && sourceDest === targetDest) {
                const stEl = document.getElementById(`st_charge_${i}`);
                if (stEl) {
                    stEl.value = val;
                    recalculateRowTotal(i);
                }
            }
        }
        recalculateTotals();
    };

    // Live Cascading: When Unload Rate changes on row index, cascade to rows below with SAME DESTINATION
    window.onUnloadRateInput = function(index, val) {
        const rows = document.querySelectorAll('#partyBillTableBody tr[id^="row_"]');
        const totalRows = rows.length;
        const sourceDest = getRowDestination(index);

        updateRowUnloadRate(index, val);

        for (let i = index + 1; i < totalRows; i++) {
            const targetDest = getRowDestination(i);
            if (sourceDest && targetDest && sourceDest === targetDest) {
                const uEl = document.getElementById(`unload_rate_${i}`);
                if (uEl) {
                    uEl.value = val;
                    updateRowUnloadRate(i, val);
                }
            }
        }
        recalculateTotals();
    };

    // Live Cascading: When Unit Cat. (weight_type) changes on row index, cascade to rows below with SAME DESTINATION
    window.onUnitCatChange = function(index, val) {
        const rows = document.querySelectorAll('#partyBillTableBody tr[id^="row_"]');
        const totalRows = rows.length;
        const sourceDest = getRowDestination(index);

        onUnitCatOrRateOrPktChange(index);

        for (let i = index + 1; i < totalRows; i++) {
            const targetDest = getRowDestination(i);
            if (sourceDest && targetDest && sourceDest === targetDest) {
                const uCatEl = document.getElementById(`weight_type_${i}`);
                if (uCatEl) {
                    uCatEl.value = val;
                    onUnitCatOrRateOrPktChange(i);
                }
            }
        }
        recalculateTotals();
    };

    window.onWeightOrRateChange = window.onUnitCatOrRateOrPktChange;

    // Grid autocomplete handler for From, Destination, Consignee
    window.attachGridAutocomplete = function() {
        document.querySelectorAll('.auto-grid-input').forEach(input => {
            if (input.dataset.autocompleteAttached) return;
            input.dataset.autocompleteAttached = 'true';


            const wrap = input.closest('.autocomplete-grid-wrap');
            if (!wrap) return;
            const dropdown = wrap.querySelector('.auto-grid-dropdown');
            if (!dropdown) return;

            const type = input.dataset.type;
            const getList = () => {
                if (type === 'location') return window.allLocationsList || [];
                if (type === 'consignee') return window.allConsigneesList || [];
                return [];
            };

            let activeIndex = -1;

            function renderList(matches) {
                if (!matches.length) {
                    dropdown.style.display = 'none';
                    return;
                }
                let html = '';
                matches.slice(0, 30).forEach((item, idx) => {
                    const esc = item.replace(/"/g, '&quot;');
                    html += `<div class="auto-grid-item" data-index="${idx}" data-val="${esc}">${item}</div>`;
                });
                dropdown.innerHTML = html;
                dropdown.style.display = 'block';
                activeIndex = -1;
            }

            input.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                const original = (this.dataset.original || '').trim().toLowerCase();
                
                if (query === original && query !== '') {
                    dropdown.style.display = 'none';
                    return;
                }

                const list = getList();
                const matches = list.filter(item => item.toLowerCase().includes(query));
                renderList(matches);
            });

            input.addEventListener('keydown', function(e) {
                if (dropdown.style.display === 'none') {
                    if (e.key === 'ArrowDown') {
                        const query = input.value.trim().toLowerCase();
                        const list = getList();
                        const matches = list.filter(item => item.toLowerCase().includes(query));
                        renderList(matches);
                        e.preventDefault();
                    }
                    return;
                }

                const items = dropdown.querySelectorAll('.auto-grid-item');
                if (!items.length) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % items.length;
                    items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
                    items[activeIndex]?.scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
                    items[activeIndex]?.scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter' || e.key === 'Tab') {
                    if (activeIndex >= 0 && items[activeIndex]) {
                        e.preventDefault();
                        input.value = items[activeIndex].dataset.val;
                        input.dataset.original = input.value;
                        dropdown.style.display = 'none';
                    }
                } else if (e.key === 'Escape') {
                    dropdown.style.display = 'none';
                }
            });

            dropdown.addEventListener('mousedown', function(e) {
                const item = e.target.closest('.auto-grid-item');
                if (item) {
                    e.preventDefault();
                    input.value = item.dataset.val;
                    input.dataset.original = input.value;
                    dropdown.style.display = 'none';
                }
            });

            input.addEventListener('blur', function() {
                setTimeout(() => {
                    dropdown.style.display = 'none';
                }, 200);
            });
        });
    };

    // Row Unloading Rate calculation
    window.updateRowUnloadRate = function(index, rate) {
        const weight = parseFloat(document.getElementById(`weight_${index}`)?.value) || 0;
        const r = parseFloat(rate) || 0;
        const unloadAmt = (weight * r);
        
        const uAmtInput = document.getElementById(`unload_amount_${index}`);
        if (uAmtInput) uAmtInput.value = unloadAmt > 0 ? (Number.isInteger(unloadAmt) ? unloadAmt : unloadAmt.toFixed(2)) : '';
        
        recalculateRowTotal(index);
    };

    // Row Unloading Amount manual edit
    window.updateRowUnloadAmount = function(index, amt) {
        recalculateRowTotal(index);
    };

    // Manual edit on row total amount
    window.onRowAmountManualEdit = function(index) {
        recalculateTotals();
    };

    // Recalculate Row Total
    window.recalculateRowTotal = function(index) {
        const frAmt = parseFloat(document.getElementById(`freight_amount_${index}`)?.value) || 0;
        const stCh = parseFloat(document.getElementById(`st_charge_${index}`)?.value) || 0;
        const othCh = parseFloat(document.getElementById(`other_charges_${index}`)?.value) || 0;
        const odaCh = parseFloat(document.getElementById(`oda_charge_${index}`)?.value) || 0;
        const unloadAmt = parseFloat(document.getElementById(`unload_amount_${index}`)?.value) || 0;

        const total = frAmt + stCh + othCh + odaCh + unloadAmt;
        const amtInput = document.getElementById(`row_amount_input_${index}`);
        if (amtInput) {
            amtInput.value = fmtCalcVal(total);
        }
        
        window.recalculateTotals();
    };

    // Print Button Handler
    window.handlePrintBtn = function() {
        const existingIdInput = document.getElementById('existing_invoice_id');
        const invoiceId = existingIdInput ? existingIdInput.value : '';

        if (invoiceId) {
            window.open(`{{ url('/invoice/print') }}/${invoiceId}`, '_blank');
        } else {
            const selectedItems = document.querySelectorAll('.row-checkbox:checked');
            const invoiceForm = document.getElementById('invoiceForm');

            if (selectedItems.length > 0) {
                Swal.fire({
                    title: 'Print Invoice',
                    text: 'Choose whether to save and print this invoice or open a print preview in the official layout:',
                    icon: 'question',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: '💾 Save & Print',
                    denyButtonText: '👁️ Print Preview',
                    cancelButtonText: '📑 Invoice Register',
                    confirmButtonColor: '#0f3460',
                    denyButtonColor: '#2563eb',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let spInput = document.getElementById('save_and_print_input');
                        if (!spInput) {
                            spInput = document.createElement('input');
                            spInput.type = 'hidden';
                            spInput.name = 'save_and_print';
                            spInput.id = 'save_and_print_input';
                            invoiceForm.appendChild(spInput);
                        }
                        spInput.value = '1';
                        invoiceForm.submit();
                    } else if (result.isDenied) {
                        const originalAction = invoiceForm.action;
                        const originalTarget = invoiceForm.target;
                        invoiceForm.action = "{{ route('invoice.preview') }}";
                        invoiceForm.target = "_blank";
                        invoiceForm.submit();
                        setTimeout(() => {
                            invoiceForm.action = originalAction;
                            invoiceForm.target = originalTarget;
                        }, 500);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = "{{ route('invoice.register') }}";
                    }
                });
            } else {
                Swal.fire({
                    title: 'No Items Selected',
                    text: 'Select an Account and Consignment Notes above to generate an invoice, or open the Invoice Register to print existing invoices.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: '📑 Go to Invoice Register',
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#0f3460'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('invoice.register') }}";
                    }
                });
            }
        }
    };

    // Cancel Button Handler
    window.handleCancelBtn = function() {
        const existingIdInput = document.getElementById('existing_invoice_id');
        const invoiceId = existingIdInput ? existingIdInput.value : '';
        const series = document.getElementById('series')?.value || '{{ $defaultSeries ?? '26-27' }}';
        const invoiceNo = document.getElementById('invoice_no')?.value || '';

        if (invoiceId) {
            Swal.fire({
                title: 'Cancel Invoice?',
                text: `Are you sure you want to CANCEL Invoice #${series}-${invoiceNo}? This will mark the bill as CANCELLED and make all attached consignment notes available to be billed again.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel Bill',
                cancelButtonText: 'No, Keep It',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    const cancelForm = document.createElement('form');
                    cancelForm.method = 'POST';
                    cancelForm.action = `{{ url('/invoice/cancel') }}/${invoiceId}`;
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = "{{ csrf_token() }}";
                    cancelForm.appendChild(csrf);
                    document.body.appendChild(cancelForm);
                    cancelForm.submit();
                }
            });
        } else {
            window.location.reload();
        }
    };

    // Delete Button Handler
    window.handleDeleteBtn = function() {
        const existingIdInput = document.getElementById('existing_invoice_id');
        const invoiceId = existingIdInput ? existingIdInput.value : '';
        const series = document.getElementById('series')?.value || '{{ $defaultSeries ?? '26-27' }}';
        const invoiceNo = document.getElementById('invoice_no')?.value || '';

        if (invoiceId) {
            Swal.fire({
                title: 'Delete Invoice?',
                text: `Are you sure you want to PERMANENTLY DELETE Invoice #${series}-${invoiceNo}? All attached consignment notes will be released.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'No, Keep It',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    const delForm = document.createElement('form');
                    delForm.method = 'POST';
                    delForm.action = `{{ url('/invoice/destroy') }}/${invoiceId}`;
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = "{{ csrf_token() }}";
                    delForm.appendChild(csrf);
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    delForm.appendChild(method);
                    document.body.appendChild(delForm);
                    delForm.submit();
                }
            });
        } else {
            Swal.fire({
                title: 'Delete Invoice',
                text: 'To manage or delete existing invoices, please navigate to the Invoice Register.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '📑 Go to Invoice Register',
                cancelButtonText: 'Close',
                confirmButtonColor: '#0f3460'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('invoice.register') }}";
                }
            });
        }
    };
</script>
@endsection
