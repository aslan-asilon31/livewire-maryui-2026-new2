<x-header title="{{ $title }}" subtitle="" separator>
  <x-slot:actions>
    {{-- Search --}}
    <div class="my-2">
      <x-input placeholder="Search name / email..." wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
        clearable />
    </div>
    @if (empty($id))
      @if ('/' . request()->path() == $url . '/create')
      @else
        @if (request()->path() != $url)
          <x-button icon-right="o-plus-circle" label="Create" link="{{ $url . '/create' }}"
            class=" btn-ghost btn-outline" />
        @endif
      @endif
    @else
      <x-button icon-right="o-list-bullet" label="{{ $title }} list" link="{{ $url }}"
        class=" btn-ghost btn-outline" />
      <x-button icon-right="o-trash" wire:click="delete" wire:confirm="Yakin hapus data?" label="Delete"
        class=" btn-error btn-outline" />
    @endif

    <x-button label="Filters" @click="$wire.filterDrawer = true" responsive icon="o-funnel" class="btn-primary" />

  </x-slot:actions>
</x-header>
