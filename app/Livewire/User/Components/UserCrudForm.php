<?php

namespace App\Livewire\User\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Livewire\User\Forms\UserForm;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\Support\Facades\DB;
use App\Models\UserDetail;

class UserCrudForm extends Component
{
  // public string $title = 'User';

  public string $title = 'User Management';
  public string $url = '/users';

  public bool $isEditMode = false;
  public bool $isReadonly = false;

  public UserForm $masterForm;

  public array $kpiChart = [];        // labels, scores, weights
  public array $kpiTrendChart = [];   // labels, scores

  public function mount(?int $id = null): void
  {

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

    // VALIDASI
    $validated = $this->masterForm->validate(
      $this->masterForm->rules()
    );

    DB::beginTransaction();

    try {
      // ======================
      // USERS
      // ======================
      $queueNumber = $validated['queue_number']
        ?? ((int) (User::max('queue_number') ?? 0) + 1);

      $user = User::create([
        'name'         => $validated['name'],
        'email'        => $validated['email'],
        'password'     => Hash::make($validated['password']),
        'is_activated' => (int) ($validated['is_activated'] ?? 1),
        'queue_number' => $queueNumber,
      ]);

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

      DB::commit();

      session()->flash('message', 'User berhasil dibuat');
      return redirect()->route('user.index');
    } catch (\Throwable $th) {
      DB::rollBack();

      report($th);

      session()->flash(
        'error',
        'Gagal membuat user. ' . $th->getMessage()
      );

      return redirect()->back();
    }
  }



  // =========================
  // UPDATE = EDIT ONLY
  // =========================
  public function update()
  {
    if ($this->isReadonly) {
      return redirect()->route('user.index');
    }

    $id = (int) ($this->masterForm->id ?? 0);
    if ($id <= 0) {
      abort(400, 'User ID tidak valid untuk update.');
    }

    // VALIDASI
    $validated = $this->masterForm->validate(
      $this->masterForm->rules($id)
    );

    DB::beginTransaction();

    try {
      // ======================
      // USERS
      // ======================
      $user = User::findOrFail($id);

      $user->update([
        'name'         => $validated['name'],
        'email'        => $validated['email'],
        'password'     => !empty($validated['password'])
          ? Hash::make($validated['password'])
          : $user->password,
        'is_activated' => (int) ($validated['is_activated'] ?? 1),
        'queue_number' => $validated['queue_number']
          ?? $user->queue_number,
      ]);

      // ======================
      // USER DETAILS
      // ======================
      $user->detail()->updateOrCreate(
        ['user_id' => $user->id],
        [
          'phone'          => $validated['phone'] ?? null,
          'address'        => $validated['address'] ?? null,
          'birth_date'     => $validated['birth_date'] ?? null,
          'gender'         => $validated['gender'] ?? null,
          'marital_status' => $validated['marital_status'] ?? null,
        ]
      );

      DB::commit();

      session()->flash('message', 'User berhasil diperbarui');
      return redirect()->route('user.index');
    } catch (\Throwable $th) {
      DB::rollBack();

      report($th);

      session()->flash(
        'error',
        'Gagal memperbarui user. ' . $th->getMessage()
      );

      return redirect()->back();
    }
  }



  public function render()
  {

    return view('livewire.user.components.user-crud-form')
      ->title($this->title);
  }
}
