<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;

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
   * @var int
   */
  public $maxValue = [type::number, null];
  /**
   * @var string
   */
  public $min = [type::string, null];
  /**
   * @var int
   */
  public $minValue = [type::number, null];
  /**
   * @var string
   */
  public $name = ''; //allow 'field[]'
  /**
   * @var string
   */
  public $onChange = [type::string, null];
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
  public $type = ['text', is::enum, ['text', 'line', 'multiline', 'password', 'date', 'number']];
  /**
   * @var string
   */
  public $value = '';
}

class Input extends HtmlComponent
{
  protected static $propertiesClass = InputProperties::class;

  protected $autoId = true;

  /** @var InputProperties */
  public $props;

  protected function preRender ()
  {
    $prop = $this->props;

//    if ($prop->type == 'date') {
//      $cal = new Calendar($this->context);
//      $cal->attachTo ($this);
//      $cal->detach ();
//    }
    $type = $prop->get ('type', 'text');
    switch ($type) {
      case 'multiline':
        $this->containerTag = 'textarea';
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

    $this->context->addInlineScript (<<<JS
function validateInput (input) {
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
}
JS
      , 'validateInput');

    switch ($type) {
      case 'multiline':
        $this->addAttrs ([
          'name'       => $name,
          'cols'       => 0,
          'readonly'   => $prop->readOnly ? 'readonly' : null,
          'disabled'   => $prop->disabled ? 'disabled' : null,
          'tabindex'   => $prop->tabIndex,
          'autofocus'  => $prop->autofocus,
          'onfocus'    => $prop->autoselect ? 'this.select()' : null,
          'onchange'   => $prop->onChange,
          'spellcheck' => 'false',
          'maxlength'  => $prop->maxLength,
          'required'   => $prop->required,
        ]);
        $this->setContent ($prop->value);
        break;
      case 'date':
      case 'datetime':
        $this->context->addScript ('lib/moment/min/moment-with-locales.min.js');
        $this->context->addScript ('lib/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
        $this->context->addStylesheet ('lib/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css');

        $this->addAttrs ([
          'type'       => 'text',
          'name'       => $name,
          'value'      => $prop->value,
          'readonly'   => $prop->readOnly ? 'readonly' : null,
          'disabled'   => $prop->disabled ? 'disabled' : null,
          'tabindex'   => $prop->tabIndex,
          'autofocus'  => $prop->autofocus,
          'onfocus'    => $prop->autoselect ? 'this.select()' : null,
          'onchange'   => $prop->onChange,
          'onkeypress' => $action,
          'max'        => $prop->max,
          'min'        => $prop->min,
          'maxlength'  => $prop->maxLength,
          'pattern'    => $prop->pattern,
          'required'   => $prop->required,
        ]);
        $hasTime = boolToStr ($type == 'datetime');
        $this->context->addInlineScript (<<<HTML
$(function () {
  $('#{$name}0').datetimepicker({
    locale:      '$prop->lang',
    defaultDate: '$prop->value' || new moment(),
    format:      '$prop->dateFormat',
    sideBySide:  $hasTime,
    showTodayButton: true,
    showClear: true,
    showClose: true
  });
});
HTML
        );
        break;
      case 'text':
        /** @noinspection PhpMissingBreakStatementInspection */
      case 'line':
        $type = 'text';
      // no break
      default:
        $this->addAttrs ([
          'type'         => $type,
          'name'         => $name,
          'value'        => $prop->value,
          'placeholder'  => $prop->placeholder,
          'readonly'     => $prop->readOnly ? 'readonly' : null,
          'autocomplete' => $prop->autocomplete ? null : 'off',
          'disabled'     => $prop->disabled ? 'disabled' : null,
          'tabindex'     => $prop->tabIndex,
          'autofocus'    => $prop->autofocus,
          'onfocus'      => $prop->autoselect ? 'this.select()' : null,
          'onchange'     => $prop->onChange,
          'onkeypress'   => $action,
          'max'          => $prop->max,
          'min'          => $prop->min,
          'maxlength'    => $prop->maxLength,
          'pattern'      => $prop->pattern,
          'required'     => $prop->required,
          'step'         => $prop->step,
        ]);
    }
  }

}
