<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\Type;
use Selenia\Matisse\Component;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\VisualComponent;

class SelectorAttributes extends VisualComponentAttributes
{
  public $name;
  public $value;
  public $values;
  public $valueField       = '0';
  public $labelField       = '1';
  public $list_item;
  public $data;
  public $autofocus        = false;
  public $emptySelection   = false;
  public $autoselectFirst  = false;
  public $loadLinkedOnInit = true;
  public $emptyLabel       = '';
  public $onChange;
  public $sourceUrl; //use $name in the URL to bind to the value of the $name field, otherwhise the linked value will be appended
  public $linkedSelector;
  public $autoOpenLinked;
  public $multiple         = false;
  public $strict           = false;

  protected function typeof_name () { return Type::ID; }

  protected function typeof_value () { return Type::TEXT; }

  protected function typeof_values () { return Type::DATA; }

  protected function typeof_valueField () { return Type::ID; }

  protected function typeof_labelField () { return Type::ID; }

  protected function typeof_listItem () { return Type::SRC; }

  protected function typeof_data () { return Type::DATA; }

  protected function typeof_autofocus () { return Type::BOOL; }

  protected function typeof_emptySelection () { return Type::BOOL; }

  protected function typeof_emptyLabel () { return Type::TEXT; }

  protected function typeof_autoselectFirst () { return Type::BOOL; }

  protected function typeof_loadLinkedOnInit () { return Type::BOOL; }

  protected function typeof_onChange () { return Type::TEXT; }

  protected function typeof_sourceUrl () { return Type::TEXT; }

  protected function typeof_linkedSelector () { return Type::ID; }

  protected function typeof_autoOpenLinked () { return Type::BOOL; }

  protected function typeof_multiple () { return Type::BOOL; }

  protected function typeof_strict () { return Type::BOOL; }
}

class Selector extends VisualComponent
{
  protected $autoId = true;

  protected $containerTag = 'select';
  private   $selectedLabel;

  /**
   * Returns the component's attributes.
   * @return SelectorAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return SelectorAttributes
   */
  public function newAttributes ()
  {
    return new SelectorAttributes($this);
  }

  protected function render ()
  {
    $attr       = $this->attrs ();
    $isMultiple = $attr->multiple;

    $this->attr ('name', $attr->name);
    $this->attrIf ($isMultiple, 'multiple', '');
    $this->attrIf ($attr->onChange, 'onchange', $attr->onChange);
    $this->beginContent ();
    if ($attr->emptySelection) {
      $sel = exists ($attr->value) ? '' : ' selected';
      echo '<option value=""' . $sel . '>' . $attr->emptyLabel . '</option>';
    }
    $this->contextualModel = $attr->get ('data');
    if (isset($this->contextualModel)) {
      /** @var \Iterator $dataIter */
      $dataIter = $this->contextualModel->getIterator ();
      $dataIter->rewind ();
      if ($dataIter->valid ()) {
        $template = $attr->get ('list_item');
        if (isset($template)) {
          do {
            $template->value = $this->evalBinding ('{' . $attr->valueField . '}');
            Component::renderSet ($template);
            $dataIter->next ();
          } while ($dataIter->valid ());
        }
        else {
          $selValue = strval ($attr->get ('value'));
          if ($isMultiple) {
            $values = $attr->values;
            if (method_exists ($values, 'getIterator')) {
              $it = $values->getIterator ();
              if (!$it->valid ())
                $values = [];
              else $values = iterator_to_array ($it);
            }
            if (!is_array ($values))
              throw new ComponentException($this,
                "Value of multiple selection component must be an array; " . gettype ($values) .
                " was given, with value: " . print_r ($values, true));
          }
          $first = true;
          do {
            $v = $dataIter->current ();
//            $label = $this->evalBinding ('{' . $attr->labelField . '}');
//            $value = strval ($this->evalBinding ('{' . $attr->valueField . '}'));
            $label = $v[$attr->labelField];
            $value = $v[$attr->valueField];
            if ($first && !$attr->emptySelection && $attr->autoselectFirst &&
                !exists ($selValue)
            )
              $attr->value = $selValue = $value;
            if (!strlen ($label))
              $label = $attr->emptyLabel;

            if ($isMultiple) {
              $sel = array_search ($value, $values) !== false ? ' selected' : '';
            }
            else {
              $eq = $attr->strict ? $value === $selValue : $value == $selValue;
              if ($eq)
                $this->selectedLabel = $label;
              $sel = $eq ? ' selected' : '';
            }
            echo "<option value=\"$value\"$sel>$label</option>";
            $dataIter->next ();
            $first = false;
          } while ($dataIter->valid ());
        }
      }
    }
  }

}

