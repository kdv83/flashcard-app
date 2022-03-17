<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashcardPractice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'flashcard_practice';

    public function flashcard()
    {
        return $this->belongsTo(Flashcard::class);
    }

    public function getQuestionAttribute()
    {
        return $this->flashcard->question;
    }
}
