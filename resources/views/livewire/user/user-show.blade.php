<div>
  <x-list-menu :title="$title" :url="$url" shadow />

  <livewire:user.components.user-crud-form :id="$id" :isReadonly="$isReadonly" />
</div>
