<?php

namespace App\Livewire\User;

use App\Models\User;
use Livewire\Component;

class UserCreate extends Component
{

  public string $url = '/user';

  public bool $isEditMode = false;
  public bool $isReadonly = false;

  public function mount() {}


  public function render()
  {
    return view('livewire.user.user-create')
      ->title($this->title);
  }

  public string $title = 'User (Buat Baru)';
  public string $baseUrl = '/user';
}
