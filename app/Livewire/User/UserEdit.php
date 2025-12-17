<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\User;

class UserEdit extends Component
{
  public $title = 'User (Edit)';
  public $id;
  // public $name, $bukti_penerimaan_id, $created_by, $updated_by, $is_activated;
  public string $url = '/user';

  public function render()
  {
    return view('livewire.user.user-edit')->title($this->title);
  }

  public function mount()
  {
    $userQuery = User::query()
      // ->with(['buktiPenerimaan.details', 'details'])
      ->where('id', $this->id)
      ->first();
  }





  #[\Livewire\Attributes\Locked]
  public bool $isReadonly = false;
}
