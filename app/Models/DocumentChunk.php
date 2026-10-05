<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentChunk extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidate_document_id',
        'chunk_index',
        'content',
        'page_number',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
    public function candidateDocument()
    {
        return $this->belongsTo(CandidateDocument::class);
    }
}
