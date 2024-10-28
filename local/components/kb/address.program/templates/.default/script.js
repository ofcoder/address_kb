function changeToggleIcon() {
    let popup = document.querySelector('.address-container');
    popup.classList.toggle('df');
    let icon = document.querySelector('.hover-toggle');
    icon.classList.toggle('hover-toggle-up');
    icon.classList.toggle('hover-toggle-down');
}