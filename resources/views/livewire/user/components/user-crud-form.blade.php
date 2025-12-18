<div>
  <div class="card">
    <div class="card-header">
      {{-- optional title --}}
    </div>

    <div class="card-body">
      @if (session()->has('message'))
        <div class="alert alert-success">
          {{ session('message') }}
        </div>
      @endif

      <x-form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">


        {{-- USER --}}
        <x-input label="Nama" wire:model="masterForm.name" :error="$errors->first('masterForm.name')" :readonly="$isReadonly" />

        <x-input label="Email" type="email" wire:model="masterForm.email" :error="$errors->first('masterForm.email')" :readonly="$isReadonly" />

        <x-select label="Status" wire:model="masterForm.is_activated" :options="[['id' => 1, 'name' => 'Aktif'], ['id' => 0, 'name' => 'Non Aktif']]" placeholder="Pilih Status"
          :readonly="$isReadonly" :error="$errors->first('masterForm.is_activated')" />

        <x-input label="Nomor Antrian" type="number" wire:model="masterForm.queue_number" :error="$errors->first('masterForm.queue_number')"
          :readonly="true" />

        {{-- USER DETAILS --}}
        <hr>

        <x-input label="Nomor Telepon" wire:model="masterForm.phone" :error="$errors->first('masterForm.phone')" :readonly="$isReadonly" />

        <x-input label="Alamat" wire:model="masterForm.address" :error="$errors->first('masterForm.address')" :readonly="$isReadonly" />

        <x-input label="Tanggal Lahir" type="date" wire:model="masterForm.birth_date" :error="$errors->first('masterForm.birth_date')"
          :readonly="$isReadonly" />

        <x-select label="Jenis Kelamin" wire:model="masterForm.gender" :options="[['id' => 'male', 'name' => 'Laki-laki'], ['id' => 'female', 'name' => 'Perempuan']]" placeholder="Pilih"
          :readonly="$isReadonly" :error="$errors->first('masterForm.gender')" />

        <x-input label="Status Pernikahan" wire:model="masterForm.marital_status" :error="$errors->first('masterForm.marital_status')"
          :readonly="$isReadonly" />

        {{-- KPI (READONLY VIEW) --}}
        @if ($isReadonly)
          <livewire:user.components.user-chart />
        @endif


        {{-- ACTIONS --}}
        <x-slot:actions>
          <x-button label="Batal" link="{{ route('user.index') }}" />

          @if (!$isReadonly)
            <x-button label="{{ $isEditMode ? 'Update' : 'Simpan' }}" class="btn-primary" type="submit"
              spinner="{{ $isEditMode ? 'update' : 'store' }}" />
          @endif
        </x-slot:actions>



      </x-form>

      @if (session()->has('message'))
        <div class="alert alert-success mt-3">
          {{ session('message') }}
        </div>
      @endif
    </div>
  </div>
</div>
