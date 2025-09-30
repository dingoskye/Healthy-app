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

        sec.querySelectorAll(".time-slot").forEach(slot => {
            const time = slot.getAttribute('data-time') || '';
            slot.innerHTML = `
        <p class="font-semibold">${time}</p>
        <button type="button"
                class="add-meal-btn bg-[var(--elements)] text-white rounded px-3 py-1 mt-2 hover:opacity-90">
            add meal
        </button>
    `;
        });

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
    const time = container.getAttribute("data-time"); // ⬅️ haal time op

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
        appendFoodToContainer(container, product, dateStr, time);
        saveMeal(dateStr, time, product); // ✅ tijd meegeven
        wrapper.remove();
    });
    wrapper.appendChild(btn);
    container.appendChild(wrapper);

    autocomplete(input, listContainer, (product) => {
        appendFoodToContainer(container, product, dateStr, time);
        saveMeal(dateStr, time, product); //
        wrapper.remove();
    });
}

function appendFoodToContainer(container, product, dateStr, time) {
    let ul = container.querySelector("ul");
    if (!ul) {
        ul = document.createElement("ul");
        ul.className = "list-disc list-inside text-gray-700 mt-2 space-y-1";
        container.appendChild(ul);
    }
    const li = document.createElement("li");
    li.className = "flex items-center space-x-3 meal-item cursor-pointer hover:bg-gray-100 p-1 rounded";
    li.title = "Click for more information";

    if (product.image_front_small_url || product.image_small_url || product.image_url) {
        const img = document.createElement("img");
        img.src = product.image_front_small_url || product.image_small_url || product.image_url;
        img.alt = product.product_name || "food";
        img.className = "w-10 h-10 object-cover rounded";
        li.appendChild(img);
    }

    const span = document.createElement("span");
    span.innerText = product.product_name || "Unknown product";
    span.className = "meal-text flex-1";
    li.appendChild(span);

    const delBtn = document.createElement("button");
    delBtn.innerText = "delete";
    delBtn.className = "text-red-500 hover:text-red-700";
    delBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        li.remove();
        deleteMeal(dateStr, time, product); // ✅ tijd meegeven
    });
    li.appendChild(delBtn);
    ul.appendChild(li);

    updateAITips(product.product_name || "");
}

function saveMeal(dateStr, time, product) {
    const key = `meals_${dateStr}_${time}`; // ✅ sleutel bevat ook tijdslot
    let meals = JSON.parse(localStorage.getItem(key) || "[]");
    meals.push(product);
    localStorage.setItem(key, JSON.stringify(meals));
}

function deleteMeal(dateStr, time, product) {
    const key = `meals_${dateStr}_${time}`;
    let meals = JSON.parse(localStorage.getItem(key) || "[]");
    meals = meals.filter(p => p.product_name !== product.product_name);
    localStorage.setItem(key, JSON.stringify(meals));
}


function restoreMeals(section, dateStr) {
    section.querySelectorAll(".time-slot").forEach(slot => {
        const time = slot.getAttribute("data-time");
        const key = `meals_${dateStr}_${time}`;
        const meals = JSON.parse(localStorage.getItem(key) || "[]");

        meals.forEach(product => appendFoodToContainer(slot, product, dateStr, time));
    });
}

function autocomplete(input, listContainer, onSelectProduct) {
    let controller = null;

    input.addEventListener("input", async function () {
        const value = this.value.trim().toLowerCase();
        listContainer.innerHTML = ""
        if (controller) controller.abort();
        if (!value) return;

        controller = new AbortController();
        try {
            const url = `https://world.openfoodfacts.org/cgi/search.pl?search_terms=${encodeURIComponent(value)}&search_simple=1&action=process&json=1&page_size=20`;
            const res = await fetch(url, { signal: controller.signal });
            const data = await res.json();

            if (!data.products || data.products.length === 0) {
                const noItem = document.createElement("div");
                noItem.className = "px-2 py-1 text-gray-500 italic";
                noItem.innerText = `"${value}" not found.`;
                listContainer.appendChild(noItem);
                return;
            }

            const sorted = data.products
                .filter(p => p.product_name)
                .sort((a, b) => {
                    const an = a.product_name.toLowerCase();
                    const bn = b.product_name.toLowerCase();
                    const aStarts = an.startsWith(value);
                    const bStarts = bn.startsWith(value);
                    if (aStarts && !bStarts) return -1;
                    if (!aStarts && bStarts) return 1;
                    return an.localeCompare(bn); // fallback alfabetisch
                });

            sorted.forEach(product => {
                const lowerName = product.product_name.toLowerCase();
                const index = lowerName.indexOf(value);

                const item = document.createElement("div");
                item.className = "cursor-pointer px-2 py-1 hover:bg-gray-100 flex items-center";

                if (product.image_front_small_url || product.image_small_url || product.image_url) {
                    const img = document.createElement("img");
                    img.src = product.image_front_small_url || product.image_small_url || product.image_url;
                    img.className = "w-8 h-8 object-cover rounded mr-2";
                    item.appendChild(img);
                }

                const nameWrapper = document.createElement("div");
                nameWrapper.className = "truncate";

                if (index !== -1) {
                    const before = document.createTextNode(product.product_name.substring(0, index));
                    const match = document.createElement("span");
                    match.style.fontWeight = "bold";
                    match.innerText = product.product_name.substring(index, index + value.length);
                    const after = document.createTextNode(product.product_name.substring(index + value.length));

                    nameWrapper.appendChild(before);
                    nameWrapper.appendChild(match);
                    nameWrapper.appendChild(after);
                } else {
                    nameWrapper.innerText = product.product_name;
                }

                item.appendChild(nameWrapper);

                item.addEventListener("click", function () {
                    input.value = product.product_name;
                    listContainer.innerHTML = "";
                    showFoodInfo(product);
                    if (typeof onSelectProduct === "function") {
                        onSelectProduct(product);
                    }
                });

                listContainer.appendChild(item);
            });
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error("Error fetching OpenFoodFacts:", err);
            }
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

function setupMealModal() {
    const mealModal = document.getElementById("meal-modal");
    const closeBtn = document.getElementById("meal-modal-close-btn");
    closeBtn.addEventListener("click", () => {
        mealModal.classList.add("hidden");
        mealModal.classList.remove("flex");
    });
    mealModal.addEventListener("click", (e) => {
        if (e.target === mealModal) {
            mealModal.classList.add("hidden");
            mealModal.classList.remove("flex");
        }
    });

    document.addEventListener("click", (e) => {
        const mealItem = e.target.closest(".meal-item");
        if (!mealItem) return;
        const productName = mealItem.querySelector("span")?.innerText || "Onbekend";
        const productImg = mealItem.querySelector("img")?.src || "https://via.placeholder.com/128";
        document.getElementById("meal-modal-title").innerText = productName;
        document.getElementById("meal-modal-img").src = productImg;
        document.getElementById("meal-modal-brand").innerText = "Laden...";
        document.getElementById("meal-modal-energy").innerText = "Laden...";
        document.getElementById("meal-modal-sugars").innerText = "Laden...";

        mealModal.classList.remove("hidden");
        mealModal.classList.add("flex");

        fetchProductInfo(productName);
    });
}

async function fetchProductInfo(productName) {
    try {
        const url = `https://world.openfoodfacts.org/cgi/search.pl?search_terms=${encodeURIComponent(productName)}&search_simple=1&action=process&json=1&page_size=1`;
        const res = await fetch(url);
        const data = await res.json();

        if (data.products && data.products.length > 0) {
            const product = data.products[0];
            document.getElementById("meal-modal-brand").innerText = product.brands || "Onbekend merk";
            document.getElementById("meal-modal-energy").innerText =
                (product.nutriments?.energy_kcal_100g || product.nutriments?.energy_100g || "Onbekend") + " kcal / 100g";
            document.getElementById("meal-modal-sugars").innerText =
                (product.nutriments?.sugars_100g || "unknown") + " g / 100g";

            if (product.image_url) {
                document.getElementById("meal-modal-img").src = product.image_url;
            }
        } else {
            document.getElementById("meal-modal-brand").innerText = "No information found";
            document.getElementById("meal-modal-energy").innerText = "Not available";
            document.getElementById("meal-modal-sugars").innerText = "Not available";
        }
    } catch (error) {
        console.error("Fout bij ophalen productinfo:", error);
        document.getElementById("meal-modal-brand").innerText = "Error";
        document.getElementById("meal-modal-energy").innerText = "Try again";
        document.getElementById("meal-modal-sugars").innerText = "Try again";
    }
}

document.addEventListener("DOMContentLoaded", () => {
    setupMealModal();
});

function updateAITips(food) {
    const msgBox = document.getElementById("ai-messages");
    if (!msgBox) return;

    let message;
    const normalized = (food || "").toLowerCase();
    if (normalized.includes("ice") || normalized.includes("nutella") || normalized.includes("sugar")) {
        message = `"${food}" looks tasty BUT be careful with sugars!`;
    } else if (normalized.includes("apple") || normalized.includes("banana") || normalized.includes("salad") || normalized.includes("cucumber") || normalized.includes("mango")) {
        message = `Good! "${food}" that's a healthy choice.`;
    } else {
        message = `ℹ You added "${food}". Keeps a nice balance with your meals.`;
    }

    const p = document.createElement("p");
    p.innerText = message;
    msgBox.appendChild(p);

    const aiCoach = document.getElementById('ai-coach');
    if (aiCoach) aiCoach.classList.remove('translate-x-full');
}