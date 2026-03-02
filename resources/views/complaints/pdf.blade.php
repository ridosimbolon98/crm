<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Export PDF</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; margin: 24px; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        p { margin: 0 0 12px; font-size: 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; }
        .meta { margin-bottom: 16px; }
        @media print {
            .no-print { display: none; }
            body { margin: 8px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:12px;">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>
    <h1>Customer Complaint Export</h1>
    <p>Generated at: {{ $generatedAt->format('d M Y H:i:s') }}</p>

    <div class="meta">
        <p>Filters:
            Search={{ $filters['search'] ?? '-' }},
            Status={{ $filters['status'] ?? '-' }},
            Severity={{ $filters['severity'] ?? '-' }},
            Brand={{ $filters['brand'] ?? '-' }},
            From={{ $filters['from'] ?? '-' }},
            To={{ $filters['to'] ?? '-' }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Customer</th>
                <th>Brand</th>
                <th>Category</th>
                <th>Date</th>
                <th>Severity</th>
                <th>Status</th>
                <th>CAPA</th>
                <th>PIC</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->ticket_number }}</td>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->brand?->name }}</td>
                    <td>{{ $row->category?->name }}</td>
                    <td>{{ $row->complaint_date?->format('Y-m-d') }}</td>
                    <td>{{ $row->severity }}</td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->capa_status }}</td>
                    <td>{{ $row->assigned_to }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
