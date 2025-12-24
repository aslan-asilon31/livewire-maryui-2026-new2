<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SalesIndex extends Component
{
    use Toast;

    /**
     * Gabungan data temporary + database
     */
    public array $details = [];

    // input produk baru
    public string $newProductName = '';
    public int $newQuantity = 1;
    public int $newUnitPrice = 0;

    // autocomplete
    public bool $showDropdown = false;
    public array $suggestions = [];

    public function mount(): void
    {
        $this->loadDetails();
    }

    /**
     * Load details dari database untuk tampilan awal
     */
    public function loadDetails(): void
    {
        $salesDetails = DB::table('sales_products')
            ->join('products', 'sales_products.product_id', '=', 'products.id')
            ->select(
                'sales_products.id as detail_id',
                'products.id as product_id',
                'products.name',
                'sales_products.quantity',
                'sales_products.unit_price',
                'sales_products.amount',
                'sales_products.tax'
            )
            ->get()
            ->map(fn($item) => (array)$item)
            ->toArray();

        $this->details = array_values($salesDetails);
    }

    /**
     * Simpan semua produk temporary ke database
     */
    public function saveAll(): void
    {
        DB::transaction(function () {
            // buat sale baru hanya untuk produk baru
            $sale = Sale::create([
                'sale_date' => now(),
                'invoice_number' => 'INV-' . now()->format('YmdHis'),
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($this->details as $index => $item) {
                // -----------------------------
                // ITEM BARU (temporary)
                // -----------------------------
                if (empty($item['product_id'])) {
                    $product = Product::firstOrCreate(
                        ['name' => $item['name']],
                        ['default_price' => $item['unit_price'], 'tax' => $item['tax']]
                    );

                    $item['product_id'] = $product->id;
                    $this->details[$index]['product_id'] = $product->id;

                    DB::table('sales_products')->insert([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $item['amount'],
                        'tax' => $item['tax'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalAmount += $item['amount'];
                }

                // -----------------------------
                // ITEM EXISTING (sudah di DB)
                // -----------------------------
                elseif (!empty($item['detail_id'])) {
                    // update pivot table
                    DB::table('sales_products')
                        ->where('id', $item['detail_id'])
                        ->update([
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'amount' => $item['amount'],
                            'tax' => $item['tax'],
                            'updated_at' => now(),
                        ]);

                    // update product jika nama atau default_price berubah
                    DB::table('products')
                        ->where('id', $item['product_id'])
                        ->update([
                            'name' => $item['name'],
                            'default_price' => $item['unit_price'],
                            'updated_at' => now(),
                        ]);

                    $totalAmount += $item['amount'];
                }
            }

            // update total amount sale (hanya untuk sale baru)
            $sale->update(['total_amount' => $totalAmount]);
        });

        $this->success('Semua data berhasil disimpan ke database!');

        // reload data dari database
        $this->loadDetails();

        // reset input temporary
        $this->resetInput();
    }


    // ---------------------------
    // OPERASI DETAIL (temporary)
    // ---------------------------
    public function updatedNewProductName(string $value): void
    {
        $this->showDropdown = true;

        $this->suggestions = collect($this->allProducts)
            ->filter(fn($item) => stripos($item, $value) !== false)
            ->values()
            ->all();
    }

    public function selectSuggestion(string $name): void
    {
        $this->newProductName = $name;
        $this->showDropdown = false;
    }

    public function updateProductName(int $index): void
    {
        $this->success('Nama produk diperbarui.'); // Optional, bisa hapus
    }


    public function addProduct(): void
    {
        if (trim($this->newProductName) === '') return;

        $this->details[] = [
            'detail_id' => null,
            'product_id' => null,
            'name' => $this->newProductName,
            'quantity' => $this->newQuantity,
            'unit_price' => $this->newUnitPrice,
            'tax' => 12,
            'amount' => $this->newQuantity * $this->newUnitPrice,
        ];

        $this->resetInput();
        $this->success('Produk ditambahkan (temporary).');
    }

    public function updateQuantity(int $index, int $value): void
    {
        $this->details[$index]['quantity'] = $value;
        $this->recalculateAmount($index);
        $this->success('Quantity diperbarui.');
    }

    public function updateUnitPrice(int $index, int $value): void
    {
        $this->details[$index]['unit_price'] = $value;
        $this->recalculateAmount($index);
        $this->success('Harga diperbarui.');
    }

    private function recalculateAmount(int $index): void
    {
        $this->details[$index]['amount'] =
            $this->details[$index]['quantity'] *
            $this->details[$index]['unit_price'];
    }

    public function deleteProduct(int $index): void
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);
        $this->success('Produk dihapus.');
    }

    private function resetInput(): void
    {
        $this->newProductName = '';
        $this->newQuantity = 1;
        $this->newUnitPrice = 0;
        $this->showDropdown = false;
        $this->suggestions = [];
    }

    // semua nama produk (autocomplete)
    public function getAllProductsProperty(): array
    {
        return collect($this->details)
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.sales.sales-index');
    }
}
