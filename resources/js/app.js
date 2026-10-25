import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;
window.showPotionNotice = (message) => window.dispatchEvent(new CustomEvent('pv-notice', { detail: message }));
Alpine.data('potionUi', () => ({ notice: '', open: false, init() { window.addEventListener('pv-notice', event => { this.notice = event.detail; setTimeout(() => this.notice = '', 3200); }); } }));
Alpine.start();
document.addEventListener('DOMContentLoaded', () => {
    const imageInput = document.querySelector('[data-image-input]');
    const imagePreview = document.querySelector('[data-image-preview]');
    const previewImage = document.querySelector('[data-preview-image]');
    if (imageInput && imagePreview && previewImage) imageInput.addEventListener('change', () => {
        const [file] = imageInput.files;
        if (!file) { imagePreview.hidden = true; return; }
        previewImage.src = URL.createObjectURL(file);
        imagePreview.hidden = false;
    });
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.nav');
    if (menuToggle && nav) menuToggle.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        menuToggle.setAttribute('aria-expanded', String(open));
    });
    const trend = document.getElementById('rentalTrendChart');
    if (trend) new Chart(trend, { type: 'bar', data: { labels: JSON.parse(trend.dataset.labels), datasets: [{ label: 'Rental', data: JSON.parse(trend.dataset.values), backgroundColor: '#c89b3c', borderRadius: 6 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
    const rarity = document.getElementById('rarityDistributionChart');
    if (rarity) new Chart(rarity, { type: 'doughnut', data: { labels: JSON.parse(rarity.dataset.labels), datasets: [{ data: JSON.parse(rarity.dataset.values), backgroundColor: ['#9f7aea', '#46b9ff', '#f4c45f', '#ff6672'], borderColor: '#15122b', borderWidth: 3 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: '#d9cff4', padding: 14 } } } } });
});
