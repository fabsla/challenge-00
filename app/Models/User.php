<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'cpf_cnpj',
        'password',
        'lojista',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Attributes appended when model is serialized.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'cpf_cnpj_formatado',
    ];

    /**
     * Mutator for `cpf_cnpj`: store only digits (normalize on set).
     */
    protected function cpfCnpj(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : preg_replace('/[^a-zA-Z0-9]/', '', $value),
            get: fn (?string $value) => $value,
        );
    }

    /**
     * Accessor returning CPF/CNPJ formatted for presentation.
     */
    public function getCpfCnpjFormatadoAttribute(): ?string
    {
        $value = $this->attributes['cpf_cnpj'] ?? null;

        if ($value === null) {
            return null;
        }

        $len = mb_strlen($value);

        if ($len === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $value);
        }

        if ($len === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3\/$4-$5', $value);
        }

        return $value;
    }
}
