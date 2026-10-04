<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CandidateDocument;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_role_id',
        'reference_code',
    ];

    public function documents()
    {
        return $this->hasMany(CandidateDocument::class);
    }
}
