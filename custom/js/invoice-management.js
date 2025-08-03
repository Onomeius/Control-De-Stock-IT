document.addEventListener('DOMContentLoaded', () => {
    console.log("Current User Role in JS: ", userRole); // DEBUG LINE
    const uploadForm = document.getElementById('uploadForm');
    const invoiceTableBody = document.getElementById('invoiceTableBody');
    const searchInput = document.getElementById('searchInput');
    const downloadBtn = document.getElementById('downloadBtn');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const uploadMessages = document.getElementById('upload-messages');

    const fetchInvoices = async (searchTerm = '') => {
        try {
            const response = await fetch(`php_action/fetchFacturas.php?search=${encodeURIComponent(searchTerm)}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const invoices = await response.json();
            renderInvoices(invoices);
        } catch (error) {
            console.error("Error al cargar las facturas:", error);
            invoiceTableBody.innerHTML = `<tr><td colspan="5" class="text-center">Error al cargar los datos. Revise la consola.</td></tr>`;
        }
    };

    const renderInvoices = (invoices) => {
        invoiceTableBody.innerHTML = '';
        // Determine the correct colspan based on user role
        const colspanValue = userRole === 3 ? 5 : 6; // 5 columns for visualizer (no checkbox), 6 for others

        if (invoices.length === 0) {
            invoiceTableBody.innerHTML = `<tr><td colspan="${colspanValue}" class="text-center">No se encontraron facturas.</td></tr>`;
            return;
        }
        invoices.forEach(invoice => {
            const row = document.createElement('tr');
            let actionHtml = '';
            if (userRole === 3) { // Visualizer: show download button
                actionHtml = `<td><button class="btn btn-sm btn-primary download-single-btn" data-id="${invoice.id}"><i class="glyphicon glyphicon-download-alt"></i></button></td>`;
            } else { // Admin/SuperAdmin: show checkbox
                actionHtml = `<td><input type="checkbox" class="invoice-checkbox" data-id="${invoice.id}"></td>`;
            }
            row.innerHTML = `
                ${actionHtml}
                <td>${invoice.numero_oc || 'N/A'}</td>
                <td>${invoice.numero_factura || 'N/A'}</td>
                <td>${invoice.nombre_archivo}</td>
                <td>${invoice.fecha_carga}</td>
            `;
            invoiceTableBody.appendChild(row);
        });

        if (userRole === 3) {
            selectAllCheckbox.style.display = 'none'; // Hide select all checkbox
            downloadBtn.style.display = 'none'; // Hide multi-download button
        } else {
            selectAllCheckbox.style.display = ''; // Ensure visible for others
            downloadBtn.style.display = ''; // Ensure visible for others
        }

        // Add event listeners for single download buttons
        document.querySelectorAll('.download-single-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                const invoiceId = e.currentTarget.dataset.id;
                downloadSingleInvoice(invoiceId);
            });
        });
    };

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(uploadForm);
        uploadMessages.innerHTML = '';

        try {
            const response = await fetch('php_action/uploadFactura.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                uploadMessages.innerHTML = `<div class="alert alert-success" role="alert"><strong>Éxito:</strong> ${result.messages}</div>`;
                uploadForm.reset();
                fetchInvoices();
            } else {
                uploadMessages.innerHTML = `<div class="alert alert-danger" role="alert"><strong>Error:</strong> ${result.messages}</div>`;
            }
        } catch (error) {
            console.error('Error en la subida:', error);
            uploadMessages.innerHTML = `<div class="alert alert-danger" role="alert"><strong>Error:</strong> Ocurrió un error de red.</div>`;
        }
    });

    searchInput.addEventListener('input', () => {
        fetchInvoices(searchInput.value.trim());
    });

    const updateDownloadButtonState = () => {
        const selectedCheckboxes = document.querySelectorAll('.invoice-checkbox:checked');
        downloadBtn.disabled = selectedCheckboxes.length === 0;
    };

    invoiceTableBody.addEventListener('change', (e) => {
        if (e.target.classList.contains('invoice-checkbox')) {
            updateDownloadButtonState();
        }
    });
    
    selectAllCheckbox.addEventListener('change', (e) => {
        const checkboxes = document.querySelectorAll('.invoice-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        updateDownloadButtonState();
    });

    downloadBtn.addEventListener('click', async () => {
        const selectedIds = Array.from(document.querySelectorAll('.invoice-checkbox:checked'))
                                 .map(cb => cb.dataset.id);

        if (selectedIds.length === 0) return;

        try {
            const response = await fetch('php_action/downloadFacturas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `ids=${JSON.stringify(selectedIds)}`
            });

            if (!response.ok) {
                const errorResult = await response.json().catch(() => ({ message: 'Error desconocido en el servidor.' }));
                throw new Error(errorResult.message);
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `facturas_${Date.now()}.zip`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();

        } catch (error) {
            console.error('Error en la descarga:', error);
            alert(`No se pudo descargar el archivo: ${error.message}`);
        }
    });

    // Carga inicial de facturas
    fetchInvoices();

    // Function to download a single invoice
    const downloadSingleInvoice = async (invoiceId) => {
        try {
            const response = await fetch(`php_action/downloadSingleFactura.php?id=${invoiceId}`);
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Error al descargar la factura: ${errorText}`);
            }

            const blob = await response.blob();
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = 'factura.pdf';
            if (contentDisposition && contentDisposition.indexOf('attachment') !== -1) {
                const filenameRegex = /filename[^;=
]*=((['"])(.*?)\2|[^;
]*)/;
                const matches = filenameRegex.exec(contentDisposition);
                if (matches != null && matches[3]) {
                    filename = decodeURIComponent(matches[3].replace(/\+/g, ' '));
                }
            }

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();

        } catch (error) {
            console.error('Error en la descarga de factura individual:', error);
            alert(`No se pudo descargar la factura: ${error.message}`);
        }
    };

    // Hide download button if user is visualizer
    if (userRole === 3) {
        downloadBtn.style.display = 'none';
    }