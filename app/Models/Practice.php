<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Practice extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function practice_flashcards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FlashcardPractice::class);
    }
}
