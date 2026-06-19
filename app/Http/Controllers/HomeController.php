<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\GalleryItem;
use App\Models\TimeSlot;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $branches = Branch::where('active', true)
            ->orderBy('name')
            ->get();

        $gallery = GalleryItem::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Есть ли свободные окна для записи на сегодня (тот же критерий, что и в BookingWizard).
        $hasSlotsToday = TimeSlot::query()
            ->bookable()
            ->whereDate('starts_at', today())
            ->exists();

        return view('home', compact('branches', 'gallery', 'hasSlotsToday'));
    }

    public function booking(): View
    {
        return view('booking.index');
    }
}
