<?php

namespace App\Models;

use Database\Factories\MembershipTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipType extends Model
{
    /** @use HasFactory<MembershipTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_months',
        'max_borrowed_books',
    ];

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
