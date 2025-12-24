<div>
  <table class="table">
    <thead>
      <tr>
        <th>Product</th>
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Taxes</th>
        <th>Amount</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($details as $index => $detail)
        <tr>
          <td>
            <input type="text" wire:model="details.{{ $index }}.name"
              wire:change="updateProductName({{ $index }})" />
          </td>
          <td>
            <input type="number" min="1" step="1" wire:model.lazy="details.{{ $index }}.quantity"
              wire:change="updateQuantity({{ $index }}, $event.target.value)"
              oninput="this.value = this.value.replace(/[^0-9]/g,'')" />
          </td>
          <td>
            <input type="number" min="0" step="1" wire:model.lazy="details.{{ $index }}.unit_price"
              wire:change="updateUnitPrice({{ $index }}, $event.target.value)"
              oninput="this.value = this.value.replace(/[^0-9]/g,'')" />
          </td>
          <td>{{ $detail['tax'] }}% (Non-Luxury Good)</td>
          <td>Rp {{ number_format($detail['amount'], 2, ',', '.') }}</td>
          <td>
            <x-mary-button wire:click="deleteProduct({{ $index }})" icon="o-trash" color="danger"
              size="sm" />
          </td>
        </tr>
      @endforeach


      <!-- Input Produk Baru -->
      <tr>
        <td style="position: relative;">
          <input type="text" placeholder="Masukkan nama produk" wire:model="newProductName"
            wire:keydown.enter="addProduct" wire:focus="$set('showDropdown', true)"
            wire:blur="$set('showDropdown', false)" autocomplete="off" />

          <!-- Dropdown Suggestions -->
          @if ($showDropdown)
            <ul class="suggestions"
              style="position: absolute; bottom: 100%; left: 0; background: #fff; border: 1px solid #ccc;
                       width: 100%; z-index: 1000; list-style: none; padding: 0; margin: 0;
                       max-height: 200px; overflow-y: auto;">
              @forelse ($suggestions as $suggestion)
                <li wire:click="selectSuggestion('{{ $suggestion }}')" style="padding: 5px; cursor: pointer;">
                  {{ $suggestion }}
                </li>
              @empty
                <li style="padding: 5px; color: #999;">Tidak ada hasil</li>
              @endforelse
              <li style="padding: 5px; cursor: pointer; font-weight: bold;" wire:click="addProduct">
                + Buat baru "{{ $newProductName }}"
              </li>
            </ul>
          @endif
        </td>

        <td>
          <input type="number" min="1" wire:model.defer="newQuantity" />
        </td>
        <td>
          <input type="number" min="0" wire:model.defer="newUnitPrice" />
        </td>
        <td>12% (Default)</td>
        <td>Rp 0,00</td>
      </tr>
    </tbody>
  </table>

  <div class="mt-3">
    <x-mary-button wire:click="saveAll" color="success">
      Save All
    </x-mary-button>
  </div>

  <style>
    .suggestions li:hover {
      background-color: #f0f0f0;
    }

    .suggestions::-webkit-scrollbar {
      width: 6px;
    }

    .suggestions::-webkit-scrollbar-thumb {
      background-color: #ccc;
      border-radius: 3px;
    }
  </style>
</div>
