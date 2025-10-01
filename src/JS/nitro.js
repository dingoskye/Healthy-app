
// ================== FRONTEND LOGIC ==================

// Wacht tot de pagina geladen is en start de init-functie
window.addEventListener("load", () => init());

// URL voor de API-aanroepen (GET en POST)
const apiUrl = "https://project.cmi.hr.nl/2025_2026/tle1_t1/extra_testing_2/healthy-app/api/chat.php";

// Variabelen voor geselecteerde fruit-optie en DOM-elementen
let selectedFruit = "";      // Opslag van gekozen fruit-knop
let fruitButtons = [];       // Array met alle fruit-knoppen
let statusEl;                // Element waar statusmeldingen komen

// ====== INIT ======
const init = () => {
    fruitButtons = document.querySelectorAll(".fruit-btn");
    statusEl = document.getElementById("status");

    // ✅ Voeg klik-events toe aan fruit-knoppen
    fruitButtons.forEach(btn => {
        btn.addEventListener("click", () => selectFruit(btn.innerText));
    });

    // ✅ Velden bij pagina-laden leegmaken
    document.getElementById("vegetables").value = "";
    document.getElementById("carbs").value = "";
    document.getElementById("dairy").value = "";
    document.getElementById("protein").value = "";
    selectedFruit = "";
    fruitButtons.forEach(b => b.classList.remove("ring","ring-[#264653]"));

    // ✅ Koppel klik-event aan de "Save Nutrition" knop
    document.getElementById("submit").addEventListener("click", () => saveNutritionData());
};

// ====== Fruit selectie ======
const selectFruit = (value) => {
    selectedFruit = value; // Bewaar de waarde (bijv. "2-3")
    // Verwijder highlight van alle knoppen
    fruitButtons.forEach(b => b.classList.remove("ring","ring-[#264653]"));
    // Voeg highlight toe aan de geselecteerde knop
    fruitButtons.forEach(b => { if (b.innerText === value) b.classList.add("ring","ring-[#264653]"); });
};

// ====== Data opslaan (POST) ======
// ====== Data opslaan (POST) met validatie ======
const saveNutritionData = () => {
    // Controleer of alles ingevuld is
    if (
        selectedFruit === "" ||
        document.getElementById("vegetables").value.trim() === "" ||
        document.getElementById("carbs").value.trim() === "" ||
        document.getElementById("dairy").value.trim() === "" ||
        document.getElementById("protein").value.trim() === ""
    ) {
        statusEl.innerText = "❌ Please fill in all fields before submitting!";
        return; // Stop de functie, submit niet
    }

    // Bouw object met huidige invoerwaarden
    const payload = {
        fruit: selectedFruit,
        vegetables: document.getElementById("vegetables").value.trim(),
        carbs: document.getElementById("carbs").value.trim(),
        dairy: document.getElementById("dairy").value.trim(),
        protein: document.getElementById("protein").value.trim()
    };

    // Verstuur JSON naar PHP API
    fetch(apiUrl, {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify(payload)
    })
        .then(res => res.json())
        .then(resp => {
            console.log("✅ Saved:", resp);
            statusEl.innerText = "✅ Nutrition saved successfully!";

            // Velden leegmaken na submit
            document.getElementById("vegetables").value = "";
            document.getElementById("carbs").value = "";
            document.getElementById("dairy").value = "";
            document.getElementById("protein").value = "";

            // Fruit selectie resetten
            selectedFruit = "";
            fruitButtons.forEach(b => b.classList.remove("ring","ring-[#264653]"));
        })
        .catch(err => {
            console.error("❌ Error saving:", err);
            statusEl.innerText = "❌ Error saving data.";
        });
};
