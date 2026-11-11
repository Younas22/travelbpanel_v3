{{--
    Invoice page actions, shared by the hotel, flight, tour and umrah invoices:
    Print (one A4 sheet), Download PDF, Send Email (button data-url) and a
    single Pay Now submit. Needs #invDoc, #invPrint, #invPdf, #invEmail,
    #invToast and optional #invPayForm. Param: $invoiceRef (PDF file name).
--}}
<script>
(function () {
    const toast = document.getElementById('invToast');
    let toastTimer;
    function notify(message, isError) {
        toast.textContent = message;
        toast.classList.toggle('is-error', !!isError);
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.hidden = true, 5000);
    }
    function busy(btn, on, label) {
        const text = btn.querySelector('span');
        if (on) { btn.dataset.label = text.textContent; text.textContent = label; }
        else if (btn.dataset.label) { text.textContent = btn.dataset.label; }
        btn.disabled = on;
        btn.classList.toggle('is-loading', on);
    }

    // A4 sheet = 794 x 1123 px. Slightly taller content is scaled to fit one
    // page (not below 80%); longer content flows onto more A4 pages.
    const A4_W = 794, A4_H = 1123, MIN_FIT = 0.8;
    function sheetFit(height) {
        // a few px of headroom so rounding never pushes the sheet onto page 2
        const fit = (A4_H - 8) / height;
        return fit >= 1 || fit < MIN_FIT ? 1 : fit;
    }

    // Print: measure the sheet as printed (logo shown, Pay Now hidden)
    window.addEventListener('beforeprint', () => {
        document.body.classList.add('is-pdf', 'is-measure');
        const height = document.getElementById('invDoc').offsetHeight;
        document.body.classList.remove('is-pdf', 'is-measure');
        document.documentElement.style.setProperty('--inv-fit', sheetFit(height));
    });
    window.addEventListener('afterprint', () => document.documentElement.style.removeProperty('--inv-fit'));
    document.getElementById('invPrint').addEventListener('click', () => window.print());

    // PDF: capture the sheet (action bar and Pay Now are skipped) and place it
    // on A4 pages. The libraries are only downloaded when someone asks for a PDF.
    const pdfBtn = document.getElementById('invPdf');
    function loadScript(src, ready) {
        if (ready()) return Promise.resolve();
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }
    // html2canvas misplaces icon-font glyphs, stretches object-fit images and
    // (with Tailwind's `img { display: block }`) draws text too low. So, for
    // the PDF only: each icon is drawn here as the browser lays it out and
    // given to the copy as a picture, and images become backgrounds.
    const BLANK = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    function pdfParts(sheet) {
        const icons = [];
        sheet.querySelectorAll('i').forEach((icon) => {
            const box = icon.getBoundingClientRect();
            const before = getComputedStyle(icon, '::before');
            const glyph = (before.content || '').replace(/^["']|["']$/g, '');
            if (!glyph || glyph === 'none' || !box.width || !box.height) return;
            // drawn 4x, with a 1px empty edge so scaling never bleeds the glyph across
            const k = 4, pad = 4;
            const canvas = document.createElement('canvas');
            canvas.width = Math.ceil(box.width * k) + pad * 2;
            canvas.height = Math.ceil(box.height * k) + pad * 2;
            const ctx = canvas.getContext('2d');
            ctx.font = `${before.fontStyle} ${before.fontWeight} ${parseFloat(before.fontSize) * k}px ${before.fontFamily}`;
            ctx.fillStyle = before.color;
            // same side of a wide icon box as the page (text-align / direction)
            const cs = getComputedStyle(icon);
            const align = { start: cs.direction === 'rtl' ? 'right' : 'left', end: cs.direction === 'rtl' ? 'left' : 'right' }[cs.textAlign] || cs.textAlign;
            const x = align === 'left' ? pad + parseFloat(cs.paddingLeft) * k
                : align === 'right' ? canvas.width - pad - parseFloat(cs.paddingRight) * k
                : canvas.width / 2;
            ctx.textAlign = ['left', 'right'].includes(align) ? align : 'center';
            const m = ctx.measureText(glyph);
            ctx.fillText(glyph, x, (canvas.height - m.fontBoundingBoxAscent - m.fontBoundingBoxDescent) / 2 + m.fontBoundingBoxAscent);
            icon.dataset.pdfIcon = icons.push([canvas.toDataURL(), canvas.width / k, canvas.height / k, pad / k]) - 1;
        });
        const images = [];
        sheet.querySelectorAll('img').forEach((img) => {
            const cs = getComputedStyle(img);
            if (!img.currentSrc || !['cover', 'contain'].includes(cs.objectFit)) return;
            // contain: the picture's own box inside the padding (no stretching)
            let place = null;
            if (cs.objectFit === 'contain' && img.naturalWidth && img.naturalHeight) {
                const box = img.getBoundingClientRect();
                const pl = parseFloat(cs.paddingLeft), pt = parseFloat(cs.paddingTop);
                const w = box.width - pl - parseFloat(cs.paddingRight), h = box.height - pt - parseFloat(cs.paddingBottom);
                const scale = Math.min(w / img.naturalWidth, h / img.naturalHeight);
                const fw = img.naturalWidth * scale, fh = img.naturalHeight * scale;
                place = { left: img.offsetLeft + pl + (w - fw) / 2, top: img.offsetTop + pt + (h - fh) / 2, width: fw, height: fh };
            }
            img.dataset.pdfImage = images.push([img.currentSrc, cs.objectFit, place]) - 1;
        });
        return function (doc) {
            const style = doc.createElement('style');
            style.textContent = 'i[data-pdf-icon] > * { visibility: hidden !important; }';
            doc.head.appendChild(style);
            doc.querySelectorAll('[data-pdf-icon]').forEach((icon) => {
                const [src, w, h, edge] = icons[icon.dataset.pdfIcon];
                Object.assign(icon.style, { backgroundImage: `url(${src})`, backgroundSize: `${w}px ${h}px`, backgroundPosition: `-${edge}px -${edge}px`, backgroundRepeat: 'no-repeat' });
            });
            doc.querySelectorAll('[data-pdf-image]').forEach((img) => {
                const [src, fit, place] = images[img.dataset.pdfImage];
                if (place) {
                    Object.assign(img.style, { inset: 'auto', left: place.left + 'px', top: place.top + 'px', width: place.width + 'px', height: place.height + 'px', padding: '0', objectFit: 'fill' });
                    return;
                }
                Object.assign(img.style, { backgroundImage: `url("${src}")`, backgroundSize: fit, backgroundPosition: 'center', backgroundRepeat: 'no-repeat', backgroundOrigin: 'content-box', backgroundClip: 'content-box' });
                img.src = BLANK;
            });
        };
    }
    async function buildPdf() {
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', () => window.html2canvas);
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', () => window.jspdf);
        const sheet = document.getElementById('invDoc');
        // html2canvas measures the text baseline on this page: let its test image sit inline
        const baseline = document.createElement('style');
        baseline.textContent = 'body > div[style*="hidden"] > img { display: inline !important; }';
        document.head.appendChild(baseline);
        let canvas;
        try {
            canvas = await html2canvas(sheet, { scale: 2, useCORS: true, backgroundColor: '#FFFFFF', windowWidth: 1100, onclone: pdfParts(sheet) });
        } finally {
            baseline.remove();
            sheet.querySelectorAll('[data-pdf-icon], [data-pdf-image]').forEach((el) => { delete el.dataset.pdfIcon; delete el.dataset.pdfImage; });
        }
        const pdf = new window.jspdf.jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });
        const pageW = 210, pageH = 297;
        const imgH = canvas.height * pageW / canvas.width;
        const fit = sheetFit(imgH * A4_H / pageH);
        if (imgH * fit <= pageH + 0.5) {
            // one page, scaled down a little when needed and centred
            const w = pageW * fit;
            pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', (pageW - w) / 2, 0, w, Math.min(imgH * fit, pageH));
        } else {
            // longer documents: one A4 slice per page
            const slicePx = Math.floor(canvas.width * pageH / pageW);
            for (let y = 0, page = 0; y < canvas.height; y += slicePx, page++) {
                const part = document.createElement('canvas');
                part.width = canvas.width;
                part.height = Math.min(slicePx, canvas.height - y);
                part.getContext('2d').drawImage(canvas, 0, -y);
                if (page) pdf.addPage();
                pdf.addImage(part.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, pageW, part.height * pageW / canvas.width);
            }
        }
        return pdf;
    }
    pdfBtn.addEventListener('click', async () => {
        if (pdfBtn.disabled) return;
        busy(pdfBtn, true, @json(t('hotelinvoice.preparingPdf')));
        document.body.classList.add('is-pdf');
        try {
            (await buildPdf()).save(@json('invoice-' . $invoiceRef . '.pdf'));
        } catch (e) {
            notify(@json(t('hotelinvoice.pdfFailed')), true);
        } finally {
            document.body.classList.remove('is-pdf');
            busy(pdfBtn, false);
        }
    });
    window.invBuildPdf = buildPdf;

    // Email the invoice link to the booking contact (once at a time)
    const emailBtn = document.getElementById('invEmail');
    emailBtn.addEventListener('click', async () => {
        if (emailBtn.disabled) return;
        busy(emailBtn, true, @json(t('hotelinvoice.sending')));
        try {
            const res = await fetch(emailBtn.dataset.url, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                credentials: 'same-origin',
            });
            const json = await res.json().catch(() => ({}));
            notify(json.message || @json(t('hotelinvoice.emailFailed')), !res.ok || !json.success);
        } catch (e) {
            notify(@json(t('hotelinvoice.emailFailed')), true);
        } finally {
            busy(emailBtn, false);
        }
    });

    // Pay Now: one submission only
    const payForm = document.getElementById('invPayForm');
    if (payForm) payForm.addEventListener('submit', (e) => {
        const btn = payForm.querySelector('button');
        if (btn.disabled) { e.preventDefault(); return; }
        busy(btn, true, @json(t('hotelinvoice.processing')));
    });
    window.addEventListener('pageshow', (e) => {
        if (e.persisted && payForm) busy(payForm.querySelector('button'), false);
    });
})();
</script>
