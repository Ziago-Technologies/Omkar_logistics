@extends('layouts.app')

@section('title', 'Account Ledger - Omkaar Logistics')

@section('styles')
<style>
    /* ── Two-panel wrapper ── */
    .ledger-wrapper {
        display: flex;
        height: calc(100vh - 148px);
        gap: 0;
        border: 1.5px solid #94a3b8;
        background: #f1f5f9;
        font-size: 13px;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* ── Left panel: list ── */
    .ledger-list-panel {
        width: 330px;
        min-width: 330px;
        border-right: 2px solid #94a3b8;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .ledger-list-filter {
        padding: 6px;
        border-bottom: 1.5px solid #cbd5e1;
    }

    .ledger-list-filter select {
        width: 100%;
        font-size: 13px;
        font-weight: 600;
        padding: 4px 6px;
        border: 1.5px solid #94a3b8;
        height: 30px;
    }

    .ledger-list-scroll {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .ledger-list-item {
        display: block;
        padding: 6px 10px;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        text-decoration: none;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ledger-list-item:hover {
        background: #c5ddf4;
    }

    .ledger-list-item.active {
        background: #003087;
        color: #fff;
    }

    /* ── Right panel: form ── */
    .ledger-form-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Title bar */
    .ledger-title-bar {
        background: #8b0000;
        color: #fff;
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        padding: 7px 10px;
        letter-spacing: 0.5px;
        flex-shrink: 0;
    }

    /* Info bar (code + datetime) */
    .ledger-info-bar {
        background: #e2e8f0;
        border-bottom: 1.5px solid #cbd5e1;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 13px;
        flex-shrink: 0;
    }

    .ledger-info-bar label {
        font-weight: 700;
        color: #0f172a;
        margin-right: 4px;
        font-size: 13px;
    }

    .ledger-info-bar input {
        border: 1.5px solid #94a3b8;
        padding: 3px 8px;
        font-size: 13px;
        font-weight: 700;
        background: #ffffcc;
        height: 28px;
    }

    .ledger-info-bar .datetime {
        color: #475569;
        font-size: 12px;
        font-weight: 600;
    }

    /* Scrollable form body */
    .ledger-form-body {
        flex: 1;
        overflow-y: auto;
        padding: 10px 16px;
        background: #f8fafc;
    }

    /* Field rows */
    .f-row {
        display: flex;
        align-items: center;
        margin-bottom: 6px;
        gap: 8px;
    }

    .f-row label {
        width: 130px;
        min-width: 130px;
        text-align: right;
        padding-right: 8px;
        font-size: 13px;
        color: #0f172a;
        font-weight: 700;
    }

    .f-row-wrapper {
        display: flex;
        flex-direction: column;
        margin-bottom: 6px;
    }

    .validation-error-msg {
        color: #d32f2f;
        font-size: 12px;
        font-weight: 600;
        margin-left: 138px;
        margin-top: 2px;
        display: none;
    }

    input.input-error, select.input-error, textarea.input-error {
        border-color: #d32f2f !important;
        background-color: #ffebee !important;
    }

    .f-row input[type="text"],
    .f-row input[type="email"],
    .f-row input[type="number"],
    .f-row input[type="date"],
    .f-row select,
    .f-row textarea {
        flex: 1;
        border: 1.5px solid #94a3b8;
        padding: 3px 8px;
        font-size: 13px;
        font-weight: 600;
        color: #000000;
        background: #fff;
        height: 28px;
        outline: none;
        border-radius: 3px;
    }

    .f-row select {
        cursor: pointer;
    }

    .f-row textarea {
        height: 50px;
        resize: vertical;
    }

    .f-row input:focus, .f-row select:focus {
        border-color: #003087;
        background: #fffff0;
        box-shadow: 0 0 0 2px rgba(0, 48, 135, 0.2);
    }

    /* Two-column layout sections */
    .f-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 24px;
    }

    /* Section separator */
    .f-divider {
        border: none;
        border-top: 1.5px solid #cbd5e1;
        margin: 14px 0;
    }

    /* Sub-heading section title */
    .f-section-title {
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(90deg, #003087, #8b0000);
        margin: 18px 0 10px 138px;
        padding: 5px 12px;
        border-radius: 3px;
        letter-spacing: 0.3px;
    }

    /* Inline double fields (Credit Limit + Limit Days) */
    .f-inline {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .f-inline input {
        border: 1.5px solid #94a3b8;
        padding: 3px 8px;
        font-size: 13px;
        font-weight: 600;
        background: #fff;
        height: 28px;
        width: 90px;
    }

    .f-inline label {
        font-size: 13px;
        font-weight: 700;
    }

    /* Radio button rows */
    .f-radio-row {
        display: flex;
        align-items: center;
        gap: 24px;
        padding: 4px 0 4px 138px;
    }

    .f-radio-row label {
        font-size: 13px;
        font-weight: 700;
        width: auto;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    /* Series wise opening table */
    .series-section {
        margin-top: 10px;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        padding: 8px;
        border-radius: 4px;
    }

    .series-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .series-header span {
        font-weight: 700;
        font-size: 13px;
        color: #0f172a;
    }

    .btn-add-row {
        background: #c5ddf4;
        border: 1.5px solid #7da9d4;
        padding: 3px 12px;
        font-size: 12px;
        cursor: pointer;
        font-weight: 700;
        border-radius: 3px;
    }

    .btn-add-row:hover {
        background: #4a90d4;
        color: #fff;
    }

    .series-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .series-table th {
        background: #e2e8f0;
        border: 1px solid #94a3b8;
        padding: 5px 10px;
        text-align: left;
        font-weight: 700;
        color: #0f172a;
    }

    .series-table td {
        border: 1px solid #cbd5e1;
        padding: 3px 6px;
    }

    .series-table td input {
        width: 100%;
        border: none;
        padding: 3px 6px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        background: transparent;
    }

    .series-table td input:focus {
        background: #fffff0;
    }

    /* ── Bottom action bar ── */
    .ledger-action-bar {
        background: #e2e8f0;
        border-top: 2px solid #94a3b8;
        padding: 8px 14px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-shrink: 0;
    }

    .btn-action {
        width: 40px;
        height: 34px;
        border: 1.5px solid #888;
        background: #d4d4d4;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: background 0.1s;
    }

    .btn-action:hover { background: #b8d4f0; }
    .btn-action.btn-save { background: #c5ddf4; border-color: #7da9d4; }
    .btn-action.btn-save:hover { background: #4a90d4; color: #fff; }
    .btn-action.btn-delete { background: #ffc5c5; border-color: #d47a7a; }
    .btn-action.btn-delete:hover { background: #cc0000; color: #fff; }
    .btn-action.btn-new { background: #c5f4c5; border-color: #7ab47a; }
    .btn-action.btn-new:hover { background: #2a9a2a; color: #fff; }

    /* Ledger name highlight style */
    .ledger-name-input {
        background: #ffffcc !important;
        font-weight: 700 !important;
    }
</style>
@endsection

@section('content')
<div class="ledger-wrapper">

    {{-- ── Left panel: list ── --}}
    <div class="ledger-list-panel">
        <div class="ledger-list-filter">
            <select id="groupFilter" onchange="filterList()" style="margin-bottom: 5px;">
                <option value="">---All---</option>
                @foreach($groups as $g)
                    <option value="{{ $g }}">{{ $g }}</option>
                @endforeach
            </select>
            <input type="text" id="searchFilter" onkeyup="filterList()" placeholder="Search Name or GST..." style="width: 100%; font-size: 12px; padding: 3px 6px; border: 1px solid #999; outline: none; box-sizing: border-box;">
        </div>
        <div class="ledger-list-scroll" id="ledgerList">
            @foreach($ledgers as $l)
                <a href="{{ route('account.ledger.load', $l->id) }}"
                   class="ledger-list-item {{ $selected->id == $l->id ? 'active' : '' }}"
                   data-group="{{ $l->under_group }}"
                   data-gst="{{ strtolower($l->gst_no ?? '') }}">
                    {{ $l->ledger_name ?? '(Unnamed)' }}
                </a>
            @endforeach
            @if($ledgers->isEmpty())
                <div style="padding:8px;color:#888;font-size:11px;">No ledgers yet.</div>
            @endif
        </div>
    </div>

    {{-- ── Right panel: form ── --}}
    <div class="ledger-form-panel">

        <div class="ledger-title-bar">Account Ledger</div>

        <div class="ledger-info-bar">
            <div style="position:relative; display:flex; align-items:center; gap:6px;">
                <label id="code_label" style="font-weight:600;">State Code</label>
                @php
                    $initialStateCode = old('state_code', $selected->state_code ?? '');
                @endphp
                <div style="position:relative;">
                    <input type="text" name="state_code" id="top_state_code" form="ledgerForm" value="{{ $initialStateCode }}" placeholder="Code" maxlength="10" autocomplete="off" onfocus="openStateCodeDropdown()" oninput="filterStateCodeDropdown(this.value)" onblur="handleStateCodeBlur(this.value)" style="background:#ffffcc; font-weight:bold; color:#003087; border:1px solid #aaa; padding:2px 6px; font-size:12px; height:22px; width:70px; text-transform:uppercase; text-align:center; cursor:pointer;">
                    <div id="state_code_dropdown" style="display:none; position:absolute; top:25px; left:0; width:220px; max-height:240px; overflow-y:auto; background:#ffffff; border:1px solid #0f3460; box-shadow:0 4px 12px rgba(0,0,0,0.18); z-index:9999; border-radius:2px; font-size:11px; text-align:left;">
                        @if(isset($states) && count($states) > 0)
                            @foreach($states as $s)
                                @php $pCode = str_pad($s->code, 2, '0', STR_PAD_LEFT); @endphp
                                <div class="state-code-item" data-code="{{ $pCode }}" data-name="{{ $s->name }}" onmousedown="selectStateCode('{{ $pCode }}')" style="padding:4px 8px; cursor:pointer; border-bottom:1px solid #f0f0f0; color:#333;">
                                    <strong>({{ $pCode }})</strong> - {{ $s->name }}
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="datetime">{{ now()->format('d/m/Y h:i A') }}</div>
            <div style="margin-left:auto;">
                @if(session('success'))
                    <span style="color:green;font-weight:600;">✔ {{ session('success') }}</span>
                @endif
            </div>
        </div>

        {{-- Form --}}
        @if($selected->id)
            <form method="POST" action="{{ route('account.ledger.update', $selected->id) }}" id="ledgerForm" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
                @csrf @method('PUT')
                <div class="ledger-form-body">
        @else
            <form method="POST" action="{{ route('account.ledger.store') }}" id="ledgerForm" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
                @csrf
                <div class="ledger-form-body">
        @endif

            {{-- SINGLE COLUMN FORM --}}
                <div class="f-row">
                    <label>Under Group</label>
                    <select name="under_group" id="under_group" onchange="toggleFormFields()">
                        <option value="">-- Select Group --</option>
                        @foreach($groups as $g)
                            <option value="{{ $g }}" {{ old('under_group', $selected->under_group) == $g ? 'selected' : '' }}>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="f-row">
                    <label>Ledger Name</label>
                    <input type="text" name="ledger_name" id="ledger_name" class="form-field ledger-name-input" required value="{{ old('ledger_name', $selected->ledger_name) }}">
                </div>
                    <input type="hidden" name="code" value="{{ $selected->code ?? $nextCode }}">
                {{-- STANDARD FIELDS (shown for non-bank accounts) --}}
                <div id="standard_fields_section">
                    <div class="f-row" style="position: relative;">
                        <label>GST No.</label>
                        <input type="text" name="gst_no" id="gst_no" class="form-field" value="{{ old('gst_no', $selected->gst_no) }}" autocomplete="off">
                        <span id="gst_status" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 11px; font-weight: 600; color: #555; pointer-events: none;"></span>
                    </div>
                    <hr class="f-divider">
                    <div class="f-row">
                        <label>Contact Person</label>
                        <input type="text" name="contact_person" id="contact_person" class="form-field" value="{{ old('contact_person', $selected->contact_person) }}">
                    </div>
                    <div class="f-row">
                        <label>Address</label>
                        <textarea name="address" id="address" class="form-field">{{ old('address', $selected->address) }}</textarea>
                    </div>
                    <div class="f-row">
                        <label>Country</label>
                        <select name="country_id" id="country_id" class="form-field" onchange="filterStatesByCountry()">
                            <option value="">-- Select Country --</option>
                            @if(isset($countries) && count($countries) > 0)
                                @foreach($countries as $c)
                                    <option value="{{ $c->id }}" {{ (old('country_id', $selected->country_id) == $c->id || (empty(old('country_id', $selected->country_id)) && $c->name === 'INDIA')) ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            @else
                                <option value="1" selected>INDIA</option>
                            @endif
                        </select>
                    </div>
                    <div class="f-row">
                        <label>State</label>
                        <select name="state_id" id="state_id" class="form-field" onchange="filterCitiesByState()">
                            <option value="">-- Select State --</option>
                            @if(isset($states) && count($states) > 0)
                                @foreach($states as $s)
                                    @php
                                        $paddedCode = str_pad($s->code, 2, '0', STR_PAD_LEFT);
                                    @endphp
                                    <option value="{{ $s->id }}" data-country-id="{{ $s->country_id }}" data-code="{{ $paddedCode }}" {{ old('state_id', $selected->state_id) == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="f-row">
                        <label>City</label>
                        <select name="city_id" id="city_id" class="form-field" onchange="syncStateFromCity()">
                            <option value="">-- Select City --</option>
                            @if(isset($cities) && count($cities) > 0)
                                @foreach($cities as $ct)
                                    <option value="{{ $ct->id }}" data-state-id="{{ $ct->state_id }}" data-country-id="{{ $ct->stateRelation->country_id ?? '' }}" {{ old('city_id', $selected->city_id) == $ct->id ? 'selected' : '' }}>
                                        {{ $ct->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="f-row">
                        <label>Pin Code</label>
                        <input type="text" name="pin_code" id="pin_code" class="form-field" value="{{ old('pin_code', $selected->pin_code) }}" style="width:90px;flex:none;">
                    </div>
                    <div class="f-row">
                        <label>Phone (O)</label>
                        <input type="text" name="phone_o" id="phone_o" class="form-field" value="{{ old('phone_o', $selected->phone_o) }}">
                    </div>
                    <div class="f-row">
                        <label>Phone (R)</label>
                        <input type="text" name="phone_r" id="phone_r" class="form-field" value="{{ old('phone_r', $selected->phone_r) }}">
                    </div>
                    <div class="f-row">
                        <label>Mobile</label>
                        <input type="text" name="mobile" id="mobile" class="form-field" value="{{ old('mobile', $selected->mobile) }}">
                    </div>
                    <div class="f-row">
                        <label>Email ID</label>
                        <input type="email" name="email" id="email" class="form-field" value="{{ old('email', $selected->email) }}">
                    </div>
                </div>

                {{-- BANK DETAILS FIELDS (shown only when 'Bank Accounts' is selected) --}}
                <div id="bank_fields_section" style="display: none;">
                    <div class="f-section-title">Bank Details</div>
                    <div class="f-row">
                        <label>Ledger Bank Name</label>
                        <input type="text" name="bank_name" id="bank_name" class="form-field bank-input" value="{{ old('bank_name', $selected->bank_name) }}">
                    </div>
                    <div class="f-row">
                        <label>Account type</label>
                        <select name="di_no" id="di_no" class="form-field bank-input">
                            <option value="Savings" {{ old('di_no', $selected->di_no) == 'Savings' ? 'selected' : '' }}>Savings</option>
                            <option value="Current" {{ old('di_no', $selected->di_no) == 'Current' ? 'selected' : '' }}>Current</option>
                            <option value="Cash Credit" {{ old('di_no', $selected->di_no) == 'Cash Credit' ? 'selected' : '' }}>Cash Credit</option>
                        </select>
                    </div>
                    <div class="f-row-wrapper">
                        <div class="f-row">
                            <label>Acc No</label>
                            <input type="text" name="account_no" id="account_no" class="form-field bank-input" value="{{ old('account_no', $selected->account_no) }}">
                        </div>
                        <div class="validation-error-msg" id="err_account_no">Account number must be 18 digits.</div>
                    </div>
                    <div class="f-row-wrapper">
                        <div class="f-row">
                            <label>IFSC code</label>
                            <input type="text" name="ifsc" id="ifsc" class="form-field bank-input" value="{{ old('ifsc', $selected->ifsc) }}">
                        </div>
                        <div class="validation-error-msg" id="err_ifsc">IFSC must be 11 characters (4 letters, '0', then 6 alphanumeric characters).</div>
                    </div>
                    <div class="f-row">
                        <label>Branch / division</label>
                        <input type="text" name="transport" id="transport" class="form-field bank-input" value="{{ old('transport', $selected->transport) }}">
                    </div>
                    <div class="f-row">
                        <label>Bank address</label>
                        <textarea name="address_bank" id="address_bank" class="form-field bank-input">{{ old('address', $selected->address) }}</textarea>
                    </div>
                    <div class="f-row">
                        <label>Bank Email ID</label>
                        <input type="email" name="email_bank" id="email_bank" class="form-field bank-input" value="{{ old('email', $selected->email) }}">
                    </div>
                </div>

                {{-- STAFF SALARY FIELDS (shown only when 'Staff Salary' is selected) --}}
                <div id="staff_fields_section" style="display: none;">
                    <div class="f-section-title">Staff Details</div>
                    <div class="f-row">
                        <label>Staff Name</label>
                        <input type="text" id="staff_name_display" class="form-field staff-input" readonly value="{{ old('ledger_name', $selected->ledger_name) }}" style="background:#e8f4fd; font-weight:bold; color:#003087;">
                    </div>
                    
                    <div class="f-section-title">Personal Documents</div>
                    
                    <div class="f-row-wrapper">
                        <div class="f-row">
                            <label>Voter Card No.</label>
                            <input type="text" name="voter_card" id="voter_card" class="form-field staff-input" placeholder="Enter Voter ID Card Number" value="{{ old('voter_card', $selected->fax) }}">
                            <input type="file" name="doc_voter" id="doc_voter" class="form-field staff-input" style="width: auto; flex: none;">
                        </div>
                        <div class="validation-error-msg" id="err_voter_card">Voter ID must be 10 characters (3 letters + 7 digits).</div>
                    </div>
                    
                    <div class="f-row-wrapper">
                        <div class="f-row">
                            <label>Aadhaar Card No.</label>
                            <input type="text" name="adhar_card" id="adhar_card" class="form-field staff-input" placeholder="Enter Aadhaar Number" value="{{ old('adhar_card', $selected->salesman) }}">
                            <input type="file" name="doc_adhar" id="doc_adhar" class="form-field staff-input" style="width: auto; flex: none;">
                        </div>
                        <div class="validation-error-msg" id="err_adhar_card">Aadhaar must be 12 digits.</div>
                    </div>
                    
                    <div class="f-row-wrapper">
                        <div class="f-row">
                            <label>PAN Card No.</label>
                            <input type="text" name="pan_card" id="pan_card" class="form-field staff-input" placeholder="Enter PAN Number" value="{{ old('pan_card', $selected->web) }}">
                            <input type="file" name="doc_pan" id="doc_pan" class="form-field staff-input" style="width: auto; flex: none;">
                        </div>
                        <div class="validation-error-msg" id="err_pan_card">PAN Card must be 10 characters (5 letters + 4 digits + 1 letter).</div>
                    </div>
                    
                    <div class="f-row-wrapper">
                        <div class="f-row">
                            <label>Driving License No.</label>
                            <input type="text" name="driving_license" id="driving_license" class="form-field staff-input" placeholder="Enter Driving License Number" value="{{ old('driving_license', $selected->di_no) }}">
                            <input type="file" name="doc_dl" id="doc_dl" class="form-field staff-input" style="width: auto; flex: none;">
                        </div>
                        <div class="validation-error-msg" id="err_driving_license">Driving License must be between 13 and 16 characters.</div>
                    </div>

                    <div class="f-section-title">Salary Bank Details</div>

                    <div class="f-row">
                        <label>Bank Name</label>
                        <input type="text" name="staff_bank_name" id="staff_bank_name" class="form-field staff-input" placeholder="Bank Name" value="{{ old('staff_bank_name', $selected->bank_name) }}">
                    </div>
                    <div class="f-row-wrapper">
                        <div class="f-row">
                            <label>Account No.</label>
                            <input type="text" name="staff_account_no" id="staff_account_no" class="form-field staff-input" placeholder="Account Number" value="{{ old('staff_account_no', $selected->account_no) }}">
                        </div>
                        <div class="validation-error-msg" id="err_staff_account_no">Account number must be 18 digits.</div>
                    </div>
                    <div class="f-row-wrapper">
                        <div class="f-row">
                            <label>IFSC Code</label>
                            <input type="text" name="staff_ifsc" id="staff_ifsc" class="form-field staff-input" placeholder="IFSC Code" value="{{ old('staff_ifsc', $selected->ifsc) }}">
                        </div>
                        <div class="validation-error-msg" id="err_staff_ifsc">IFSC must be 11 characters (4 letters, '0', then 6 alphanumeric characters).</div>
                    </div>
                    <div class="f-row">
                        <label style="line-height: 1.2;">Passbook 1st Page / Canceled Check</label>
                        <input type="file" name="doc_passbook" id="doc_passbook" class="form-field staff-input" style="width: auto; flex: none;">
                    </div>

                    <div class="f-section-title">PF / ESI Details</div>

                    <div class="f-row">
                        <label>ESI Number</label>
                        <input type="text" name="esi_number" id="esi_number" class="form-field staff-input" placeholder="Enter ESI Number" value="{{ old('esi_number', $selected->phone_o) }}">
                    </div>
                    <div class="f-row">
                        <label>PF Number</label>
                        <input type="text" name="pf_number" id="pf_number" class="form-field staff-input" placeholder="Enter PF Number" value="{{ old('pf_number', $selected->phone_r) }}">
                    </div>

                    <div class="f-section-title">Salary Information</div>

                    <div class="f-row">
                        <label>Gross Salary</label>
                        <input type="number" name="points" id="gross_salary" class="form-field staff-input" placeholder="0" step="0.01" oninput="calculateNetSalary()" value="{{ old('points', $selected->points) }}">
                    </div>
                    <div class="f-row">
                        <label>Deduction</label>
                        <input type="number" name="discnt" id="deduction" class="form-field staff-input" placeholder="0" step="0.01" oninput="calculateNetSalary()" value="{{ old('discnt', $selected->discnt) }}">
                    </div>
                    <div class="f-row">
                        <label>Net Salary</label>
                        <input type="number" name="opening" id="net_salary" class="form-field staff-input" placeholder="0" step="0.01" readonly style="background:#e8f4fd; font-weight:bold; color:#003087;" value="{{ old('opening', $selected->opening) }}">
                    </div>
                </div>

                {{-- VEHICLE EXPENSE FIELDS (shown only when 'Vehicle Expense' is selected) --}}
                <div id="vehicle_fields_section" style="display: none;">
                    <div class="f-section-title">Vehicle Expense Details</div>
                    <div class="f-row">
                        <label>Vehicle No.</label>
                        <input type="text" name="vehicle_gst" id="vehicle_gst" class="form-field vehicle-input" placeholder="Enter Vehicle Registration Number" value="{{ old('gst_no', $selected->gst_no) }}">
                    </div>
                    <div class="f-row">
                        <label>Driver Name</label>
                        <input type="text" name="driver_name" id="driver_name" class="form-field vehicle-input" placeholder="Driver Name" value="{{ old('contact_person', $selected->contact_person) }}">
                    </div>
                    <div class="f-row">
                        <label>Driver Phone Number</label>
                        <input type="text" name="driver_phone" id="driver_phone" class="form-field vehicle-input" placeholder="Driver Phone Number" value="{{ old('mobile', $selected->mobile) }}">
                    </div>
                </div>

            {{-- Validation errors --}}
            @if($errors->any())
                <div style="color:red;font-size:11px;margin-top:8px;padding:4px 8px;background:#ffebeb;border:1px solid #f99;">
                    {{ $errors->first() }}
                </div>
            @endif

        </div>{{-- /ledger-form-body --}}

        {{-- ── Action Bar ── --}}
        <div class="ledger-action-bar">
            <a href="{{ route('account.ledger') }}" class="btn-action btn-new" title="New">➕</a>
            <button type="button" class="btn-action" title="Print" onclick="window.print()">🖨</button>
            <button type="button" class="btn-action btn-save" title="Clear Form" onclick="clearAllFields()" style="background:#ffe0b2; border-color:#ffb74d;">🧹</button>
            <button type="submit" class="btn-action btn-save" title="Save">💾</button>
            @if($selected->id)
                <button type="button" class="btn-action btn-delete" title="Delete"
                    onclick="SysDialog.delete('Delete this ledger?').then(yes => { if(yes) document.getElementById('deleteForm').submit(); })">❌</button>
            @else
                <button type="button" class="btn-action btn-delete" title="Delete" disabled style="opacity:0.4;">❌</button>
            @endif
            <a href="{{ route('dashboard') }}" class="btn-action" title="Back" style="font-size:14px;">↩</a>
        </div>

        </form>

        {{-- Hidden delete form --}}
        @if($selected->id)
        <form id="deleteForm" method="POST" action="{{ route('account.ledger.destroy', $selected->id) }}" style="display:none;">
            @csrf @method('DELETE')
        </form>
        @endif

    </div>{{-- /ledger-form-panel --}}
</div>{{-- /ledger-wrapper --}}
@endsection

@section('scripts')
<script>
    function filterList() {
        const group = (document.getElementById('groupFilter').value || '').trim().toUpperCase();
        const query = document.getElementById('searchFilter').value.toLowerCase().trim();

        document.querySelectorAll('.ledger-list-item').forEach(function(item) {
            const itemGroup = (item.dataset.group || '').trim().toUpperCase();
            const matchesGroup = !group || itemGroup === group;
            const textContent = item.textContent.toLowerCase();
            const gstContent = (item.dataset.gst || '').toLowerCase();
            const matchesQuery = !query || textContent.includes(query) || gstContent.includes(query);

            if (matchesGroup && matchesQuery) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function addSeriesRow() {
        const tbody = document.getElementById('seriesBody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" name="series_name[]" placeholder="26-27"></td>
            <td><input type="number" name="series_opening[]" step="0.01" placeholder="0"></td>
            <td><input type="number" name="series_closing[]" step="0.01" placeholder="0"></td>
        `;
        tbody.appendChild(row);
    }
    // GST auto-pull lookup
    document.getElementById('gst_no').addEventListener('input', function() {
        const gstVal = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        this.value = gstVal;

        const statusEl = document.getElementById('gst_status');

        function selectStateByCode(code, targetCityName = null) {
            const countrySelect = document.getElementById('country_id');
            const stateSelect = document.getElementById('state_id');
            const citySelect = document.getElementById('city_id');
            const topInput = document.getElementById('top_state_code');
            const targetCode = String(code).padStart(2, '0');

            if (topInput) {
                topInput.value = targetCode;
            }

            if (countrySelect) {
                for (let i = 0; i < countrySelect.options.length; i++) {
                    if (countrySelect.options[i].text.toUpperCase() === 'INDIA') {
                        countrySelect.selectedIndex = i;
                        break;
                    }
                }
            }
            filterStatesByCountry();

            if (stateSelect) {
                for (let i = 0; i < stateSelect.options.length; i++) {
                    const opt = stateSelect.options[i];
                    if (opt.getAttribute('data-code') === targetCode) {
                        stateSelect.selectedIndex = i;
                        filterCitiesByState();
                        break;
                    }
                }
            }

            if (targetCityName && citySelect) {
                for (let i = 0; i < citySelect.options.length; i++) {
                    const opt = citySelect.options[i];
                    if (opt.text.trim().toUpperCase() === targetCityName.trim().toUpperCase()) {
                        citySelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }

        if (gstVal.length === 15) {
            statusEl.innerHTML = '<span style="color:#f57c00;">⏳ Verifying GSTIN...</span>';

            fetch(`{{ url('/api/gst/lookup') }}/${encodeURIComponent(gstVal)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    const stateCode = data.state_code || gstVal.substring(0, 2);
                    
                    if (data.legal_name) {
                        const ledgerNameEl = document.getElementById('ledger_name');
                        if (ledgerNameEl && (!ledgerNameEl.value || ledgerNameEl.value.startsWith('AUTO PULL'))) {
                            ledgerNameEl.value = data.legal_name;
                        }
                    }
                    if (data.trade_name) {
                        const contactEl = document.getElementById('contact_person');
                        if (contactEl && !contactEl.value) {
                            contactEl.value = data.trade_name;
                        }
                    }
                    if (data.address) {
                        const addrEl = document.getElementById('address');
                        if (addrEl && (!addrEl.value || addrEl.value.startsWith('Principal Place'))) {
                            addrEl.value = data.address;
                        }
                    }
                    if (data.pincode) {
                        const pinEl = document.getElementById('pin_code');
                        if (pinEl) pinEl.value = data.pincode;
                    }

                    selectStateByCode(stateCode, data.city);

                    if (data.is_verified) {
                        const statusBadge = (data.status || 'Active').toUpperCase();
                        const isLive = data.source === 'live_api';
                        statusEl.innerHTML = `<span style="color:#2e7d32; font-weight:700;">✔ ${statusBadge} ${isLive ? '(Live)' : ''}</span>`;
                    } else {
                        statusEl.innerHTML = `<span style="color:#003087; font-weight:600;">✔ ${data.state_name || ('State ' + stateCode)}</span>`;
                    }
                } else {
                    selectStateByCode(gstVal.substring(0, 2));
                    statusEl.innerHTML = '<span style="color:#c62828;">⚠️ ' + (data.message || 'Invalid GSTIN') + '</span>';
                }
            })
            .catch(err => {
                console.error('GST lookup error:', err);
                const stateCode = gstVal.substring(0, 2);
                selectStateByCode(stateCode);
                statusEl.innerHTML = `<span style="color:#003087;">✔ State ${stateCode}</span>`;
            });
        } else {
            statusEl.textContent = '';
        }
    });

    // Toggle disabled status and visible sections based on Under Group selection
    function toggleFormFields() {
        const selectEl = document.getElementById('under_group');
        const standardSection = document.getElementById('standard_fields_section');
        const bankSection = document.getElementById('bank_fields_section');
        const staffSection = document.getElementById('staff_fields_section');
        const vehicleSection = document.getElementById('vehicle_fields_section');
        const rawGroup = selectEl ? selectEl.value : '';
        const selectedGroup = rawGroup.trim().toUpperCase();

        updateCodeFormat();

        const isGroupEmpty = !selectedGroup;
        const isBankAccount = (selectedGroup === 'BANK ACCOUNTS' || selectedGroup === 'BANK ACCOUNT' || selectedGroup.includes('BANK'));
        const isStaffSalary = (selectedGroup === 'STAFF SALARY' || selectedGroup.includes('STAFF') || selectedGroup.includes('SALARY'));
        const isVehicleOrOilExpense = (selectedGroup === 'VEHICLE EXPENSE' || selectedGroup === 'OIL EXPENSE' || selectedGroup.includes('VEHICLE') || selectedGroup.includes('OIL'));

        // Hide all initially
        standardSection.style.display = 'none';
        bankSection.style.display = 'none';
        staffSection.style.display = 'none';
        vehicleSection.style.display = 'none';

        // Disable all inputs in standard, bank, staff, and vehicle sections first
        document.querySelectorAll('#standard_fields_section .form-field').forEach(field => field.disabled = true);
        document.querySelectorAll('#bank_fields_section .form-field').forEach(field => field.disabled = true);
        document.querySelectorAll('#staff_fields_section .form-field').forEach(field => field.disabled = true);
        document.querySelectorAll('#vehicle_fields_section .form-field').forEach(field => field.disabled = true);

        if (isBankAccount) {
            bankSection.style.display = 'block';
            document.querySelectorAll('#bank_fields_section .form-field').forEach(field => {
                field.disabled = false;
                field.style.backgroundColor = '#ffffff';
                field.style.opacity = '1';
            });
        } else if (isStaffSalary) {
            staffSection.style.display = 'block';
            document.querySelectorAll('#staff_fields_section .form-field').forEach(field => {
                field.disabled = false;
                field.style.backgroundColor = '#ffffff';
                field.style.opacity = '1';
            });
        } else if (isVehicleOrOilExpense) {
            vehicleSection.style.display = 'block';
            document.querySelectorAll('#vehicle_fields_section .form-field').forEach(field => {
                field.disabled = false;
                field.style.backgroundColor = '#ffffff';
                field.style.opacity = '1';
            });
        } else {
            standardSection.style.display = 'block';
            document.querySelectorAll('#standard_fields_section .form-field').forEach(field => {
                field.disabled = isGroupEmpty;
                if (isGroupEmpty) {
                    field.style.backgroundColor = '#eaeaea';
                    field.style.opacity = '0.7';
                } else {
                    field.style.backgroundColor = '#ffffff';
                    field.style.opacity = '1';
                }
            });
        }
    }

    // Clear all input elements
    function clearAllFields() {
        const fields = document.querySelectorAll('.form-field');
        fields.forEach(field => {
            if (field.tagName === 'SELECT') {
                field.selectedIndex = 0;
            } else {
                field.value = '';
            }
        });
        const topSelect = document.getElementById('top_state_code');
        if (topSelect) topSelect.selectedIndex = 0;
        document.getElementById('gst_status').textContent = '';
        toggleFormFields();
        filterStatesByCountry();
    }

    // Calculate net salary dynamically
    function calculateNetSalary() {
        const gross = parseFloat(document.getElementById('gross_salary').value) || 0;
        const deduction = parseFloat(document.getElementById('deduction').value) || 0;
        document.getElementById('net_salary').value = (gross - deduction).toFixed(2);
    }

    // Automatically update the Code label and formatted value depending on Under Group selection
    function updateCodeFormat() {
        const selectEl = document.getElementById('under_group');
        const codeLabel = document.getElementById('code_label');
        const codeInput = document.getElementById('top_state_code');
        const rawCode = "{{ $selected->code ?? $nextCode }}";
        const selectedGroup = (selectEl && selectEl.value ? selectEl.value : '').trim().toUpperCase();

        if (selectedGroup === 'STAFF SALARY' || selectedGroup.includes('STAFF') || selectedGroup.includes('SALARY')) {
            if (codeLabel) codeLabel.textContent = 'Emp Code';
            const formattedNum = String(rawCode).padStart(2, '0');
            if (codeInput) codeInput.value = 'OML' + formattedNum;
        } else {
            if (codeLabel) codeLabel.textContent = 'State Code';
            if (codeInput && !codeInput.value.startsWith('OML')) {
                // keep current state code or restore
            } else if (codeInput) {
                codeInput.value = "{{ old('state_code', $selected->state_code ?? '') }}";
            }
        }
    }

    // Real-time client side validations for required formats
    function setupRealTimeValidations() {
        const rules = [
            {
                id: 'voter_card',
                errorId: 'err_voter_card',
                validate: (val) => val === '' || /^[A-Z]{3}\d{7}$/.test(val)
            },
            {
                id: 'adhar_card',
                errorId: 'err_adhar_card',
                validate: (val) => val === '' || /^\d{12}$/.test(val)
            },
            {
                id: 'pan_card',
                errorId: 'err_pan_card',
                validate: (val) => val === '' || /^[A-Z]{5}\d{4}[A-Z]{1}$/.test(val)
            },
            {
                id: 'driving_license',
                errorId: 'err_driving_license',
                validate: (val) => val === '' || (val.length >= 13 && val.length <= 16)
            },
            {
                id: 'account_no',
                errorId: 'err_account_no',
                validate: (val) => val === '' || /^\d{9,18}$/.test(val)
            },
            {
                id: 'staff_account_no',
                errorId: 'err_staff_account_no',
                validate: (val) => val === '' || /^\d{9,18}$/.test(val)
            },
            {
                id: 'ifsc',
                errorId: 'err_ifsc',
                validate: (val) => val === '' || /^[A-Z]{4}0[A-Z0-9]{6}$/.test(val)
            },
            {
                id: 'staff_ifsc',
                errorId: 'err_staff_ifsc',
                validate: (val) => val === '' || /^[A-Z]{4}0[A-Z0-9]{6}$/.test(val)
            },
            {
                id: 'gst_no',
                errorId: null,
                validate: (val) => val === '' || /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/.test(val),
                msg: 'GST format: 15 chars (e.g. 18AABCT1234F1Z5)'
            },
            {
                id: 'pin_code',
                errorId: null,
                validate: (val) => val === '' || /^[1-9][0-9]{5}$/.test(val),
                msg: 'PIN must be 6 digits'
            }
        ];

        rules.forEach(rule => {
            const input = document.getElementById(rule.id);
            const errMsg = rule.errorId ? document.getElementById(rule.errorId) : null;
            if (!input) return;

            const check = () => {
                const val = input.value.trim().toUpperCase();
                if (rule.id !== 'pin_code') {
                    input.value = val;
                }
                const isValid = rule.validate(val);
                if (!isValid) {
                    input.classList.add('input-error');
                    if (errMsg) errMsg.style.display = 'block';
                    if (rule.msg) input.title = rule.msg;
                } else {
                    input.classList.remove('input-error');
                    if (errMsg) errMsg.style.display = 'none';
                    input.removeAttribute('title');
                }
            };

            input.addEventListener('input', check);
            input.addEventListener('blur', check);
        });

        // Block submit if validations are failing
        const form = document.getElementById('ledgerForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                let hasErrors = false;
                rules.forEach(rule => {
                    const input = document.getElementById(rule.id);
                    if (input && input.classList.contains('input-error') && !input.disabled) {
                        hasErrors = true;
                    }
                });
                if (hasErrors) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation Errors',
                            text: 'Please correct the validation errors (marked in red) before saving.',
                            confirmButtonColor: '#0f3460'
                        });
                    } else if (typeof SysDialog !== 'undefined' && SysDialog.alert) {
                        SysDialog.alert('Please correct the validation errors (marked in red) before saving.', 'Validation Errors');
                    } else {
                        alert('Please correct the validation errors (marked in red) before saving.');
                    }
                }
            });
        }
    }

    function openStateCodeDropdown() {
        const dd = document.getElementById('state_code_dropdown');
        if (dd) {
            dd.style.display = 'block';
            const items = dd.querySelectorAll('.state-code-item');
            items.forEach(it => it.style.display = '');
        }
    }

    function filterStateCodeDropdown(query) {
        const dd = document.getElementById('state_code_dropdown');
        if (!dd) return;
        dd.style.display = 'block';
        const q = (query || '').trim().toUpperCase();
        const items = dd.querySelectorAll('.state-code-item');
        items.forEach(it => {
            const code = (it.getAttribute('data-code') || '').toUpperCase();
            const name = (it.getAttribute('data-name') || '').toUpperCase();
            if (!q || code.includes(q) || name.includes(q)) {
                it.style.display = '';
            } else {
                it.style.display = 'none';
            }
        });
    }

    function selectStateCode(code) {
        const topInput = document.getElementById('top_state_code');
        if (topInput) {
            topInput.value = code;
        }
        const dd = document.getElementById('state_code_dropdown');
        if (dd) {
            dd.style.display = 'none';
        }
    }

    function handleStateCodeBlur(val) {
        setTimeout(() => {
            const dd = document.getElementById('state_code_dropdown');
            if (dd) dd.style.display = 'none';

            const cleanVal = (val || '').trim().toUpperCase();
            if (!cleanVal) return;

            const stateSelect = document.getElementById('state_id');
            let match = false;
            if (stateSelect) {
                const paddedVal = cleanVal.padStart(2, '0');
                for (let i = 0; i < stateSelect.options.length; i++) {
                    const opt = stateSelect.options[i];
                    if (!opt.value) continue;
                    const code = opt.getAttribute('data-code');
                    if (code === paddedVal || code === cleanVal || opt.text.trim().toUpperCase() === cleanVal) {
                        match = true;
                        document.getElementById('top_state_code').value = code;
                        break;
                    }
                }
            }

            if (!match) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'State Not Found',
                        text: `No state found with code "${cleanVal}". Would you like to create this state now?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0f3460',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, Create State',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('master.state') }}?code=" + encodeURIComponent(cleanVal);
                        } else {
                            document.getElementById('top_state_code').value = '';
                        }
                    });
                } else {
                    SysDialog.confirm(`No state found with code "${cleanVal}". Would you like to create this state now?`, 'State Not Found').then(confirmed => {
                        if (confirmed) {
                            window.location.href = "{{ route('master.state') }}?code=" + encodeURIComponent(cleanVal);
                        } else {
                            document.getElementById('top_state_code').value = '';
                        }
                    });
                }
            }
        }, 200);
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        const topInput = document.getElementById('top_state_code');
        const dd = document.getElementById('state_code_dropdown');
        if (dd && topInput && !topInput.contains(e.target) && !dd.contains(e.target)) {
            dd.style.display = 'none';
        }
    });

    function filterStatesByCountry(targetStateId = null) {
        const countrySelect = document.getElementById('country_id');
        const stateSelect = document.getElementById('state_id');
        if (!countrySelect || !stateSelect) return;

        const countryId = countrySelect.value;
        const currentStateId = targetStateId || stateSelect.value;

        let isCurrentStateVisible = false;

        for (let i = 0; i < stateSelect.options.length; i++) {
            const opt = stateSelect.options[i];
            if (!opt.value) continue; // Skip header option

            const optCountryId = opt.getAttribute('data-country-id');
            if (!countryId || !optCountryId || optCountryId === String(countryId)) {
                opt.style.display = '';
                opt.disabled = false;
                if (currentStateId && opt.value === String(currentStateId)) {
                    isCurrentStateVisible = true;
                    stateSelect.selectedIndex = i;
                }
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        }

        if (!isCurrentStateVisible && countryId) {
            stateSelect.value = '';
        }

        filterCitiesByState();
    }

    function filterCitiesByState(targetCityId = null) {
        const countrySelect = document.getElementById('country_id');
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        if (!citySelect) return;

        const countryId = countrySelect ? countrySelect.value : null;
        const stateId = stateSelect ? stateSelect.value : null;
        const currentCityId = targetCityId || citySelect.value;

        let isCurrentCityVisible = false;

        for (let i = 0; i < citySelect.options.length; i++) {
            const opt = citySelect.options[i];
            if (!opt.value) continue; // Skip header "-- Select City --"

            const optStateId = opt.getAttribute('data-state-id');
            const optCountryId = opt.getAttribute('data-country-id');

            let isAllowed = false;
            if (stateId) {
                isAllowed = (optStateId === String(stateId));
            } else if (countryId) {
                isAllowed = (optCountryId === String(countryId) || !optCountryId);
            } else {
                isAllowed = true;
            }

            if (isAllowed) {
                opt.style.display = '';
                opt.disabled = false;
                if (currentCityId && opt.value === String(currentCityId)) {
                    isCurrentCityVisible = true;
                    citySelect.selectedIndex = i;
                }
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        }

        if (!isCurrentCityVisible && (stateId || countryId)) {
            citySelect.value = '';
        }
    }

    // Auto-sync parent State when a City is selected directly
    function syncStateFromCity() {
        const citySelect = document.getElementById('city_id');
        const stateSelect = document.getElementById('state_id');
        if (!citySelect || !stateSelect) return;

        const selectedCityOpt = citySelect.options[citySelect.selectedIndex];
        if (!selectedCityOpt || !selectedCityOpt.value) return;

        const cityStateId = selectedCityOpt.getAttribute('data-state-id');
        if (cityStateId && stateSelect.value !== String(cityStateId)) {
            stateSelect.value = cityStateId;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleFormFields();
        setupRealTimeValidations();
        filterStatesByCountry("{{ old('state_id', $selected->state_id ?? '') }}");
        filterCitiesByState("{{ old('city_id', $selected->city_id ?? '') }}");

        // Sync Ledger Name with Staff Name display
        const ledgerNameInput = document.getElementById('ledger_name');
        const staffNameDisplay = document.getElementById('staff_name_display');
        if (ledgerNameInput && staffNameDisplay) {
            ledgerNameInput.addEventListener('input', () => {
                staffNameDisplay.value = ledgerNameInput.value;
            });
        }
    });
</script>
@endsection
