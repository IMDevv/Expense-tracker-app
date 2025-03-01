<?php

namespace App\Livewire;

use Livewire\Component;

abstract class AppComponent extends Component
{
    protected function layout(): string
    {
        return 'layouts.app';
    }

    abstract protected function view(): string;

    public function render()
    {
        return view($this->view())
            ->layout($this->layout());
    }
} 