<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\Attributes\VisualComponentAttributes;
use Selene\Matisse\VisualComponent;

class InputAttributes extends VisualComponentAttributes
{
  public $name;
  public $value;
  public $type;
  public $autofocus    = false;
  public $readOnly    = false;
  public $autoselect   = false;
  public $autocomplete = true;
  public $onChange;
  /** @var string Triggers an action when the user presses Enter */
  public $action       = '';
  public $dateFormat  = 'YYYY-MM-DD';
  public $maxValue    = '';
  public $minValue    = '';
  public $popupAnchor = '';
  public $startDate;
  public $tabIndex;
  public $placeholder;
  public $lang         = '';

  public $max;
  public $min;
  public $maxLength;
  public $pattern;
  public $required;
  public $step;

  protected function typeof_name () { return AttributeType::ID; }

  protected function typeof_value () { return AttributeType::TEXT; }

  protected function typeof_type () { return AttributeType::ID; }

  protected function enum_type () { return ['line', 'multiline', 'password', 'date', 'number']; }

  protected function typeof_autofocus () { return AttributeType::BOOL; }

  protected function typeof_autocomplete () { return AttributeType::BOOL; }

  protected function typeof_readOnly () { return AttributeType::BOOL; }

  protected function typeof_autoselect () { return AttributeType::BOOL; }

  protected function typeof_onChange () { return AttributeType::TEXT; }

  protected function typeof_action () { return AttributeType::TEXT; }

  protected function typeof_dateFormat () { return AttributeType::TEXT; }

  protected function typeof_maxValue () { return AttributeType::NUM; }

  protected function typeof_minValue () { return AttributeType::NUM; }

  protected function typeof_popupAnchor () { return AttributeType::ID; }

  protected function typeof_startDate () { return AttributeType::TEXT; }

  protected function typeof_tabIndex () { return AttributeType::NUM; }

  protected function typeof_placeholder () { return AttributeType::TEXT; }

  protected function typeof_lang () { return AttributeType::TEXT; }

  protected function typeof_max () { return AttributeType::TEXT; }

  protected function typeof_min () { return AttributeType::TEXT; }

  protected function typeof_maxLength () { return AttributeType::NUM; }

  protected function typeof_pattern () { return AttributeType::TEXT; }

  protected function typeof_required () { return AttributeType::BOOL; }

  protected function typeof_step () { return AttributeType::TEXT; }

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
    if ($attr->type == 'date') {
      $cal = new Calendar($this->context);
      $cal->attachTo ($this);
      $cal->detach ();
    }
    $type = $attr->get ('type', 'line');
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
    $type   = $attr->get ('type', 'line');
    $name   = $attr->name;
    $action = ifset ($attr->action, "checkKeybAction(event,'" . $attr->action . "')");

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
        $this->addAttributes ([
          'name'       => $name,
          'cols'       => 0,
          'readonly'   => $attr->readOnly ? 'readonly' : null,
          'disabled'   => $attr->disabled ? 'disabled' : null,
          'tabindex'   => $attr->tabIndex,
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
        $this->page->addScript ('modules/admin/js/moment-with-locales.min.js');
        $this->page->addScript ('modules/admin/js/bootstrap-datetimepicker.min.js');
        $this->page->addStylesheet ('modules/admin/css/bootstrap-datetimepicker.min.css');

        $this->addAttributes ([
          'type'       => 'text',
          'name'       => $name,
          'value'      => $attr->value,
          'readonly'   => $attr->readOnly ? 'readonly' : null,
          'disabled'   => $attr->disabled ? 'disabled' : null,
          'tabindex'   => $attr->tabIndex,
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
        $this->beginContent ();
        echo <<<HTML
<script type="text/javascript">
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
</script>
HTML;
        break;
      case 'line':
        $type = 'text';
      // no break
      default:
        $this->addAttributes ([
          'type'         => $type,
          'name'         => $name,
          'value'        => $attr->value,
          'placeholder'  => $attr->placeholder,
          'readonly'     => $attr->readOnly ? 'readonly' : null,
          'autocomplete' => $attr->autocomplete ? null : 'off',
          'disabled'     => $attr->disabled ? 'disabled' : null,
          'tabindex'     => $attr->tabIndex,
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
