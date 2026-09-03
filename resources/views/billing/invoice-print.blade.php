<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, 'Helvetica Neue', Arial, sans-serif;
            color: #1f2937;
            background: #f3f4f6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .toolbar {
            position: sticky; top: 0; z-index: 10;
            display: flex; align-items: center; justify-content: space-between; gap: 16px;
            background: #111827; color: #fff; padding: 12px 24px;
        }
        .toolbar .left { font-size: 14px; font-weight: 600; letter-spacing: .02em; }
        .toolbar button {
            border: 0; border-radius: 8px; padding: 10px 18px; font-size: 14px; font-weight: 600;
            cursor: pointer;
        }
        .toolbar .print-btn { background: #465FFF; color: #fff; }
        .toolbar .print-btn:hover { background: #3a4fe0; }
        .toolbar .close-btn { background: transparent; color: #d1d5db; border: 1px solid #4b5563; margin-left: 8px; }
        .sheet {
            width: 820px; max-width: 100%;
            margin: 28px auto; background: #fff; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
            overflow: hidden;
        }
        .invoice-header {
            padding: 40px 48px 28px;
            display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap;
            border-bottom: 1px solid #eef1f5;
        }
        .brand { display: flex; align-items: center; gap: 14px; }
        .brand .badge {
            width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center;
            background: #465FFF; color: #fff; font-weight: 800; font-size: 20px;
        }
        .brand .name { font-size: 19px; font-weight: 700; }
        .brand .addr { font-size: 12px; color: #6b7280; margin-top: 3px; max-width: 320px; line-height: 1.5; }
        .doc-title { text-align: right; }
        .doc-title .label { font-size: 11px; text-transform: uppercase; letter-spacing: .12em; color: #9ca3af; }
        .doc-title .num { font-size: 22px; font-weight: 800; color: #111827; margin-top: 4px; }
        .badge-status {
            display: inline-block; margin-top: 10px; padding: 4px 12px; border-radius: 999px;
            font-size: 11px; font-weight: 700; text-transform: capitalize;
        }
        .st-paid { background: #dcfce7; color: #15803d; }
        .st-overdue { background: #fee2e2; color: #b91c1c; }
        .st-pending { background: #e0e7ff; color: #3730a3; }
        .st-cancelled, .st-draft { background: #f3f4f6; color: #4b5563; }
        .meta-grid {
            display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 24px;
            padding: 28px 48px;
        }
        .meta-block .m-label { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: #9ca3af; font-weight: 700; }
        .meta-block .m-value { margin-top: 6px; font-size: 14px; }
        .meta-block .m-value strong { color: #111827; font-weight: 700; }
        .meta-block p.m-sub { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .balance-row { border-top: 2px solid #465FFF; }
        table.items { width: 100%; border-collapse: collapse; margin: 0 0 16px; }
        .items-wrap { padding: 0 48px 8px; }
        table.items th {
            text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: #9ca3af;
            padding: 12px 12px; border-bottom: 1px solid #e5e7eb; background: #f9fafb;
        }
        table.items th.num, table.items td.num { text-align: right; }
        table.items td { padding: 14px 12px; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        table.items td.desc { font-weight: 600; color: #111827; }
        .totals { margin-left: auto; width: 280px; padding: 8px 48px 28px 0; }
        .totals .row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 14px; color: #374151; }
        .totals .row.grand { border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 12px; font-weight: 800; color: #111827; font-size: 16px; }
        .totals .row .green { color: #15803d; font-weight: 700; }
        .payments { padding: 0 48px 20px; }
        .payments .p-label { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: #9ca3af; font-weight: 700; margin-bottom: 8px; }
        .payments table { width: 100%; border-collapse: collapse; }
        .payments td { font-size: 13px; padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .invoice-footer {
            margin-top: 12px; padding: 20px 48px; border-top: 1px solid #eef1f5;
            background: #f9fafb; display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap;
        }
        .invoice-footer p { font-size: 12px; color: #6b7280; }
        .invoice-footer .brand-foot { font-weight: 700; color: #111827; }
        .no-print { print-color-adjust: exact; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { box-shadow: none; margin: 0 auto; border-radius: 0; }
            @page { margin: 14mm; }
        }
    </style>
</head>
<body>
    @php
        $tenant = $invoice->tenant;
        $brandName = ($branding?->is_enabled && $branding->brand_name) ? $branding->brand_name : ($tenant->name ?? 'BeeCore');
        $brandLogo = ($branding?->is_enabled && $branding->logo_path)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($branding->logo_path)
            : null;
        $brandColor = $branding?->brand_color ?? '#465FFF';
        $paid = (float) $invoice->paid_amount;
        $outstanding = (float) $invoice->outstanding_amount;
        $statusClass = match ($invoice->status) {
            'paid' => 'st-paid', 'overdue' => 'st-overdue', 'pending' => 'st-pending',
            'cancelled', 'draft' => 'st-cancelled', default => 'st-pending',
        };
    @endphp

    <div class="toolbar no-print">
        <span class="left">Invoice print preview — {{ $invoice->invoice_number }}</span>
        <span>
            <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
            <button class="close-btn" onclick="window.close()">Close</button>
        </span>
    </div>

    <div class="sheet">
        <div class="invoice-header">
            <div class="brand">
                @if($brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}" style="height:46px;width:auto;max-width:180px;object-fit:contain;">
                @else
                    <span class="badge" style="background:{{ $brandColor }}">{{ strtoupper(substr($brandName, 0, 1)) }}</span>
                @endif
                <div>
                    <div class="name">{{ $brandName }}</div>
                    @if($tenant?->contact_address)
                        <div class="addr">{{ $tenant->contact_address }}</div>
                    @endif
                </div>
            </div>
            <div class="doc-title">
                <div class="label">Invoice</div>
                <div class="num">{{ $invoice->invoice_number }}</div>
                <span class="badge-status {{ $statusClass }}">{{ $invoice->status }}</span>
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-block">
                <div class="m-label">Billed to</div>
                <div class="m-value"><strong>{{ $invoice->customer?->name ?? 'Deleted customer' }}</strong></div>
                @if($invoice->customer?->email)<p class="m-sub">{{ $invoice->customer->email }}</p>@endif
                @if($invoice->customer?->phone)<p class="m-sub">{{ $invoice->customer->phone }}</p>@endif
            </div>
            <div class="meta-block">
                <div class="m-label">Issued</div>
                <div class="m-value"><strong>{{ $invoice->created_at->format('d M Y') }}</strong></div>
                <div class="m-label" style="margin-top:14px">Due date</div>
                <div class="m-value"><strong>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</strong></div>
            </div>
            <div class="meta-block">
                <div class="m-label">Balance</div>
                <div class="m-value" style="display:flex;flex-direction:column;gap:6px;margin-top:6px">
                    <span style="display:flex;justify-content:space-between;font-size:13px"><span style="color:#6b7280">Total</span><strong>৳{{ number_format($invoice->total, 2) }}</strong></span>
                    <span style="display:flex;justify-content:space-between;font-size:13px"><span style="color:#15803d">Paid</span><strong style="color:#15803d">৳{{ number_format($paid, 2) }}</strong></span>
                    <span style="display:flex;justify-content:space-between;font-size:14px" class="balance-row"><span>Balance due</span><strong>৳{{ number_format($outstanding, 2) }}</strong></span>
                </div>
            </div>
        </div>

        <div class="items-wrap">
            <table class="items">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="num">Qty</th>
                        <th class="num">Rate</th>
                        <th class="num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr>
                            <td class="desc">{{ $item->description }}</td>
                            <td class="num">{{ $item->quantity }}</td>
                            <td class="num">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="num">৳{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:20px">No line items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="totals">
            <div class="row"><span>Subtotal</span><span>৳{{ number_format($invoice->subtotal, 2) }}</span></div>
            <div class="row"><span>Tax</span><span>৳{{ number_format($invoice->tax_amount, 2) }}</span></div>
            <div class="row grand"><span>Total</span><span>৳{{ number_format($invoice->total, 2) }}</span></div>
            <div class="row"><span class="green">Paid</span><span class="green">− ৳{{ number_format($paid, 2) }}</span></div>
            <div class="row" style="font-weight:800;color:#111827;font-size:15px"><span>Balance due</span><span>৳{{ number_format($outstanding, 2) }}</span></div>
        </div>

        @if($invoice->payments->isNotEmpty())
            <div class="payments">
                <div class="p-label">Payment history</div>
                <table>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                            <tr>
                                <td style="font-weight:600;color:#111827;text-transform:capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                <td>{{ $payment->transaction_id }}</td>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td style="text-align:right;font-weight:700;color:#15803d">৳{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($tenant?->billingSetting('invoice_terms'))
            <div style="padding:0 48px 8px">
                <p style="font-size:12px;line-height:1.6;color:#6b7280;border-top:1px dashed #e5e7eb;padding-top:14px">
                    {{ $tenant->billingSetting('invoice_terms') }}
                </p>
            </div>
        @endif

        @php
            $collection = $tenant?->settings['collection'] ?? [];
            $collectionMode = $collection['mode'] ?? 'bee';
            $collectionMethods = $collection['methods'] ?? [];
            $beeFeePercent = \App\Models\SystemSetting::beeFeePercent();
        @endphp
        <div style="padding:0 48px 22px">
            <div style="border:1px solid #e5e7eb;border-radius:10px;background:#f9fafb;padding:14px 18px">
                <p style="margin:0 0 8px;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;font-weight:700">Payment instructions</p>
                @if($collectionMode === 'bee')
                    @php $payLink = route('bee-pay.invoice', ['invoice' => $invoice->id]); @endphp
                    <p style="margin:0;font-size:13px;color:#374151;line-height:1.6">
                        Pay securely online through the <strong>Bee Payment Gateway</strong>. A <strong>{{ $beeFeePercent }}%</strong> processing fee applies.
                    </p>
                    <p style="margin:10px 0 0;font-size:13px">
                        <a href="{{ $payLink }}" style="color:#465FFF;font-weight:700;text-decoration:none">Pay {{ $invoice->invoice_number }} online →</a>
                    </p>
                    <p style="margin:8px 0 0;font-size:11px;color:#6b7280;word-break:break-all">{{ $payLink }}</p>
                @else
                    @if(($collectionMethods['bkash']['enabled'] ?? false) && ($collectionMethods['bkash']['number'] ?? null))
                        <p style="margin:0 0 4px;font-size:13px;color:#374151"><strong>bKash:</strong> {{ $collectionMethods['bkash']['number'] }}</p>
                    @endif
                    @if(($collectionMethods['nagad']['enabled'] ?? false) && ($collectionMethods['nagad']['number'] ?? null))
                        <p style="margin:0 0 4px;font-size:13px;color:#374151"><strong>Nagad:</strong> {{ $collectionMethods['nagad']['number'] }}</p>
                    @endif
                    @if(($collectionMethods['bank']['enabled'] ?? false) && ($collectionMethods['bank']['details'] ?? null))
                        <p style="margin:0;font-size:13px;color:#374151;line-height:1.6">{{ $collectionMethods['bank']['details'] }}</p>
                    @endif
                    @if(! (($collectionMethods['bkash']['enabled'] ?? false) || ($collectionMethods['nagad']['enabled'] ?? false) || ($collectionMethods['bank']['enabled'] ?? false)))
                        <p style="margin:0;font-size:13px;color:#374151">Pay directly to this ISP — contact them for account details.</p>
                    @endif
                @endif
            </div>
        </div>

        <div class="invoice-footer">
            <p>Thank you for your business. Questions about this invoice? Contact <span class="brand-foot">{{ $brandName }}</span>.</p>
            <p>{{ $invoice->invoice_number }} · Generated by {{ $brandName }}</p>
        </div>
    </div>
</body>
</html>
