var methods = {
    read: 'GET',
    write: 'POST',
    delete: 'DELETE'
};

function loadFiles(selectedFile) {
    $.get('index.php', { action: 'list' }, function (response) {
        $('#file').empty();

        $.each(response.data, function (index, fileName) {
            $('#file').append($('<option>').text(fileName));
        });

        if (selectedFile) {
            $('#file').val(selectedFile);
        }
    });
}

function showMessage(text, isError) {
    $('#message').attr('class', isError ? 'error' : 'success').text(text);
}

function getErrorMessage(xhr) {
    if (xhr.responseJSON) {
        return xhr.responseJSON.message;
    }

    return 'Something went wrong.';
}

function uploadFile() {
    var file = this.files[0];

    if (!file) {
        return;
    }

    var formData = new FormData();
    formData.append('file', file);
    $(this).val('');

    $.ajax({
        url: 'index.php?action=upload',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false
    }).done(function (response) {
        $('#upload-message').attr('class', 'success').text(response.message + ' Now choose an action.');
        loadFiles(response.data.file);
    }).fail(function (xhr) {
        $('#upload-message').attr('class', 'error').text(getErrorMessage(xhr));
    });
}

function runAction() {
    var action = $(this).data('action');
    var query = $.param({ action: action, file: $('#file').val() });

    $.ajax({
        url: 'index.php?' + query,
        method: methods[action],
        data: action === 'write' ? $('#data').val() : ''
    }).done(function (response) {
        showMessage(response.message, false);
        $('#result').text(action === 'read' ? JSON.stringify(response.data, null, 2) : '');

        if (action === 'delete') {
            loadFiles();
        }
    }).fail(function (xhr) {
        showMessage(getErrorMessage(xhr), true);
        $('#result').text('');
    });
}

$(function () {
    loadFiles();
    $('#upload').on('click', function () {
        $('#upload-file').click();
    });
    $('#upload-file').on('change', uploadFile);
    $('.action').on('click', runAction);
});
