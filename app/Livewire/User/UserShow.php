<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\User;

class UserShow extends Component
{
  public $title = 'User (Lihat)';
  public string $url = '/user';
  public $user;



  public function render()
  {
    return view('livewire.user.user-show', [
      'user' => $this->user,
    ])->title($this->title);
  }

  #[\Livewire\Attributes\Locked]
  public string $id = '';

  #[\Livewire\Attributes\Locked]
  public bool $isReadonly = true;

  public function mount() {}
}
