<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserIndex extends Component
{
    use WithPagination;

    public string $title = 'User Management';
    public string $url = '/user';

    public string $search = '';
    public bool $filterDrawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    public function filter(): void
    {
        $this->resetPage();
        $this->filterDrawer = false;
    }

    public array $filterForm = [
        'name' => null,
        'email' => null,
        'is_activated' => null,
    ];


    public function clear(): void
    {
        $this->filterForm = [
            'name' => null,
            'email' => null,
            'is_activated' => null,
        ];

        $this->search = '';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $user = User::with(['detail', 'avatar'])->findOrFail($id);

            $user->delete();
        });

        session()->flash('message', 'User berhasil dihapus');
        $this->resetPage();
    }

    public array $headers = [
        ['key' => 'id', 'label' => 'ID', 'sortable' => true],
        ['key' => 'avatar', 'label' => 'Avatar'],
        ['key' => 'name', 'label' => 'Name', 'sortable' => true],
        ['key' => 'email', 'label' => 'Email', 'sortable' => true],
        // ['key' => 'queue_number', 'label' => 'Queue'],
        ['key' => 'is_activated', 'label' => 'Active'],
        ['key' => 'action', 'label' => 'Action'],
    ];

    public function mount() {}

    public function updatedSearch(): void
    {
        $this->resetPage();
    }


    public function getRowsProperty()
    {
        return User::query()
            ->with(['avatar'])

            // Search global
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })

            // Filter: name
            ->when(!empty($this->filterForm['name']), function ($q) {
                $q->where('name', 'like', '%' . $this->filterForm['name'] . '%');
            })

            // Filter: email
            ->when(!empty($this->filterForm['email']), function ($q) {
                $q->where('email', 'like', '%' . $this->filterForm['email'] . '%');
            })

            // Filter: is_activated (handle null / '')
            ->when(
                $this->filterForm['is_activated'] !== null && $this->filterForm['is_activated'] !== '',
                fn($q) => $q->where('is_activated', (int) $this->filterForm['is_activated'])
            )

            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);
    }


    public function render()
    {
        return view('livewire.user.user-index');
    }
}
