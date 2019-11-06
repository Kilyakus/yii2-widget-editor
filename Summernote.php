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
    public $pluginOptions   = [];
    public $usePresets = true;
    protected $pluginPresets = [
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

    public $uploadUrl;

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

        if(!empty($this->pluginOptions) && $this->usePresets == true){

            $pluginOptions = $this->pluginOptions;

            foreach ($this->pluginPresets as $attribute => $option) {
                if($attribute == 'toolbar' && $this->pluginOptions[$attribute]){
                    foreach ($this->pluginOptions[$attribute] as $pluginKey => $pluginAttribute) {
                        foreach ($option as $optionKey => $optionAttribute) {
                            if($pluginAttribute[0] == $optionAttribute[0]){
                                $this->pluginPresets[$attribute][$optionKey] = $this->pluginOptions[$attribute][$pluginKey];
                                unset($this->pluginOptions[$attribute][$pluginKey]);
                            }
                        }
                    }
                }
            }

            $this->pluginOptions = ArrayHelper::merge($this->pluginPresets, $this->pluginOptions);
        }

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

        $callbacks = ArrayHelper::getValue($this->pluginOptions, 'callbacks', []);

        $this->uploadUrl = \yii\helpers\Url::to(['/admin/redactor/upload', 'dir' => 'userfiles/' . Yii::$app->user->id . '/images']);

        $callbacks['onImageUpload'] = new JsExpression('function(files) {
                for(var i=0; i < files.length; i++) {
                    sendCMSFile(files[i],"#'.$this->options['id'].'");
                }
        }');

        $this->pluginOptions['callbacks'] = $callbacks;


        $js = <<< JS
        function sendCMSFile(file,container) {
            console.log(container)
            if (file.type.includes('image')) {
                var name = file.name.split(".");
                name = name[0];
                var data = new FormData();
                data.append('action', 'imgUpload');
                data.append('file', file);
                $.ajax({
                    url: "$this->uploadUrl",
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
        }

JS;
        $view = $this->getView();
        $view->registerJs($js, $view::POS_END);
    }
}