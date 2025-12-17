<div>
  <x-list-menu :title="$title" :url="$url" shadow />

  <div class="flex gap-2 mb-3">
    <x-button label="Create User" link="{{ route('user.create') }}" class="btn-primary" />
  </div>

  {{-- Search --}}
  <div class="my-2">
    <x-input placeholder="Search name / email..." wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
      clearable />
  </div>

  {{-- Table --}}
  <x-table :headers="$headers" :rows="$this->rows" :sort-by="$sortBy" with-pagination show-empty-text>
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
      <x-badge :value="$row->is_activated ? 'Yes' : 'No'"
        class="{{ $row->is_activated ? 'badge-primary badge-soft' : 'badge-error badge-soft' }}" />
    @endscope

    @scope('cell_action', $row)
      <x-dropdown>
        <x-menu-item title="Show" icon="o-eye" link="{{ route('user.show', $row->id) }}" />
        <x-menu-item title="Edit" icon="o-pencil-square" link="{{ route('user.edit', $row->id) }}" />
        <x-menu-item title="Delete" icon="o-trash" class="text-red-600"
          onclick="if (confirm('Yakin hapus user {{ $row->name }}?')) {
        Livewire.find('{{ $this->getId() }}').call('delete', {{ $row->id }})
      }" />
      </x-dropdown>
    @endscope
  </x-table>

  {{-- Filter Drawer --}}
  <x-drawer wire:model="filterDrawer" class="w-11/12 lg:w-1/3" title="Filter User" right separator with-close-button>

    <x-form wire:submit.prevent="filter">

      <x-input label="Name" wire:model="filterForm.name" />
      <x-input label="Email" wire:model="filterForm.email" />

      <x-select label="Is Activated" wire:model="filterForm.is_activated" :options="[['id' => 1, 'name' => 'Yes'], ['id' => 0, 'name' => 'No']]"
        placeholder="- Is Activated -" placeholder-value="" />

      <x-slot:actions>
        <x-button label="Filter" class="btn-primary" type="submit" />
        <x-button label="Clear" wire:click="clear" />
      </x-slot:actions>

    </x-form>
  </x-drawer>
</div>
