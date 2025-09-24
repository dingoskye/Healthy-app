//Globals
const settingsBtn = document.getElementById('settingsBtn');
const settingsSidebar = document.getElementById('settingsSidebar');
const closeSettings = document.getElementById('closeSettings');
const settingsBackdrop = document.getElementById('settingsBackdrop');

//opening the sidebar
function openSettings() {
    settingsSidebar.classList.remove('translate-x-full');
    settingsBackdrop.classList.remove('hidden');
}

//closing the sidebar
function closeSettingsSidebar() {
    settingsSidebar.classList.add('translate-x-full');
    settingsBackdrop.classList.add('hidden');
}

//events
settingsBtn.addEventListener('click', (e) => {
    e.preventDefault();
    openSettings();
});

closeSettings.addEventListener('click', closeSettingsSidebar);
settingsBackdrop.addEventListener('click', closeSettingsSidebar);