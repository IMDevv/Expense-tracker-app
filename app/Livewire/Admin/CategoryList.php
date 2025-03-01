<?php

namespace App\Livewire\Admin;

use App\Livewire\AppComponent;
use App\Models\Category;
use Livewire\Attributes\Rule;

class CategoryList extends AppComponent
{
    #[Rule('required|string|unique:categories,name')]
    public $name = '';

    #[Rule('nullable|string')]
    public $description = '';

    public $editingCategory = null;

    public function createCategory()
    {
        $this->validate();

        Category::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->reset(['name', 'description']);
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Category created successfully!'
        ]);
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
    }

    protected function view(): string
    {
        return 'livewire.admin.category-list';
    }

    public function render()
    {
        return view($this->view(), [
            'categories' => Category::orderBy('name')->get()
        ])->layout($this->layout());
    }
} 