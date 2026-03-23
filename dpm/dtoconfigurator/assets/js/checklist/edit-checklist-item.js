let quillEditor;
let originalData = {};

$(document).ready(async function() {
    $('#checklistPage .loader').hide();
    $('.checklistPageContainer').transition('pulse');

    $('.ui.dropdown').dropdown({
        transition: 'fade up',
        selectOnKeydown: false,
        forceSelection: false,
        allowAdditions: false
    });

    initializeRichTextEditor();
    await loadCategories();
    await loadProducts();
    await loadChecklistItemData();

    bindEvents();
});

async function loadChecklistItemData() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ChecklistController.php', {
            params: {
                action: 'getChecklistItem',
                id: CHECKLIST_ITEM_ID
            }
        });

        const data = response.data;
        originalData = data;

        if (data.detail_html) {
            // Decode HTML entities first
            const decodedHtml = $('<textarea>').html(data.detail_html).text();
            // Clear editor first
            quillEditor.setText('');
            // Set the decoded HTML
            quillEditor.clipboard.dangerouslyPasteHTML(0, decodedHtml);
        }

        $('#category-select').dropdown('set selected', data.category_id);

        // Set product types
        const productIds = data.products.map(p => p.product_id.toString());
        $('select[name="product_types[]"]').dropdown('set selected', productIds);

        // Show existing image if available
        if (data.image_file_name) {
            $('#fileName').val(data.image_file_name);
            showExistingImage(data.image_file_name);
        }

        // Update character count after a short delay to ensure content is loaded
        setTimeout(() => {
            const textLength = quillEditor.getText().trim().length;
            $('#char-count').text(textLength);
        }, 100);

    } catch (error) {
        showErrorDialog('Failed to load checklist item data');
        console.error('Error loading checklist item:', error);
    }
}

function showExistingImage(fileName) {
    $('#imagePreviewSegment').html(`
        <div class="ui segment" id="imagePreview" style="margin-top: 10px; text-align: center;">
            <img src="/dpm/dtoconfigurator/partials/getNoteImages.php?type=5&file=${fileName}" 
                 style="max-width: 300px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="margin-top: 8px; color: #666; font-size: 12px;">Current Image</div>
        </div>
    `);
}

function bindEvents() {
    $('#uploadBtn').click(function() {
        $('#imageInput').click();
    });

    $('#imageInput').change(function() {
        const file = this.files[0];
        if (file) {
            $('#fileName').val(file.name);

            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            if (fileSize > 5) {
                showMessage('error', 'File size should not exceed 5MB');
                $(this).val('');
                $('#fileName').val('');
                return;
            }

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreviewSegment').html(`
                        <div class="ui segment" id="imagePreview" style="margin-top: 10px; text-align: center;">
                            <img src="${e.target.result}" style="max-width: 300px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                            <div style="margin-top: 8px; color: #666; font-size: 12px;">New Image Preview</div>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            }
        } else {
            $('#fileName').val('');
            $('#imagePreview').remove();
        }
    });

    $('#updateChecklistItemButton').on('click', async function(e) {
        e.preventDefault();
        $(this).addClass('loading disabled');

        const checklistDetailHtml = quillEditor.root.innerHTML;
        const checklistDetailPlain = quillEditor.getText().trim();
        const categorySelect = $('#category-select').dropdown('get value');
        const selectedProducts = $('select[name="product_types[]"]').val();

        if (!checklistDetailPlain || !categorySelect || !selectedProducts) {
            showErrorDialog('Detail, category and products fields are mandatory!');
            $(this).removeClass('loading disabled');
            return;
        }

        const formElement = document.getElementById('checklistForm');
        const formData = new FormData(formElement);
        formData.append('action', 'updateChecklistItem');
        formData.append('id', CHECKLIST_ITEM_ID);
        formData.append('checklistDetail', checklistDetailPlain);
        formData.append('checklistDetailHtml', checklistDetailHtml);
        formData.append('categorySelect', categorySelect);
        formData.append('selectedProducts', selectedProducts);

        try {
            await axios.post('/dpm/dtoconfigurator/api/controllers/ChecklistController.php', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            showSuccessDialog('Checklist item successfully updated.').then(() => {
                window.location.href = '/dpm/dtoconfigurator/core/checklist/index.php';
            });

        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Contact DGT Team!";
            showErrorDialog(errorMessage);
        } finally {
            $(this).removeClass('loading disabled');
        }
    });
}

// Copy the same functions from add-checklist-item.js
async function loadCategories() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ChecklistController.php', {
            params: { action: 'getChecklistCategories' }
        });

        const data = response.data;
        let html = '<option value="">Choose a category...</option>';
        data.forEach(function(category) {
            html += `<option value="${category.id}">${category.name}</option>`;
        });
        $('select[name="category_id"]').html(html);
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadProducts() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ChecklistController.php', {
            params: { action: 'getChecklistProducts' }
        });

        const data = response.data;
        let html = '';
        data.forEach(function(product) {
            html += `<option value="${product.id}">${product.product_type}</option>`;
        });
        $('select[name="product_types[]"]').html(html);
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

function initializeRichTextEditor() {
    const toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'header': [1, 2, 3, false] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['clean']
    ];

    quillEditor = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Describe what needs to be checked or completed...',
        modules: { toolbar: toolbarOptions }
    });

    quillEditor.on('text-change', function() {
        const text = quillEditor.getText();
        const html = quillEditor.root.innerHTML;
        $('#char-count').text(text.trim().length);
        $('#checklist-detail').val(html);
    });
}

function showMessage(type, message) {
    const messageEl = $('#formMessage');
    messageEl.removeClass('error success').addClass(type);

    if (type === 'success') {
        messageEl.html(`<i class="check circle icon"></i>${message}`);
    } else {
        messageEl.html(`<i class="warning circle icon"></i>${message}`);
    }

    messageEl.show().transition('fade in');

    setTimeout(function() {
        messageEl.transition('fade out');
    }, 5000);
}