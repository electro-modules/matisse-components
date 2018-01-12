<?php
namespace Electro\Plugins\MatisseComponents;

use Matisse\Components\Base\HtmlComponent;
use Matisse\Lib\JavascriptCodeGen;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\is;
use Matisse\Properties\TypeSystem\type;

class InputProperties extends HtmlComponentProperties
{
  /**
   * @var string Triggers an action when the user presses Enter
   */
  public $action = [type::id, null];
  /**
   * @var bool
   */
  public $autocomplete = true;
  /**
   * @var bool
   */
  public $autofocus = false;
  /**
   * @var bool
   */
  public $autoselect = false;
  /**
   * @var string
   */
  public $dateFormat = 'YYYY-MM-DD';
  /**
   * @var string
   */
  public $datetimeFormat = 'YYYY-MM-DD hh:mm:ss';
  /**
   * @var int|null Number of decimal points. Defaults to 0 if not specified.
   */
  public $decimals = type::number;
  /**
   * @var string If not empty and the field's value is empty, the later will be set to this value.
   */
  public $defaultValue = type::string;
  /**
   * @var string
   */
  public $lang = 'en';
  /**
   * @var string
   */
  public $max = [type::string, null];
  /**
   * @var int
   */
  public $maxLength = [type::number, null];
  /**
   * @var string
   */
  public $min = [type::string, null];
  /**
   * @var int
   */
  public $minLength = [type::number, null];
  /**
   * @var string
   */
  public $name = '';
  /**
   * @var string
   */
  public $onChange = [type::string, null]; //allow 'field[]'
  /**
   * @var string
   */
  public $pattern = [type::string, null];
  /**
   * @var string
   */
  public $placeholder = [type::string, null];
  /**
   * @var string
   */
  public $popupAnchor = type::id;
  /**
   * @var bool
   */
  public $readOnly = false;
  /**
   * @var bool
   */
  public $required = false;
  /**
   * @var string
   */
  public $startDate = '';
  /**
   * @var string
   */
  public $step = [type::string, null];
  /**
   * @var int
   */
  public $tabIndex = [type::number, null];
  /**
   * @var string
   */
  public $timeFormat = 'hh:mm:ss';
  /**
   * @var string
   */
  public $type = [
    'text', is::enum, [
      'text', 'line', 'multiline', 'password', 'date', 'time', 'datetime', 'number', 'color', 'hidden',
      'url', 'email', 'tel', 'range', 'search', 'month', 'week',
    ],
  ];
  /**
   * @var string
   */
  public $value = '';
}

class Input extends HtmlComponent
{
  const propertiesClass = InputProperties::class;
  /** @var InputProperties */
  public    $props;
  protected $autoId = true;

  protected function init ()
  {
    parent::init ();
    $ctx    = $this->context;
    $assets = $ctx->getAssetsService ();
    switch ($this->props->get ('type', 'text')) {
      case 'multiline':
        $assets->addScript ('lib/textarea-autosize/dist/jquery.textarea_autosize.min.js');
        break;
      case 'date':
      case 'time':
      case 'datetime':
        $assets->addScript ('lib/moment/min/moment-with-locales.min.js');
        $assets
          ->addScript ('lib/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')
          ->addStylesheet ('lib/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css');
        break;
      case 'color':
        $assets
          ->addScript ('lib/mjolnic-bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js')
          ->addStylesheet ('lib/mjolnic-bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css');
        break;
      case 'number':
        $assets
          ->addScript ('lib/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js')
          ->addStylesheet ('lib/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css');
        break;
    }
    $assets->addInlineScript (<<<JS
function checkKeybAction(event,action) {
  if (event.keyCode == 13) setTimeout(function(){
    selenia.doAction(action);
  },1);
}
JS
      , 'initInput');
  }

  protected function preRender ()
  {
    $prop = $this->props;
    $type = $prop->get ('type', 'text');
    switch ($type) {
      case 'multiline':
        $this->containerTag = 'textarea';
        break;
      case 'color':
        $this->containerTag = 'div';
        $this->addClass ('input-group');
        break;
      default:
        $this->containerTag = 'input';
        $this->addClass ("type-$type");
    }
    if ($prop->readOnly)
      $this->addClass ('readonly');
    parent::preRender ();
  }

  protected function render ()
  {
    $prop   = $this->props;
    $type   = $prop->get ('type', 'text');
    $name   = $prop->name;
    $action = when ($prop->action, "checkKeybAction(event,'" . $prop->action . "')");
    $assets = $this->context->getAssetsService ();

    $assets->addInlineScript (<<<JS
selenia.validateInput = function (input) {
  var v = input.validity;
  input.setCustomValidity(
    v.badInput        && "\$VALIDATION_BAD_INPUT" ||
    v.patternMismatch && "\$VALIDATION_PATTERN_MISMATCH" ||
    v.rangeOverflow   && "\$VALIDATION_RANGE_OVERFLOW" ||
    v.rangeUnderflow  && "\$VALIDATION_RANGE_UNDERFLOW" ||
    v.stepMismatch    && "\$VALIDATION_STEP_MISMATCH" ||
    v.tooLong         && "\$VALIDATION_TOO_LONG" ||
    v.tooShort        && "\$VALIDATION_TOO_SHORT" ||
    v.typeMismatch    && "\$VALIDATION_TYPE_MISMATCH" ||
    v.valueMissing    && "\$VALIDATION_VALUE_MISSING" ||
    ''
  );
};
JS
      , 'validateInput');

    $value = either($prop->value, $prop->defaultValue);

    switch ($type) {
      case 'multiline':
        $assets->addInlineScript (<<<'JS'
          $('textarea.Input').textareaAutoSize();
          selenia.on('languageChanged',function(lang){
            $('textarea.Input[lang='+lang+']').trigger('input');
          });
          selenia.on('tabChanged',function(tab){
            tab.find('textarea.Input').trigger('input');
          });
JS
          , 'input-autosize');
        $this->addAttrs ([
          'name'       => $name,
          'cols'       => 0,
          'readonly'   => $prop->readOnly ? 'readonly' : null,
          'disabled'   => $prop->disabled ? 'disabled' : null,
          'tabindex'   => $prop->tabIndex,
          'autofocus'  => $prop->autofocus,
          'onfocus'    => $prop->autoselect ? 'this.select()' : 'this.value=this.value',
          'onchange'   => $prop->onChange,
          'spellcheck' => 'false',
          'maxlength'  => $prop->maxLength,
          'minlength'  => $prop->minLength,
          'required'   => $prop->required,
        ]);
        $this->setContent ($value);
        break;
      case 'date':
      case 'time':
      case 'datetime':
        $this->addAttrs ([
          'type'       => 'text',
          'name'       => $name,
          'value'      => $value,
          'readonly'   => $prop->readOnly ? 'readonly' : null,
          'disabled'   => $prop->disabled ? 'disabled' : null,
          'tabindex'   => $prop->tabIndex,
          'autofocus'  => $prop->autofocus,
          'onfocus'    => $prop->autoselect ? 'this.select()' : 'this.value=this.value',
          'onchange'   => $prop->onChange,
          'onkeypress' => $action,
          'max'        => $prop->max,
          'min'        => $prop->min,
          'maxlength'  => $prop->maxLength,
          'minlength'  => $prop->minLength,
          'pattern'    => $prop->pattern,
          'required'   => $prop->required,
        ]);

        switch ($type) {
          case 'date':
            $format = $prop->dateFormat;
            break;
          case 'time':
            $format = $prop->timeFormat;
            break;
          default:
            $format = $prop->datetimeFormat;
        }
        $assets->addInlineScript (<<<JS
$('#{$prop->id}').datetimepicker({
  locale:      '$prop->lang',
  defaultDate: '$value' || new moment(),
  format:      '$format',
  sideBySide:  true,
  showTodayButton: true,
  showClear: true,
  showClose: true
}).on('dp.change',function(e){
    $prop->onChange
});
JS
        );
        break;
      case 'color':
        $this->beginContent ();
        $this->tag ('input', [
          'type'       => 'text',
          'class'      => 'form-control',
          'name'       => $name,
          'value'      => $value,
          'readonly'   => $prop->readOnly ? 'readonly' : null,
          'disabled'   => $prop->disabled ? 'disabled' : null,
          'tabindex'   => $prop->tabIndex,
          'autofocus'  => $prop->autofocus,
          'onfocus'    => $prop->autoselect ? 'this.select()' : 'this.value=this.value',
          'onchange'   => $prop->onChange,
          'onkeypress' => $action,
          'max'        => $prop->max,
          'min'        => $prop->min,
          'maxlength'  => $prop->maxLength,
          'minlength'  => $prop->minLength,
          'pattern'    => $prop->pattern,
          'required'   => $prop->required,
        ]);
        echo '<span class="input-group-addon"><i></i></span>';
        $assets->addInlineScript ("$('#{$name}0').colorpicker();");
        break;
      case 'number':
        $this->addAttrs ([
          'type'         => 'text',
          'name'         => $name,
          'value'        => $value,
          'placeholder'  => $prop->placeholder,
          'readonly'     => $prop->readOnly ? 'readonly' : null,
          'autocomplete' => $prop->autocomplete ? null : 'off',
          'disabled'     => $prop->disabled ? 'disabled' : null,
          'tabindex'     => $prop->tabIndex,
          'autofocus'    => $prop->autofocus,
          'onfocus'      => $prop->autoselect ? 'this.select()' : 'this.value=this.value',
          'onchange'     => $prop->onChange,
          'onkeypress'   => $action,
          'maxlength'    => $prop->maxLength,
          'minlength'    => $prop->minLength,
          'pattern'      => $prop->pattern,
          'required'     => $prop->required,
        ]);
        $options = JavascriptCodeGen::makeOptions ([
          'max'                   => $prop->max,
          'min'                   => $prop->min,
          'step'                  => $prop->step,
          'decimals'              => $prop->decimals,
          'forcestepdivisibility' => 'none',
        ], '  ');
        $assets->addInlineScript ("$('#{$prop->id}').TouchSpin($options);");
        break;
      default:
        $this->addAttrs ([
          'type'         => $type,
          'name'         => $name,
          'value'        => $value,
          'placeholder'  => $prop->placeholder,
          'readonly'     => $prop->readOnly ? 'readonly' : null,
          'autocomplete' => $prop->autocomplete ? null : 'off',
          'disabled'     => $prop->disabled ? 'disabled' : null,
          'tabindex'     => $prop->tabIndex,
          'autofocus'    => $prop->autofocus,
          'onfocus'      => $prop->autoselect ? 'this.select()' : 'this.value=this.value',
          'onchange'     => $prop->onChange,
          'onkeypress'   => $action,
          'max'          => $prop->max,
          'min'          => $prop->min,
          'maxlength'    => $prop->maxLength,
          'minlength'    => $prop->minLength,
          'pattern'      => $prop->pattern,
          'required'     => $prop->required,
          'step'         => $prop->step,
        ]);
    }
  }

}
