<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\Component;
use Selene\Matisse\VisualComponentAttributes;
use Selene\Matisse\Exceptions\ComponentException;
use Selene\Matisse\VisualComponent;

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

  protected function typeof_name () { return AttributeType::ID; }

  protected function typeof_value () { return AttributeType::TEXT; }

  protected function typeof_values () { return AttributeType::DATA; }

  protected function typeof_valueField () { return AttributeType::ID; }

  protected function typeof_labelField () { return AttributeType::ID; }

  protected function typeof_listItem () { return AttributeType::SRC; }

  protected function typeof_data () { return AttributeType::DATA; }

  protected function typeof_autofocus () { return AttributeType::BOOL; }

  protected function typeof_emptySelection () { return AttributeType::BOOL; }

  protected function typeof_emptyLabel () { return AttributeType::TEXT; }

  protected function typeof_autoselectFirst () { return AttributeType::BOOL; }

  protected function typeof_loadLinkedOnInit () { return AttributeType::BOOL; }

  protected function typeof_onChange () { return AttributeType::TEXT; }

  protected function typeof_sourceUrl () { return AttributeType::TEXT; }

  protected function typeof_linkedSelector () { return AttributeType::ID; }

  protected function typeof_autoOpenLinked () { return AttributeType::BOOL; }

  protected function typeof_multiple () { return AttributeType::BOOL; }
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
    $isMultiple = $this->attrs ()->multiple;
    $this->addAttribute ('name', $this->attrs ()->name);
    $this->addAttributeIf ($isMultiple, 'multiple', '');
    $this->addAttributeIf ($this->attrs ()->onChange, 'onchange', $this->attrs ()->onChange);
    $this->beginContent ();
    if ($this->attrs ()->emptySelection) {
      $sel = exists ($this->attrs ()->value) ? '' : ' selected';
      echo '<option value=""' . $sel . '>' . $this->attrs ()->emptyLabel . '</option>';
    }
    $this->defaultDataSource = $this->attrs ()->get ('data');
    if (isset($this->defaultDataSource)) {
      $dataIter = $this->defaultDataSource->getIterator ();
      $dataIter->rewind ();
      if ($dataIter->valid ()) {
        $template = $this->attrs ()->get ('list_item');
        if (isset($template)) {
          do {
            $template->value = $this->evalBinding ('{' . $this->attrs ()->valueField . '}');
            Component::renderSet ($template);
            $dataIter->next ();
          } while ($dataIter->valid ());
        }
        else {
          $selValue = strval ($this->attrs ()->get ('value'));
          if ($isMultiple) {
            $values = $this->attrs ()->values;
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
            $label = $this->evalBinding ('{' . $this->attrs ()->labelField . '}');
            $value = strval ($this->evalBinding ('{' . $this->attrs ()->valueField . '}'));
            if ($first && !$this->attrs ()->emptySelection && $this->attrs ()->autoselectFirst &&
                !exists ($selValue)
            )
              $this->attrs ()->value = $selValue = $value;
            if (!strlen ($label))
              $label = $this->attrs ()->emptyLabel;

            if ($isMultiple) {
              $sel = array_search ($value, $values) !== false ? ' selected' : '';
            }
            else {
              if ($value === $selValue)
                $this->selectedLabel = $label;
              $sel = $value === $selValue ? ' selected' : '';
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

