<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Training Report</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        h2 { font-size: 13px; margin: 22px 0 6px; color: #b45309; text-transform: uppercase; letter-spacing: .05em; }
        .muted { color: #6b7280; font-size: 11px; }
        .kpis { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .kpis td { width: 20%; border: 1px solid #e5e7eb; padding: 8px; text-align: center; }
        .kpis .value { font-size: 18px; font-weight: bold; }
        .kpis .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data th { background: #f9fafb; text-align: left; padding: 6px 8px; border-bottom: 2px solid #e5e7eb; font-size: 10px; text-transform: uppercase; color: #6b7280; }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; }
        table.data td.num, table.data th.num { text-align: right; }
    </style>
</head>
<body>
    <h1>Training Report</h1>
    <p class="muted">Generated {{ $generatedAt }}</p>

    <table class="kpis">
        <tr>
            <td><div class="value">{{ $overview['trainees'] }}</div><div class="label">Trainees</div></td>
            <td><div class="value">{{ $overview['completion'] }}%</div><div class="label">Completion</div></td>
            <td><div class="value">{{ $overview['average_score'] ?? '—' }}{{ $overview['average_score'] !== null ? '%' : '' }}</div><div class="label">Avg score</div></td>
            <td><div class="value">{{ $overview['fully_trained'] }}</div><div class="label">Fully trained</div></td>
            <td><div class="value">{{ $overview['at_risk'] }}</div><div class="label">At risk</div></td>
        </tr>
    </table>

    <h2>Store performance</h2>
    <table class="data">
        <thead>
            <tr><th>Store</th><th class="num">Trainees</th><th class="num">Completion</th><th class="num">Avg score</th></tr>
        </thead>
        <tbody>
            @forelse ($stores as $store)
                <tr>
                    <td>{{ $store['name'] }}</td>
                    <td class="num">{{ $store['trainees'] }}</td>
                    <td class="num">{{ $store['completion'] }}%</td>
                    <td class="num">{{ $store['average_score'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No store data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Stations</h2>
    <table class="data">
        <thead>
            <tr><th>Station</th><th class="num">Completion</th><th class="num">Avg score</th></tr>
        </thead>
        <tbody>
            @forelse ($stations as $station)
                <tr>
                    <td>{{ $station['title'] }}</td>
                    <td class="num">{{ $station['completion'] }}%</td>
                    <td class="num">{{ $station['average_score'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No station data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Trainees</h2>
    <table class="data">
        <thead>
            <tr><th>Trainee</th><th>Store</th><th>Status</th><th class="num">Completion</th><th class="num">Score</th></tr>
        </thead>
        <tbody>
            @forelse ($trainees as $trainee)
                <tr>
                    <td>{{ $trainee['name'] }}</td>
                    <td>{{ $trainee['store'] }}</td>
                    <td>{{ str($trainee['status'])->headline() }}</td>
                    <td class="num">{{ $trainee['completion'] }}%</td>
                    <td class="num">{{ $trainee['average_score'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No trainees.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
