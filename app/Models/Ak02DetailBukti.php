<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ak02DetailBukti extends Model
{
    protected $table = 'ak02_detail_bukti';

    protected $fillable = [
        'ak02_detail_id',
        'bukti_id',
    ];

    public function detail()
    {
        return $this->belongsTo(Ak02SubmissionDetail::class, 'ak02_detail_id');
    }

    public function bukti()
    {
        return $this->belongsTo(BuktiDokumenAssesi::class, 'bukti_id');
    }
}
