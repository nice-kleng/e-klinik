<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\MedicalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request, MedicalRecord $medicalRecord): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:10240',
            'category' => 'nullable|in:lab_result,xray,photo,other',
        ], [
            'file.required' => 'File harus dipilih',
            'file.file' => 'File tidak valid',
            'file.mimes' => 'Tipe file harus: jpg, jpeg, png, gif, pdf, doc, docx',
            'file.max' => 'Ukuran file maksimal 10 MB',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::uuid() . '.' . $extension;
        $filePath = $file->storeAs('attachments/' . $medicalRecord->id, $fileName, 'public');

        $medicalRecord->attachments()->create([
            'patient_id' => $medicalRecord->patient_id,
            'file_name' => $originalName,
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'category' => $request->input('category', 'other'),
        ]);

        return redirect()->back()->with('success', 'Berkas berhasil diunggah');
    }

    public function download(Attachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return redirect()->back()->with('success', 'Berkas berhasil dihapus');
    }
}
