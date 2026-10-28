<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public ?string $title;
    public ?string $description;
    public string $theme;
    public bool $hideNav;

    public function __construct(?string $title = null, ?string $description = null, string $theme = 'dark', bool $hideNav = false)
    {
        $this->title = $title;
        $this->description = $description;
        $this->theme = $theme;
        $this->hideNav = $hideNav;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest', [
            'description' => $this->description,
            'theme' => $this->theme,
            'hideNav' => $this->hideNav,
        ]);
    }
}
