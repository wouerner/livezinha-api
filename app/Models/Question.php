<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'live_stream_id',
        'name',
        'tiktok_handle',
        'question_text',
        'passcode',
        'status',
        'is_tagged',
        'is_hidden',
        'displayed_at',
        'removed_at',
        'duration_seconds',
    ];

    public function liveStream()
    {
        return $this->belongsTo(LiveStream::class);
    }

    public function votes()
    {
        return $this->hasMany(QuestionVote::class);
    }

    public static function generateUniquePasscode(): string
    {
        $nouns = [
            'gato', 'cachorro', 'leao', 'tigre', 'pinguim', 'coelho', 'elefante', 'macaco', 'panda', 'urso',
            'passaro', 'peixe', 'cafe', 'bolo', 'cha', 'pao', 'queijo', 'suco', 'abacaxi', 'melao',
            'sol', 'lua', 'estrela', 'rio', 'mar', 'vento', 'fogo', 'livro', 'balao', 'pipoca', 'chocolate'
        ];

        $adjectives = [
            'azul', 'verde', 'vermelho', 'amarelo', 'preto', 'branco', 'rosa', 'roxo', 'cinza', 'marrom',
            'alegre', 'feliz', 'forte', 'rapido', 'quente', 'frio', 'doce', 'salgado', 'esperto', 'calmo',
            'bravo', 'bonito', 'lindo', 'jovem', 'velho', 'novo', 'pequeno', 'grande', 'macio', 'leve'
        ];

        do {
            $noun = $nouns[array_rand($nouns)];
            $adjective = $adjectives[array_rand($adjectives)];
            $passcode = "{$noun}-{$adjective}";
        } while (self::where('passcode', $passcode)->exists());

        return $passcode;
    }
}
