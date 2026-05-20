document.addEventListener('DOMContentLoaded', function () {
    // Function: Disable Copy/Paste/Right-Click/Shortcuts
    function disableActions(targetDoc) {
        if (!targetDoc) return;

        // Block Context Menu
        targetDoc.addEventListener('contextmenu', event => {
            event.preventDefault();
        });

        // Block Copy, Cut, Paste
        ['copy', 'cut', 'paste'].forEach(evt => {
            targetDoc.addEventListener(evt, event => {
                event.preventDefault();
            });
        });

        // Block Shortcuts
        targetDoc.addEventListener('keydown', event => {
            const isCtrlOrMeta = event.ctrlKey || event.metaKey;
            const key = event.key.toLowerCase();

            // Block PrintScreen
            if (event.key === 'PrintScreen' || event.keyCode === 44) {
                event.preventDefault();
                alert("Aksi screenshot tidak diizinkan.");
            }

            // Block Ctrl+P (Print), Ctrl+S (Save), Ctrl+C/X/V (Copy/Cut/Paste), Ctrl+A (Select All), Ctrl+U (View Source)
            if (isCtrlOrMeta && ['p', 's', 'c', 'x', 'v', 'a', 'u'].includes(key)) {
                event.preventDefault();
                alert("Hayo mau ngapain ....");
            }

            // Block DevTools (Ctrl+Shift+I)
            if (isCtrlOrMeta && event.shiftKey && key === 'i') {
                event.preventDefault();
                alert("Hayo mau ngapain ....");
            }
        });
    }

    // Apply to Main Document
    disableActions(document);

    // Apply to PDF Iframes when loaded (Handles both 'pdf-frame' and 'pdfIframe')
    ['pdf-frame', 'pdfIframe'].forEach(id => {
        const frame = document.getElementById(id);
        if (frame) {
            frame.addEventListener('load', function () {
                try {
                    const iframeDoc = frame.contentDocument || frame.contentWindow.document;
                    disableActions(iframeDoc);
                } catch (e) {
                    console.warn(`Cannot access iframe content for ${id} (Cross-Origin?):`, e);
                }
            });
        }
    });

    // --- Treeview and Document Loading Logic ---
    const documentTree = document.getElementById('document-tree');
    if (!documentTree) {
        // If no treeview, stop here but security is already applied above
        return;
    }

    const searchInput = document.getElementById('document-search-input');
    const dateFilterInput = document.getElementById('date-filter-input');
    const departementFilterSelect = document.getElementById('departement-filter-select');
    const pdfFrame = document.getElementById('pdf-frame');
    const noDocumentSelected = document.getElementById('no-document-selected');
    let activeDocument = null;
    let activeDocumentName = '';

    function logUserAction(actionType, documentName) {
        fetch('api/log_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action_type: actionType, document_name: documentName }),
        }).catch(error => console.error('Gagal mengirim log:', error));
    }

    function fetchTreeviewData(query = '', date_filter = '', dept_filter = '') {
        const params = new URLSearchParams();
        if (query) params.append('q', query);
        if (date_filter) params.append('date', date_filter);
        if (dept_filter) params.append('dept', dept_filter);
        fetch(`api/get_tree.php?${params.toString()}`).then(response => response.json()).then(data => { renderTree(data); if (query.length > 0 || date_filter.length > 0 || dept_filter.length > 0) { documentTree.querySelectorAll('.folder-item > ul').forEach(ul => { ul.style.display = 'block'; ul.parentNode.classList.add('expanded'); }); } }).catch(error => console.error('Ada masalah saat mengambil data treeview:', error));
    }

    function renderTree(data) {
        documentTree.innerHTML = '';
        if (data && data.length > 0) {
            buildTree(data, documentTree);
        } else {
            documentTree.innerHTML = '<li class="p-3 text-muted">Tidak ada dokumen yang sesuai.</li>';
        }
    }

    function buildTree(items, parentElement) {
        items.forEach(item => {
            const li = document.createElement('li');
            li.id = item.id;

            if (item.type === 'folder') {
                li.classList.add('folder-item', 'list-group-item', 'border-0');
                li.innerHTML = `<div class="folder-name-container"><span class="folder-name">${item.name}</span></div>`;
                const ul = document.createElement('ul');
                ul.classList.add('list-group', 'list-group-flush', 'ps-3');
                ul.style.display = 'none';
                li.appendChild(ul);
                if (item.children && item.children.length > 0) {
                    buildTree(item.children, ul);
                }
            } else if (item.type === 'document') {
                li.classList.add('document-item', 'list-group-item', 'list-group-item-action', 'border-0', 'd-flex', 'justify-content-between', 'align-items-center');
                const favoriteIconClass = item.is_favorite ? 'fas' : 'far';
                const favoriteIconColor = item.is_favorite ? 'text-warning' : '';
                li.innerHTML = `<span class="document-name text-truncate">${item.name}</span> <i class="${favoriteIconClass} fa-star favorite-icon ${favoriteIconColor}" data-doc-id="${item.doc_id}"></i>`;
                li.setAttribute('data-file', item.file_name);
                li.setAttribute('data-doc-name', item.name);
                li.setAttribute('data-tahun', item.tahun);
                li.setAttribute('data-dept', item.dept);
            }
            parentElement.appendChild(li);
        });
    }

    function applyFilters() {
        const query = searchInput.value ? searchInput.value.trim() : '';
        const dateFilter = dateFilterInput ? dateFilterInput.value : '';
        const deptFilter = departementFilterSelect ? departementFilterSelect.value : '';
        fetchTreeviewData(query, dateFilter, deptFilter);
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (dateFilterInput) dateFilterInput.addEventListener('change', applyFilters);
    if (departementFilterSelect) departementFilterSelect.addEventListener('change', applyFilters);

    fetchTreeviewData();

    documentTree.addEventListener('click', function (event) {
        const target = event.target;
        const clickedItem = target.closest('li');
        if (!clickedItem) return;

        // Logika untuk favorit
        if (target.classList.contains('favorite-icon')) {
            const docId = target.dataset.docId;
            target.classList.toggle('far');
            target.classList.toggle('fas');
            target.classList.toggle('text-warning');
            fetch('api/toggle_favorite.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ document_id: docId })
            }).catch(error => console.error('Gagal memfavoritkan:', error));
            return; // Hentikan propagasi agar tidak membuka dokumen
        }

        if (clickedItem.classList.contains('folder-item')) {
            const ul = clickedItem.querySelector('ul');
            if (ul) {
                ul.style.display = ul.style.display === 'none' ? 'block' : 'none';
                clickedItem.classList.toggle('expanded');
            }
        } else if (clickedItem.classList.contains('document-item')) {
            if (activeDocument) {
                activeDocument.classList.remove('active');
            }
            clickedItem.classList.add('active');
            activeDocument = clickedItem;

            const fileName = clickedItem.getAttribute('data-file');
            const tahun = clickedItem.getAttribute('data-tahun');
            const dept = clickedItem.getAttribute('data-dept');
            activeDocumentName = clickedItem.getAttribute('data-doc-name');
            if (fileName && tahun && dept) {
                const origin = window.location.origin;
                const pdfUrl = `${origin}/portal/uploads/${tahun}/${dept}/${fileName}`;
                loadPDF(pdfUrl, activeDocumentName);
                logUserAction('view_document', activeDocumentName);
            }
        }
    });

    function loadPDF(url, documentName) {
        const viewerUrl = `/iportal/assets/pdfjs/web/viewer.html?file=${encodeURIComponent(url)}`;

        if (pdfFrame && pdfFrame.src !== viewerUrl) {
            pdfFrame.src = viewerUrl;
        }

        // Update Modal Title
        const modalTitle = document.getElementById('pdfViewerModalLabel');
        if (modalTitle) {
            modalTitle.textContent = documentName || 'Document Viewer';
        }

        // Show Modal
        const modalEl = document.getElementById('pdfViewerModal');
        if (modalEl) {
            const pdfModal = new bootstrap.Modal(modalEl);
            pdfModal.show();
        }
    }
});