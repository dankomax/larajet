<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Item;
use Livewire\WithPagination;

class Items extends Component
{
    use WithPagination;

    public $active;
    public $q;
    public $sortBy = 'id';
    public $sortAsc = true;
    public $item;

    public $confirmingItemDeletion = false;
    public $confirmingItemAdd = false;

    protected $queryString = [
        'active' => ['except' => false],
        'q' => ['except' => ''],
        'sortBy' => ['except' => 'id'],
        'sortAsc' => ['except' => true],
    ];

    protected $rules = [
        'item.name' => 'required|string|min:4',
        'item.price' => 'required|numeric|between:1,100',
        'item.status' => 'boolean',
    ];

    public function render()
    {
        $items = Item::where('user_id', auth()->user()->id)
            ->when($this->q, function($query) {
                return $query->where(function($query) {
                    $query->where('name', 'like', '%' . $this->q . '%')
                        ->orWhere('price', 'like', '%' . $this->q . '%');
                });
            })
            ->when($this->active, function($query) {
                return $query->active();
                // return $query->where('status', 1);
            })
            ->orderBy($this->sortBy, $this->sortAsc ? 'ASC' : 'DESC');
        // $query = $items->toSql();
        $items = $items->paginate(7);

        return view('livewire.items', [
            'items' => $items,
            // 'query' => $query
        ]);
    }

    public function updatingActive()
    {
        $this->resetPage();
    }

    public function updatingQ()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($field == $this->sortBy) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortBy = $field;
    }

    // public function confirmItemDeletion(Item $item)
    public function confirmItemDeletion($id)
    {
        // $item->delete();
        $this->confirmingItemDeletion = $id;
    }

    public function deleteItem(Item $item)
    {
        $item->delete();
        $this->confirmingItemDeletion = false;
        session()->flash('message', 'Item Deleted Successfully');
    }

    public function confirmItemAdd()
    {
        $this->reset(['item']);
        $this->confirmingItemAdd = true;
    }

    public function saveItem()
    {
        $this->validate();

        if (isset($this->item->id)) {
            $this->item->save();
            session()->flash('message', 'Item Saved Successfully');
        } else {
            auth()->user()->items()->create([
                'name' => $this->item['name'],
                'price' => $this->item['price'],
                'status' => $this->item['status'] ?? 0
            ]);
            session()->flash('message', 'Item Added Successfully');
        }

        $this->confirmingItemAdd = false;
    }

    public function confirmItemEdit(Item $item)
    {
        $this->item = $item;
        $this->confirmingItemAdd = true;
    }
}
