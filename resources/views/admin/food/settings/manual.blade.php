<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Food Setting User Manual</title>
    <style>
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 20px;
        }
        h1 { 
            text-align: center; 
            color: #2c3e50; 
            border-bottom: 2px solid #2c3e50; 
            padding-bottom: 10px; 
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            font-size: 13px;
            margin-bottom: 30px;
        }
        .intro {
            font-size: 14px;
            margin-bottom: 30px;
            color: #555;
        }
        .setting-box { 
            border: 1px solid #ddd; 
            padding: 18px; 
            margin-bottom: 20px; 
            border-radius: 6px; 
            background-color: #f9f9f9;
            page-break-inside: avoid;
        }
        .setting-title { 
            font-weight: bold; 
            color: #c0392b; 
            font-size: 15px; 
            margin-bottom: 8px; 
            border-bottom: 1px solid #e74c3c;
            padding-bottom: 5px;
        }
        p { 
            margin: 6px 0; 
            font-size: 13px;
        }
        .label {
            font-weight: bold;
            color: #2c3e50;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 11px;
            color: #aaa;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Food Management Settings</h1>
    <div class="subtitle">User Manual &mdash; School Management System</div>

    <div class="intro">
        This document explains each setting available in the <strong>Food Settings</strong> panel
        of the School Management System. Use these settings to configure how food fees,
        deductions, and billing are handled for students.
    </div>

    <div class="setting-box">
        <div class="setting-title">1. Food Fee Adjustment Enabled</div>
        <p>Controls whether students are eligible for food fee deductions when they miss meals for an extended period.</p>
        <p><span class="label">ON:</span> The system will automatically calculate and apply deductions to monthly fees when the absence threshold is met.</p>
        <p><span class="label">OFF:</span> No deductions will be applied regardless of how many days a student is absent.</p>
    </div>

    <div class="setting-box">
        <div class="setting-title">2. Minimum Non-Consumption Days</div>
        <p>The minimum number of consecutive days a student must not consume food to qualify for a fee deduction.</p>
        <p><span class="label">Example:</span> If set to <strong>10</strong>, a student absent for 9 days receives no discount. A student absent for 10 or more days will have those days deducted from their monthly bill.</p>
    </div>

    <div class="setting-box">
        <div class="setting-title">3. Billing Days</div>
        <p>Defines the standard number of days in a billing month (typically 30).</p>
        <p>The system uses this value to calculate the cost per day:</p>
        <p><strong>Daily Cost = Monthly Fee &divide; Billing Days</strong></p>
        <p>This per-day cost is used when calculating any deductions.</p>
    </div>

    <div class="setting-box">
        <div class="setting-title">4. Calculation Method</div>
        <p>Determines how meal absences are recorded for fee deductions.</p>
        <p><span class="label">Based on Food Day (Daily Absences):</span> A full day is charged if the student consumes even one meal that day. Deductions only apply when ALL meals in a day are missed.</p>
        <p><span class="label">Based on Individual Meal Slots:</span> Each missed meal is calculated separately. If a student skips Dinner but has Breakfast and Lunch, only the dinner cost is deducted.</p>
    </div>

    <div class="setting-box">
        <div class="setting-title">5. Allow Manual Admin Fee Adjustments / Waivers</div>
        <p>Grants administrators the ability to manually override or waive food fees for individual students.</p>
        <p><span class="label">Enabled:</span> Admins can manually adjust or waive a student's food fee beyond what the system automatically calculates.</p>
        <p><span class="label">Disabled:</span> Only automatic system calculations apply; no manual overrides are allowed.</p>
    </div>

    <div class="setting-box">
        <div class="setting-title">6. Auto Generate Monthly Food Fee Invoices</div>
        <p>Automates the monthly billing process for the dining or hostel management.</p>
        <p><span class="label">Enabled:</span> The system automatically generates food fee invoices for all enrolled students at the start of each billing cycle. No manual entry is required.</p>
        <p><span class="label">Disabled:</span> Administrators must manually create invoices for each student every month.</p>
    </div>

    <div class="footer">
        School Management System &bull; Generated on {{ date('d F Y') }}
    </div>
</body>
</html>