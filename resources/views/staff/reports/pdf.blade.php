<!DOCTYPE html>
<html>
<head>
    <title>Monthly Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>DaTeam Monthly Report</h2>
    <p>Generated on: {{ $date }}</p>

    <div style="margin-bottom: 20px; padding: 10px; border: 1px solid #ddd;">
        <strong>Total Revenue:</strong> RM {{ number_format($total_revenue, 2) }} <br>
        <strong>Total Bookings:</strong> {{ $total_bookings }}
    </div>

    <h3>Recent Bookings</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>#{{ $booking->bookingID }}</td>
                <td>{{ $booking->customer->fullName ?? 'Guest' }}</td>
                <td>{{ $booking->vehicle->model ?? 'Unknown' }}</td>
                <td>{{ $booking->bookingStatus }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>