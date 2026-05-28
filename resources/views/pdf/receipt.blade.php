<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Receipt #{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 14px; }
        .header { width: 100%; border-bottom: 2px solid #ddd; margin-bottom: 20px; padding-bottom: 10px; text-align: center; }
        .logo { font-size: 24px; font-weight: bold; color: #333; }
        .company-info { font-size: 12px; color: #666; margin-top: 5px; line-height: 1.4; }
        
        .receipt-title { text-align: center; font-size: 20px; font-weight: bold; letter-spacing: 2px; margin: 20px 0; text-decoration: underline; }
        
        .info-box { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .info-box td { padding: 5px; vertical-align: top; }
        .label { width: 150px; font-weight: bold; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 30px; }
        .table th { background: #f4f4f4; padding: 12px; text-align: left; border: 1px solid #ddd; font-weight: bold; }
        .table td { padding: 12px; border: 1px solid #eee; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; font-size: 16px; background-color: #f9f9f9; }
        
        .footer { margin-top: 50px; font-size: 12px; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
        
        .signature-box { margin-top: 60px; width: 100%; }
        .signature-line { width: 200px; border-top: 1px solid #333; text-align: center; font-size: 12px; font-weight: bold; font-style: italic; padding-top: 5px;}
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">HASTA TRAVEL & TOURS SDN. BHD.</div>
        <div class="company-info">
            (Company No: 1359376-T)<br>
            7A, Jalan Kebudayaan 1A, Taman Universiti, 81310 Skudai, Johor<br>
            Tel: +6011-10900700 | Email: hastatraveltours@gmail.com
        </div>
    </div>

    <div class="receipt-title">OFFICIAL RECEIPT</div>

    <table class="info-box">
        <tr>
            <td class="label">Receipt No</td>
            <td>: OR-{{ str_pad($booking->bookingID, 6, '0', STR_PAD_LEFT) }}</td>
            <td class="label" style="text-align: right;">Date</td>
            @php
                $completionDate = $booking->actualReturnDate ? \Carbon\Carbon::parse($booking->actualReturnDate)->format('d M Y') : now()->format('d M Y');
            @endphp
            <td style="text-align: right;">: {{ $completionDate }}</td>
        </tr>
        <tr>
            <td class="label">Received From</td>
            <td colspan="3">: {{ mb_strtoupper($booking->customer->fullName) }}</td>
        </tr>
        <tr>
            <td class="label">Payment Method</td>
            <td colspan="3">: {{ mb_strtoupper($booking->payment->paymentMethod ?? 'ONLINE TRANSFER') }}</td>
        </tr>
        <tr>
            <td class="label">Booking Status</td>
            <td colspan="3">: <span style="color: green; font-weight: bold;">{{ mb_strtoupper($booking->bookingStatus) }}</span></td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Description</th>
                <th class="text-right">Amount (RM)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td>
                    Full Payment and Completion of Car Rental Services<br>
                    <span style="font-size: 12px; color: #555;">
                        Vehicle: {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }} ({{ $booking->vehicle->plateNo }})<br>
                        Duration: {{ \Carbon\Carbon::parse($booking->originalDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($booking->returnDate)->format('d M Y') }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($booking->totalCost, 2) }}</td>
            </tr>
            @if($booking->voucher)
            <tr>
                <td style="text-align: center;">2</td>
                <td>
                    Voucher Applied ({{ $booking->voucher->code }})
                </td>
                <td class="text-right">- {{ number_format($booking->voucher->discount_amount ?? 0, 2) }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right">TOTAL RECEIVED:</td>
                <td class="text-right">RM {{ number_format($booking->totalCost, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Text to indicate amount in words if needed (Optional) -->
    <!-- <div style="margin-top: 20px; font-style: italic;">
        <strong>Amount in words:</strong> 
    </div> -->

    <table class="signature-box">
        <tr>
            <td style="padding-top: 100px;">
                <div class="signature-line">
                    Authorised Signature<br>
                    <span style="font-weight: normal; color: #555;">Hasta Travel & Tours Sdn. Bhd.</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Thank you for choosing Hasta Travel & Tours!<br>
        This is a computer-generated receipt. No physical signature is required.
    </div>

</body>
</html>
