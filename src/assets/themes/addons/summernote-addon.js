var CodemirrorFormatCode, initCMFormatter, initEmojis, onImageUpload;
(function ($) {
    "use strict";
    CodemirrorFormatCode = function(id) {
        var $rich = $('#' + id), $code = $rich.next().find('textarea.note-codable'), editor, totalLines, totalChars;
        if ($code.length) {
            editor = $code.data('cmEditor');
            if (editor) {
                totalLines = editor.lineCount();
                totalChars = editor.getTextArea().value.length;
                editor.autoFormatRange({line:0, ch:0}, {line:totalLines, ch:totalChars});
            }
        }
    };
    initCMFormatter = function(id) {
        $('#' + id).off('summernote.codeview.toggled').on('summernote.codeview.toggled', function() {
            CodemirrorFormatCode(id);
        });
    };
    initEmojis = function(dir = 'https://api.github.com/emojis') {
        $.ajax({
            url: dir,
            async: false
        }).then(function(data) {
            window.kvEmojis = Object.keys(data);
            window.kvEmojiUrls = data;
        });
    };
    onImageUpload = function(file,container,dir) {
        if (file.type.includes('image')) {
            var name = file.name.split(".");
            name = name[0];
            var data = new FormData();
            data.append('action', 'imgUpload');
            data.append('file', file);
            $.ajax({
                url: dir,
                type: 'POST',
                contentType: false,
                cache: false,
                processData: false,
                dataType: 'JSON',
                data: data,
                success: function (response) {
                    $(container).summernote('insertImage', response.filelink);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error(textStatus + " " + errorThrown);
                }
            });
        }
    };
})(window.jQuery);