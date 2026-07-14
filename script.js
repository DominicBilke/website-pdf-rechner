document.addEventListener('DOMContentLoaded', () => {
    const els = {
        uploadForm: document.getElementById('uploadForm'),
        pdfFile: document.getElementById('pdfFile'),
        dropZone: document.getElementById('dropZone'),
        fileList: document.getElementById('fileList'),
        queueCount: document.getElementById('queueCount'),
        progressBar: document.getElementById('progressBar'),
        processBtn: document.getElementById('processBtn'),
        language: document.getElementById('language'),
        uploadStatus: document.getElementById('uploadStatus'),
        resultList: document.getElementById('resultList'),
        clearResults: document.getElementById('clearResults'),
        clearAll: document.getElementById('clearAll'),
        summaryBody: document.getElementById('summaryBody'),
        metricInvoices: document.getElementById('metricInvoices'),
        metricGross: document.getElementById('metricGross'),
        metricVat: document.getElementById('metricVat'),
        langDe: document.getElementById('langDe'),
        langEn: document.getElementById('langEn')
    };

    const copy = {
        de: {
            brandSubtitle: 'PDF-Rechner',
            navUpload: 'Upload',
            navSummary: 'Auswertung',
            eyebrow: 'Rechnungen schneller erfassen',
            heroTitle: 'PDF-Rechnungen hochladen, Betraege pruefen, Monate auswerten.',
            heroText: 'Ein kompaktes Dashboard fuer OCR-Erkennung, Netto-/MwSt.-Berechnung und monatliche Uebersichten.',
            metricInvoices: 'Rechnungen',
            metricGross: 'Brutto gesamt',
            metricVat: 'MwSt. gesamt',
            uploadKicker: 'Import',
            uploadTitle: 'Rechnungen verarbeiten',
            dropTitle: 'PDFs auswaehlen oder hier ablegen',
            dropHelp: 'Mehrere Dateien moeglich, maximal 10 MB je PDF.',
            languageLabel: 'Rechnungssprache',
            processBtn: 'Verarbeiten',
            queueTitle: 'Upload-Warteschlange',
            resultsKicker: 'Extraktion',
            resultsTitle: 'Letzte Ergebnisse',
            clearResults: 'Liste leeren',
            emptyResults: 'Noch keine Verarbeitung gestartet.',
            summaryKicker: 'Monate',
            summaryTitle: 'Finanzuebersicht',
            deleteAll: 'Alle Daten loeschen',
            monthCol: 'Monat',
            countCol: 'Anzahl',
            netCol: 'Netto',
            vatCol: 'MwSt.',
            grossCol: 'Brutto',
            emptySummary: 'Noch keine Rechnungen verarbeitet.',
            selected: 'ausgewaehlt',
            pending: 'Wartet',
            processing: 'Laeuft',
            success: 'Fertig',
            error: 'Fehler',
            noFiles: 'Bitte waehlen Sie mindestens eine PDF-Datei aus.',
            uploading: 'Rechnungen werden verarbeitet...',
            complete: 'Verarbeitung abgeschlossen.',
            cleared: 'Alle Daten wurden geloescht.',
            confirmClear: 'Alle gespeicherten Rechnungen und Uploads wirklich loeschen?',
            requestFailed: 'Serverantwort konnte nicht gelesen werden.',
            fileTooLarge: 'Datei ist groesser als 10 MB.',
            invalidFile: 'Nur PDF-Dateien werden verarbeitet.',
            date: 'Datum',
            net: 'Netto',
            vat: 'MwSt.',
            gross: 'Brutto',
            vatRate: 'Satz',
            method: 'Quelle',
            invoices: 'Rechnungen'
        },
        en: {
            brandSubtitle: 'PDF calculator',
            navUpload: 'Upload',
            navSummary: 'Summary',
            eyebrow: 'Capture invoices faster',
            heroTitle: 'Upload invoice PDFs, verify amounts, analyze months.',
            heroText: 'A compact dashboard for OCR extraction, net/VAT calculation, and monthly summaries.',
            metricInvoices: 'Invoices',
            metricGross: 'Gross total',
            metricVat: 'VAT total',
            uploadKicker: 'Import',
            uploadTitle: 'Process invoices',
            dropTitle: 'Choose PDFs or drop them here',
            dropHelp: 'Multiple files supported, up to 10 MB per PDF.',
            languageLabel: 'Invoice language',
            processBtn: 'Process',
            queueTitle: 'Upload queue',
            resultsKicker: 'Extraction',
            resultsTitle: 'Latest results',
            clearResults: 'Clear list',
            emptyResults: 'No processing started yet.',
            summaryKicker: 'Months',
            summaryTitle: 'Financial overview',
            deleteAll: 'Delete all data',
            monthCol: 'Month',
            countCol: 'Count',
            netCol: 'Net',
            vatCol: 'VAT',
            grossCol: 'Gross',
            emptySummary: 'No invoices processed yet.',
            selected: 'selected',
            pending: 'Pending',
            processing: 'Processing',
            success: 'Done',
            error: 'Error',
            noFiles: 'Please select at least one PDF file.',
            uploading: 'Processing invoices...',
            complete: 'Processing complete.',
            cleared: 'All data has been deleted.',
            confirmClear: 'Really delete all stored invoices and uploads?',
            requestFailed: 'Could not read the server response.',
            fileTooLarge: 'File is larger than 10 MB.',
            invalidFile: 'Only PDF files are processed.',
            date: 'Date',
            net: 'Net',
            vat: 'VAT',
            gross: 'Gross',
            vatRate: 'Rate',
            method: 'Source',
            invoices: 'invoices'
        }
    };

    const state = {
        lang: localStorage.getItem('invoicePilotLang') || 'de',
        files: [],
        results: [],
        processing: false
    };

    applyLanguage();
    bindEvents();
    loadSummary();

    function bindEvents() {
        els.langDe.addEventListener('click', () => setLanguage('de'));
        els.langEn.addEventListener('click', () => setLanguage('en'));
        els.pdfFile.addEventListener('change', () => setFiles(Array.from(els.pdfFile.files)));
        els.uploadForm.addEventListener('submit', processSelectedFiles);
        els.clearResults.addEventListener('click', clearLocalResults);
        els.clearAll.addEventListener('click', clearAllData);

        ['dragenter', 'dragover'].forEach((eventName) => {
            els.dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                els.dropZone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            els.dropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                els.dropZone.classList.remove('drag-over');
            });
        });

        els.dropZone.addEventListener('drop', (event) => {
            setFiles(Array.from(event.dataTransfer.files || []));
        });
    }

    function setLanguage(lang) {
        state.lang = lang;
        localStorage.setItem('invoicePilotLang', lang);
        applyLanguage();
        renderQueue();
        renderResults();
        loadSummary();
    }

    function applyLanguage() {
        document.documentElement.lang = state.lang;
        document.querySelectorAll('[data-i18n]').forEach((node) => {
            const key = node.getAttribute('data-i18n');
            node.textContent = t(key);
        });
        els.langDe.classList.toggle('active', state.lang === 'de');
        els.langEn.classList.toggle('active', state.lang === 'en');
    }

    function setFiles(files) {
        state.files = files.map((file, index) => ({
            id: `${Date.now()}-${index}`,
            file,
            status: validateFile(file)
        }));
        renderQueue();
    }

    function validateFile(file) {
        if (!file.name.toLowerCase().endsWith('.pdf') && file.type !== 'application/pdf') {
            return 'invalid';
        }
        if (file.size > 10 * 1024 * 1024) {
            return 'oversize';
        }
        return 'pending';
    }

    async function processSelectedFiles(event) {
        event.preventDefault();
        const queue = state.files.filter((entry) => entry.status === 'pending');

        if (!queue.length) {
            showStatus(state.files.length ? t('invalidFile') : t('noFiles'), 'error');
            return;
        }

        state.processing = true;
        els.processBtn.disabled = true;
        showStatus(t('uploading'), 'processing');

        let completed = 0;
        for (const entry of queue) {
            entry.status = 'processing';
            renderQueue(completed / queue.length);

            const formData = new FormData();
            formData.append('pdfFile', entry.file);
            formData.append('language', els.language.value);

            try {
                const response = await fetch('process_pdf.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || t('requestFailed'));
                }

                entry.status = 'success';
                prependResult({
                    ok: true,
                    filename: entry.file.name,
                    invoice: data.invoice,
                    extraction: data.extraction || {}
                });
            } catch (error) {
                entry.status = 'error';
                prependResult({
                    ok: false,
                    filename: entry.file.name,
                    message: error.message || t('requestFailed')
                });
            }

            completed += 1;
            renderQueue(completed / queue.length);
        }

        state.processing = false;
        els.processBtn.disabled = false;
        showStatus(t('complete'), 'success');
        els.uploadForm.reset();
        loadSummary();
    }

    function prependResult(result) {
        state.results.unshift(result);
        state.results = state.results.slice(0, 12);
        renderResults();
    }

    function clearLocalResults() {
        state.results = [];
        renderResults();
        hideStatus();
    }

    async function clearAllData() {
        if (!window.confirm(t('confirmClear'))) {
            return;
        }

        try {
            const response = await fetch('clear_data.php', { method: 'POST' });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || t('requestFailed'));
            }

            state.files = [];
            state.results = [];
            renderQueue();
            renderResults();
            showStatus(t('cleared'), 'success');
            loadSummary();
        } catch (error) {
            showStatus(error.message || t('requestFailed'), 'error');
        }
    }

    async function loadSummary() {
        try {
            const response = await fetch(`get_summary.php?lang=${encodeURIComponent(state.lang)}`);
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || t('requestFailed'));
            }
            renderSummary(data.summary || [], data.totals || null);
        } catch (error) {
            els.summaryBody.replaceChildren(emptySummaryRow(error.message || t('requestFailed')));
        }
    }

    function renderQueue(progress = null) {
        els.queueCount.textContent = `${state.files.length} ${t('selected')}`;
        els.fileList.replaceChildren();

        if (!state.files.length) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = t('emptyResults');
            els.fileList.append(empty);
            els.progressBar.style.width = '0%';
            return;
        }

        state.files.forEach((entry) => {
            const item = document.createElement('article');
            item.className = 'file-item';

            const main = document.createElement('div');
            const name = document.createElement('div');
            name.className = 'file-name';
            name.textContent = entry.file.name;

            const meta = document.createElement('div');
            meta.className = 'file-meta';
            meta.textContent = formatBytes(entry.file.size);

            main.append(name, meta);
            item.append(main, badgeFor(entry.status));
            els.fileList.append(item);
        });

        if (progress === null) {
            const done = state.files.filter((entry) => entry.status === 'success' || entry.status === 'error').length;
            progress = state.files.length ? done / state.files.length : 0;
        }
        els.progressBar.style.width = `${Math.round(progress * 100)}%`;
    }

    function renderResults() {
        els.resultList.replaceChildren();
        if (!state.results.length) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.textContent = t('emptyResults');
            els.resultList.append(empty);
            return;
        }

        state.results.forEach((result) => {
            const card = document.createElement('article');
            card.className = `result-card${result.ok ? '' : ' error'}`;

            const title = document.createElement('div');
            title.className = 'result-title';
            title.textContent = result.filename;

            const meta = document.createElement('div');
            meta.className = 'result-meta';
            meta.textContent = result.ok
                ? `${t('method')}: ${result.extraction.method || 'OCR'}`
                : result.message;

            card.append(title, meta);

            if (result.ok) {
                const invoice = result.invoice || {};
                const grid = document.createElement('div');
                grid.className = 'result-grid';
                [
                    [t('date'), invoice.date || '-'],
                    [t('gross'), money(invoice.amount)],
                    [t('net'), money(invoice.net_amount)],
                    [t('vat'), money(invoice.vat_amount)],
                    [t('vatRate'), invoice.vat ? `${invoice.vat}%` : '-']
                ].forEach(([label, value]) => grid.append(resultStat(label, value)));
                card.append(grid);
            }

            els.resultList.append(card);
        });
    }

    function renderSummary(summary, totals) {
        els.summaryBody.replaceChildren();

        if (!summary.length) {
            els.summaryBody.append(emptySummaryRow(t('emptySummary')));
        } else {
            summary.forEach((month) => {
                const row = document.createElement('tr');
                [
                    month.month_label || month.month,
                    month.invoice_count,
                    money(month.total_net),
                    money(month.total_vat),
                    money(month.total)
                ].forEach((value) => {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.append(cell);
                });
                els.summaryBody.append(row);
            });
        }

        const derivedTotals = totals || summary.reduce((acc, month) => {
            acc.invoice_count += Number(month.invoice_count || 0);
            acc.total += Number(month.total || 0);
            acc.total_vat += Number(month.total_vat || 0);
            return acc;
        }, { invoice_count: 0, total: 0, total_vat: 0 });

        els.metricInvoices.textContent = derivedTotals.invoice_count || 0;
        els.metricGross.textContent = money(derivedTotals.total);
        els.metricVat.textContent = money(derivedTotals.total_vat);
    }

    function resultStat(label, value) {
        const stat = document.createElement('div');
        stat.className = 'result-stat';
        const labelNode = document.createElement('span');
        labelNode.textContent = label;
        const valueNode = document.createElement('strong');
        valueNode.textContent = value;
        stat.append(labelNode, valueNode);
        return stat;
    }

    function emptySummaryRow(message) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.className = 'empty-cell';
        cell.colSpan = 5;
        cell.textContent = message;
        row.append(cell);
        return row;
    }

    function badgeFor(status) {
        const badge = document.createElement('span');
        const normalized = status === 'invalid' || status === 'oversize' ? 'error' : status;
        badge.className = `badge ${normalized}`;
        badge.textContent = status === 'invalid'
            ? t('invalidFile')
            : status === 'oversize'
                ? t('fileTooLarge')
                : t(status);
        return badge;
    }

    function showStatus(message, type) {
        els.uploadStatus.textContent = message;
        els.uploadStatus.className = `status-message ${type} active`;
    }

    function hideStatus() {
        els.uploadStatus.className = 'status-message';
        els.uploadStatus.textContent = '';
    }

    function money(value) {
        const amount = Number(value || 0);
        return new Intl.NumberFormat(state.lang === 'de' ? 'de-DE' : 'en-US', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }

    function formatBytes(bytes) {
        if (!bytes) {
            return '0 KB';
        }
        const units = ['B', 'KB', 'MB', 'GB'];
        const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        const value = bytes / Math.pow(1024, index);
        return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
    }

    function t(key) {
        return copy[state.lang][key] || copy.de[key] || key;
    }
});
