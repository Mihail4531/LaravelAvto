<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;

class CategoryList extends Component
{
    public function render()
    {
        $categories = Category::where('active', true)
            ->orderBy('sort_order')
            ->with(['services' => fn ($q) => $q->where('active', true)->orderBy('sort_order')])
            ->get();

        return view('livewire.category-list', compact('categories'));
    }
}
