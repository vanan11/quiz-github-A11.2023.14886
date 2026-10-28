<?php

namespace App\View\Components;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class LoncengNotifikasi extends Component
{
    /** @var Collection<int, DatabaseNotification> */
    public Collection $notifikasis;

    public int $jumlahBelumDibaca = 0;

    public function __construct()
    {
        $this->notifikasis = new Collection;

        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $this->jumlahBelumDibaca = $user
            ->unreadNotifications()
            ->count();

        $this->notifikasis = $user
            ->notifications()
            ->reorder()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    public function render(): View
    {
        return view('components.lonceng-notifikasi');
    }
}