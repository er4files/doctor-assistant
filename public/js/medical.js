// === Toggle textarea percakapan ===
document.getElementById("infoBtn").addEventListener("click", () => {
    const box = document.getElementById("conversationBox");
    box.style.display = box.style.display === "none" ? "block" : "none";
});

// === Elemen penting ===
const voiceBtn = document.getElementById("voiceBtn");
const conversationInput = document.getElementById("conversation");
const realtimePopup = document.getElementById("realtimePopup");
const reAnalyzeBtn = document.getElementById("reAnalyzeBtn");
const loadingOverlay = document.getElementById("loadingOverlay");

let recognition, isRecording = false,
    recordedText = "",
    previousTranscript = "";

// === Fungsi helper untuk loading ===
function showLoading(message = "Sedang menganalisis...") {
    loadingOverlay.style.display = "flex";
    loadingOverlay.querySelector("p").textContent = message;
}

function hideLoading() {
    loadingOverlay.style.display = "none";
}

// === Setup Speech Recognition ===
if ("webkitSpeechRecognition" in window) {
    recognition = new webkitSpeechRecognition();
    recognition.lang = "id-ID";
    recognition.continuous = true;
    recognition.interimResults = true;

    recognition.onresult = (event) => {
        let transcript = "";
        let isFinal = false;

        for (let i = event.resultIndex; i < event.results.length; ++i) {
            transcript += event.results[i][0].transcript;
            if (event.results[i].isFinal) isFinal = true;
        }

        if (isFinal && transcript !== previousTranscript) {
            recordedText += transcript + " ";
            previousTranscript = transcript;
            conversationInput.value = recordedText;

            realtimePopup.textContent = transcript;
            realtimePopup.style.display = "block";
            clearTimeout(window.popupTimeout);
            window.popupTimeout = setTimeout(() => realtimePopup.style.display = "none", 1500);
        }
    };

    recognition.onend = async () => {
        if (!isRecording && recordedText.trim() !== "") {
            console.log("🎯 Rekaman selesai, analisis otomatis...");
            await analyzeConversation(recordedText);
        }
    };
} else {
    alert("Browser kamu tidak mendukung Speech Recognition!");
}

// === Tombol rekam suara ===
voiceBtn.addEventListener("click", () => {
    if (!recognition) return;

    if (!isRecording) {
        recognition.start();
        isRecording = true;
        recordedText = "";
        previousTranscript = "";
        voiceBtn.textContent = "🛑 Stop Rekam";
        voiceBtn.classList.remove("btn-record");
        voiceBtn.classList.add("btn-stop");
    } else {
        recognition.stop();
        isRecording = false;
        voiceBtn.textContent = "🎙 Mulai Rekam";
        voiceBtn.classList.remove("btn-stop");
        voiceBtn.classList.add("btn-record");
    }
});

// === Analisis Percakapan ===
async function analyzeConversation(conversation) {
    showLoading("Menganalisis percakapan dokter-pasien...");

    try {
        const response = await axios.post("/analyze", {
            conversation,
            subject: document.getElementById("subject").value,
            assessment: document.getElementById("assessment").value,
            object: document.getElementById("object").value,
            plan: document.getElementById("plan").value
        });

        const data = response.data;

        document.getElementById("subject").value = data.subject || "";
        document.getElementById("assessment").value = data.assessment || "";
        document.getElementById("object").value = data.object || "";
        document.getElementById("plan").value = data.plan || "";

        updateDiagnosa(data);
    } catch (error) {
        console.error(error);
        alert("❌ Gagal menganalisis percakapan!");
    } finally {
        hideLoading();
    }
}

// === Analisis Diagnosa Ulang ===
reAnalyzeBtn.addEventListener("click", async () => {
    reAnalyzeBtn.textContent = "⏳ Menganalisis...";
    reAnalyzeBtn.disabled = true;
    showLoading("Menganalisis diagnosa ulang...");

    const data = {
        subject: document.getElementById("subject").value,
        assessment: document.getElementById("assessment").value,
        object: document.getElementById("object").value,
        plan: document.getElementById("plan").value,
    };

    try {
        const response = await axios.post("/reanalyze", data);
        updateDiagnosa(response.data);
    } catch (error) {
        console.error(error);
        alert("❌ Gagal menganalisis diagnosa ulang!");
    } finally {
        hideLoading();
        reAnalyzeBtn.textContent = "🔎 Analisis Diagnosa";
        reAnalyzeBtn.disabled = false;
    }
});

// === Update hasil diagnosa ===
function updateDiagnosa(data) {
    const utama = data.diagnosa_utama;
    const sek1 = data.diagnosa_sekunder_1;
    const sek2 = data.diagnosa_sekunder_2;

    function formatDiagnosa(d) {
        if (!d) return "";
        const name = d.diagnosa || "-";
        const icd = d.icd10 || "";
        const conf = d.confidence ? ` (${(d.confidence * 100).toFixed(1)}%)` : "";
        // format: K29.7 - Gastritis (82.0%)
        return `${icd} - ${name}${conf}`;
    }

    document.getElementById("utama").value = formatDiagnosa(utama);
    document.getElementById("sekunder1").value = formatDiagnosa(sek1);
    document.getElementById("sekunder2").value = formatDiagnosa(sek2);
}
