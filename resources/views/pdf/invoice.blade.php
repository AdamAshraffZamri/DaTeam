<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 14px; }
        .header { width: 100%; border-bottom: 2px solid #ddd; margin-bottom: 20px; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #333; }
        .company-info { font-size: 12px; color: #666; margin-top: 5px; }
        
        .invoice-box { width: 100%; margin-bottom: 20px; }
        .invoice-details { text-align: right; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background: #f4f4f4; padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .table td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; font-size: 16px; border-top: 2px solid #333; }
        
        .footer { margin-top: 50px; font-size: 12px; text-align: center; color: #999; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">HASTA TRAVEL & TOURS SDN. BHD.</div>
        <div class="company-info">
            (1359376-T)<br>
            7A, Jalan Kebudayaan 1A, Taman Universiti, 81310 Skudai, Johor<br>
            Phone: +6011-10900700
        </div>
    </div>

    <table class="invoice-box">
        <tr>
            <td style="vertical-align: top;">
                <strong>Bill To:</strong><br>
                {{ $booking->customer->fullName }}<br>
                {{ $booking->customer->address ?? $booking->customer->homeAddress }}<br>
                {{ $booking->customer->email }}<br>
                {{ $booking->customer->phoneNo }}
            </td>
            <td class="invoice-details" style="vertical-align: top;">
                <h2>INVOICE</h2>
                <strong>Invoice No:</strong> INV-{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}<br>
                <strong>Date:</strong> {{ now()->format('d M Y') }}<br>
                <strong>Status:</strong> <span style="color: green; font-weight: bold;">PAID</span>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Dates</th>
                <th class="text-right">Amount (RM)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Car Rental: {{ $booking->vehicle->model }} ({{ $booking->vehicle->plateNo }})</td>
                <td>
                    {{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }} - 
                    {{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}
                </td>
                <td class="text-right">{{ number_format($booking->totalCost, 2) }}</td>
            </tr>
            @if($booking->voucher)
            <tr>
                <td>Voucher Discount ({{ $booking->voucher->code }})</td>
                <td></td>
                <td class="text-right">- {{ number_format($booking->voucher->discount_amount ?? 0, 2) }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">Grand Total:</td>
                <td class="text-right">RM {{ number_format($booking->totalCost, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px;">
        <strong>Payment Method:</strong> {{ $booking->payment->paymentMethod ?? 'Online Transfer' }}<br>
        <strong>Payment Date:</strong> {{ optional($booking->payment)->created_at ? $booking->payment->created_at->format('d M Y') : 'N/A' }}
    </div>

    <div class="footer">
        Thank you for choosing Hasta Travel & Tours!<br>
        This is a computer-generated invoice. No signature is required.
    </div>

</body>
</html>