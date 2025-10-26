document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const pdfFile = document.getElementById('pdfFile');
    const uploadStatus = document.getElementById('uploadStatus');
    const invoiceDetails = document.getElementById('invoiceDetails');
    const monthlySummary = document.getElementById('monthlySummary');
    const clearAllBtn = document.getElementById('clearAll');
    const selectedFilesDiv = document.getElementById('selectedFiles');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const langEnBtn = document.getElementById('langEn');
    const langDeBtn = document.getElementById('langDe');

    // Initialize language from localStorage or default to German
    let currentLang = localStorage.getItem('language') || 'de';
    updateLanguage(currentLang);
    
    // Setup language switcher
    langEnBtn.addEventListener('click', () => switchLanguage('en'));
    langDeBtn.addEventListener('click', () => switchLanguage('de'));
    
    function switchLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('language', lang);
        updateLanguage(lang);
    }
    
    function updateLanguage(lang) {
        // Update all elements with data attributes
        document.querySelectorAll('[data-en][data-de]').forEach(el => {
            el.textContent = el.getAttribute(`data-${lang}`);
        });
        
        // Update button states
        langEnBtn.classList.toggle('active', lang === 'en');
        langDeBtn.classList.toggle('active', lang === 'de');
        
        // Update selected files display if files are selected
        if (selectedFilesDiv.classList.contains('active') && pdfFile.files.length > 0) {
            const titleText = lang === 'de' ? 'Ausgewählte Dateien' : 'Selected files';
            let html = `<div class="selected-files-title">${titleText} (${pdfFile.files.length}):</div>`;
            Array.from(pdfFile.files).forEach((file, index) => {
                const fileSize = (file.size / 1024).toFixed(2);
                html += `<div class="selected-file-item">
                    <span class="selected-file-name">${file.name}</span>
                    <span class="selected-file-size">${fileSize} KB</span>
                </div>`;
            });
            selectedFilesDiv.innerHTML = html;
        }
    }

    // Load monthly summary on page load
    loadMonthlySummary();
    
    // Show selected files
    pdfFile.addEventListener('change', function() {
        const files = this.files;
        if (files.length > 0) {
            const titleText = currentLang === 'de' ? 'Ausgewählte Dateien' : 'Selected files';
            let html = `<div class="selected-files-title">${titleText} (${files.length}):</div>`;
            Array.from(files).forEach((file, index) => {
                const fileSize = (file.size / 1024).toFixed(2);
                html += `<div class="selected-file-item">
                    <span class="selected-file-name">${file.name}</span>
                    <span class="selected-file-size">${fileSize} KB</span>
                </div>`;
            });
            selectedFilesDiv.innerHTML = html;
            selectedFilesDiv.classList.add('active');
        } else {
            selectedFilesDiv.classList.remove('active');
        }
    });

    // Handle file upload (single or multiple files)
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!pdfFile.files.length) {
            const errorMsg = currentLang === 'de' ? 'Bitte wählen Sie PDF-Dateien aus.' : 'Please select PDF files.';
            showStatus(errorMsg, 'error');
            return;
        }

        const files = pdfFile.files;
        const language = document.getElementById('language').value;
        
        // If only one file, use original behavior
        if (files.length === 1) {
            uploadSingleFile(files[0], language);
        } else {
            // Multiple files - upload sequentially
            uploadMultipleFiles(files, language);
        }
    });
    
    function uploadSingleFile(file, language) {
        const formData = new FormData();
        formData.append('pdfFile', file);
        formData.append('language', language);
        
        const processingMsg = currentLang === 'de' ? 'Rechnung wird verarbeitet...' : 'Processing invoice...';
        showStatus(processingMsg, 'processing');
        
        fetch('process_pdf.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const successMsg = currentLang === 'de' ? 'Rechnung erfolgreich verarbeitet!' : 'Invoice processed successfully!';
                showStatus(successMsg, 'success');
                displayInvoiceDetails(data.invoice);
                loadMonthlySummary();
                
                setTimeout(() => {
                    uploadForm.reset();
                    uploadStatus.style.display = 'none';
                    selectedFilesDiv.classList.remove('active');
                    document.getElementById('invoiceBreakdown').style.display = 'none';
                }, 3000);
            } else {
                const errorMsg = currentLang === 'de' ? 'Fehler beim Verarbeiten der Rechnung.' : 'Error processing invoice.';
                showStatus(data.message || errorMsg, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = currentLang === 'de' 
                ? 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.' 
                : 'An error occurred. Please try again.';
            showStatus(errorMsg, 'error');
        });
    }
    
    async function uploadMultipleFiles(files, language) {
        uploadProgress.style.display = 'block';
        const totalFiles = files.length;
        let successCount = 0;
        let errorCount = 0;
        
        const uploadStartMsg = currentLang === 'de' 
            ? `Starte Upload von ${totalFiles} Rechnungen...` 
            : `Starting upload of ${totalFiles} invoices...`;
        showStatus(uploadStartMsg, 'processing');
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const progress = ((i + 1) / totalFiles) * 100;
            
            const progressMsg = currentLang === 'de'
                ? `Verarbeite Rechnung ${i + 1} von ${totalFiles}...`
                : `Processing invoice ${i + 1} of ${totalFiles}...`;
            progressText.textContent = progressMsg;
            progressBar.style.width = progress + '%';
            
            const formData = new FormData();
            formData.append('pdfFile', file);
            formData.append('language', language);
            
            try {
                const response = await fetch('process_pdf.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successCount++;
                } else {
                    errorCount++;
                }
            } catch (error) {
                console.error('Error uploading file:', file.name, error);
                errorCount++;
            }
        }
        
        progressBar.style.width = '100%';
        const progressCompleteMsg = currentLang === 'de' ? 'Alle Rechnungen verarbeitet!' : 'All invoices processed!';
        progressText.textContent = progressCompleteMsg;
        
        if (successCount === totalFiles) {
            const successMsg = currentLang === 'de'
                ? `${successCount} Rechnung(en) erfolgreich verarbeitet!`
                : `${successCount} invoice(s) processed successfully!`;
            showStatus(successMsg, 'success');
        } else {
            const resultMsg = currentLang === 'de'
                ? `${successCount} erfolgreich, ${errorCount} Fehler.`
                : `${successCount} successful, ${errorCount} error(s).`;
            showStatus(resultMsg, errorCount > 0 ? 'error' : 'success');
        }
        
        loadMonthlySummary();
        
        // Hide progress bar after 3 seconds and reset form
        setTimeout(() => {
            uploadProgress.style.display = 'none';
            uploadForm.reset();
            selectedFilesDiv.classList.remove('active');
            uploadStatus.style.display = 'none';
            progressBar.style.width = '0%';
            progressText.textContent = '';
            document.getElementById('invoiceBreakdown').style.display = 'none';
        }, 3000);
    }

    // Handle clear all button
    clearAllBtn.addEventListener('click', function() {
        const confirmMsg = currentLang === 'de' ? 'Möchten Sie wirklich alle Daten löschen?' : 'Do you really want to delete all data?';
        if (confirm(confirmMsg)) {
            fetch('clear_data.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const deleteMsg = currentLang === 'de' ? 'Alle Daten wurden gelöscht.' : 'All data has been deleted.';
                    showStatus(deleteMsg, 'success');
                    loadMonthlySummary();
                    invoiceDetails.classList.remove('active');
                    invoiceDetails.innerHTML = '';
                    document.getElementById('invoiceBreakdown').style.display = 'none';
                    document.getElementById('monthlyVATSummary').style.display = 'none';
                }
            });
        }
    });

    function showStatus(message, type) {
        uploadStatus.textContent = message;
        uploadStatus.className = `status-message ${type}`;
        if (type === 'processing') {
            uploadStatus.style.background = '#fff3cd';
            uploadStatus.style.color = '#856404';
            uploadStatus.style.borderColor = '#ffeeba';
            uploadStatus.style.display = 'block';
        }
    }

    function displayInvoiceDetails(invoice) {
        const dateLabel = currentLang === 'de' ? 'Datum:' : 'Date:';
        const amountLabel = currentLang === 'de' ? 'Gesamtbetrag:' : 'Total Amount:';
        const vatLabel = currentLang === 'de' ? 'MwSt-Satz:' : 'VAT Rate:';
        const vatAmountLabel = currentLang === 'de' ? 'MwSt-Betrag:' : 'VAT Amount:';
        const netLabel = currentLang === 'de' ? 'Nettobetrag:' : 'Net Amount:';
        const notFound = currentLang === 'de' ? 'Nicht gefunden' : 'Not found';
        
        // Basic details
        invoiceDetails.innerHTML = `
            <div class="detail-row">
                <span class="detail-label">${dateLabel}</span>
                <span class="detail-value">${invoice.date || notFound}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">${amountLabel}</span>
                <span class="detail-value">${invoice.amount ? parseFloat(invoice.amount).toFixed(2) + ' €' : notFound}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">${vatLabel}</span>
                <span class="detail-value">${invoice.vat ? invoice.vat + '%' : notFound}</span>
            </div>
        `;
        invoiceDetails.classList.add('active');
        
        // Show breakdown if available
        const breakdownDiv = document.getElementById('invoiceBreakdown');
        if (invoice.vat_amount || invoice.net_amount) {
            let breakdownHtml = '';
            
            if (invoice.net_amount) {
                breakdownHtml += `
                    <div class="detail-row highlight">
                        <span class="detail-label">${netLabel}</span>
                        <span class="detail-value">${parseFloat(invoice.net_amount).toFixed(2)} €</span>
                    </div>
                `;
            }
            
            if (invoice.vat_amount) {
                breakdownHtml += `
                    <div class="detail-row highlight">
                        <span class="detail-label">${vatAmountLabel}</span>
                        <span class="detail-value">${parseFloat(invoice.vat_amount).toFixed(2)} €</span>
                    </div>
                `;
            }
            
            breakdownDiv.innerHTML = breakdownHtml;
            breakdownDiv.style.display = 'block';
        } else {
            breakdownDiv.style.display = 'none';
        }
    }

    function loadMonthlySummary() {
        fetch('get_summary.php')
            .then(response => response.json())
            .then(data => {
                if (data.summary && data.summary.length > 0) {
                    let html = '';
                    let vatHtml = '';
                    const vatSummaryDiv = document.getElementById('monthlyVATSummary');
                    
                    data.summary.forEach(monthData => {
                        const invoiceCount = monthData.invoice_count || 0;
                        const invoiceText = currentLang === 'de' 
                            ? (invoiceCount === 1 ? 'Rechnung' : 'Rechnungen')
                            : (invoiceCount === 1 ? 'invoice' : 'invoices');
                        html += `
                            <div class="summary-item">
                                <div>
                                    <div class="summary-month">${monthData.month}</div>
                                    <div class="summary-count">${invoiceCount} ${invoiceText}</div>
                                </div>
                                <div class="summary-amount">${parseFloat(monthData.total).toFixed(2)} €</div>
                            </div>
                        `;
                        
                        // VAT Breakdown
                        if (monthData.total_net && parseFloat(monthData.total_net) > 0) {
                            const netTotal = parseFloat(monthData.total_net);
                            const vatTotal = parseFloat(monthData.total_vat || 0);
                            const totalAmount = parseFloat(monthData.total);
                            
                            vatHtml += `
                                <div class="vat-summary-item">
                                    <h3>${monthData.month}</h3>
                                    <div class="vat-breakdown">
                                        <div class="vat-row">
                                            <span class="vat-label">${currentLang === 'de' ? 'Netto:' : 'Net:'}</span>
                                            <span class="vat-value">${netTotal.toFixed(2)} €</span>
                                        </div>
                                        <div class="vat-row highlight">
                                            <span class="vat-label">${currentLang === 'de' ? 'MwSt:' : 'VAT:'}</span>
                                            <span class="vat-value">${vatTotal.toFixed(2)} €</span>
                                        </div>
                                        <div class="vat-row total">
                                            <span class="vat-label">${currentLang === 'de' ? 'Gesamt:' : 'Total:'}</span>
                                            <span class="vat-value">${totalAmount.toFixed(2)} €</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    });
                    monthlySummary.innerHTML = html;
                    
                    // Show VAT summary if available
                    if (vatHtml) {
                        vatSummaryDiv.innerHTML = vatHtml;
                        vatSummaryDiv.style.display = 'block';
                    } else {
                        vatSummaryDiv.style.display = 'none';
                    }
                } else {
                    const noDataMsg = currentLang === 'de' 
                        ? 'Noch keine Rechnungen verarbeitet.' 
                        : 'No invoices processed yet.';
                    monthlySummary.innerHTML = `<div class="no-data">${noDataMsg}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading summary:', error);
            });
    }
});

