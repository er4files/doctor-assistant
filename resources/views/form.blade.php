<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AI Medical Scribe</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/medical.css') }}">
</head>

<body>
    <div class="header-bar">
        <div class="header-left">
            <button id="voiceBtn" class="btn-record">🎙 Mulai Rekam</button>
            <button id="infoBtn" class="btn-info">ℹ️ Info</button>
        </div>
        <div>
            <span class="text-muted fw-semibold">How was your AI Medical Scribe?</span>
            ⭐⭐⭐⭐⭐
        </div>
        <button class="btn btn-outline-primary btn-sm">👤 Privacy Concern</button>
    </div>

    <div class="cppt-section">
        <div class="section-header"># CPPT</div>

        {{-- 🎙 Textarea percakapan — disembunyikan awalnya --}}
        <div id="conversationBox" style="display: none;">
            <textarea id="conversation" placeholder="Masukkan atau rekam percakapan dokter-pasien di sini..."></textarea>
        </div>

        {{-- ✏️ Input CPPT bisa diedit --}}
        <div class="row g-3 mt-4">
            <div class="col-md-6">
                <label>Subjek:</label>
                <textarea class="form-control" id="subject" rows="3"></textarea>
            </div>
            <div class="col-md-6">
                <label>Asesmen:</label>
                <textarea class="form-control" id="assessment" rows="3"></textarea>
            </div>
            <div class="col-md-6">
                <label>Objek:</label>
                <textarea class="form-control" id="object" rows="3"></textarea>
            </div>
            <div class="col-md-6">
                <label>Plan:</label>
                <textarea class="form-control" id="plan" rows="3"></textarea>
            </div>
        </div>

        {{-- 📋 Bagian Diagnosa --}}
        <div class="mt-4 p-3 border rounded bg-light shadow-sm">
            <h5 class="mb-3">📋 Hasil Diagnosa</h5>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label>Diagnosa Utama:</label>
                    <input type="text" class="form-control" id="utama" readonly>
                </div>
                <div class="col-md-4">
                    <label>Diagnosa Sekunder 1:</label>
                    <input type="text" class="form-control" id="sekunder1" readonly>
                </div>
                <div class="col-md-4">
                    <label>Diagnosa Sekunder 2:</label>
                    <input type="text" class="form-control" id="sekunder2" readonly>
                </div>
            </div>

            <button id="reAnalyzeBtn" class="btn btn-primary">🔎 Analisis Diagnosa</button>
        </div>
    </div>

    <div id="realtimePopup" class="realtime-popup"></div>
    <!-- 🌀 Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner-border text-light" role="status"></div>
            <p class="mt-3">Sedang menganalisis...</p>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/medical.js') }}"></script>
</body>

</html>
