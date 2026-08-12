<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $uploads = Upload::query()
            ->withCount('products')
            ->latest()
            ->paginate(10);

        return view('dashboard.index', compact('uploads'));
    }

    public function show(Upload $upload): View
    {
        $products = $upload->products()
            ->orderBy('row_number')
            ->paginate(25);

        $errorLogs = $upload->errorLogs()
            ->latest()
            ->limit(50)
            ->get();

        return view('dashboard.show', compact('upload', 'products', 'errorLogs'));
    }
}
