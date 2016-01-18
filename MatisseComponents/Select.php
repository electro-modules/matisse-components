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
  public $emptyLabel = '--- select ---';
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
  public $name = ''; //allow 'field[]'
  /**
   * @var bool When true, the native HTML Select element is used instead of the javascript widget.
   */
  public $native = false;
  /**
   * @var string
   */
  public $onChange = '';
  /**
   * > **Note:** use $name in the URL to bind to the value of the $name field, otherwhise the linked value will be
   * appended
   *
   * @var string
   */
  public $sourceUrl = '';
  /**
   * @var bool
   */
  public $strict = false;
  /**
   * @var mixed
   */
  public $value = type::any;
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

  /** @var SelectProperties */
  public $props;

  protected $autoId       = true;
  protected $containerTag = 'select';

  private $selectedLabel;

  protected function preRender ()
  {
    if (!$this->props->native) {
      $emptyLabel = $this->props->emptyLabel;
      $this->addClass ('chosen-select');
      $this->context->addStylesheet ('lib/chosen/chosen.min.css');
      $this->context->addScript ('lib/chosen/chosen.jquery.min.js');
      $this->context->addInlineScript ("
$ ('.chosen-select').chosen ({
  placeholder_text: '$emptyLabel'
});
$ ('.chosen-container').css ('width', '');
", 'init-select');
    }
    parent::preRender ();
  }

  protected function render ()
  {
    $prop       = $this->props;
    $isMultiple = $prop->multiple;

    $this->attr ('name', $prop->name);
    $this->attrIf ($isMultiple, 'multiple', '');
    $this->attrIf ($prop->onChange, 'onchange', $prop->onChange);
    $this->beginContent ();
    if ($prop->emptySelection) {
      $sel = exists ($prop->value) ? '' : ' selected';
      echo '<option value=""' . $sel . '>' . $prop->emptyLabel . '</option>';
    }
    $this->viewModel = $prop->get ('data');
    if (isset($this->viewModel)) {
      /** @var \Iterator $dataIter */
      $dataIter = iteratorOf ($this->viewModel);
      $dataIter->rewind ();
      if ($dataIter->valid ()) {
        $template = $prop->get ('list_item');
        if (isset($template)) {
          do {
            $template->value = $this->evalBinding ('{' . $prop->valueField . '}');
            Component::renderSet ($template);
            $dataIter->next ();
          } while ($dataIter->valid ());
        }
        else {
          if ($isMultiple) {
            $selValue = $prop->get ('value');
            $values   = $prop->values ?: [];
            if (method_exists ($values, 'getIterator')) {
              /** @var \Iterator $it */
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
          else $selValue = strval ($prop->get ('value'));
          $first = true;
          do {
            $v = $dataIter->current ();
//            $label = $this->evalBinding ('{' . $prop->labelField . '}');
//            $value = strval ($this->evalBinding ('{' . $prop->valueField . '}'));
            $label = $v[$prop->labelField];
            $value = $v[$prop->valueField];
            if ($first && !$prop->emptySelection && $prop->autoselectFirst &&
                !exists ($selValue)
            )
              $prop->value = $selValue = $value;
            if (!strlen ($label))
              $label = $prop->emptyLabel;

            if ($isMultiple) {
              $sel = array_search ($value, $values) !== false ? ' selected' : '';
            }
            else {
              $eq = $prop->strict ? $value === $selValue : $value == $selValue;
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

