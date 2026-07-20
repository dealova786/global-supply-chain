<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NegativeWord;
use App\Models\PositiveWord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SentimentWordController extends Controller
{
    public function index()
    {
        $positiveWords = PositiveWord::orderBy('word')->get();
        $negativeWords = NegativeWord::orderBy('word')->get();

        return view('admin.sentiment_words.index', compact(
            'positiveWords',
            'negativeWords'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'word' => 'required|string|max:100',
            'type' => 'required|in:positive,negative',
        ]);

        $word = Str::lower(trim($request->word));

        if ($request->type === 'positive') {
            PositiveWord::firstOrCreate([
                'word' => $word,
            ]);
        }

        if ($request->type === 'negative') {
            NegativeWord::firstOrCreate([
                'word' => $word,
            ]);
        }

        return redirect()
            ->route('admin.sentiment-words.index')
            ->with('success', 'Sentiment word berhasil ditambahkan.');
    }

    public function destroy($type, $id)
    {
        if ($type === 'positive') {
            $word = PositiveWord::findOrFail($id);
            $word->delete();
        }

        if ($type === 'negative') {
            $word = NegativeWord::findOrFail($id);
            $word->delete();
        }

        return redirect()
            ->route('admin.sentiment-words.index')
            ->with('success', 'Sentiment word berhasil dihapus.');
    }
}