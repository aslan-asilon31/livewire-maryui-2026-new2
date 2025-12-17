<?php

namespace App\Livewire\User\Forms;

use Livewire\Form;

class UserForm extends Form
{

  // user table
  public ?int $id = null;
  public ?string $name = null;
  public ?string $email = null;
  public ?string $password = null;
  public ?string $email_verified_at = null;
  public ?int $is_activated = 1;
  public ?int $queue_number = null;
  public ?string $remember_token = null;
  public ?string $created_at = null;
  public ?string $updated_at = null;

  // kpi table
  public ?string $kpi_id = null;
  public ?string $kpi_name = null;
  public ?string $description = null;


  // kpi factors 
  public ?string $kpi_factor_name = null;
  public ?string $code = null;
  public ?string $kpi_factor_weight = null;
  public ?string $kpi_factor_definition = null;

  // user details
  public ?string $phone = null;
  public ?string $address = null;
  public ?string $birth_date = null;
  public ?string $gender = null;
  public ?string $marital_status = null;


  // user kpi periods
  public ?string $year = null;
  public ?string $month = null;
  public ?string $quarter = null;
  public ?string $final_score = null;

  // user kpi factor scores
  public ?string $score = null;
  public ?string $note = null;

  public function rules(?int $id = null): array
  {
    return [
      // users
      'id' => 'nullable',
      'name' => 'required|string|max:255',
      'email' => [
        'required',
        'email',
        'max:255',
        'unique:users,email' . ($id ? ',' . $id : ''),
      ],
      'password' => $id
        ? 'nullable|string|min:8'
        : 'required|string|min:8',
      'email_verified_at' => 'nullable|date',
      'is_activated' => 'required|integer|in:0,1',
      'queue_number' => 'nullable|integer|min:1',
      'remember_token' => 'nullable|string|max:100',
      'created_at' => 'nullable|date',
      'updated_at' => 'nullable|date',

      // user_details
      'phone' => 'nullable|string|max:30',
      'address' => 'nullable|string|max:255',
      'birth_date' => 'nullable|date',
      'gender' => 'nullable|in:male,female',
      'marital_status' => 'nullable|string|max:50',

      // kpis (master KPI)
      'kpi_id' => 'nullable|integer|exists:kpis,id',
      'kpi_name' => 'nullable|string|max:255',
      'description' => 'nullable|string',

      // kpi_factors (master faktor KPI)
      'kpi_factor_name' => 'nullable|string|max:255',
      'code' => 'nullable|string|max:50',
      'kpi_factor_weight' => 'nullable|numeric|min:0|max:100',
      'kpi_factor_definition' => 'nullable|string',

      // user_kpi_periods
      'year' => 'nullable|integer|min:2000|max:2100',
      'month' => 'nullable|integer|min:1|max:12',
      'quarter' => 'nullable|integer|min:1|max:4',
      'final_score' => 'nullable|numeric|min:0|max:100',

      // user_kpi_factor_scores
      'score' => 'nullable|numeric|min:0|max:100',
      'note' => 'nullable|string|max:1000',
    ];
  }


  public function attributes(): array
  {
    return [
      // users
      'name' => 'Nama',
      'email' => 'Email',
      'password' => 'Password',
      'email_verified_at' => 'Email Terverifikasi',
      'is_activated' => 'Status Aktif',
      'queue_number' => 'Nomor Antrian',
      'remember_token' => 'Remember Token',
      'created_at' => 'Dibuat Pada',
      'updated_at' => 'Diupdate Pada',

      // user_details
      'phone' => 'Nomor Telepon',
      'address' => 'Alamat',
      'birth_date' => 'Tanggal Lahir',
      'gender' => 'Jenis Kelamin',
      'marital_status' => 'Status Pernikahan',

      // kpis
      'kpi_id' => 'KPI',
      'kpi_name' => 'Nama KPI',
      'description' => 'Deskripsi KPI',

      // kpi_factors
      'kpi_factor_name' => 'Nama Faktor KPI',
      'code' => 'Kode Faktor',
      'kpi_factor_weight' => 'Bobot Faktor',
      'kpi_factor_definition' => 'Definisi Faktor',

      // periods
      'year' => 'Tahun',
      'month' => 'Bulan',
      'quarter' => 'Kuartal',
      'final_score' => 'Skor Akhir KPI',

      // factor scores
      'score' => 'Skor Faktor',
      'note' => 'Catatan',
    ];
  }
}
