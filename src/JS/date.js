// date.js
const d = new Date();
const n = d.getDate();
const s = ["th","st","nd","rd"][n%10] && ![11,12,13].includes(n%100)
    ? ["th","st","nd","rd"][n%10]
    : "th";

document.getElementById("current-date").textContent =
    d.toLocaleString('en-US', { month: 'long' }) + " " + n + s;
