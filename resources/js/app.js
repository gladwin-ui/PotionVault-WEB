import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;
window.showPotionNotice = (message) => window.dispatchEvent(new CustomEvent('pv-notice', { detail: message }));
Alpine.data('potionUi', () => ({ notice: '', open: false, init() { window.addEventListener('pv-notice', event => { this.notice = event.detail; setTimeout(() => this.notice = '', 3200); }); } }));
Alpine.start();
document.addEventListener('DOMContentLoaded', () => { const canvas = document.getElementById('rentalTrendChart'); if (!canvas) return; new Chart(canvas, { type: 'bar', data: { labels: JSON.parse(canvas.dataset.labels), datasets: [{ label: 'Rentals', data: JSON.parse(canvas.dataset.values), backgroundColor: '#c89b3c', borderRadius: 6 }] }, options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } }); });
