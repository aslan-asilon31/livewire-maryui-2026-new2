<div>

  <hr>

  <div class="p-3 border rounded">
    <div class="mb-2">
      <strong>KPI</strong> : {{ $masterForm->kpi_name ?? 'N/A' }}
    </div>

    <div class="mb-2">
      <strong>Periode</strong> :
      @php
        $period = null;
        if (!empty($masterForm->year)) {
            if (!empty($masterForm->month)) {
                $period = $masterForm->month . '/' . $masterForm->year;
            } elseif (!empty($masterForm->quarter)) {
                $period = 'Q' . $masterForm->quarter . ' ' . $masterForm->year;
            } else {
                $period = $masterForm->year;
            }
        }
      @endphp
      {{ $period ?? 'N/A' }}
    </div>

    <div class="mb-0">
      <strong>Skor Akhir KPI</strong> : {{ $masterForm->final_score ?? 'N/A' }}
    </div>
  </div>

  <div class="mt-3 p-3 border rounded">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div>
        <strong>Rincian KPI</strong>
        <div class="text-muted" style="font-size: 12px;">
          Skor (0–100), bobot ditampilkan sebagai komposisi.
        </div>
      </div>
      <div class="text-muted" style="font-size: 12px;">
        Total Faktor: {{ count($kpiChart['labels'] ?? []) }}
      </div>
    </div>

    @if (!empty($kpiChart['labels']) && $kpiFactorBarChart && $kpiWeightDoughnutChart && $kpiFinalLineChart)
      <div class="row g-3">
        <div class="col-12 col-lg-6">
          <div class="p-3 border rounded" style="height: 340px;" wire:key="bar-{{ $masterForm->id }}">
            <x-chartjs-component :chart="$kpiFactorBarChart" />
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="p-3 border rounded" style="height: 340px;" wire:key="donut-{{ $masterForm->id }}">
            <x-chartjs-component :chart="$kpiWeightDoughnutChart" />
          </div>
        </div>

        <div class="col-12">
          <div class="p-3 border rounded" style="height: 320px;" wire:key="line-{{ $masterForm->id }}">
            <x-chartjs-component :chart="$kpiFinalLineChart" />
          </div>
        </div>
      </div>

      <div class="mt-3">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th>Faktor</th>
              <th class="text-end">Skor</th>
              <th class="text-end">Bobot (%)</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($kpiChart['labels'] as $i => $label)
              <tr>
                <td>{{ $label }}</td>
                <td class="text-end">{{ $kpiChart['scores'][$i] }}</td>
                <td class="text-end">{{ $kpiChart['weights'][$i] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="text-muted">
        Belum ada data KPI faktor untuk user ini.
      </div>
    @endif
  </div>

</div>
