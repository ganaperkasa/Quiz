<?php

namespace App\Http\Controllers;

use App\Models\Quiz;

use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        $questions = Quiz::all();
        $answers = $questions->pluck('answer')->shuffle()->values();

        return view('quiz', compact('questions', 'answers'));
    }
    public function check(Request $request)
    {
        $score = 0;
        $results = [];

        foreach ($request->answers as $qid => $userAnswer) {
            $correctAnswer = Quiz::find($qid)->answer ?? null;
            $isCorrect = ($userAnswer === $correctAnswer);

            if ($isCorrect) {
                $score += 10;
            }

            $results[$qid] = [
                'correct' => $correctAnswer,
                'user' => $userAnswer,
                'isCorrect' => $isCorrect
            ];
        }

        return response()->json([
            'score' => $score,
            'results' => $results
        ]);
    }
}
