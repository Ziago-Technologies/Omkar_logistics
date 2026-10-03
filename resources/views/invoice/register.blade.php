@extends('layouts.app')

@section('title', 'Invoice Register - Omkaar Logistics')

@section('styles')
<style>
    /* Window Container */
    .party-bill-register-window {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        background: #d4d0c8;
        border: 1px solid #808080;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        font-size: 13px;
        color: #000;
    }

    /* 1. Top Red Header Bar */
    .register-header-red {
        background: #8b0000;
        color: #ffffff;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 0.5px;
        padding: 6px 0;
        border-bottom: 2px solid #5a0000;
        user-select: none;
    }

    /* 2. Controls & Filter Bar (Grey ERP background) */
    .register-filter-panel {
        background: #d4d0c8;
        padding: 10px 14px;
        border-bottom: 1.5px solid #808080;
    }

    .filter-grid-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .filter-grid-row:last-child {
        margin-bottom: 0;
    }

    .ctrl-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .ctrl-item label {
        font-weight: bold;
        font-size: 13px;
        color: #000;
        white-space: nowrap;
        margin: 0;
    }

    .ctrl-item input[type="text"],
    .ctrl-item input[type="date"],
    .ctrl-item select {
        height: 28px;
        border: 1.5px solid #7f9db9;
        background: #ffffff;
        font-size: 13px;
        font-weight: 600;
        padding: 2px 6px;
        color: #000;
        box-sizing: border-box;
    }

    .ctrl-item input[type="text"]:focus,
    .ctrl-item input[type="date"]:focus,
    .ctrl-item select:focus {
        outline: 1.5px solid #0055ff;
    }

    /* Radio button groups */
    .radio-pill-group {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        margin-left: 10px;
    }

    .radio-pill-group label {
        font-weight: bold;
        font-size: 13px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0;
    }

    .radio-pill-group input[type="radio"] {
        margin: 0;
        cursor: pointer;
        width: 15px;
        height: 15px;
    }

    /* Square search button */
    .btn-search-box {
        width: 36px;
        height: 34px;
        background: linear-gradient(to bottom, #ffffff 0%, #e6e6e6 100%);
        border: 1.5px solid #7f9db9;
        border-radius: 3px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 1px 2px rgba(0,0,0,0.1);
        font-size: 17px;
        margin-left: auto;
    }

    .btn-search-box:hover {
        background: linear-gradient(to bottom, #f0f7ff 0%, #d8e8f8 100%);
        border-color: #3b6c8c;
    }

    .btn-search-box:active {
        background: #cce0f5;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.2);
    }

    /* 3. Main Data Table Container */
    .register-grid-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: auto;
        height: calc(100vh - 290px);
        min-height: 380px;
        background: #d8e6f8; /* Light pastel blue fill for empty space */
        border-top: 1.5px solid #808080;
        border-bottom: 1.5px solid #808080;
    }

    .register-data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
        background: #ffffff;
    }

    .register-data-table th {
        background: #e4e2de;
        color: #000;
        font-weight: bold;
        padding: 6px 6px;
        border: 1px solid #808080;
        text-align: center;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 1px rgba(0,0,0,0.08);
        font-size: 12.5px;
    }

    .register-data-table td {
        border: 1px solid #b4b4b4;
        padding: 4px 6px;
        white-space: nowrap;
        color: #000;
        font-size: 12.5px;
        font-weight: 500;
    }

    .register-data-table tr.data-row {
        cursor: pointer;
        background: #ffffff;
    }

    .register-data-table tr.data-row:hover {
        background-color: #f0f7ff !important;
    }

    /* Distinct Yellow Highlight for Draft entries */
    .register-data-table tr.draft-row,
    .register-data-table tr.draft-row:nth-child(even),
    .register-data-table tr.draft-row td {
        background-color: #fef08a !important;
        color: #000000 !important;
    }

    .register-data-table tr.draft-row:hover,
    .register-data-table tr.draft-row:hover td {
        background-color: #fde047 !important;
        color: #000000 !important;
    }

    .register-data-table tr.data-row.active-row td {
        background-color: #dbeafe !important;
        color: #1e3a8a !important;
    }

    .register-data-table tr.draft-row.active-row td {
        background-color: #fde047 !important;
        color: #000000 !important;
        outline: 1.5px solid #d97706;
    }

    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }

    /* Total Pink Sub-total Row */
    .total-summary-row td {
        background: #fedbdb !important;
        font-weight: bold;
        border-top: 1.5px solid #808080;
        border-bottom: 1.5px solid #808080;
        color: #000 !important;
    }

    /* 4. Bottom Footer Bar */
    .register-footer-bar {
        background: #d4d0c8;
        padding: 4px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #808080;
        min-height: 38px;
    }

    .net-amt-display {
        font-size: 12.5px;
        font-weight: bold;
        color: #000;
    }

    .net-amt-val {
        margin-left: 15px;
        font-size: 13.5px;
    }

    .footer-actions-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-print-list {
        background: linear-gradient(to bottom, #ffffff 0%, #e6e6e6 100%);
        border: 1px solid #7f9db9;
        font-size: 11px;
        font-weight: bold;
        color: #000;
        padding: 3px 12px;
        cursor: pointer;
        height: 25px;
        border-radius: 2px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.7), 0 1px 2px rgba(0,0,0,0.1);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-print-list:hover {
        background: #eef5fc;
    }

    .btn-action-icon {
        width: 28px;
        height: 25px;
        border: 1px solid #7f9db9;
        border-radius: 2px;
        background: #ffffff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.7), 0 1px 2px rgba(0,0,0,0.1);
        font-size: 13px;
    }

    .btn-action-icon:hover {
        background: #f0f7ff;
    }

    /* Print styling */
    @media print {
        .register-header-red,
        .register-filter-panel,
        .register-footer-bar,
        .navbar,
        footer {
            display: none !important;
        }
        .register-grid-container {
            height: auto !important;
            overflow: visible !important;
            background: #fff !important;
            border: none !important;
        }
        .register-data-table {
            border: 1px solid #000 !important;
        }
        .register-data-table th,
        .register-data-table td {
            border: 1px solid #000 !important;
            font-size: 9px !important;
            padding: 2px 3px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid" style="padding: 6px 10px;">

    <div class="party-bill-register-window">
        <!-- 1. Top Red Title Bar -->
        <div class="register-header-red">
            INVOICE REGISTER
        </div>

        <!-- 2. Controls & Filters Form -->
        <form action="{{ route('invoice.register') }}" method="GET" id="registerFilterForm">
            <div class="register-filter-panel">
                <!-- Row 1: Party, From, To, Summary/Detail/Due, Search Button -->
                <div class="filter-grid-row">
                    <div class="ctrl-item">
                        <label for="party_input" style="min-width: 42px;">PARTY</label>
                        <input type="text" name="party" id="party_input" list="parties_datalist" value="{{ request('party', request('account_name')) }}" placeholder="SEARCH PARTY..." style="width: 240px;">
                        <datalist id="parties_datalist">
                            @foreach ($parties as $p)
                                <option value="{{ $p }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="ctrl-item" style="margin-left: 10px;">
                        <label for="from_date">FROM</label>
                        <input type="date" name="from_date" id="from_date" value="{{ request('from_date', $fromDate) }}" style="width: 115px;">
                    </div>

                    <div class="ctrl-item">
                        <label for="to_date">TO</label>
                        <input type="date" name="to_date" id="to_date" value="{{ request('to_date', $toDate) }}" style="width: 115px;">
                    </div>

                    <div class="radio-pill-group">
                        <label>
                            <input type="radio" name="report_type" value="summary" {{ request('report_type', 'summary') == 'summary' ? 'checked' : '' }}>
                            SUMMARY
                        </label>
                        <label>
                            <input type="radio" name="report_type" value="detail" {{ request('report_type', 'detail') == 'detail' ? 'checked' : '' }}>
                            DETAIL
                        </label>
                        <label>
                            <input type="radio" name="report_type" value="due" {{ request('report_type', 'due') == 'due' ? 'checked' : '' }}>
                            DUE
                        </label>
                    </div>

                    <button type="submit" class="btn-search-box" title="Fetch Register">
                        🔍
                    </button>
                </div>

                <!-- Row 2: Series, User, Mobile No., Non Cancel/Cancel/All -->
                <div class="filter-grid-row" style="margin-top: 4px;">
                    <div class="ctrl-item">
                        <label for="series_select" style="min-width: 42px;">SERIES</label>
                        <select name="series" id="series_select" style="width: 80px; height: 26px; font-size: 12px;">
                            <option value="">ALL</option>
                            @if(isset($seriesList))
                                @foreach($seriesList as $s)
                                    @if($s->name !== 'A')
                                        <option value="{{ $s->name }}" {{ request('series') == $s->name ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="ctrl-item" style="margin-left: 10px;">
                        <label for="user_select">USER</label>
                        <select name="user_id" id="user_select" style="width: 130px;">
                            <option value="all">ALL USER</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ctrl-item" style="margin-left: 10px;">
                        <input type="checkbox" id="mobile_check" {{ request('mobile') ? 'checked' : '' }} onchange="toggleMobileInput(this.checked)">
                        <label for="mobile_input">MOBILE NO.</label>
                        <input type="text" name="mobile" id="mobile_input" value="{{ request('mobile') }}" placeholder="MOBILE NO." style="width: 140px;" {{ request('mobile') ? '' : 'disabled' }}>
                    </div>

                    <div class="radio-pill-group" style="margin-left: 20px;">
                        <label>
                            <input type="radio" name="cancel_status" value="non_cancel" {{ request('cancel_status', 'non_cancel') == 'non_cancel' ? 'checked' : '' }}>
                            NON CANCEL
                        </label>
                        <label>
                            <input type="radio" name="cancel_status" value="cancel" {{ request('cancel_status', 'cancel') == 'cancel' ? 'checked' : '' }}>
                            CANCEL
                        </label>
                        <label>
                            <input type="radio" name="cancel_status" value="all" {{ request('cancel_status', 'all') == 'all' ? 'checked' : '' }}>
                            ALL
                        </label>
                    </div>
                </div>
            </div>
        </form>

        <!-- 3. Main Table Grid Container -->
        <div class="register-grid-container">
            <table class="register-data-table" id="partyRegisterTable">
                <thead>
                    <tr>
                        <th style="width: 28px;"><input type="checkbox" id="checkAllRows" checked title="Select All / Deselect All"></th>
                        <th style="width: 42px;">SRNO.</th>
                        <th style="width: 42px;">SERIES</th>
                        <th style="width: 65px;">INVOICE NO</th>
                        <th style="width: 90px;">INVOICE DATE</th>
                        <th style="width: 75px;">TIME</th>
                        <th style="min-width: 220px;">DETAILS OF BUYER (BILLED TO)</th>
                        <th style="min-width: 200px;">ADDRESS</th>
                        <th style="width: 85px;">MOBILE</th>
                        <th style="width: 90px;">INVOICE AMT.</th>
                        <th style="width: 50px;">GST%</th>
                        <th style="width: 75px;">CGST</th>
                        <th style="width: 75px;">SGST</th>
                        <th style="width: 75px;">IGST</th>
                        <th style="width: 90px;">NET AMT</th>
                        <th style="width: 85px;">DUE AMT</th>
                        <th style="min-width: 120px;">REMARK</th>
                        <th style="width: 80px;">USER</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $srNo = 1;
                    @endphp
                    @forelse ($invoices as $inv)
                        @php
                            $billAmt = (float)$inv->bill_amount;
                            $gstAmt = (float)$inv->gst_amount;
                            $cgst = (!$inv->is_igst && $gstAmt > 0) ? ($gstAmt / 2) : 0;
                            $sgst = (!$inv->is_igst && $gstAmt > 0) ? ($gstAmt / 2) : 0;
                            $igst = ($inv->is_igst && $gstAmt > 0) ? $gstAmt : 0;
                            $netAmt = (float)$inv->total_amount;
                            $dueAmt = (float)$inv->total_amount;
                            $isDraft = ($inv->status === 'draft');
                        @endphp
                        <tr class="data-row {{ $isDraft ? 'draft-row' : '' }}" onclick="selectRegisterRow(event, this)" ondblclick="window.location.href='{{ route('invoice.edit', $inv->id) }}'" title="Double click to edit Invoice #{{ $inv->series }}-{{ $inv->invoice_no }} {{ $isDraft ? '[DRAFT]' : '' }}">
                            <td class="text-center" onclick="event.stopPropagation();">
                                <input type="checkbox" class="row-checkbox" value="{{ $inv->id }}" checked onchange="toggleRowSelection(this)">
                            </td>
                            <td class="text-center">{{ $srNo++ }}</td>
                            <td class="text-center">{{ $inv->series }}</td>
                            <td class="text-center font-bold">
                                <a href="{{ route('invoice.edit', $inv->id) }}" style="color: #0044cc; text-decoration: underline; font-weight: bold;" title="Edit Invoice {{ $inv->formatted_invoice_no }}">
                                    {{ $inv->formatted_invoice_no }}
                                </a>
                                @if($isDraft)
                                    <span style="background: #fffbeb; color: #92400e; border: 1px solid #d97706; font-size: 10px; padding: 1px 6px; border-radius: 4px; margin-left: 4px; font-weight: 800; display: inline-block; white-space: nowrap;" title="Saved as Draft">Draft</span>
                                @elseif($inv->status === 'cancelled')
                                    <span style="background: #fef2f2; color: #b91c1c; border: 1px solid #ef4444; font-size: 10px; padding: 1px 6px; border-radius: 4px; margin-left: 4px; font-weight: 800; display: inline-block; white-space: nowrap;" title="Cancelled Invoice">Cancelled</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $inv->invoice_date ? $inv->invoice_date->format('d-m-Y') : '' }}</td>
                            <td class="text-center">{{ $inv->created_at ? $inv->created_at->format('h:i A') : '' }}</td>
                            <td class="text-left font-bold" style="max-width: 220px; overflow: hidden; text-overflow: ellipsis;" title="{{ $inv->account_name }}">
                                {{ $inv->account_name }}
                            </td>
                            <td class="text-left" style="max-width: 240px; overflow: hidden; text-overflow: ellipsis;" title="{{ $inv->account ? $inv->account->address : '' }}">
                                {{ $inv->account ? $inv->account->address : '' }}
                            </td>
                            <td class="text-center">
                                {{ $inv->account ? ($inv->account->mobile ?: ($inv->account->phone_o ?: '')) : '' }}
                            </td>
                            <td class="text-right font-bold row-bill-amt" data-val="{{ $billAmt }}">{{ number_format($billAmt, 2) }}</td>
                            <td class="text-right">{{ number_format($inv->gst_percent, 2) }}</td>
                            <td class="text-right row-cgst-amt" data-val="{{ $cgst }}">{{ $cgst > 0 ? number_format($cgst, 2) : '0' }}</td>
                            <td class="text-right row-sgst-amt" data-val="{{ $sgst }}">{{ $sgst > 0 ? number_format($sgst, 2) : '0' }}</td>
                            <td class="text-right row-igst-amt" data-val="{{ $igst }}">{{ $igst > 0 ? number_format($igst, 2) : '' }}</td>
                            <td class="text-right font-bold row-net-amt" data-val="{{ $netAmt }}">{{ number_format($netAmt, 2) }}</td>
                            <td class="text-right row-due-amt" data-val="{{ $dueAmt }}">{{ number_format($dueAmt, 2) }}</td>
                            <td class="text-left" style="max-width: 140px; overflow: hidden; text-overflow: ellipsis;" title="{{ $inv->remark }}">
                                {{ $inv->remark }}
                            </td>
                            <td class="text-center font-bold" style="white-space: nowrap;">
                                {{ $inv->user ? ($inv->user->name ?: $inv->user->username) : 'ADMIN' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="18" style="text-align: center; color: #666; padding: 40px; font-size: 12px; background: #fff;">
                                No bills found matching the selected filters.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Total Row Matching Screenshot -->
                    @if (count($invoices) > 0)
                        <tr class="total-summary-row">
                            <td colspan="9" class="text-center font-bold" style="letter-spacing: 0.5px;">
                                Total :
                            </td>
                            <td class="text-right font-bold" id="total_bill_cell">{{ number_format($totalBillAmt, 2) }}</td>
                            <td class="text-right"></td>
                            <td class="text-right font-bold" id="total_cgst_cell">{{ number_format($totalCgstAmt, 2) }}</td>
                            <td class="text-right font-bold" id="total_sgst_cell">{{ number_format($totalSgstAmt, 2) }}</td>
                            <td class="text-right font-bold" id="total_igst_cell">{{ $totalIgstAmt > 0 ? number_format($totalIgstAmt, 2) : '0.00' }}</td>
                            <td class="text-right font-bold" id="total_net_cell">{{ number_format($totalNetAmt, 2) }}</td>
                            <td class="text-right font-bold" id="total_due_cell">{{ number_format($totalDueAmt, 2) }}</td>
                            <td class="text-left"></td>
                            <td class="text-center"></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- 4. Bottom Footer Bar -->
        <div class="register-footer-bar">
            <div class="net-amt-display">
                Net Amt. : <span class="net-amt-val">{{ number_format($totalNetAmt, 2) }}</span>
            </div>

            <div class="footer-actions-group">
                <button type="button" class="btn-print-list" onclick="window.print();">
                    Print List
                </button>

                <!-- Purple Printer Button -->
                <button type="button" class="btn-action-icon" style="background: #a855f7; color: #fff;" title="Print Register" onclick="window.print();">
                    🖨
                </button>

                <!-- Green Excel Button -->
                <a href="{{ route('invoice.register.export', request()->all()) }}" class="btn-action-icon" style="background: #22c55e; color: #fff;" title="Export to Excel">
                    📗
                </a>

                <!-- Red Clear Button -->
                <a href="{{ route('invoice.register') }}" class="btn-action-icon" style="background: #ef4444; color: #fff;" title="Reset Filters">
                    ❌
                </a>

                <!-- Green Exit Button -->
                <a href="{{ route('invoice.create') }}" class="btn-action-icon" style="background: #10b981; color: #fff;" title="Exit to Create Invoice">
                    ↪
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function toggleMobileInput(isChecked) {
        const mobInput = document.getElementById('mobile_input');
        if (mobInput) {
            mobInput.disabled = !isChecked;
            if (isChecked) {
                mobInput.focus();
            } else {
                mobInput.value = '';
            }
        }
    }

    function selectRegisterRow(event, row) {
        if (event && (event.target.type === 'checkbox' || event.target.tagName === 'A')) {
            return;
        }
        document.querySelectorAll('#partyRegisterTable tr.data-row').forEach(r => r.classList.remove('active-row'));
        row.classList.add('active-row');
    }

    function toggleRowSelection(checkbox) {
        recalculateRegisterTotals();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAllRows');
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = isChecked;
                });
                recalculateRegisterTotals();
            });
        }
    });

    function recalculateRegisterTotals() {
        let totalBill = 0;
        let totalCgst = 0;
        let totalSgst = 0;
        let totalIgst = 0;
        let totalNet = 0;
        let totalDue = 0;

        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        checkedBoxes.forEach(cb => {
            const tr = cb.closest('tr');
            if (tr) {
                totalBill += parseFloat(tr.querySelector('.row-bill-amt')?.dataset.val || 0);
                totalCgst += parseFloat(tr.querySelector('.row-cgst-amt')?.dataset.val || 0);
                totalSgst += parseFloat(tr.querySelector('.row-sgst-amt')?.dataset.val || 0);
                totalIgst += parseFloat(tr.querySelector('.row-igst-amt')?.dataset.val || 0);
                totalNet += parseFloat(tr.querySelector('.row-net-amt')?.dataset.val || 0);
                totalDue += parseFloat(tr.querySelector('.row-due-amt')?.dataset.val || 0);
            }
        });

        const fmt = num => num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const billEl = document.getElementById('total_bill_cell');
        if (billEl) billEl.textContent = fmt(totalBill);

        const cgstEl = document.getElementById('total_cgst_cell');
        if (cgstEl) cgstEl.textContent = fmt(totalCgst);

        const sgstEl = document.getElementById('total_sgst_cell');
        if (sgstEl) sgstEl.textContent = fmt(totalSgst);

        const igstEl = document.getElementById('total_igst_cell');
        if (igstEl) igstEl.textContent = totalIgst > 0 ? fmt(totalIgst) : '0.00';

        const netEl = document.getElementById('total_net_cell');
        if (netEl) netEl.textContent = fmt(totalNet);

        const dueEl = document.getElementById('total_due_cell');
        if (dueEl) dueEl.textContent = fmt(totalDue);

        const footerNetEl = document.querySelector('.net-amt-val');
        if (footerNetEl) footerNetEl.textContent = fmt(totalNet);
    }
</script>
@endsection
