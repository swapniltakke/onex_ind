let quillEditor;
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

    $('#uploadBtn').click(function() {
        $('#imageInput').click();
    });

    $('#imageInput').change(function() {
        const file = this.files[0];
        if (file) {
            $('#fileName').val(file.name);

            // Show file size validation
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
                    if (!$('#imagePreview').length) {
                        $('#imagePreviewSegment').html(`
                            <div class="ui segment" id="imagePreview" style="margin-top: 10px; text-align: center;">
                                <img src="" style="max-width: 300px;  border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                <div style="margin-top: 8px; color: #666; font-size: 12px;">Preview</div>
                            </div>
                        `);
                    }
                    $('#imagePreview img').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        } else {
            $('#fileName').val('');
            $('#imagePreview').remove();
        }
    });
});


$('#addChecklistItemButton').on('click', async function (e) {
    e.preventDefault();
    $(this).addClass('loading disabled');

    const checklistDetailHtml = quillEditor.root.innerHTML; // HTML version
    const checklistDetailPlain = quillEditor.getText().trim(); // Plain text version
    const categorySelect =  $('#category-select').dropdown('get value');
    const imageFileName = $('#imageInput').val().split('\\').pop().split('/').pop(); // Get File Name
    const selectedProducts = $('select[name="product_types[]"]').val();

    if (!checklistDetailPlain || !categorySelect || !selectedProducts) {
        showErrorDialog('Detail, category and products fields are mandatory!');
        $(this).removeClass('loading disabled');
        return;
    }

    const formElement = document.getElementById('checklistForm');
    const formData = new FormData(formElement);
    formData.append('action', 'addCheckListItem');
    formData.append('checklistDetail', checklistDetailPlain);
    formData.append('checklistDetailHtml', checklistDetailHtml);
    formData.append('categorySelect', categorySelect);
    formData.append('imageFileName', imageFileName);
    formData.append('selectedProducts', selectedProducts);

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/ChecklistController.php?', formData, { headers: { 'Content-Type': 'multipart/form-data' }} );

        showSuccessDialog('Checklist item successfully inserted.').then(() => {
            window.location.href = '/dpm/dtoconfigurator/core/checklist/index.php';
        });

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(errorMessage);
    }
    finally {
        $(this).removeClass('loading disabled');
    }
});


async function loadCategories() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ChecklistController.php', { params: { action: 'getChecklistCategories'} });

        const data = response.data;

        let html = '<option value="">Choose a category...</option>';
        data.forEach(function(category) {
            html += `<option value="${category.id}">${category.name}</option>`;
        });
        $('select[name="category_id"]').html(html);
    } catch (error) {
        fireToastr('error', 'Error fetching released projects:', error);
    }
}

async function loadProducts() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ChecklistController.php', { params: { action: 'getChecklistProducts'} });

        const data = response.data;

        let html = '';
        data.forEach(function(product) {
            html += `<option value="${product.id}">${product.product_type}</option>`;
        });
        $('select[name="product_types[]"]').html(html);
    } catch (error) {
        fireToastr('error', 'Error fetching released projects:', error);
    }
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

    // Auto hide after 5 seconds
    setTimeout(function() {
        messageEl.transition('fade out');
    }, 5000);
}

function clearForm() {
    quillEditor.setContents([]);
    $('#char-count').text('0');
    $('#checklistForm')[0].reset();
    $('.ui.dropdown').dropdown('clear');
    $('#imagePreview').remove();
    $('#fileName').val('');
    $('#formMessage').hide();
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