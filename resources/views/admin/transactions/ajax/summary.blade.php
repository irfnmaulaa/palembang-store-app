@if(count($transactions) > 0)
    <div class="text-muted"><small>Ditampilkan {{number_format($transactions->firstItem(), 0, ',', '.')}} - {{number_format($transactions->count() - 1 + $transactions->firstItem(), 0, ',', '.')}} dari {{number_format($transactions->total(), 0, ',', '.')}} data</small></div>
@endif
