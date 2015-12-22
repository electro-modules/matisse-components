<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\Component;
use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class SelectProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $autoOpenLinked = false;
  /**
   * @var bool
   */
  public $autofocus = false;
  /**
   * @var bool
   */
  public $autoselectFirst = false;
  /**
   * @var string
   */
  public $data = type::data;
  /**
   * @var string
   */
  public $emptyLabel = '';
  /**
   * @var bool
   */
  public $emptySelection = false;
  /**
   * @var string
   */
  public $labelField = '1';
  /**
   * @var string
   */
  public $linkedSelector = type::id;
  /**
   * @var string
   */
  public $list_item = type::content;
  /**
   * @var bool
   */
  public $loadLinkedOnInit = true;
  /**
   * @var bool
   */
  public $multiple = false;
  /**
   * @var string
   */
  public $name = type::id;
  /**
   * @var string
   */
  public $onChange = '';
  /**
   * > **Note:** use $name in the URL to bind to the value of the $name field, otherwhise the linked value will be
   * appended
   * @var string
   */
  public $sourceUrl = '';
  /**
   * @var bool
   */
  public $strict = false;
  /**
   * @var string
   */
  public $value = '';
  /**
   * @var string
   */
  public $valueField = '0';
  /**
   * @var string
   */
  public $values = type::data;
}

class Select extends HtmlComponent
{
  protected static $propertiesClass = SelectProperties::class;

  protected $autoId = true;

  protected $containerTag = 'select';
  private   $selectedLabel;

  /**
   * Returns the component's attributes.
   * @return SelectProperties
   */
  public function props ()
  {
    return $this->props;
  }

  protected function render ()
  {
    $attr       = $this->props ();
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

