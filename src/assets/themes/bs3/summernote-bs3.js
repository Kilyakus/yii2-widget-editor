var kvFormatCode, kvInitCMFormatter;
(function ($) {
    "use strict";
    kvFormatCode = function(id) {
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
    kvInitCMFormatter = function(id) {
        $('#' + id).off('summernote.codeview.toggled').on('summernote.codeview.toggled', function() {
            kvFormatCode(id);
        });
    };
})(window.jQuery);