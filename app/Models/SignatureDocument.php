<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignatureDocument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime', 'certificate_valid_until' => 'date'];
    }

    public function signedFileName(): string
    {
        return pathinfo($this->file_name, PATHINFO_FILENAME).'-signe.pdf';
    }
}
