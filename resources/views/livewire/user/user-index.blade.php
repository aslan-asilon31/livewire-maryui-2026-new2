<div>
  <x-list-menu :title="$title" :url="$url" shadow />


  {{-- Export Button --}}
  {{-- <div class="my-3"> --}}

  {{-- <small class="text-muted d-block mt-1">
      Hanya bisa mengekspor file Excel (.xlsx)
    </small> --}}
  {{-- </div> --}}

  <div class="my-3">
    {{-- Export Button --}}
    <x-button label="Export(Excel)" @click="$wire.drawerExport = true" responsive icon="o-arrow-up" class="btn-primary" />

    {{-- Import Button --}}
    <x-button label="Import(Excel)" @click="$wire.drawerImport = true" responsive icon="o-arrow-down"
      class="btn-secondary" />

  </div>


  {{-- Table --}}
  <x-table :headers="$headers" :rows="$this->rows" :sort-by="$sortBy" with-pagination show-empty-text>

    @scope('cell_id', $row)
      <div class="cursor-pointer" ondblclick="window.location='{{ route('user.edit', $row->id) }}'"
        style="text-decoration: underline; color: inherit;">
        {{ $row->id }}
      </div>
    @endscope

    @scope('cell_position', $row)
      {{ $row->getRoleNames()->first() ?? '-' }}
    @endscope


    @scope('cell_avatar', $row)
      @php
        $avatarUrl = $row->avatar?->file_url ? str_replace('\\', '/', ltrim($row->avatar->file_url, '\\/')) : null;
      @endphp

      <div class="d-flex justify-content-center">
        @if ($avatarUrl)
          <img src="{{ asset($avatarUrl) }}" alt="Avatar" class="rounded-circle"
            style="width:40px;height:40px;object-fit:cover;">
        @else
          <img
            src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/No_image_available.svg/500px-No_image_available.svg.png?20251111182856"
            alt="Avatar" class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
        @endif
      </div>
    @endscope

    @scope('cell_is_activated', $row)
      <x-toggle :checked="$row->is_activated === 1" wire:click="toggleActive({{ $row->id }})" wire:loading.attr="disabled"
        wire:target="toggleActive" :class="$row->is_activated ? 'bg-green-300' : 'bg-red-300'" />
    @endscope



    @scope('cell_action', $row)
      <x-dropdown>
        <x-menu-item title="Show" icon="o-eye" link="{{ route('user.show', $row->id) }}" />
        <x-menu-item title="Edit" icon="o-pencil-square" link="{{ route('user.edit', $row->id) }}" />
        <x-menu-item title="Delete" icon="o-trash" class="text-red-300"
          onclick="if (confirm('Yakin hapus user {{ $row->name }}?')) {
        Livewire.find('{{ $this->getId() }}').call('delete', {{ $row->id }})
      }" />
      </x-dropdown>
    @endscope
  </x-table>

  {{-- Filter Drawer --}}
  <x-drawer wire:model="filterDrawer" class="w-11/12 lg:w-1/3" title="Filter User" right separator with-close-button>

    <x-form wire:submit="filter">

      <x-input label="Name" wire:model="filterForm.name" />
      <x-input label="Email" wire:model="filterForm.email" />

      <x-select label="Is Activated" wire:model="filterForm.is_activated" :options="[['id' => 'true', 'name' => 'Yes'], ['id' => 'false', 'name' => 'No']]"
        placeholder="- Is Activated -" placeholder-value="" />

      <x-slot:actions>
        <x-button label="Filter" class="btn-primary" type="submit" />
        <x-button label="Clear" wire:click="clear" />
      </x-slot:actions>

    </x-form>
  </x-drawer>

  {{-- Export Drawer --}}
  <x-drawer wire:model="drawerExport" class="w-11/12 lg:w-1/3" title="Export Excel" right separator with-close-button>
    <p class="text-sm text-gray-500 mb-3">
      Hanya bisa mengekspor file Excel (.xlsx).
    </p>
    <x-button label="Export" wire:click="export" class="btn-success" />
  </x-drawer>

  {{-- Import Drawer --}}
  <x-drawer wire:model="drawerImport" class="w-11/12 lg:w-1/3" title="Import Excel" right separator with-close-button>
    <p class="text-sm text-gray-500 mb-3">
      Hanya bisa mengimpor file Excel (.xlsx). Pastikan format kolom sesuai template.
    </p>

    <x-form wire:submit.prevent="import">
      <x-input type="file" label="Pilih file Excel" wire:model="file" />
      @error('file')
        <span class="text-danger">{{ $message }}</span>
      @enderror

      <x-slot:actions>
        <x-button label="Import" class="btn-primary" type="submit" />
      </x-slot:actions>
    </x-form>
  </x-drawer>
</div>
