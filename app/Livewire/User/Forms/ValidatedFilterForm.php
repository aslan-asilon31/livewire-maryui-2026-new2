<?php

namespace App\Livewire\User\Forms;

use Livewire\Form;

class ValidatedFilterForm extends Form
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


    ];
  }
}
