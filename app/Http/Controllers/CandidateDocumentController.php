<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCandidateDocumentRequest;
use App\Models\Candidate;
use App\Services\CandidateDocumentService;

class CandidateDocumentController extends Controller
{
    public function __construct(
        private CandidateDocumentService $candidateDocumentService
    ) {
    }

    public function store(
        StoreCandidateDocumentRequest $request,
        Candidate $candidate
    ) {
        $document = $this->candidateDocumentService->upload(
            $candidate,
            $request->file('file')
        );

        return response()->json([
            'message' => 'Document uploaded successfully.',
            'data' => $document,
        ], 201);
    }
}