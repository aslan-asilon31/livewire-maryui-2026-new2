<div>
  <div class="card">
    <div class="card-header">
      {{-- optional title --}}
    </div>

    <div class="card-body">
      @if (session()->has('message'))
        <div class="alert alert-success">
          {{ session('message') }}
        </div>
      @endif

      <x-form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">


        {{-- USER --}}
        <x-input label="Nama" wire:model="masterForm.name" :error="$errors->first('masterForm.name')" :readonly="$isReadonly" />

        <x-input label="Email" type="email" wire:model="masterForm.email" :error="$errors->first('masterForm.email')" :readonly="$isReadonly" />

        <x-select label="Status" wire:model="masterForm.is_activated" :options="[['id' => 1, 'name' => 'Aktif'], ['id' => 0, 'name' => 'Non Aktif']]" placeholder="Pilih Status"
          :readonly="$isReadonly" :error="$errors->first('masterForm.is_activated')" />

        <x-input label="Nomor Antrian" type="number" wire:model="masterForm.queue_number" :error="$errors->first('masterForm.queue_number')"
          :readonly="true" />

        {{-- USER DETAILS --}}
        <hr>

        <x-input label="Nomor Telepon" wire:model="masterForm.phone" :error="$errors->first('masterForm.phone')" :readonly="$isReadonly" />

        <x-input label="Alamat" wire:model="masterForm.address" :error="$errors->first('masterForm.address')" :readonly="$isReadonly" />

        <x-input label="Tanggal Lahir" type="date" wire:model="masterForm.birth_date" :error="$errors->first('masterForm.birth_date')"
          :readonly="$isReadonly" />

        <x-select label="Jenis Kelamin" wire:model="masterForm.gender" :options="[['id' => 'male', 'name' => 'Laki-laki'], ['id' => 'female', 'name' => 'Perempuan']]" placeholder="Pilih"
          :readonly="$isReadonly" :error="$errors->first('masterForm.gender')" />

        <x-input label="Status Pernikahan" wire:model="masterForm.marital_status" :error="$errors->first('masterForm.marital_status')"
          :readonly="$isReadonly" />

        {{-- KPI (READONLY VIEW) --}}
        @if ($isReadonly)
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
        @endif


        {{-- ACTIONS --}}
        <x-slot:actions>
          <x-button label="Batal" link="{{ route('user.index') }}" />

          @if (!$isReadonly)
            <x-button label="{{ $isEditMode ? 'Update' : 'Simpan' }}" class="btn-primary" type="submit"
              spinner="{{ $isEditMode ? 'update' : 'store' }}" />
          @endif
        </x-slot:actions>



      </x-form>

      @if (session()->has('message'))
        <div class="alert alert-success mt-3">
          {{ session('message') }}
        </div>
      @endif
    </div>
  </div>
</div>
