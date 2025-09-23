document.addEventListener('DOMContentLoaded', () => {
    const datePicker = document.getElementById('datePicker');
    const prevWeekBtn = document.getElementById('prevWeekBtn');
    const nextWeekBtn = document.getElementById('nextWeekBtn');
    const aiToggleBtn = document.getElementById('ai-toggle-btn');
    const aiCloseBtn = document.getElementById('ai-close-btn');
    const aiCoach = document.getElementById('ai-coach');
    const today = new Date();
    today.setHours(0,0,0,0);

    let selectedDate = new Date(today);
    datePicker.value = formatDate(selectedDate);

    renderWeek(selectedDate);

    prevWeekBtn.addEventListener('click', () => {
        selectedDate.setDate(selectedDate.getDate() - 7);
        datePicker.value = formatDate(selectedDate);
        renderWeek(selectedDate);
    });

    nextWeekBtn.addEventListener('click', () => {
        const tmp = new Date(selectedDate);
        tmp.setDate(tmp.getDate() + 7);
        const nextMonday = getMonday(tmp);
        if (nextMonday > today) return;
        selectedDate = tmp;
        datePicker.value = formatDate(selectedDate);
        renderWeek(selectedDate);
    });

    datePicker.addEventListener('change', (e) => {
        const d = new Date(e.target.value);
        d.setHours(0,0,0,0);
        if (d > today) {
            selectedDate = new Date(today);
            datePicker.value = formatDate(today);
        } else {
            selectedDate = d;
        }
        renderWeek(selectedDate);
    });

    aiToggleBtn.addEventListener('click', () => {
        aiCoach.classList.toggle('translate-x-full');
    });
    aiCloseBtn.addEventListener('click', () => {
        aiCoach.classList.add('translate-x-full');
    });

    document.getElementById('eating-area').addEventListener('click', (ev) => {
        const btn = ev.target.closest('.add-meal-btn');
        if (!btn) return;
        const container = btn.closest('.time-slot');
        const daySection = btn.closest('.day-section');
        const dateStr = daySection.getAttribute('data-date');
        if (!dateStr) return;
        const containerDate = new Date(dateStr + 'T00:00:00');
        containerDate.setHours(0,0,0,0);
        if (containerDate > today) {
            alert("Je kunt voor toekomstige dagen niet toevoegen.");
            return;
        }
        addMealUI(container, dateStr);
    });
});

function formatDate(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth()+1).padStart(2,'0');
    const day = String(d.getDate()).padStart(2,'0');
    return `${y}-${m}-${day}`;
}
function formatDisplayDate(d) {
    const dd = String(d.getDate()).padStart(2,'0');
    const mm = String(d.getMonth()+1).padStart(2,'0');
    const yy = String(d.getFullYear());
    return `${dd}-${mm}-${yy}`;
}
function getMonday(d) {
    const date = new Date(d);
    const day = date.getDay();
    const diff = date.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(date.setDate(diff));
}

function renderWeek(selectedDate) {
    const monday = getMonday(selectedDate);
    const today = new Date();
    today.setHours(0,0,0,0);

    const daySections = document.querySelectorAll('.day-section');
    daySections.forEach((sec, idx) => {
        const dayDate = new Date(monday);
        dayDate.setDate(monday.getDate() + idx);
        dayDate.setHours(0,0,0,0);
        const ds = formatDate(dayDate);
        sec.setAttribute('data-date', ds);

        const dayDateSpan = sec.querySelector('.day-date');
        if (dayDateSpan) dayDateSpan.innerText = `• ${formatDisplayDate(dayDate)}`;

        sec.querySelectorAll('.add-meal-btn').forEach(btn => {
            if (dayDate > today) {
                btn.disabled = true;
                btn.classList.add('opacity-50','cursor-not-allowed');
            } else {
                btn.disabled = false;
                btn.classList.remove('opacity-50','cursor-not-allowed');
            }
        });
        restoreMeals(sec, ds);
    });

    const nextMonday = new Date(monday);
    nextMonday.setDate(monday.getDate() + 7);
    const nextWeekBtn = document.getElementById('nextWeekBtn');
    if (nextMonday > today) {
        nextWeekBtn.disabled = true;
        nextWeekBtn.classList.add('opacity-50','cursor-not-allowed');
    } else {
        nextWeekBtn.disabled = false;
        nextWeekBtn.classList.remove('opacity-50','cursor-not-allowed');
    }
}

function addMealUI(container, dateStr) {
    if (container.querySelector('.meal-input-wrapper')) return;

    const wrapper = document.createElement('div');
    wrapper.className = "meal-input-wrapper mt-2 relative";

    const input = document.createElement('input');
    input.type = "text";
    input.placeholder = "Search food...";
    input.className = "border rounded px-2 py-1 w-full";
    wrapper.appendChild(input);

    const listContainer = document.createElement('div');
    listContainer.className = "border rounded bg-white absolute z-10 max-h-40 overflow-y-auto w-full shadow mt-1";
    wrapper.appendChild(listContainer);

    const btn = document.createElement('button');
    btn.type = "button";
    btn.innerText = "Add";
    btn.className = "mt-2 bg-[var(--elements)] text-white rounded px-3 py-1 hover:opacity-90 block";
    btn.addEventListener('click', () => {
        const food = input.value.trim();
        if (!food) return;
        const product = { product_name: food };
        appendFoodToContainer(container, product, dateStr);
        saveMeal(dateStr, product);
        wrapper.remove();
    });
    wrapper.appendChild(btn);

    container.appendChild(wrapper);

    autocomplete(input, listContainer, (product) => {
        appendFoodToContainer(container, product, dateStr);
        saveMeal(dateStr, product);
        wrapper.remove();
    });
}

function appendFoodToContainer(container, product, dateStr) {
    let ul = container.querySelector("ul");
    if (!ul) {
        ul = document.createElement("ul");
        ul.className = "list-disc list-inside text-gray-700 mt-2 space-y-1";
        container.appendChild(ul);
    }

    const li = document.createElement("li");
    li.className = "flex items-center space-x-3";

    if (product.image_front_small_url || product.image_small_url || product.image_url) {
        const img = document.createElement("img");
        img.src = product.image_front_small_url || product.image_small_url || product.image_url;
        img.alt = product.product_name || "food";
        img.className = "w-10 h-10 object-cover rounded";
        li.appendChild(img);
    }

    const span = document.createElement("span");
    span.innerText = product.product_name || "Onbekend product";

    li.appendChild(span);
    ul.appendChild(li);

    updateAITips(product.product_name || "");
}

function saveMeal(dateStr, product) {
    const key = `meals_${dateStr}`;
    let meals = JSON.parse(localStorage.getItem(key) || "[]");
    meals.push(product);
    localStorage.setItem(key, JSON.stringify(meals));
}

function restoreMeals(section, dateStr) {
    const key = `meals_${dateStr}`;
    const meals = JSON.parse(localStorage.getItem(key) || "[]");
    const slots = section.querySelectorAll(".time-slot");
    if (meals.length > 0 && slots.length > 0) {
        const container = slots[0];
        meals.forEach(product => appendFoodToContainer(container, product, dateStr));
    }
}

function autocomplete(input, listContainer, onSelectProduct) {
    let controller = null;
    input.addEventListener("input", async function () {
        const value = this.value.trim().toLowerCase();
        listContainer.innerHTML = "";
        if (controller) controller.abort();
        if (!value) return;

        controller = new AbortController();
        try {
            const url = `https://world.openfoodfacts.org/cgi/search.pl?search_terms=${encodeURIComponent(value)}&search_simple=1&action=process&json=1&page_size=8`;
            const res = await fetch(url, { signal: controller.signal });
            const data = await res.json();

            if (data.products && data.products.length > 0) {
                data.products.forEach(product => {
                    if (!product.product_name) return;

                    const item = document.createElement("div");
                    item.className = "cursor-pointer px-2 py-1 hover:bg-gray-100 flex items-center";

                    if (product.image_front_small_url || product.image_small_url || product.image_url) {
                        const img = document.createElement("img");
                        img.src = product.image_front_small_url || product.image_small_url || product.image_url;
                        img.className = "w-8 h-8 object-cover rounded mr-2";
                        item.appendChild(img);
                    }

                    const div = document.createElement("div");
                    div.innerText = product.product_name;
                    div.className = "truncate";
                    item.appendChild(div);

                    item.addEventListener("click", function () {
                        input.value = product.product_name;
                        listContainer.innerHTML = "";
                        showFoodInfo(product);
                        if (typeof onSelectProduct === "function") onSelectProduct(product);
                    });

                    listContainer.appendChild(item);
                });
            }
        } catch (err) {
            if (err.name !== 'AbortError') console.error("Error fetching OpenFoodFacts:", err);
        }
    });

    document.addEventListener("click", (e) => {
        if (!listContainer.contains(e.target) && e.target !== input) {
            listContainer.innerHTML = "";
        }
    });
}

function showFoodInfo(product) {
    const modal = document.getElementById("food-modal");
    document.getElementById("modal-title").innerText = product.product_name || "Onbekend";
    document.getElementById("modal-img").src =
        product.image_url || product.image_front_small_url || product.image_small_url || "https://via.placeholder.com/128";
    document.getElementById("modal-img").alt = product.product_name || "food";
    document.getElementById("modal-brand").innerText = product.brands || "Onbekend";
    document.getElementById("modal-energy").innerText =
        (product.nutriments?.energy_kcal_100g || product.nutriments?.energy_100g || "?") + " kcal / 100g";
    document.getElementById("modal-sugars").innerText =
        (product.nutriments?.sugars_100g || "?") + " g / 100g";

    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.getElementById("modal-close-btn").onclick = () => {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    };

    modal.onclick = (e) => {
        if (e.target === modal) {
            modal.classList.add("hidden");
            modal.classList.remove("flex");
        }
    };
}

function updateAITips(food) {
    const msgBox = document.getElementById("ai-messages");
    if (!msgBox) return;

    let message;
    const normalized = (food || "").toLowerCase();
    if (normalized.includes("ice") || normalized.includes("nutella") || normalized.includes("sugar")) {
        message = `"${food}" is lekker — let op je suikerinname!`;
    } else if (normalized.includes("apple") || normalized.includes("banana") || normalized.includes("salad") || normalized.includes("cucumber") || normalized.includes("mango")) {
        message = `Goed bezig! "${food}" is een gezonde keuze.`;
    } else {
        message = `ℹ Je hebt "${food}" toegevoegd. Zorg voor balans in je maaltijden.`;
    }

    const p = document.createElement("p");
    p.innerText = message;
    msgBox.appendChild(p);

    const aiCoach = document.getElementById('ai-coach');
    if (aiCoach) aiCoach.classList.remove('translate-x-full');
}