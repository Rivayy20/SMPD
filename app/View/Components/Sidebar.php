<?php

namespace App\View\Components;

use App\Support\Navigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class Sidebar extends Component
{
    /**
     * Grup menu yang sudah disaring berdasarkan role user.
     *
     * @var array<int, array{heading: string, items: array<int, array<string, mixed>>}>
     */
    public array $groups;

    public function __construct()
    {
        $this->groups = Navigation::sidebar(Auth::user());
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('components.sidebar');
    }
}
