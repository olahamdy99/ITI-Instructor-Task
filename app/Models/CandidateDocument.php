<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateDocument extends Model
{
    use HasFactory;
    protected $fillable = [
        'candidate_id',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'processing_status',
        'failure_reason',
    ];

    public function chunks()
    {
        return $this->hasMany(DocumentChunk::class);
    }
}
