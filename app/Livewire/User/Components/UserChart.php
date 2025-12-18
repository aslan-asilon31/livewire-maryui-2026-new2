<?php

namespace App\Livewire\User\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Livewire\User\Forms\UserForm;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Support\Facades\DB;
use App\Models\UserDetail;

class UserChart extends Component
{
  // public string $title = 'User';

  public string $title = '';
  public string $url = '/users';

  public bool $isEditMode = false;
  public bool $isReadonly = false;

  public UserForm $masterForm;

  // SAFE for Livewire (arrays only)
  public array $kpiChart = [];        // labels, scores, weights
  public array $kpiTrendChart = [];   // labels, scores

  public function mount(?int $id = null): void
  {
    if (!$id) {
      $lastQueue = (int) (User::max('queue_number') ?? 0);
      $this->masterForm->queue_number = $lastQueue + 1;

      return;
    }

    $user = User::query()
      ->with([
        'detail',
        'kpiPeriods.kpi',
        'kpiPeriods.factorScores.kpiFactor',
      ])
      ->findOrFail($id);

    // ===== users =====
    $this->masterForm->id = $user->id;
    $this->masterForm->name = $user->name;
    $this->masterForm->email = $user->email;
    $this->masterForm->is_activated = (int) $user->is_activated;
    $this->masterForm->queue_number = $user->queue_number;
    $this->masterForm->email_verified_at = $user->email_verified_at?->toDateTimeString();

    // ===== user_details =====
    $this->masterForm->phone = $user->detail?->phone;
    $this->masterForm->address = $user->detail?->address;
    $this->masterForm->birth_date = $user->detail?->birth_date?->toDateString();
    $this->masterForm->gender = $user->detail?->gender;
    $this->masterForm->marital_status = $user->detail?->marital_status;

    // ===== KPI latest period =====
    $latestPeriod = $user->kpiPeriods
      ->sortByDesc(fn($p) => sprintf('%04d-%02d-%01d', $p->year, $p->month ?? 0, $p->quarter ?? 0))
      ->first();

    if ($latestPeriod) {
      $this->masterForm->kpi_id = (string) $latestPeriod->kpi_id;
      $this->masterForm->kpi_name = $latestPeriod->kpi?->name;
      $this->masterForm->description = $latestPeriod->kpi?->description;

      $this->masterForm->year = (string) $latestPeriod->year;
      $this->masterForm->month = $latestPeriod->month ? (string) $latestPeriod->month : null;
      $this->masterForm->quarter = $latestPeriod->quarter ? (string) $latestPeriod->quarter : null;
      $this->masterForm->final_score = (string) $latestPeriod->final_score;

      $factorRows = $latestPeriod->factorScores
        ->sortBy(fn($fs) => $fs->kpiFactor?->name)
        ->map(fn($fs) => [
          'label'  => $fs->kpiFactor?->name ?? 'Unknown',
          'score'  => (float) $fs->score,
          'weight' => (float) ($fs->kpiFactor?->weight ?? 0),
        ])
        ->values();

      $this->kpiChart = [
        'labels'  => $factorRows->pluck('label')->all(),
        'scores'  => $factorRows->pluck('score')->all(),
        'weights' => $factorRows->pluck('weight')->all(),
      ];
    }

    // ===== LINE trend final_score (6 terakhir) =====
    $periods = $user->kpiPeriods
      ->sortBy(fn($p) => sprintf('%04d-%02d-%01d', $p->year, $p->month ?? 0, $p->quarter ?? 0))
      ->take(-6)
      ->values();

    $trendLabels = $periods->map(function ($p) {
      if (!empty($p->month)) return sprintf('%02d/%04d', $p->month, $p->year);
      if (!empty($p->quarter)) return 'Q' . $p->quarter . ' ' . $p->year;
      return (string) $p->year;
    })->all();

    $trendScores = $periods->map(fn($p) => (float) $p->final_score)->all();

    $this->kpiTrendChart = [
      'labels' => $trendLabels,
      'scores' => $trendScores,
    ];

    // show mode
    $this->isEditMode = true;
    $this->title = 'Detail User';
  }

  private function buildCharts(): array
  {
    if (!$this->isReadonly || empty($this->kpiChart['labels'])) {
      return [
        'kpiFactorBarChart' => null,
        'kpiWeightDoughnutChart' => null,
        'kpiFinalLineChart' => null,
      ];
    }

    $uid = $this->masterForm->id ?? 'x';

    $kpiFactorBarChart = Chartjs::build()
      ->name("KpiFactorBarChart_$uid")
      ->type("bar")
      ->size(["width" => 900, "height" => 300])
      ->labels($this->kpiChart['labels'])
      ->datasets([
        [
          "label" => "Skor per Faktor (0–100)",
          "data" => $this->kpiChart['scores'],
          "borderWidth" => 1,
          "borderRadius" => 6,
        ]
      ])
      ->options([
        "responsive" => true,
        "maintainAspectRatio" => false,
        "indexAxis" => "y",
        "scales" => [
          "x" => [
            "min" => 0,
            "max" => 100,
            "ticks" => ["stepSize" => 10],
          ],
        ],
        "plugins" => [
          "legend" => ["display" => true],
          "title" => ["display" => true, "text" => "Skor per Faktor"],
        ],
      ]);

    $kpiWeightDoughnutChart = Chartjs::build()
      ->name("KpiWeightDoughnutChart_$uid")
      ->type("doughnut")
      ->size(["width" => 900, "height" => 300])
      ->labels($this->kpiChart['labels'])
      ->datasets([
        [
          "label" => "Bobot (%)",
          "data" => $this->kpiChart['weights'],
          "borderWidth" => 1,
        ]
      ])
      ->options([
        "responsive" => true,
        "maintainAspectRatio" => false,
        "plugins" => [
          "legend" => ["position" => "bottom"],
          "title" => ["display" => true, "text" => "Komposisi Bobot Faktor"],
        ],
        "cutout" => "60%",
      ]);

    $kpiFinalLineChart = Chartjs::build()
      ->name("KpiFinalLineChart_$uid")
      ->type("line")
      ->size(["width" => 900, "height" => 260])
      ->labels($this->kpiTrendChart['labels'] ?? [])
      ->datasets([
        [
          "label" => "Skor Akhir KPI (0–100)",
          "data" => $this->kpiTrendChart['scores'] ?? [],
          "tension" => 0.35,
          "borderWidth" => 2,
          "pointRadius" => 4,
          "pointHoverRadius" => 6,
          "fill" => false,
        ]
      ])
      ->options([
        "responsive" => true,
        "maintainAspectRatio" => false,
        "scales" => [
          "y" => [
            "min" => 0,
            "max" => 100,
            "ticks" => ["stepSize" => 10],
          ],
        ],
        "plugins" => [
          "legend" => ["display" => true],
          "title" => ["display" => true, "text" => "Tren Skor Akhir KPI (6 Periode Terakhir)"],
        ],
      ]);

    return compact('kpiFactorBarChart', 'kpiWeightDoughnutChart', 'kpiFinalLineChart');
  }

  public function toggleActive(): void
  {
    $user = User::findOrFail($this->masterForm->id);

    $user->update([
      'is_activated' => !$user->is_activated
    ]);

    $this->masterForm->is_activated = (int) $user->is_activated;
  }

  // =========================
  // STORE = CREATE ONLY
  // =========================
  public function store()
  {
    if ($this->isReadonly) {
      return redirect()->route('user.index');
    }

    $this->isEditMode = false;

    if (empty($this->masterForm->id)) {
      $this->masterForm->id = random_int(1, 500);
    }

    $validated = $this->masterForm->validate(
      $this->masterForm->rules($this->masterForm->id)
    );

    if (!$this->masterForm->queue_number) {
      $lastQueue = (int) (User::max('queue_number') ?? 0);
      $this->masterForm->queue_number = $lastQueue + 1;
    }


    // dd($validated);
    // 🔒 TRANSAKSI BIAR AMAN
    DB::transaction(function () use ($validated) {

      // ======================
      // USERS
      // ======================
      $user = User::create([
        'name'         => $validated['name'],
        'email'        => $validated['email'],
        'password'     => Hash::make($validated['password']),
        'is_activated' => (int) ($validated['is_activated'] ?? 1),
        'queue_number' => $validated['queue_number']
          ?? ((User::max('queue_number') ?? 0) + 1),
      ]);

      // ✅ SET ID SETELAH CREATE (INI BARU BENAR)

      // ======================
      // USER DETAILS
      // ======================
      $user->detail()->create([
        'phone'          => $validated['phone'] ?? null,
        'address'        => $validated['address'] ?? null,
        'birth_date'     => $validated['birth_date'] ?? null,
        'gender'         => $validated['gender'] ?? null,
        'marital_status' => $validated['marital_status'] ?? null,
      ]);
    });

    session()->flash('message', 'User berhasil dibuat');

    return redirect()->route('user.index');
  }


  // =========================
  // UPDATE = EDIT ONLY
  // =========================
  public function update()
  {
    if ($this->isReadonly) return redirect()->route('user.index');

    $id = (int) ($this->masterForm->id ?? 0);
    if ($id <= 0) {
      abort(400, 'User id tidak ada untuk update.');
    }

    $validatedForm = $this->masterForm->validate(
      $this->masterForm->rules($id)
    );


    if (!$this->masterForm->queue_number) {
      $lastQueue = (int) (User::max('queue_number') ?? 0);
      $this->masterForm->queue_number = $lastQueue + 1;
    }

    DB::transaction(function () use ($validatedForm, $id) {
      $user = User::query()->with('detail')->findOrFail($id);

      $userPayload = [
        'name'         => $validatedForm['name'],
        'email'        => $validatedForm['email'],
        'is_activated' => (int) ($validatedForm['is_activated'] ?? $user->is_activated),
        'queue_number' => $validatedForm['queue_number'] ?? $user->queue_number,
      ];

      // password optional
      if (!empty($validatedForm['password'])) {
        $userPayload['password'] = bcrypt($validatedForm['password']);
      }

      $user->update($userPayload);

      // DETAIL payload
      $detailPayload = [
        'phone'          => $validatedForm['phone'] ?? null,
        'address'        => $validatedForm['address'] ?? null,
        'birth_date'     => $validatedForm['birth_date'] ?? null,
        'gender'         => $validatedForm['gender'] ?? null,
        'marital_status' => $validatedForm['marital_status'] ?? null,
      ];

      $user->detail()->updateOrCreate(
        ['user_id' => $user->id],
        $detailPayload
      );
    });

    // session()->flash('message', 'Data berhasil diupdate');
    return redirect()->route('user.index');
  }

  public function render()
  {
    $charts = $this->buildCharts();

    return view('livewire.user.components.user-chart', $charts)
      ->title($this->title);
  }
}
