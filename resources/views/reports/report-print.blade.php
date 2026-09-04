<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Business report') }} — {{ $workspace->name }} ({{ $period['from'] }} → {{ $period['to'] }})</title>
    @php
        \Carbon\Carbon::setLocale(app()->getLocale());
        $fromLabel = \Carbon\Carbon::parse($period['from'])->translatedFormat('j M Y');
        $toLabel = \Carbon\Carbon::parse($period['to'])->translatedFormat('j M Y');
        $nowLabel = now()->translatedFormat('j M Y, H:i');
        $companyName = $workspace->company_legal_name ?: $workspace->name;
        $phone = $workspace->contact_phone ?: $workspace->owner_phone;
        $contactLine = collect([$workspace->contact_address, $phone ? __('Tel: :phone', ['phone' => $phone]) : null, $workspace->owner_email])->filter()->implode(' &nbsp;·&nbsp; ');
        $currency = $workspace->currency ?: 'BDT';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body {
            font-family: 'Segoe UI', -apple-system, 'Helvetica Neue', Arial, sans-serif;
            background: #eef1f5;
            color: #1f2937;
            font-size: 13px;
            line-height: 1.45;
        }
        /* ---- Screen toolbar (never printed) ---- */
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

        /* ---- A4 sheet ---- */
        .sheet {
            width: 210mm;
            max-width: 100%;
            margin: 18px auto;
            background: #fff;
            padding: 46px 48px 38px;
            box-shadow: 0 12px 34px rgba(15, 23, 42, .08);
        }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .muted { color: #6b7280; }

        /* ---- Letterhead ---- */
        .letterhead {
            display: flex; justify-content: space-between; align-items: flex-start; gap: 28px;
            padding-bottom: 14px;
            border-bottom: 3px double #0f172a;
        }
        .lh-left { min-width: 0; padding-right: 16px; }
        .company { font-size: 22px; font-weight: 800; letter-spacing: -.01em; color: #0f172a; }
        .lh-line { margin-top: 3px; font-size: 11px; color: #6b7280; line-height: 1.55; }
        .lh-right { text-align: right; flex-shrink: 0; }
        .doc-type { font-size: 10px; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; color: #64748b; }
        .doc-range { margin-top: 6px; font-size: 18px; font-weight: 800; color: #0f172a; }
        .doc-meta { margin-top: 4px; font-size: 11px; color: #6b7280; }

        /* ---- Section blocks ---- */
        .section { margin-top: 18px; }
        .sec-title {
            font-size: 10.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
            color: #0f172a; padding-bottom: 5px; margin-bottom: 7px;
            border-bottom: 1px solid #0f172a;
            display: flex; align-items: baseline; justify-content: space-between; gap: 12px;
        }
        .sec-title .hint { font-size: 10px; font-weight: 500; letter-spacing: .02em; text-transform: none; color: #9ca3af; }

        /* ---- KPI strip (Excel-style bordered band) ---- */
        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); border: 1px solid #cbd2dc; background: #fff; }
        .kpi { padding: 10px 14px 11px; border-right: 1px solid #cbd2dc; }
        .kpi + .kpi { border-left: 0; }
        .kpi:last-child { border-right: 0; }
        .k-label { font-size: 9.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #7c8794; }
        .k-value { margin-top: 3px; font-size: 17px; font-weight: 800; color: #0f172a; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .k-sub { margin-top: 2px; font-size: 10.5px; color: #8a94a3; }

        /* ---- Data tables (grid-lined like printed spreadsheets) ---- */
        table.data { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.data thead { display: table-header-group; }
        table.data th {
            background: #eef1f5; color: #475569; font-weight: 700;
            font-size: 10px; letter-spacing: .06em; text-transform: uppercase;
            border: 1px solid #cbd2dc; padding: 5px 10px; text-align: left; white-space: nowrap;
        }
        table.data td { border: 1px solid #d7dce4; padding: 5px 10px; vertical-align: middle; }
        table.data tbody tr { page-break-inside: avoid; }
        table.data tr.subtotal td, table.data tfoot td {
            background: #f6f8fa; font-weight: 700; color: #111827;
            border-top: 1.5px solid #94a3b8;
        }
        table.data tr.total td { background: #eef1f5; font-weight: 800; border-top: 1px solid #0f172a; }

        .badge {
            display: inline-block; padding: 1px 9px; border: 1px solid #cbd2dc; border-radius: 999px;
            font-size: 10.5px; font-weight: 600; text-transform: capitalize; color: #334155;
        }

        /* ---- Footer ---- */
        .foot {
            margin-top: 22px; padding-top: 8px; border-top: 1px solid #d7dce4;
            display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap;
            font-size: 9.5px; color: #9ca3af;
        }

        @media (max-width: 700px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .kpi:nth-child(2) { border-right: 0; }
            .kpi:nth-child(n + 3) { border-top: 1px solid #cbd2dc; }
        }
        @page { size: A4; margin: 12mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { width: auto; margin: 0; padding: 0; box-shadow: none; }
            .letterhead, .section, .foot { page-break-inside: avoid; }
            table.data { page-break-inside: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span class="left">BeeCore · {{ __('Printable report') }}</span>
        <div class="actions">
            <a href="javascript:window.close();" class="back-btn">{{ __('Close') }}</a>
            <button type="button" class="print-btn" onclick="window.print()">{{ __('Print / Save as PDF') }}</button>
        </div>
    </div>

    <main class="sheet">
        <header class="letterhead">
            <div class="lh-left">
                <div class="company">{{ $companyName }}</div>
                @if($contactLine)
                    <div class="lh-line">{!! $contactLine !!}</div>
                @endif
            </div>
            <div class="lh-right">
                <div class="doc-type">{{ __('Business report') }}</div>
                <div class="doc-range">{{ $fromLabel }} – {{ $toLabel }}</div>
                <div class="doc-meta">{{ __('Generated') }} {{ $nowLabel }}</div>
            </div>
        </header>

        <section class="section">
            <div class="sec-title">
                <span>{{ __('Summary') }}</span>
                <span class="hint">{{ __('Amounts in :currency', ['currency' => $currency]) }}</span>
            </div>
            <div class="kpi-grid">
                <div class="kpi">
                    <div class="k-label">{{ __('Collections') }}</div>
                    <div class="k-value">৳{{ number_format($metrics['collections'], 2) }}</div>
                    <div class="k-sub">{{ __(':count transactions', ['count' => number_format($metrics['transactions'])]) }}</div>
                </div>
                <div class="kpi">
                    <div class="k-label">{{ __('Invoiced') }}</div>
                    <div class="k-value">৳{{ number_format($metrics['invoiced'], 2) }}</div>
                    <div class="k-sub">{{ __(':pct% collected', ['pct' => $metrics['collection_rate']]) }}</div>
                </div>
                <div class="kpi">
                    <div class="k-label">{{ __('Customers') }}</div>
                    <div class="k-value">{{ number_format($metrics['customers']) }}</div>
                    <div class="k-sub">{{ __(':count active', ['count' => number_format($metrics['active_customers'])]) }}</div>
                </div>
                <div class="kpi">
                    <div class="k-label">{{ __('Avg payment') }}</div>
                    <div class="k-value">৳{{ number_format($metrics['avg_payment'], 2) }}</div>
                    <div class="k-sub">{{ __(':count devices online', ['count' => $metrics['online_devices']]) }}</div>
                </div>
            </div>
        </section>

        @if($paymentMethods->isNotEmpty())
            <section class="section">
                <div class="sec-title">
                    <span>{{ __('Collections by method') }}</span>
                    <span class="hint">{{ number_format($paymentMethods->count()) }} {{ __('method(s)') }}</span>
                </div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>{{ __('Method') }}</th>
                            <th class="num">{{ __('Transactions') }}</th>
                            <th class="num">{{ __('Amount') }}</th>
                            <th class="num">{{ __('Share') }}</th>
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
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>{{ __('Total') }}</td>
                            <td class="num">{{ number_format($metrics['transactions']) }}</td>
                            <td class="num">৳{{ number_format($metrics['collections'], 2) }}</td>
                            <td class="num">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </section>
        @endif

        @if(count($invoiceStatuses) > 0)
            @php
                $totalInvoices = collect($invoiceStatuses)->sum('count');
                $totalValue = collect($invoiceStatuses)->sum('value');
            @endphp
            <section class="section">
                <div class="sec-title">
                    <span>{{ __('Invoice status') }}</span>
                    <span class="hint">{{ number_format($totalInvoices) }} {{ __('invoice(s)') }}</span>
                </div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>{{ __('Status') }}</th>
                            <th class="num">{{ __('Invoices') }}</th>
                            <th class="num">{{ __('Value') }}</th>
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
                    <tfoot>
                        <tr>
                            <td>{{ __('Total') }}</td>
                            <td class="num">{{ number_format($totalInvoices) }}</td>
                            <td class="num">৳{{ number_format($totalValue, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </section>
        @endif

        <footer class="foot">
            <span>{{ __('Generated by BeeCore for :workspace. Figures cover :from to :to.', ['workspace' => $companyName, 'from' => $fromLabel, 'to' => $toLabel]) }}</span>
            <span>BeeCore</span>
        </footer>
    </main>
</body>
</html>
