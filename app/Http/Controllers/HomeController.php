<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\GalleryItem;
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

        return view('home', compact('branches', 'gallery'));
    }

    public function booking(): View
    {
        return view('booking.index');
    }
}
