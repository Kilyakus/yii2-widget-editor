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



(function (factory) {
	if (typeof define === 'function' && define.amd) {
		define(['jquery'], factory);
	} else if (typeof module === 'object' && module.exports) {
		module.exports = factory(require('jquery'));
	} else {
		factory(window.jQuery);
	}
}(function ($) {
	$.extend($.summernote.plugins, {
		emoji: function (context) {
			var self = this;
			var ui = $.summernote.ui;

			var data = $.ajax({
				url: 'https://api.github.com/emojis',
				async: false
			}).responseJSON;

			var emojis = [];
			for(var i in data){
				emojis.push([i, data[i]]);
			}

			var chunk = function (val, chunkSize) {
				var R = [];
				for (var i = 0; i < val.length; i += chunkSize){
					R.push(val.slice(i, i + chunkSize));
				}
				return R;
			};

			/*IE polyfill*/
			if (!Array.prototype.filter) {
				Array.prototype.filter = function (fun /*, thisp*/) {
					var len = this.length >>> 0;
					if (typeof fun != "function")
						throw new TypeError();

					var res = [];
					var thisp = arguments[1];
					for (var i = 0; i < len; i++) {
						if (i in this) {
							var val = this[i];
							if (fun.call(thisp, val, i, this))
								res.push(val);
						}
					}
					return res;
				};
			}

			var addListener = function () {
				$('body').on('click', '.emoji-filter', function (e) {
					e.stopPropagation();
					$('.emoji-filter').focus();
				});
				$('body').on('keyup', '.emoji-filter', function (e) {
					var filteredList = filterEmoji($(e.currentTarget).val());
					$(".dropdown-emoji .emoji-list").html(filteredList);
				});
				$(document).on('click', '.selectEmoji', function(){
					var img = new Image();
					img.src = $(this).attr('data-src');
					img.alt = $(this).attr('data-value');
                    img.style = 'width: 20px;';
					context.invoke('editor.insertNode', img);

				});
			};

			var render = function (emojis) {
				var emoList = '';
				/*limit list to 24 images*/
				var emojis = emojis;
				var chunks = chunk(emojis, 6);
				for (var j = 0; j < chunks.length; j++) {
					emoList += '<div class="row">';
					for (var i = 0; i < chunks[j].length; i++) {
						var emo = chunks[j][i];
						emoList += '<div class="col-xs-2">' +
						'<a href="javascript:void(0)" class="selectEmoji closeEmoji" title=":' + emo[0] + ':" data-value="' + emo[0] + '" data-src="' + emo[1] + '"><span class="emoji-icon" style="background-image: url(\'' + emo[1] + '\');"></span></a>' +
						'</div>';
					}
					emoList += '</div>';
				}

				return emoList;
			};

			var filterEmoji = function (value) {

				var filtered = emojis.filter(function (el) {
					return el[0].indexOf(value) > -1;
				});
				return render(filtered);
			};

			// add emoji button
			context.memo('button.emoji', function () {
				return ui.buttonGroup([
					ui.button({
						className: 'dropdown-toggle',
						contents: '<i class="fa fa-smile"/>',
						tooltip: 'emoji',
						data: {
							toggle: 'dropdown'
						}
					}),
					ui.dropdown({
						className: 'dropdown-emoji',
						title: 'emoji',
						items: [
							'<div class="row">', 
								'<div class="col-md-12">', 
									'<input type="text" class="form-control emoji-filter" placeholder="...">', 
								'</div>', 
							'</div>', 
							'<div class="emoji-list">', render(emojis), '</div>'
						].join(''),
					})
				]).render();
			});

			// This events will be attached when editor is initialized.
			this.events = {
				// This will be called after modules are initialized.
				'summernote.init': function (we, e) {
					addListener();
				},
				// This will be called when user releases a key on editable.
				'summernote.keyup': function (we, e) {
				}
			};
		}
	});
}));
