<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiDokumenAssesi extends Model
{
    protected $table = 'bukti_dokumen_assesi';

    protected $fillable = [
        'assesi_id',
        'nama_dokumen',
        'file_path',
        'created_at',
        'updated_at',
    ];

    public function assesi()
    {
        return $this->belongsTo(Assesi::class, 'assesi_id');
    }

    public function submissionBukti()
    {
        return $this->hasMany(SubmissionBukti::class, 'bukti_id');
    }

    public function getSubmissionBukti()
    {
        return $this->submissionBukti()->with('submission')->get();
    }
    public function getAssesi()
    {
        return $this->assesi()->with('user')->first();
    }
    public function getAssesiSubmissions()
    {
        return $this->assesi()->with('submissions')->first();
    }
    public function getAssesiSubmissionsWithBukti()
    {
        return $this->assesi()->with(['submissions' => function ($query) {
            $query->with('submissionBukti.bukti');
        }])->first();  
    }

}
