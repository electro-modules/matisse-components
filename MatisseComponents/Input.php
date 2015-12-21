<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\is;
use Selenia\Matisse\Properties\Types\type;

class InputProperties extends HtmlComponentProperties
{
  /**
   * @var string Triggers an action when the user presses Enter
   */
  public $action = '';
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
  public $max = '';
  /**
   * @var int
   */
  public $maxLength = 0;
  /**
   * @var int
   */
  public $maxValue = 0;
  /**
   * @var string
   */
  public $min = '';
  /**
   * @var int
   */
  public $minValue = 0;
  /**
   * @var string
   */
  public $name = type::id;
  /**
   * @var string
   */
  public $onChange = '';
  /**
   * @var string
   */
  public $pattern = '';
  /**
   * @var string
   */
  public $placeholder = '';
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
  public $step = '';
  /**
   * @var int
   */
  public $tabIndex = 0;
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

  protected $autoId = true;

  /**
   * Returns the component's attributes.
   * @return InputProperties
   */
  public function props ()
  {
    return $this->props;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return InputProperties
   */
  public function newProperties ()
  {
    return new InputProperties($this);
  }

  protected function preRender ()
  {
    $attr = $this->props ();

//    if ($attr->type == 'date') {
//      $cal = new Calendar($this->context);
//      $cal->attachTo ($this);
//      $cal->detach ();
//    }
    $type = $attr->get ('type', 'text');
    switch ($type) {
      case 'multiline':
        $this->containerTag = 'textarea';
        break;
      default:
        $this->containerTag = 'input';
        $this->addClass ("type-$type");
    }
    if ($attr->readOnly)
      $this->addClass ('readonly');
    parent::preRender ();
  }

  protected function render ()
  {
    $attr   = $this->props ();
    $type   = $attr->get ('type', 'text');
    $name   = $attr->name;
    $action = when ($attr->action, "checkKeybAction(event,'" . $attr->action . "')");

    $this->page->addInlineScript (<<<JS
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
          'readonly'   => $attr->readOnly ? 'readonly' : null,
          'disabled'   => $attr->disabled ? 'disabled' : null,
          'tabindex'   => $attr->tabIndex,
          'autofocus'  => $attr->autofocus,
          'onfocus'    => $attr->autoselect ? 'this.select()' : null,
          'onchange'   => $attr->onChange,
          'spellcheck' => 'false',
          'maxlength'  => $attr->maxLength,
          'required'   => $attr->required,
        ]);
        $this->setContent ($attr->value);
        break;
      case 'date':
      case 'datetime':
        $this->page->addScript ('lib/moment/min/moment-with-locales.min.js');
        $this->page->addScript ('lib/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
        $this->page->addStylesheet ('lib/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css');

        $this->addAttrs ([
          'type'       => 'text',
          'name'       => $name,
          'value'      => $attr->value,
          'readonly'   => $attr->readOnly ? 'readonly' : null,
          'disabled'   => $attr->disabled ? 'disabled' : null,
          'tabindex'   => $attr->tabIndex,
          'autofocus'  => $attr->autofocus,
          'onfocus'    => $attr->autoselect ? 'this.select()' : null,
          'onchange'   => $attr->onChange,
          'onkeypress' => $action,
          'max'        => $attr->max,
          'min'        => $attr->min,
          'maxlength'  => $attr->maxLength,
          'pattern'    => $attr->pattern,
          'required'   => $attr->required,
        ]);
        $hasTime = boolToStr ($type == 'datetime');
        $this->page->addInlineScript (<<<HTML
$(function () {
  $('#{$name}0').datetimepicker({
    locale:      '$attr->lang',
    defaultDate: '$attr->value' || new moment(),
    format:      '$attr->dateFormat',
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
          'value'        => $attr->value,
          'placeholder'  => $attr->placeholder,
          'readonly'     => $attr->readOnly ? 'readonly' : null,
          'autocomplete' => $attr->autocomplete ? null : 'off',
          'disabled'     => $attr->disabled ? 'disabled' : null,
          'tabindex'     => $attr->tabIndex,
          'autofocus'    => $attr->autofocus,
          'onfocus'      => $attr->autoselect ? 'this.select()' : null,
          'onchange'     => $attr->onChange,
          'onkeypress'   => $action,
          'max'          => $attr->max,
          'min'          => $attr->min,
          'maxlength'    => $attr->maxLength,
          'pattern'      => $attr->pattern,
          'required'     => $attr->required,
          'step'         => $attr->step,
        ]);
    }
  }

}
