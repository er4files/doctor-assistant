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
        $conversation = $request->input('conversation', '');
        $subject = $request->input('subject', '');
        $assessment = $request->input('assessment', '');
        $object = $request->input('object', '');
        $plan = $request->input('plan', '');

        $info = trim(implode(' ', array_filter([$subject, $assessment, $object, $plan])));

        // === 1️⃣ Kirim ke /parse ===
        $parseResponse = Http::post('https://medicalai-api-2zan.vercel.app/parse', [
            'conversation' => $conversation,
            'info' => $info
        ]);

        $parsedData = $parseResponse->json();

        // === 2️⃣ Kirim ke /predict ===
        $predictResponse = Http::post('https://medicalai-api-2zan.vercel.app/predict', [
            'subject' => $parsedData['subject'] ?? '',
            'assessment' => $parsedData['assessment'] ?? '',
            'object' => $parsedData['object'] ?? '',
            'plan' => $parsedData['plan'] ?? ''
        ]);

        $predictData = $predictResponse->json();

        $predictions = $predictData['predictions'] ?? [];
        $topTokens = $predictData['explainability']['top_tokens'] ?? [];

        // === Format agar ada ICD10 ===
        $diagnosaUtama = isset($predictions[0]) ? [
            'diagnosa' => $predictions[0]['name'] ?? '',
            'icd10' => $predictions[0]['icd10'] ?? '',
            'confidence' => $predictions[0]['score'] ?? 0
        ] : null;

        $diagnosaSekunder1 = isset($predictions[1]) ? [
            'diagnosa' => $predictions[1]['name'] ?? '',
            'icd10' => $predictions[1]['icd10'] ?? '',
            'confidence' => $predictions[1]['score'] ?? 0
        ] : null;

        $diagnosaSekunder2 = isset($predictions[2]) ? [
            'diagnosa' => $predictions[2]['name'] ?? '',
            'icd10' => $predictions[2]['icd10'] ?? '',
            'confidence' => $predictions[2]['score'] ?? 0
        ] : null;

        return response()->json([
            'subject' => $parsedData['subject'] ?? '',
            'assessment' => $parsedData['assessment'] ?? '',
            'object' => $parsedData['object'] ?? '',
            'plan' => $parsedData['plan'] ?? '',
            'diagnosa_utama' => $diagnosaUtama,
            'diagnosa_sekunder_1' => $diagnosaSekunder1,
            'diagnosa_sekunder_2' => $diagnosaSekunder2,
            'top_tokens' => $topTokens
        ]);
    }

    public function reanalyze(Request $request)
    {
        $data = $request->only(['subject', 'assessment', 'object', 'plan']);

        $response = Http::post('https://medicalai-api-2zan.vercel.app/predict', $data);
        $predictData = $response->json();

        $predictions = $predictData['predictions'] ?? [];
        $topTokens = $predictData['explainability']['top_tokens'] ?? [];

        $diagnosaUtama = isset($predictions[0]) ? [
            'diagnosa' => $predictions[0]['name'] ?? '',
            'icd10' => $predictions[0]['icd10'] ?? '',
            'confidence' => $predictions[0]['score'] ?? 0
        ] : null;

        $diagnosaSekunder1 = isset($predictions[1]) ? [
            'diagnosa' => $predictions[1]['name'] ?? '',
            'icd10' => $predictions[1]['icd10'] ?? '',
            'confidence' => $predictions[1]['score'] ?? 0
        ] : null;

        $diagnosaSekunder2 = isset($predictions[2]) ? [
            'diagnosa' => $predictions[2]['name'] ?? '',
            'icd10' => $predictions[2]['icd10'] ?? '',
            'confidence' => $predictions[2]['score'] ?? 0
        ] : null;

        return response()->json([
            'diagnosa_utama' => $diagnosaUtama,
            'diagnosa_sekunder_1' => $diagnosaSekunder1,
            'diagnosa_sekunder_2' => $diagnosaSekunder2,
            'top_tokens' => $topTokens
        ]);
    }
}
