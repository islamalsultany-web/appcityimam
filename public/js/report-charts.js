// Local canvas renderer: no third-party scripts, network requests or eval.
(() => {
    'use strict';
    class ReportChart {
        constructor(canvas, config) {
            this.canvas = canvas; this.config = config;
            this.observer = new ResizeObserver(() => this.resize());
            this.observer.observe(canvas.parentElement); this.resize();
        }
        resize(width, height) {
            const w = width || this.canvas.parentElement.clientWidth || 600, h = height || 230;
            const ratio = window.devicePixelRatio || 1;
            this.canvas.width = w * ratio; this.canvas.height = h * ratio;
            this.canvas.style.width = '100%'; this.canvas.style.height = `${h}px`;
            const ctx = this.canvas.getContext('2d'); ctx.scale(ratio, ratio); ctx.clearRect(0, 0, w, h);
            ctx.font = '12px Cairo, Arial, sans-serif'; ctx.direction = 'rtl';
            const data = this.config.data, values = data.datasets[0].data, colors = data.datasets[0].backgroundColor;
            if (this.config.type === 'doughnut') {
                const sum = values.reduce((a, b) => a + b, 0), cx = w * .28, cy = h / 2, radius = Math.min(w * .19, 82);
                if (!sum) { ctx.textAlign = 'center'; ctx.fillStyle = '#64748b'; ctx.fillText('لا توجد قيم لعرض نسب المخطط', w / 2, h / 2); return; }
                let start = -Math.PI / 2;
                values.forEach((value, i) => { const end = start + value / sum * Math.PI * 2; ctx.beginPath(); ctx.arc(cx, cy, radius, start, end); ctx.strokeStyle = colors[i % colors.length]; ctx.lineWidth = 24; ctx.stroke(); start = end; });
                ctx.textAlign = 'center'; ctx.fillStyle = '#243241'; ctx.fillText(sum.toLocaleString('ar-IQ'), cx, cy + 4);
                values.forEach((value, i) => { const y = 18 + i * Math.min(22, (h - 30) / values.length); ctx.fillStyle = colors[i % colors.length]; ctx.fillRect(w - 12, y - 9, 10, 10); ctx.fillStyle = '#243241'; ctx.textAlign = 'right'; const label = `${data.labels[i]}: ${(value / sum * 100).toFixed(1)}%`; ctx.fillText(label, w - 30, y, w * .5); });
            } else {
                const max = Math.max(1, ...values), rowHeight = Math.min(36, (h - 12) / values.length), labelWidth = Math.min(150, w * .32), usable = Math.max(20, w - labelWidth - 65);
                values.forEach((value, i) => { const y = 8 + i * rowHeight, bar = value / max * usable; ctx.fillStyle = '#edf2f5'; ctx.fillRect(45, y, usable, rowHeight - 9); ctx.fillStyle = colors; ctx.fillRect(45 + usable - bar, y, bar, rowHeight - 9); ctx.fillStyle = '#243241'; ctx.textAlign = 'right'; ctx.fillText(data.labels[i], w - 3, y + rowHeight * .55, labelWidth - 8); ctx.fillStyle = '#64748b'; ctx.fillText(value.toLocaleString('ar-IQ'), 39, y + rowHeight * .55, 38); });
            }
        }
        destroy() { this.observer.disconnect(); this.canvas.getContext('2d').clearRect(0, 0, this.canvas.width, this.canvas.height); }
    }
    window.Chart = ReportChart;
})();
