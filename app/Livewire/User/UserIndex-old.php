<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use function PHPUnit\Framework\isEmpty;
use Livewire\WithFileUploads;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\Storage;
use App\Livewire\User\Forms\UserFilterForm;
use App\Livewire\User\Forms\UserValidatedFilterForm;


class UserIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $title = 'User Management';
    public string $url = '/users';
    public $file;
    public string $search = '';
    public bool $filterDrawer = false;
    public bool $drawerImport = false;
    public bool $drawerExport = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];


    public UserFilterForm $filterForm;

    public function filter(): void
    {
        $validatedData = $this->validate(
            [
                'filterForm.name' => 'nullable|string|min:5',
                'filterForm.email' => 'nullable|string|min:5',
                'filterForm.is_activated' => 'nullable',
                'filterForm.queue_number' => 'nullable',
            ],
        );
        $this->validatedFilterForm =  $validatedData['filterForm'];
        $this->resetPage();
        $this->filterDrawer = false;
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        Excel::import(new UsersImport, $this->file);

        session()->flash('message', 'User berhasil diimport');
        $this->reset('file');
    }


    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function toggleActive(int $userId): void
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($userId);

            $old = $user->is_activated;
            if ($old == 1) {
                $new = 0;
            } else {
                $new = 1;
            }
            $user->update([
                'is_activated' => $new,
            ]);


            DB::commit();

            $this->refresh();
            $this->resetPage(); // reset pagination, sehingga getRowsProperty dipanggil ulang

        } catch (\Throwable $e) {
            DB::rollBack();
        }
    }

    public array $validatedFilterForm = [
        'name' => null,
        'email' => null,
        'is_activated' => null,
        'queue_number' => null,
    ];

    public function clear(): void
    {
        $this->filterForm->reset();

        $this->validatedFilterForm = [
            'name' => null,
            'email' => null,
            'is_activated' => null,
            'queue_number' => null,
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
        ['key' => 'id', 'isAvailable' => true, 'label' => '#', 'sortable' => true,  'class' => 'border'],
        ['key' => 'avatar', 'label' => 'Avatar',  'class' => 'border'],
        ['key' => 'name', 'label' => 'Name', 'sortable' => true,  'class' => 'border'],
        ['key' => 'position', 'label' => 'Jabatan', 'sortable' => true, 'class' => 'border'],
        ['key' => 'email', 'label' => 'Email', 'sortable' => true,  'class' => 'border'],
        ['key' => 'queue_number', 'label' => 'Queue',  'class' => 'border'],
        ['key' => 'is_activated', 'label' => 'Active',  'class' => 'border'],
        ['key' => 'action', 'label' => 'Action',  'class' => 'border'],
    ];

    public function mount() {}

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    //
    public function getRowsProperty()
    {
        return User::query()
            ->with(['avatar', 'roles'])

            // Search global
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })

            // Filter: name
            ->when(!empty($this->validatedFilterForm['name'] ?? ''), function ($q) {
                $q->where('name', 'like', '%' . $this->validatedFilterForm['name'] . '%');
            })

            // Filter: email
            ->when(!empty($this->validatedFilterForm['email'] ?? ''), function ($q) {
                $q->where('email', 'like', '%' . $this->validatedFilterForm['email'] . '%');
            })

            // Filter: queue_number
            ->when(!empty($this->validatedFilterForm['queue_number'] ?? ''), function ($q) {
                $q->where('queue_number', 'like', '%' . $this->validatedFilterForm['queue_number'] . '%');
            })


            // Filter: is_activated (handle null / '')
            ->when($this->validatedFilterForm['is_activated'], function ($q) {
                $isActivated = filter_var($this->validatedFilterForm['is_activated'], FILTER_VALIDATE_BOOLEAN);
                $q->where('is_activated', $isActivated);
            })

            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate(10);
    }


    public function render()
    {
        return view('livewire.user.user-index');
    }
}
