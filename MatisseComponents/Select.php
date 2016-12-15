<?php
namespace Electro\Plugins\MatisseComponents;

use Matisse\Components\Base\Component;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Exceptions\ComponentException;
use Matisse\Lib\JavascriptCodeGen;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\type;
use PhpKit\Flow\Flow;

class SelectProperties extends HtmlComponentProperties
{
  /**
   * @var bool When true, selecting a value will focus and open the linked Select.
   */
  public $autoOpenLinked = false;
  /**
   * @var bool When true, pressing Enter will create a new option on a multi-select.
   */
  public $autoTag = false;
  /**
   * @var bool
   */
  public $autofocus = false;
  /**
   * @var string
   */
  public $data = type::data;
  /**
   * @var string An URL to get JSON data from, with which to populate the dropdown at runtime.
   */
  public $dataUrl = '';
  /**
   * @var string
   */
  public $emptyLabel = '--- select ---';
  /**
   * Adds an item to the list to clear the selection.
   * ><p>**Note:** this is only applicable to single-selection Selects.
   *
   * @var bool
   */
  public $emptySelection = false;
  /**
   * @var string
   */
  public $labelField = 'name';
  /**
   * @var string ID of the cascaded Select component.
   */
  public $linkedSelector = type::id;
  /**
   * The URL to be loaded on the linked Select.
   * ><p>**Note:** use `'@`value`'` on the URL to bind to the value of the master select.
   * ><p>**Ex:** `'cities/@`value/info`'` or `'cities/@`value`'`
   *
   * @var string
   */
  public $linkedUrl = '';
  /**
   * A template for rendering each list item. In the template, {value} and {label} expressions will evaluate to
   * the current value's value and label, as set by `labelField` and `valueField`.
   * ><p>**Note:** this is implemented ONLY for server-side rendering.
   *
   * @var array
   */
  public $listItemTemplate = type::content;
  /**
   * @var bool
   */
  public $multiple = false; //allow 'field[]'
  /**
   * @var string
   */
  public $name = '';
  /**
   * @var bool When true, the native HTML Select element is used instead of the javascript widget.
   */
  public $native = false;
  /**
   * @var string
   */
  public $noResultsText = '';
  /**
   * @var string
   */
  public $onChange = '';
  /**
   * When set, selecting a value on the dropdown navigates the app to the given URL.
   * <p>The URL should define a placeholder for the selected value using the `@`value keyword.
   * ><p>Ex: `'products/@`value`'`
   *
   * @var string
   */
  public $onSelectNavigate = '';
  /**
   * <p>Note: this is implemented ONLY for server-side rendering.
   *
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
  public $valueField = 'id';
}

class Select extends HtmlComponent
{
  const PUBLIC_URI      = 'modules/electro-modules/matisse-components';
  const propertiesClass = SelectProperties::class;

  /** @var SelectProperties */
  public $props;

  protected $autoId       = true;
  protected $containerTag = 'select';

  private $selectedLabel;

  protected function init ()
  {
    parent::init ();
    $this->context
      ->getAssetsService ()
      ->addStylesheet ('lib/chosen/chosen.min.css', true)
      ->addScript ('lib/chosen/chosen.jquery.min.js')
      ->addScript (self::PUBLIC_URI . '/js/select.js');
  }

  protected function preRender ()
  {
    $props  = $this->props;
    $assets = $this->context->getAssetsService ();

    // Non-native selects are implemented with the Chosen javascript widget.
    if (!$props->native)
      $this->addClass ('chosen-select');

    parent::preRender ();
  }

  protected function render ()
  {
    $prop       = $this->props;
    $isMultiple = $prop->multiple;
    $assets     = $this->context->getAssetsService ();

    $assets->addInlineScript (
      "selenia.ext.select.props['$prop->id']=" . JavascriptCodeGen::makeOptions ([
        'autoOpenLinked'   => $prop->autoOpenLinked,
        'dataUrl'          => $prop->dataUrl,
        'emptyLabel'       => $prop->emptyLabel,
        'emptySelection'   => $prop->emptySelection,
        'id'               => $prop->id, // for debugging
        'labelField'       => $prop->labelField,
        'linkedSelector'   => $prop->linkedSelector,
        'linkedUrl'        => $prop->linkedUrl,
        'multiple'         => $prop->multiple,
        'noResultsText'    => $prop->noResultsText,
        'valueField'       => $prop->valueField,
        'value'            => $prop->value,
        'onSelectNavigate' => $prop->onSelectNavigate,
      ])
    );

    // If required, add auto-add tag behavior to this Chosen.
    if ($prop->autoTag && $prop->multiple)
      $assets->addInlineScript ("
$(function () {
  $ ('#$prop->id+.chosen-container .chosen-choices input').on ('keyup', function (ev) { console.log(ev);
    var v = $ (this).val ();
    if (ev.keyCode == 13 && v) {
      var tags  = $ ('#$prop->id option').map (function (i, e) { return $ (e).val () });
      var found = false, l = v.length;
      tags.each (function (i, x) {
        if (x.substr (0, l) == v) {
          found = true;
          return false
        }
      });
      if (found) return;
      $ ('#$prop->id').append (\"<option>\" + v + \"</option>\");
      $ ('#$prop->id').trigger ('chosen:updated');
      ev.preventDefault ();
      var e     = jQuery.Event (\"keyup\");
      e.which   = 13;
      $ ('#$prop->id+.chosen-container .chosen-choices input').val (v).trigger ('keyup').trigger (e);
    }
  })
});
");

    $this->attr ('name', $prop->multiple ? "$prop->name[]" : $prop->name);
    $this->attrIf ($isMultiple, 'multiple', '');
    $this->attrIf ($prop->onChange, 'onchange', $prop->onChange);
    $this->beginContent ();
    if ($prop->emptySelection && !$prop->multiple) {
      $sel = exists ($prop->value) ? '' : ' selected';
      echo '<option value=""' . $sel . '>' . $prop->emptyLabel . '</option>';
    }
    $viewModel = $prop->get ('data');
    if (isset($viewModel)) {
      /** @var \Iterator $dataIter */
      $dataIter = iteratorOf ($viewModel);
      $dataIter->rewind ();
      if ($dataIter->valid ()) {

        $values = $selValue = null;

        // SETUP MULTI-SELECT

        if ($isMultiple) {
          if (isset($prop->value) && !is_iterable ($prop->value))
            throw new ComponentException($this, sprintf (
              "Value of multiple selection component must be iterable or null; %s was given.",
              typeOf ($prop->value)));

          $it = Flow::from ($prop->value);
          $it->rewind ();
          $values = $it->valid () && is_scalar ($it->current ())
            ? $it->all ()
            : $it->map (pluck ($prop->valueField))->all ();
        }

        // SETUP STANDARD SELECT

        else $selValue = strval ($prop->get ('value'));

        // NOW RENDER IT

        $template = $this->getChildren ('listItemTemplate');
        $first    = true;
        do {
          $v     = $dataIter->current ();
          $value = getField ($v, $prop->valueField);
          $label = getField ($v, $prop->labelField);
          if (!strlen ($label))
            $label = $prop->emptyLabel;

          if ($isMultiple) {
            $sel = array_search ($value, $values) !== false ? ' selected' : '';
          }
          else {
            if ($first && !$prop->emptySelection && !$prop->multiple && !exists ($selValue))
              $prop->value = $selValue = $value;

            $eq = $prop->strict ? $value === $selValue : $value == $selValue;
            if ($eq)
              $this->selectedLabel = $label;
            $sel = $eq ? ' selected' : '';
          }

          if ($template) {
            // Render templated list

            $viewModel['value'] = $value;
            $viewModel['label'] = $label;
            Component::renderSet ($template);
          }
          else // Render standard list

            echo "<option value=\"$value\"$sel>$label</option>";

          $dataIter->next ();
          $first = false;
        } while ($dataIter->valid ());
      }

    }
  }

}

