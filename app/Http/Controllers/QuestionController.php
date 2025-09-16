<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionAnswer;

class QuestionController extends Controller
{
    public function uploadWord(Request $request)
    {
        $validated = $request->validate([
            'skema_id' => 'required|integer',
            'file' => 'required|mimes:docx'
        ]);

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($request->file('file')->getPathname());
        $questions = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $texts = \App\Helpers\WordParser::extractText($element);
                foreach ($texts as $line) {
                    // Format soal: "1. Apa ibukota Indonesia?"
                    // Format opsi: "A. Jakarta"
                    // Format jawaban: "Jawaban: A"

                    if (preg_match('/^\d+\.\s*(.+)$/', $line, $matches)) {
                        $questions[] = [
                            'skema_id' => $validated['skema_id'],
                            'question_text' => $matches[1],
                            'option_a' => '',
                            'option_b' => '',
                            'option_c' => '',
                            'option_d' => '',
                            'correct_option' => '',
                        ];
                    } elseif (preg_match('/^A\.\s*(.+)$/i', $line, $matches)) {
                        $questions[array_key_last($questions)]['option_a'] = $matches[1];
                    } elseif (preg_match('/^B\.\s*(.+)$/i', $line, $matches)) {
                        $questions[array_key_last($questions)]['option_b'] = $matches[1];
                    } elseif (preg_match('/^C\.\s*(.+)$/i', $line, $matches)) {
                        $questions[array_key_last($questions)]['option_c'] = $matches[1];
                    } elseif (preg_match('/^D\.\s*(.+)$/i', $line, $matches)) {
                        $questions[array_key_last($questions)]['option_d'] = $matches[1];
                    } elseif (preg_match('/^Jawaban:\s*([ABCDabcd])$/', $line, $matches)) {
                        $questions[array_key_last($questions)]['correct_option'] = strtolower($matches[1]);
                    }
                }
            }
        }

        // Simpan ke database
        $created = [];
        foreach ($questions as $q) {
            if (!empty($q['question_text']) && !empty($q['correct_option'])) {
                $created[] = \App\Models\Question::create($q);
            }
        }

        return response()->json([
            'status' => 'true',
            'message' => count($created).' questions imported successfully',
            'data' => $created
        ], 201);
    }


    public function createQuestion(Request $request)
    {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.skema_id' => 'required|integer',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string',
            'questions.*.option_b' => 'required|string',
            'questions.*.option_c' => 'required|string',
            'questions.*.option_d' => 'required|string',
            'questions.*.correct_option' => 'required|string|in:a,b,c,d',
        ]);

        $createdQuestions = [];
        foreach ($validated['questions'] as $questionData) {
            $createdQuestions[] = Question::create($questionData);
        }

        return response()->json([
            'status' => 'true',
            'message' => 'Questions created successfully',
            'data' => $createdQuestions
        ], 201);
    }

    public function getQuestionsBySkema($skema_id)
    {
        $questions = Question::where('skema_id', $skema_id)->get();
        $questions->makeHidden(['correct_option']);

        if ($questions->isEmpty()) {
            return response()->json([
                'status' => 'false',
                'message' => 'No questions found for the given skema_id'
            ], 404);
        }

        return response()->json([
            'status' => 'true',
            'message' => 'Questions retrieved successfully',
            'data' => $questions
        ], 200);
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = Question::find($id);
        if (!$question) {
            return response()->json([
                'status' => 'false',
                'message' => 'Question not found'
            ], 404);
        }

        $validated = $request->validate([
            'skema_id' => 'sometimes|integer',
            'question_text' => 'sometimes|string',
            'option_a' => 'sometimes|string',
            'option_b' => 'sometimes|string',
            'option_c' => 'sometimes|string',
            'option_d' => 'sometimes|string',
            'correct_option' => 'sometimes|string|in:a,b,c,d',
        ]);

        $question->update($validated);

        return response()->json([
            'status' => 'true',
            'message' => 'Question updated successfully',
            'data' => $question
        ], 200);
    }

    public function deleteQuestion($id)
    {

        $question = Question::find($id);
        if (!$question) {
            return response()->json([
                'status' => 'false',
                'message' => 'Question not found'
            ], 404);
        }

        $question->delete();

        return response()->json([
            'status' => 'true',
            'message' => 'Question deleted successfully'
        ], 200);
    }

    public function submitAnswer(Request $request)
    {
        $validated = $request->validate(
            [
                'assesment_asesi_id' => 'required|integer|exists:assesment_asesi,id',
                'questions' => 'required|array',
                'questions.*.question_id' => 'required|integer|exists:questions,id',
                'questions.*.selected_option' => 'required|string|in:a,b,c,d',
            ],
            [
                'assesment_asesi_id.required' => 'Assessment ID is required.',
                'assesment_asesi_id.exists'   => 'The selected assessment is not valid.',
                'questions.required'          => 'You must provide at least one question.',
                'questions.*.question_id.exists' => 'One of the provided questions does not exist.',
                'questions.*.selected_option.in' => 'Answer must be one of: a, b, c, or d.',
            ]
        );


        $answer = [];
        foreach ($validated['questions'] as $questionData) {
            $answer[] = QuestionAnswer::create([
                'assesment_asesi_id' => $validated['assesment_asesi_id'],
                'question_id' => $questionData['question_id'],
                'selected_option' => $questionData['selected_option'],
            ]);
        }

        return response()->json([
            'status' => 'true',
            'message' => 'Answer stored successfully',
            'data' => $answer
        ], 201);
    }
}
