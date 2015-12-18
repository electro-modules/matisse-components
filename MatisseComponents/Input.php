<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\Type;
use Selenia\Matisse\VisualComponent;

class InputAttributes extends VisualComponentAttributes
{
  /** @var string Triggers an action when the user presses Enter */
  public $action       = '';
  public $autocomplete = true;
  public $autofocus    = false;
  public $autoselect   = false;
  public $dateFormat   = 'YYYY-MM-DD';
  public $lang         = 'en';
  public $max;
  public $maxLength;
  public $maxValue     = '';
  public $min;
  public $minValue     = '';
  public $name;
  public $onChange;
  public $pattern;
  public $placeholder;
  public $popupAnchor  = '';
  public $readOnly     = false;
  public $required;
  public $startDate;
  public $step;
  public $tabIndex;
  public $type;
  public $value;

  protected function enum_type () { return ['text', 'line', 'multiline', 'password', 'date', 'number']; }

  protected function typeof_action () { return Type::TEXT; }

  protected function typeof_autocomplete () { return Type::BOOL; }

  protected function typeof_autofocus () { return Type::BOOL; }

  protected function typeof_autoselect () { return Type::BOOL; }

  protected function typeof_dateFormat () { return Type::TEXT; }

  protected function typeof_lang () { return Type::TEXT; }

  protected function typeof_max () { return Type::TEXT; }

  protected function typeof_maxLength () { return Type::NUM; }

  protected function typeof_maxValue () { return Type::NUM; }

  protected function typeof_min () { return Type::TEXT; }

  protected function typeof_minValue () { return Type::NUM; }

  protected function typeof_name () { return Type::ID; }

  protected function typeof_onChange () { return Type::TEXT; }

  protected function typeof_pattern () { return Type::TEXT; }

  protected function typeof_placeholder () { return Type::TEXT; }

  protected function typeof_popupAnchor () { return Type::ID; }

  protected function typeof_readOnly () { return Type::BOOL; }

  protected function typeof_required () { return Type::BOOL; }

  protected function typeof_startDate () { return Type::TEXT; }

  protected function typeof_step () { return Type::TEXT; }

  protected function typeof_tabIndex () { return Type::NUM; }

  protected function typeof_type () { return Type::ID; }

  protected function typeof_value () { return Type::TEXT; }

}

class Input extends VisualComponent
{

  protected $autoId = true;

  /**
   * Returns the component's attributes.
   * @return InputAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return InputAttributes
   */
  public function newAttributes ()
  {
    return new InputAttributes($this);
  }

  protected function preRender ()
  {
    $attr = $this->attrs ();

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
    $attr   = $this->attrs ();
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