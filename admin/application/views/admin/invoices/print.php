<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo htmlspecialchars($invoice->invoice_number); ?> - BODARE Pension House</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; margin: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #6576ff; padding-bottom: 20px; }
        .brand h1 { margin: 0; color: #6576ff; font-size: 24px; }
        .meta { text-align: right; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; }
        th { background: #f8f9fa; text-align: left; }
        .text-end { text-align: right; }
        tfoot td { font-weight: bold; }
        .balance { font-size: 18px; color: #c00; }
        @media print { body { margin: 20px; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Print Invoice</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="header">
        <div class="brand">
            <h1>BODARE Pension House</h1>
            <p>Room Billing Invoice</p>
        </div>
        <div class="meta">
            <p><strong>Invoice #:</strong> <?php echo htmlspecialchars($invoice->invoice_number); ?></p>
            <p><strong>Date:</strong> <?php echo $invoice->issued_at ? date('F d, Y', strtotime($invoice->issued_at)) : date('F d, Y'); ?></p>
            <p><strong>Due:</strong> <?php echo $invoice->due_date ? date('F d, Y', strtotime($invoice->due_date)) : 'Upon receipt'; ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($invoice->status); ?></p>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div>
            <strong>Bill To:</strong><br>
            <?php echo htmlspecialchars($invoice->guest_name); ?><br>
            <?php if ($invoice->guest_email): echo htmlspecialchars($invoice->guest_email) . '<br>'; endif; ?>
            <?php if ($invoice->guest_phone): echo htmlspecialchars($invoice->guest_phone); endif; ?>
        </div>
        <div>
            <?php if ($invoice->booking_number): ?><p><strong>Booking:</strong> <?php echo htmlspecialchars($invoice->booking_number); ?></p><?php endif; ?>
            <?php if (!empty($invoice->event_name)): ?><p><strong>Event:</strong> <?php echo htmlspecialchars($invoice->event_name); ?></p><?php endif; ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item->description); ?></td>
                <td class="text-end"><?php echo number_format($item->quantity, 2); ?></td>
                <td class="text-end">₱<?php echo number_format($item->unit_price, 2); ?></td>
                <td class="text-end">₱<?php echo number_format($item->total_price, 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">₱<?php echo number_format($invoice->subtotal, 2); ?></td></tr>
            <?php if ($invoice->discount_amount > 0): ?>
            <tr><td colspan="3" class="text-end">Discount</td><td class="text-end">-₱<?php echo number_format($invoice->discount_amount, 2); ?></td></tr>
            <?php endif; ?>
            <?php if ($invoice->tax_amount > 0): ?>
            <tr><td colspan="3" class="text-end">Tax</td><td class="text-end">₱<?php echo number_format($invoice->tax_amount, 2); ?></td></tr>
            <?php endif; ?>
            <?php if ($invoice->service_charge_amount > 0): ?>
            <tr><td colspan="3" class="text-end">Service Charge</td><td class="text-end">₱<?php echo number_format($invoice->service_charge_amount, 2); ?></td></tr>
            <?php endif; ?>
            <tr><td colspan="3" class="text-end">Total</td><td class="text-end">₱<?php echo number_format($invoice->total_amount, 2); ?></td></tr>
            <tr><td colspan="3" class="text-end">Amount Paid</td><td class="text-end">₱<?php echo number_format($invoice->amount_paid, 2); ?></td></tr>
            <tr><td colspan="3" class="text-end balance">Balance Due</td><td class="text-end balance">₱<?php echo number_format($invoice->balance_due, 2); ?></td></tr>
        </tfoot>
    </table>

    <?php if ($invoice->notes): ?>
    <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($invoice->notes)); ?></p>
    <?php endif; ?>

    <p style="margin-top: 40px; font-size: 12px; color: #666;">Thank you for choosing BODARE Pension House.</p>
</body>
</html>
