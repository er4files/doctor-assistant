<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DiagnosisController extends Controller
{
    public function index()
    {
        return view('form');
    }

    public function analyze(Request $request)
    {
        // Ambil semua input
        $conversation = $request->input('conversation', '');
        $subject = $request->input('subject', '');
        $assessment = $request->input('assessment', '');
        $object = $request->input('object', '');
        $plan = $request->input('plan', '');

        // Gabungkan semua input CPPT jadi satu string (tanpa label, tanpa null)
        $info = trim(implode(' ', array_filter([$subject, $assessment, $object, $plan])));

        // === Kirim ke Flask /parse (harus ada conversation & info) ===
        $parseResponse = Http::post('https://medicalai-api-2zan.vercel.app/parse', [
            'conversation' => $conversation,
            'info' => $info
        ]);

        $parsedData = $parseResponse->json();

        // === Lanjut kirim hasil parse ke /predict ===
        $predictResponse = Http::post('https://medicalai-api-2zan.vercel.app/predict', [
            'subject' => $parsedData['subject'] ?? '',
            'assessment' => $parsedData['assessment'] ?? '',
            'object' => $parsedData['object'] ?? '',
            'plan' => $parsedData['plan'] ?? ''
        ]);

        $predictData = $predictResponse->json();

        // === Gabungkan hasil parse + diagnosa untuk dikembalikan ke frontend ===
        return response()->json([
            'subject' => $parsedData['subject'] ?? '',
            'assessment' => $parsedData['assessment'] ?? '',
            'object' => $parsedData['object'] ?? '',
            'plan' => $parsedData['plan'] ?? '',
            'diagnosa_utama' => $predictData['diagnosa_utama'] ?? null,
            'diagnosa_sekunder_1' => $predictData['diagnosa_sekunder_1'] ?? null,
            'diagnosa_sekunder_2' => $predictData['diagnosa_sekunder_2'] ?? null,
        ]);
    }

    public function reanalyze(Request $request)
    {
        $data = $request->only(['subject', 'assessment', 'object', 'plan']);

        $response = Http::post('https://medicalai-api-2zan.vercel.app/predict', $data);

        return $response->json();
    }
}
