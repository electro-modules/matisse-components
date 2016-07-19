<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Plugins\Matisse\Components\Base\Component;
use Electro\Plugins\Matisse\Components\Base\HtmlComponent;
use Electro\Plugins\Matisse\Exceptions\ComponentException;
use Electro\Plugins\Matisse\Properties\Base\HtmlComponentProperties;
use Electro\Plugins\Matisse\Properties\TypeSystem\type;

class SelectProperties extends HtmlComponentProperties
{
  /**
   * @var bool
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
  public $labelField = 'name';
  /**
   * @var string
   */
  public $linkedSelector = type::id;
  /**
   * A template for rendering each list item. In the template, {value} and {label} expressions will evaluate to
   * the current value's value and label, as set by `labelField` and `valueField`.
   *
   * @var array
   */
  public $listItemTemplate = type::content;
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
  public $noResultsText = '';
  /**
   * @var string
   */
  public $onChange = '';
  /**
   * > **Note:** use $name in the URL to bind to the value of the $name field, otherwise the linked value will be
   * appended.
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
  public $valueField = 'id';
}

class Select extends HtmlComponent
{
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
      // Add drop-up behavior to Chosen
      ->addInlineScript ("
$ ('.chosen-select').on('chosen:showing_dropdown', function(event, params) {
  var chosen_container = $( event.target ).next( '.chosen-container' );
  var dropdown = chosen_container.find( '.chosen-drop' );
  var dropdown_top = dropdown.offset().top - $(window).scrollTop();
  var dropdown_height = dropdown.height();
  var viewport_height = $(window).height();
  if ( dropdown_top + dropdown_height > viewport_height )
    chosen_container.addClass( 'chosen-drop-up' );
}).on('chosen:hiding_dropdown', function(event, params) {
  $( event.target ).next( '.chosen-container' ).removeClass( 'chosen-drop-up' );
});
", 'init-select');
  }

  protected function preRender ()
  {
    $props = $this->props;
    $assets = $this->context->getAssetsService ();
    if (!$props->native) {
      $this->addClass ('chosen-select');
      $assets->addInlineScript ("
$ ('.chosen-select').chosen ({
  placeholder_text: '{$props->emptyLabel}',
  no_results_text: '{$props->noResultsText}'
});
$ ('.chosen-container').add('.search-field input').css ('width','');
", 'init-select2');
    }
    if ($props->autoTag && $props->multiple)
      // Add auto-add tag behavior to Chosen
      $assets->addInlineScript ("
$ ('#$props->id+.chosen-container .chosen-choices input').on ('keyup', function (ev) {
  var v = $ (this).val ();
  if (ev.keyCode == 13 && v) {
    var tags  = $ ('#$props->id option').map (function (i, e) { return $ (e).val () });
    var found = false, l = v.length;
    tags.each (function (i, x) {
      if (x.substr (0, l) == v) {
        found = true;
        return false
      }
    });
    if (found) return;
    $ ('#$props->id').append (\"<option>\" + v + \"</option>\");
    $ ('#$props->id').trigger ('chosen:updated');
    ev.preventDefault ();
    var e     = jQuery.Event (\"keyup\");
    e.which   = 13;
    $ ('#$props->id+.chosen-container .chosen-choices input').val (v).trigger ('keyup').trigger (e);
  }
});
      ", 'init-select3');
    parent::preRender ();
  }

  protected function render ()
  {
    $prop       = $this->props;
    $isMultiple = $prop->multiple;

    $this->attr ('name', $prop->multiple ? "$prop->name[]" : $prop->name);
    $this->attrIf ($isMultiple, 'multiple', '');
    $this->attrIf ($prop->onChange, 'onchange', $prop->onChange);
    $this->beginContent ();
    if ($prop->emptySelection) {
      $sel = exists ($prop->value) ? '' : ' selected';
      echo '<option value=""' . $sel . '>' . $prop->emptyLabel . '</option>';
    }
    $viewModel = $prop->get ('data');
    if (isset($viewModel)) {
      /** @var \Iterator $dataIter */
      $dataIter = iteratorOf ($viewModel);
      $dataIter->rewind ();
      if ($dataIter->valid ()) {
        $template = $this->getChildren ('listItemTemplate');
        if ($template) {
          do {
            $v                  = $dataIter->current ();
            $viewModel['value'] = getField ($v, $prop->valueField);
            $viewModel['label'] = getField ($v, $prop->labelField);
            Component::renderSet ($template);
            $dataIter->next ();
          } while ($dataIter->valid ());
        }
        else {
          if ($isMultiple) {
            $selValue = $prop->get ('value');
            $values   = $prop->value ?: [];
            if (method_exists ($values, 'getIterator')) {
              /** @var \Iterator $it */
              $it = $values->getIterator ();
              if (!$it->valid ())
                $values = [];
              else {
                // If the values are objects or arrays, extract the value field from them.
                if (!is_scalar($it->current())) {
                  $vf = $prop->valueField;
                  $values = map ($it, function ($v) use ($vf) { return getField ($v, $vf);});
                }
                // Otherwise, the values are assumed to be IDs.
                else $values = iterator_to_array ($it);
              }
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
            $label = getField ($v, $prop->labelField);
            $value = getField ($v, $prop->valueField);
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

