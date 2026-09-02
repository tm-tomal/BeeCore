<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business report — {{ $workspace->name }} ({{ $period['from'] }} → {{ $period['to'] }})</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, 'Helvetica Neue', Arial, sans-serif;
            background: #f3f4f6;
            color: #111827;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .toolbar {
            position: sticky; top: 0; z-index: 10;
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            background: #111827; color: #fff; padding: 12px 24px;
        }
        .toolbar .left { font-size: 14px; font-weight: 600; letter-spacing: .02em; }
        .toolbar .actions { display: flex; gap: 8px; }
        .toolbar button, .toolbar a {
            display: inline-flex; align-items: center; justify-content: center;
            border: 0; border-radius: 8px; padding: 9px 16px; font-size: 14px;
            font-weight: 600; cursor: pointer; text-decoration: none;
        }
        .toolbar .print-btn { background: #fff; color: #111827; }
        .toolbar .back-btn { background: transparent; color: #d1d5db; border: 1px solid #4b5563; }
        .sheet {
            width: 840px; max-width: 100%;
            margin: 24px auto; background: #fff; border: 1px solid #e5e7eb;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .07);
        }
        .report-head {
            padding: 34px 48px 24px;
            display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap;
            border-bottom: 1px solid #111827;
        }
        .brand .name { font-size: 20px; font-weight: 800; letter-spacing: -.01em; }
        .brand .sub { font-size: 12px; color: #6b7280; margin-top: 4px; max-width: 420px; }
        .doc { text-align: right; }
        .doc .label { font-size: 11px; text-transform: uppercase; letter-spacing: .14em; color: #9ca3af; }
        .doc .title { font-size: 22px; font-weight: 800; margin-top: 4px; }
        .doc .meta { font-size: 12px; color: #6b7280; margin-top: 6px; }
        .section { padding: 26px 48px 6px; }
        .section h2 {
            font-size: 11px; text-transform: uppercase; letter-spacing: .12em;
            color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; font-weight: 800;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th {
            text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .1em;
            color: #6b7280; padding: 8px 12px; border-bottom: 1px solid #111827;
        }
        td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        tr:last-child td { border-bottom: 0; }
        td.num, th.num { text-align: right; }
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border: 1px solid #e5e7eb; margin-top: 14px; }
        .kpi { padding: 16px; border-right: 1px solid #e5e7eb; }
        .kpi:last-child { border-right: 0; }
        .kpi .k-label { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: #9ca3af; }
        .kpi .k-value { font-size: 18px; font-weight: 800; margin-top: 6px; }
        .kpi .k-sub { font-size: 11px; color: #6b7280; margin-top: 3px; }
        .note { padding: 6px 48px 34px; font-size: 11px; color: #9ca3af; }
        .note .line { border-top: 1px solid #e5e7eb; margin-bottom: 10px; }
        .badge {
            display: inline-block; padding: 2px 10px; border: 1px solid #111827;
            border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: capitalize;
        }
        .muted { color: #6b7280; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { width: 100%; margin: 0; border: 0; box-shadow: none; }
            .section, .report-head, .note { padding-left: 8mm; padding-right: 8mm; }
            .kpi-grid { break-inside: avoid; }
            table { page-break-inside: auto; }
        }
        @page { size: A4; margin: 12mm; }
    </style>
</head>
<body>
    <div class="toolbar">
        <span class="left">BeeCore · Printable report</span>
        <div class="actions">
            <a href="javascript:window.close();" class="back-btn">Close</a>
            <button type="button" class="print-btn" onclick="window.print()">Print / Save as PDF</button>
        </div>
    </div>

    <main class="sheet">
        <header class="report-head">
            <div class="brand">
                <div class="name">BeeCore — {{ $workspace->name }}</div>
                <div class="sub">ISP business report · Collections, invoicing, subscribers and operations snapshot.</div>
            </div>
            <div class="doc">
                <div class="label">Business report</div>
                <div class="title">{{ $period['from'] }} – {{ $period['to'] }}</div>
                <div class="meta">Generated {{ now()->format('d M Y, H:i') }}</div>
            </div>
        </header>

        <section class="section">
            <h2>Summary</h2>
            <div class="kpi-grid">
                <div class="kpi">
                    <div class="k-label">Collections</div>
                    <div class="k-value">৳{{ number_format($metrics['collections'], 2) }}</div>
                    <div class="k-sub">{{ number_format($metrics['transactions']) }} transactions</div>
                </div>
                <div class="kpi">
                    <div class="k-label">Invoiced</div>
                    <div class="k-value">৳{{ number_format($metrics['invoiced'], 2) }}</div>
                    <div class="k-sub">{{ $metrics['collection_rate'] }}% collected</div>
                </div>
                <div class="kpi">
                    <div class="k-label">Customers</div>
                    <div class="k-value">{{ number_format($metrics['customers']) }}</div>
                    <div class="k-sub">{{ number_format($metrics['active_customers']) }} active</div>
                </div>
                <div class="kpi">
                    <div class="k-label">Avg payment</div>
                    <div class="k-value">৳{{ number_format($metrics['avg_payment'], 2) }}</div>
                    <div class="k-sub">{{ $metrics['online_devices'] }} devices online</div>
                </div>
            </div>
        </section>

        @if($paymentMethods->isNotEmpty())
            <section class="section">
                <h2>Collections by method</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th class="num">Transactions</th>
                            <th class="num">Amount</th>
                            <th class="num">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentMethods as $method)
                            <tr>
                                <td class="capitalize">{{ str_replace('_', ' ', $method['payment_method']) }}</td>
                                <td class="num">{{ number_format($method['transactions']) }}</td>
                                <td class="num">৳{{ number_format($method['total'], 2) }}</td>
                                <td class="num muted">{{ number_format($method['share'], 1) }}%</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="muted">Total</td>
                            <td class="num muted">{{ number_format($metrics['transactions']) }}</td>
                            <td class="num">৳{{ number_format($metrics['collections'], 2) }}</td>
                            <td class="num muted">100%</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        @endif

        @if(count($invoiceStatuses) > 0)
            <section class="section">
                <h2>Invoice status</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="num">Invoices</th>
                            <th class="num">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoiceStatuses as $row)
                            <tr>
                                <td><span class="badge">{{ $row['status'] }}</span></td>
                                <td class="num">{{ number_format($row['count']) }}</td>
                                <td class="num">৳{{ number_format($row['value'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        <div class="note">
            <div class="line"></div>
            Generated by BeeCore for {{ $workspace->name }}. Figures cover {{ $period['from'] }} to {{ $period['to'] }}.
        </div>
    </main>
</body>
</html>
