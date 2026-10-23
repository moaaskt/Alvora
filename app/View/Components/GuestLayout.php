<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public ?string $title;
    public string $theme;

    public function __construct(?string $title = null, string $theme = 'dark')
    {
        $this->title = $title;
        $this->theme = $theme;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest', [
            'theme' => $this->theme,
        ]);
    }
}
