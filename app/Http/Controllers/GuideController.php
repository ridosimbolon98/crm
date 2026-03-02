<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GuideController extends Controller
{
    public function index(): View
    {
        return view('guide.index');
    }

    public function pdf(): BinaryFileResponse
    {
        $path = base_path('docs/Panduan-Penggunaan-Sistem-CRM-Complaint.pdf');
        abort_unless(file_exists($path), 404, 'File panduan tidak ditemukan.');

        return response()->file($path);
    }
}
