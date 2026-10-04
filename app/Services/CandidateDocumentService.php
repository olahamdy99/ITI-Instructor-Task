<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\CandidateDocument;
use Illuminate\Http\UploadedFile;

class CandidateDocumentService
{
    public function upload(
        Candidate $candidate,
        UploadedFile $file
    ): CandidateDocument {
        $path = $file->store(
            'candidate-documents',
            'local'
        );

        return $candidate->documents()->create([
            'type' => 'cv',
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'processing_status' => 'pending',
            'failure_reason' => null,
        ]);
    }
}