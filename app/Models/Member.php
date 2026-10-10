<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $fillable = [
        'membership_number',
        'name',
        'identity_number',
        'email',
        'phone',
        'address',
        'join_date',
        'membership_type_id',
        'card_number',
        'photo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
        ];
    }

    /**
     * Simpan join_date sebagai 'Y-m-d' sesuai kolom DATE di migration.
     * Cast 'date' bawaan menyimpan 'Y-m-d H:i:s'; pembacaan tetap memakai cast.
     */
    protected function joinDate(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => blank($value) ? null : $this->asDateTime($value)->toDateString(),
        );
    }

    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class);
    }
}
