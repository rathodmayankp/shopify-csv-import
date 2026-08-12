<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCsvUploadRequest;
use App\Jobs\ProcessCsvUpload;
use App\Models\Upload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('uploads.create');
    }

    public function store(StoreCsvUploadRequest $request): RedirectResponse
    {
        $file = $request->file('csv_file');

        $storedPath = $file->store('uploads');

        $upload = Upload::create([
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'status' => Upload::STATUS_PENDING,
        ]);

        Log::info('CSV file uploaded', [
            'upload_id' => $upload->id,
            'filename' => $upload->original_filename,
        ]);

        ProcessCsvUpload::dispatch($upload);

        return redirect()
            ->route('dashboard.show', $upload)
            ->with('success', 'File uploaded successfully. Import is now processing in the background.');
    }
}
