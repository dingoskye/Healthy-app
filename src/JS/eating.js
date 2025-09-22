async function addMeal(day, time) {
    const query = prompt(`What did you eat at ${day} ${time}? (Enter product name or barcode)`);
    if (!query) return;

    let url;
    if (/^\d+$/.test(query)) {
        url = `https://world.openfoodfacts.org/api/v0/product/${query}.json`;
    } else {
        url = `https://world.openfoodfacts.org/cgi/search.pl?search_terms=${encodeURIComponent(query)}&search_simple=1&action=process&json=1`;
    }

    try {
        const res = await fetch(url);
        const data = await res.json();

        let productName = "Unknown food";
        if (data.product && data.product.product_name) {
            productName = data.product.product_name;
        } else if (data.products && data.products.length > 0) {
            productName = data.products[0].product_name || query;
        }

    }
}
