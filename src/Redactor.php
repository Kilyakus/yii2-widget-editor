<?php
namespace kilyakus\widget\redactor;

use Yii;
use kilyakus\widget\redactor\assets\CodeMirrorAsset;
use kilyakus\widget\redactor\assets\SummernoteAsset;
use kilyakus\widget\redactor\assets\SummernoteThemeAsset;
use kilyakus\widgets\InputWidget;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Inflector;
use yii\web\JsExpression;

class Redactor extends InputWidget
{
    public $pluginName = 'summernote';

    const THEME_BS3 = 'bs3';
    const THEME_BS4 = 'bs4';
    const THEME_LITE = 'lite';
    const THEME_SIMPLE = 'simple';

    public $theme = self::THEME_BS3;

    protected static $_bs3Themes = [
        self::THEME_SIMPLE,
    ];

    protected static $_bs4Themes = [
    ];

    protected static $_liteThemes = [
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
            ['style1', ['style', 'clear']],
            ['style2', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript']],
            ['font', ['fontname', 'fontsize', 'height', 'color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video', ]],
            ['misc', ['table', 'hr']]
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
        $this->initCallbacks();
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
            $script .= "initEmojis();\n";
        }
        if ($this->codemirror) {
            $script .= "initCMFormatter('{$id}');\n";
        }
        $script .= parent::getPluginScript($name, $element, $callback, $callbackCon);
        return $script;
    }

    public function registerAssets()
    {
        $fieldId = $this->options['id'];

        $view = $this->getView();

        if (!in_array($this->theme, self::$_bs3Themes) && !in_array($this->theme, self::$_bs4Themes) && !in_array($this->theme, self::$_liteThemes)) {
            SummernoteAsset::register($view)->define($this->theme, $this->i18n);
            SummernoteThemeAsset::register($view)->define($this->theme);
        }

        $this->registerBundle(self::$_bs3Themes,self::THEME_BS3);
        $this->registerBundle(self::$_bs4Themes,self::THEME_BS4);
        $this->registerBundle(self::$_liteThemes,self::THEME_LITE);
        
        if ($this->i18n) {
            $lang = [
                'lang' => Yii::$app->language . '-' . strtoupper(Yii::$app->language)
            ];
            $this->pluginOptions = ArrayHelper::merge($this->pluginOptions, $lang) ;
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

    public function registerBundle($themes,$theme)
    {
        $view = $this->getView();

        if (in_array($this->theme, $themes)) {

            SummernoteAsset::register($view)->define($theme, $this->i18n);
            SummernoteThemeAsset::register($view)->define($theme, $this->theme);
        }
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

    protected function initCallbacks()
    {
        if($this->uploadUrl){
            $callbacks = ArrayHelper::getValue($this->pluginOptions, 'callbacks', []);

            $callbacks['onImageUpload'] = new JsExpression('function(files) {
                    for(var i=0; i < files.length; i++) {
                        onImageUpload(files[i],"#'.$this->options['id'].'","'.$this->uploadUrl.'");
                    }
            }');

            $this->pluginOptions['callbacks'] = $callbacks;
        }
    }
}