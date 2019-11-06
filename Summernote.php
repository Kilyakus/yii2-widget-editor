<?php
namespace kilyakus\widget\redactor;

use Yii;
use kilyakus\widget\redactor\assets\CodeMirrorAsset;
use kilyakus\widget\redactor\assets\LangAsset;
use kilyakus\widget\redactor\assets\SummernoteBaseAsset;
use kilyakus\widget\redactor\assets\SummernoteBs3Asset;
use kilyakus\widget\redactor\assets\SummernoteBs4Asset;
use kilyakus\widget\redactor\assets\SummernoteLiteAsset;
use kilyakus\widget\redactor\assets\EmojiAsset;
use kilyakus\widgets\InputWidget;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Inflector;
use yii\web\JsExpression;

class Summernote extends InputWidget
{
    public $pluginName = 'summernote';

    const THEME_BS3 = 'bootstrap-3';
    const THEME_BS4 = 'bootstrap-4';
    const THEME_LITE = 'lite';
    const THEME_SIMPLE = 'simple';

    public $theme = self::THEME_SIMPLE;

    protected static $_bs3Themes = [
        self::THEME_SIMPLE,
    ];

    protected static $_bs4Themes = [
    ];

    public $i18n = true;
    public $emoji = false;
    public $fullscreen = true;
    public $codemirror = false;

    public $options         = [];
    public $pluginOptions   = [
        'tabsize' => 2,
        'minHeight' => 150,
        'maxHeight' => 400,
        'focus' => true,
        'toolbar' => [
            ['style1', ['style']],
            ['style2', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript']],
            ['font', ['fontname', 'fontsize', 'height', 'color', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video', 'table', 'hr']],
        ]
    ];

    public $codemirrorOptions = [
        'codemirror' => [
            // 'theme' => 'monokai',
            'mode' => 'text/html',
            'htmlMode' => true,
            'lineNumbers' => true,
            'styleActiveLine' => true,
            'matchBrackets' => true,
            'smartIndent' => true,
        ]
    ];

    public $hintWords = [];

    public $hintMentions = [];

    public $container = ['class' => 'note-editor-container'];

    public function run()
    {
        return $this->initWidget();
    }

    protected function initWidget()
    {
        $this->_msgCat = 'redactor';
        if (!empty($this->options['placeholder']) && empty($this->pluginOptions['placeholder'])) {
            $this->pluginOptions['placeholder'] = $this->options['placeholder'];
        }
        $tag = ArrayHelper::remove($this->container, 'tag', 'div');
        if (!isset($this->container['id'])) {
            $this->container['id'] = $this->options['id'] . '-container';
        }
        $this->initPresets();
        $this->initHints();
        $this->registerAssets();
        return Html::tag($tag, $this->getInput('textarea'), $this->container);
    }

    protected function initPresets()
    {
        $view = $this->getView();

        $toolView = [];

        if ($this->codemirror) {

            $toolView[] = 'codeview';

            if (isset($this->pluginOptions['codemirror'])) {
                $this->codemirrorOptions = ArrayHelper::merge($this->codemirrorOptions, $this->pluginOptions['codemirror']);
            }
            $this->pluginOptions = ArrayHelper::merge($this->pluginOptions, $this->codemirrorOptions);

            CodeMirrorAsset::register($view);
        }

        if ($this->fullscreen) {

            $toolView[] = 'fullscreen';
            
        }

        if (!empty($toolView)) {
            $this->pluginOptions['toolbar'][] = ['view', $toolView];
        }
    }

    public function getPluginScript($name, $element = null, $callback = null, $callbackCon = null)
    {
        $script = '';
        $id = $this->options['id'];
        if ($this->emoji) {
            $script .= "kvInitEmojis();\n";
        }
        // if ($this->codemirror && $this->autoFormatCode) {
        //     $script .= "kvInitCMFormatter('{$id}');\n";
        // }
        $script .= parent::getPluginScript($name, $element, $callback, $callbackCon);
        return $script;
    }

    public function registerAssets()
    {
        $fieldId = $this->options['id'];

        $view = $this->getView();

        if ($this->theme == self::THEME_BS4) {
            SummernoteBs4Asset::register($view);
        } else if ($this->theme == self::THEME_BS3) {
            SummernoteBs3Asset::register($view);
        } else {
            SummernoteLiteAsset::register($view);
        }

        if (in_array($this->theme, self::$_bs3Themes)) {

            SummernoteBs3Asset::register($view);

            $bundleClass = __NAMESPACE__ . '\assets\Summernote' . Inflector::id2camel($this->theme) . 'Asset';
            $bundleClass::register($view);
        }
        
        SummernoteBaseAsset::register($view);

        /* Регистрация языка  */
        if ($this->i18n) {
            $lang = [
                'lang' => Yii::$app->language . '-' . strtoupper(Yii::$app->language)
            ];
            LangAsset::register($view);
            $this->pluginOptions = ArrayHelper::merge($this->pluginOptions, $lang) ;
        }

        if ($this->emoji) {
            EmojiAsset::register($view);
        }

        $pluginOptions = Json::encode($this->pluginOptions);

        $js = <<< JS
        $(document).ready(function(){
            $("#$fieldId").summernote($pluginOptions);
        });
JS;
        $view->registerJs($js);

        $this->registerPlugin($this->pluginName);
    }

    protected function initHints()
    {
        $hint = ArrayHelper::getValue($this->pluginOptions, 'hint', []);
        if (!empty($this->hintWords)) {
            $hint[] = [
                'words' => $this->hintWords,
                'match' => new JsExpression('/\b(\w{1,})$/'),
                'search' => new JsExpression(
                    'function (keyword, callback) {' .
                    '    callback($.grep(this.words, function (item) {' .
                    '        return item.indexOf(keyword) === 0;' .
                    '    }));' .
                    '}'
                ),
            ];
        }
        if (!empty($this->hintMentions)) {
            $hint[] = [
                'mentions' => $this->hintMentions,
                'match' => new JsExpression('/\B@(\w*)$/'),
                'search' => new JsExpression(
                    'function (keyword, callback) {' .
                    '    callback($.grep(this.mentions, function (item) {' .
                    '        return item.indexOf(keyword) == 0;' .
                    '    }));' .
                    '}'
                ),
                'content' => new JsExpression('function (item) { return "@" + item; }'),
            ];
        }
        if ($this->emoji) {
            /** @noinspection RequiredAttributes */
            $hint[] = [
                'match' => new JsExpression('/:([\-+\w]+)$/'),
                'search' => new JsExpression(
                    'function (keyword, callback) {' .
                    '    callback($.grep(kvEmojis, function (item) {' .
                    '        return item.indexOf(keyword) === 0;' .
                    '    }));' .
                    '}'
                ),
                'template' => new JsExpression(
                    'function (item) {' .
                    '    var content = kvEmojiUrls[item];' .
                    '    return \'<img src="\' + content + \'" width="20" /> :\' + item + \':\'' .
                    '}'
                ),
                'content' => new JsExpression(
                    'function (item) {' .
                    '    var url = kvEmojiUrls[item];' .
                    '    if (url) {' .
                    '        return $("<img />").attr("src", url).css("width", 20)[0];' .
                    '    }' .
                    '    return "";' .
                    '}'
                ),
            ];
        }
        $this->pluginOptions['hint'] = $hint;
    }


    public function registerEmogi($view)
    {
        $asset = EmojiAsset::register($view);

        $baseUrl = $asset->baseUrl . '/img';

        $js = <<< JS
        $.extend($.summernote.plugins, {
            'emoji': function (context) {
                var self = this;
                var KEY_ESC = 27;
                var KEY_TAB = 9;
                var ui = $.summernote.ui;
                var icons = {};
                var reverseIcons = {};
                var editorId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
                var chunk = function (val, chunkSize) {
                    var R = [];
                    for (var i = 0; i < val.length; i += chunkSize)
                        R.push(val.slice(i, i + chunkSize));
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
                    var body = $('body');
                    body.on('keydown', function(e) {
                        if (e.keyCode === KEY_ESC || e.keyCode === KEY_TAB) {
                            self.panel.hide();
                        }
                    });
                    body.on('mouseup', function(e) {
                        e = e.originalEvent || e;
                        var target = e.target || window;
                        if ($(target).hasClass('emoji-picker') || $(target).hasClass('emoji-menu-tab')) {
                            return;
                        }
                        self.panel.hide();
                    });
                    body.on('click', '.' + editorId + ' .emoji-menu-tab', function (e) {
                        e.stopPropagation();
                        e.preventDefault();
                        var index = 0;
                        var curclass = $(this).attr("class").split(' ');
                        curclass = curclass[1].split('-');
                        if(curclass.length===3) return;
                        curclass = curclass[0]+'-'+curclass[1];
                        $('.' + editorId + ' .emoji-menu-tabs td').each(function(i){
                            var a = $(this).find('a');
                            var aclass = a.attr("class").split(' ');
                            aclass = aclass[1].split('-');
                            aclass = aclass[0]+'-'+aclass[1];
                            if(curclass === aclass){
                                a.attr('class', 'emoji-menu-tab '+aclass+'-selected');
                                index = i;
                            }else{
                                a.attr('class', 'emoji-menu-tab '+aclass);
                            }
                        });
                        updateEmojisList(index);
                    });
                    $(document).on('click', '.' + editorId + ' .emoji-items a', function(){
                        var emoji = $('.label', $(this)).text();
                        if (document.emojiType === 'unicode') {
                            context.invoke('editor.insertText', colonToUnicode(emoji));
                        } else {
                            var img = $(createdEmojiIcon(self.icons[emoji]));
                            if (img[0].attachEvent) {
                                img[0].attachEvent('onresizestart', function(e) {
                                    e.returnValue = false;
                                }, false);
                            }
                            context.invoke('editor.insertNode', img[0]);
                        }
                        ConfigStorage.get('emojis_recent', function(curEmojis) {
                            curEmojis = curEmojis || Config.defaultRecentEmojis || [];
                            var pos = curEmojis.indexOf(emoji);
                            if (!pos) {
                                return false;
                            }
                            if (pos !== -1) {
                                curEmojis.splice(pos, 1);
                            }
                            curEmojis.unshift(emoji);
                            if (curEmojis.length > 42) {
                                curEmojis = curEmojis.slice(42);
                            }
                            ConfigStorage.set({
                                emojis_recent : curEmojis
                            });
                        });
                    });
                };
                var loadEmojis = function () {
                    var column, dataItem, hex, i, icons, j, name, reverseIcons, row, totalColumns;
                    i = void 0;
                    j = void 0;
                    hex = void 0;
                    icons = {};
                    reverseIcons = {};
                    name = void 0;
                    dataItem = void 0;
                    row = void 0;
                    column = void 0;
                    totalColumns = void 0;
                    j = 0;
                    while (j < Config.EmojiCategories.length) {
                        totalColumns = Config.EmojiCategorySpritesheetDimens[j][1];
                        i = 0;
                        while (i < Config.EmojiCategories[j].length) {
                            dataItem = Config.Emoji[Config.EmojiCategories[j][i]];
                            name = dataItem[1][0];
                            row = Math.floor(i / totalColumns);
                            column = i % totalColumns;
                            icons[':' + name + ':'] = [j, row, column, ':' + name + ':'];
                            reverseIcons[name] = dataItem[0];
                            i++;
                        }
                        j++;
                    }
                    self.icons = icons;
                    self.reverseIcons = reverseIcons;
                    if (!Config.rx_codes) {
                        Config.init_unified();
                    }
                };
                var updateEmojisList = function (index) {
                    var items = $('.' + editorId + ' .emoji-items');
                    items.html('');
                    if(index > 0) {
                        $.each(self.icons, function (key, icon) {
                            if (self.icons.hasOwnProperty(key)
                                && icon[0] === (index - 1)) {
                                items.append('<a href="javascript:void(0)" title="'
                                    + Config.htmlEntities(key) + '">'
                                    + createdEmojiIcon(icon, true)
                                    + '<span class="label">' + Config.htmlEntities(key)
                                    + '</span></a>');
                            }
                        });
                    }else{
                        ConfigStorage.get('emojis_recent', function(curEmojis) {
                            curEmojis = curEmojis || Config.defaultRecentEmojis || [];
                            var key, i;
                            for (i = 0; i < curEmojis.length; i++) {
                                key = curEmojis[i];
                                if (self.icons[key]) {
                                    items.append('<a href="javascript:void(0)" title="'
                                        + Config.htmlEntities(key) + '">'
                                        + createdEmojiIcon(self.icons[key], true)
                                        + '<span class="label">' + Config.htmlEntities(key)
                                        + '</span></a>');
                                }
                            }
                        });
                    }
                };
                var createdEmojiIcon = function(emoji){
                    var category = emoji[0];
                    var row = emoji[1];
                    var column = emoji[2];
                    var name = emoji[3];
                    var filename = "$baseUrl/emoji_spritesheet_!.png";
                    var blankGifPath = "$baseUrl/blank.gif";
                    var iconSize = 25;
                    var xoffset = -(iconSize * column);
                    var yoffset = -(iconSize * row);
                    var scaledWidth = (Config.EmojiCategorySpritesheetDimens[category][1] * iconSize);
                    var scaledHeight = (Config.EmojiCategorySpritesheetDimens[category][0] * iconSize);
    
                    var style = 'display:inline-block;';
                    style += 'width:' + iconSize + 'px;';
                    style += 'height:' + iconSize + 'px;';
                    style += 'background:url(\'' + filename.replace('!', category) + '\') '
                        + xoffset + 'px ' + yoffset + 'px no-repeat;';
                    style += 'background-size:' + scaledWidth + 'px ' + scaledHeight
                        + 'px;';
                    return '<img src="' + blankGifPath + '" class="img" style="'
                        + style + '" alt="' + Config.htmlEntities(name) + '">';
                };
                var colonToUnicode = function(emoij) {
                    return emoij.replace(Config.rx_colons, function(m) {
                        var val;
                        val = Config.mapcolon[m];
                        if (val) {
                            return val;
                        } else {
                            return '';
                        }
                    });
                };
                
                context.memo('button.emoji', function () {
                    var button = ui.button({
                        contents: '<i class="far fa-smile emoji-picker-container emoji-picker"/></i>',
                        click: function () {
                            var width = self.panel.width();
                            if(width > self.panel.position().left){
                                self.panel.css({left: '0', top: '100%'});
                            }
    
                            self.panel.show();
                        }
                    });
                    self.emoji = button.render();
                    return self.emoji;
                });
    
                // This events will be attached when editor is initialized.
                this.events = {
                    'summernote.init': function (we, e) {
                        addListener();
                    }
                };
                this.initialize = function () {
                    this.panel = $('<div class="emoji-menu ' + editorId + '">\\n' + 
                        '    <div class="emoji-items-wrap1">\\n' +
                        '        <table class="emoji-menu-tabs">\\n' +
                        '            <tbody>\\n' +
                        '            <tr>\\n' +
                        '                <td><a class="emoji-menu-tab icon-recent-selected"></a></td>\\n' + 
                        '                <td><a class="emoji-menu-tab icon-smile"></a></td>\\n' +
                        '                <td><a class="emoji-menu-tab icon-flower"></a></td>\\n' +
                        '                <td><a class="emoji-menu-tab icon-bell"></a></td>\\n' +
                        '                <td><a class="emoji-menu-tab icon-car"></a></td>\\n' +
                        '                <td><a class="emoji-menu-tab icon-grid"></a></td>\\n' +
                        '            </tr>\\n' +
                        '            </tbody>\\n' +
                        '        </table>\\n' +
                        '        <div class="emoji-items-wrap mobile_scrollable_wrap scroll-block">\\n' +
                        '            <div class="emoji-items"></div>\\n' +
                        '        </div>\\n' +
                        '    </div>\\n' +
                        '</div>').hide();
                    this.panel.appendTo(this.emoji.parent());
                    loadEmojis();
                    updateEmojisList(0);
                };
                this.destroy = function () {
                    this.panel.remove();
                    this.panel = null;
                };
            }
        });

JS;
        $view->registerJs($js);
    }
}