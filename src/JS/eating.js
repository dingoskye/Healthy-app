function autocomplete(input, listContainer) {
    input.addEventListener("input", async function () {
        let value = this.value.trim().toLowerCase();
        listContainer.innerHTML = "";

        if (!value) return;

        try {
            // !! Deze is voor alle talen. heeft ook NL. IS
            const url = `https://world.openfoodfacts.org/cgi/search.pl?search_terms=${encodeURIComponent(value)}&search_simple=1&action=process&json=1&page_size=8`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.products && data.products.length > 0) {
                let valid = false;

                data.products.forEach(product => {
                    const name = product.product_name;
                    if (!name) return;

                    const lowerName = name.toLowerCase();
                    const index = lowerName.indexOf(value);

                    if (index !== -1) {
                        const highlighted =
                            name.substr(0, index) +
                            `<span class="font-bold">${name.substr(index, value.length)}</span>` +
                            name.substr(index + value.length);

                        const item = document.createElement("div");
                        item.innerHTML = highlighted;
                        item.className = "cursor-pointer px-2 py-1 hover:bg-gray-100";

                        item.addEventListener("click", function () {
                            input.value = name;
                            listContainer.innerHTML = "";
                        });

                        listContainer.appendChild(item);
                        valid = true;
                    }
                });

                if (!valid) {
                    const error = document.createElement("div");
                    error.textContent = `"${value}" not found.`;
                    error.className = "text-red-500 px-2 py-1";
                    listContainer.appendChild(error);
                }
            }
        } catch (err) {
            console.error("Error fetching OpenFoodFacts:", err);
        }
    });

    document.addEventListener("click", () => (listContainer.innerHTML = ""));
}

function addMeal(day, time) {
    const containerId = `${day}-${time}`.replace(/\s|:/g, "-");
    const container = document.getElementById(containerId);

    if (container.querySelector("input")) return;

    const wrapper = document.createElement("div");
    wrapper.className = "mt-2 relative";

    const input = document.createElement("input");
    input.type = "text";
    input.placeholder = "Search food...";
    input.className = "border rounded px-2 py-1 w-full";
    input.id = `${containerId}-input`;

    const listContainer = document.createElement("div");
    listContainer.className =
        "border rounded bg-white absolute z-10 max-h-40 overflow-y-auto w-full shadow";

    const btn = document.createElement("button");
    btn.textContent = "Add";
    btn.className =
        "mt-2 bg-[var(--elements)] text-white rounded px-3 py-1 hover:opacity-90 block";
    btn.onclick = () => {
        const food = input.value.trim();
        if (!food) return;

        let ul = container.querySelector("ul");
        if (!ul) {
            ul = document.createElement("ul");
            ul.className = "list-disc list-inside text-gray-700";
            container.appendChild(ul);
        }
        const li = document.createElement("li");
        li.textContent = food;
        ul.appendChild(li);

        wrapper.remove();
    };

    wrapper.appendChild(input);
    wrapper.appendChild(listContainer);
    wrapper.appendChild(btn);
    container.appendChild(wrapper);

    autocomplete(input, listContainer);
}

//moet nog de AI API gebruiken
function updateAITips(food) {
    const aiBox = document.getElementById("ai-coach");
    let message;

    if (["Ice Cream", "Nutella"].includes(food)) {
        message = `"${food}" is lekker, maar probeer niet te veel suiker!`;
    } else if (["Apple", "Banana", "Salad", "Cucumber"].includes(food)) {
        message = `Goed bezig! "${food}" is een gezonde keuze.`;
    } else {
        message = `ℹJe hebt "${food}" toegevoegd. Balans is belangrijk.`;
    }

    aiBox.innerHTML = `<p class="text-gray-700">${message}</p>`;
}
