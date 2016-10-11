<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Plugins\Matisse\Components\Base\Component;
use Electro\Plugins\Matisse\Components\Base\HtmlComponent;
use Electro\Plugins\Matisse\Exceptions\ComponentException;
use Electro\Plugins\Matisse\Properties\Base\HtmlComponentProperties;
use Electro\Plugins\Matisse\Properties\TypeSystem\type;
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
   * @var bool
   */
  public $autoselectFirst = false;
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
   * > **Note:** use `'$value'` on the URL to bind to the value of the master select.
   * ><p>Ex: `'cities/$value/info'` or `'cities/$value'`
   * ><p>Note: the `$` is used instead of `{}` to prevent conflicts with Matisse databindings.
   *
   * @var string
   */
  public $linkedUrl = '';
  /**
   * A template for rendering each list item. In the template, {value} and {label} expressions will evaluate to
   * the current value's value and label, as set by `labelField` and `valueField`.
   * <p>Note: this is implemented ONLY for server-side rendering.
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
selenia.ext.select = {
  props: {}
};

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
    $props    = $this->props;
    $assets   = $this->context->getAssetsService ();
    inspect($props->emptySelection);
    $autoOpen = boolToStr ($props->autoOpenLinked);
    $emptySel = boolToStr ($props->emptySelection);

    $assets->addInlineScript ("
selenia.ext.select.props['$props->id']={
  autoOpenLinked: $autoOpen,
  emptyLabel: '$props->emptyLabel',
  emptySelection: $emptySel,
  labelField: '$props->labelField',
  linkedSelector: '$props->linkedSelector',
  linkedUrl: '$props->linkedUrl',
  noResultsText: '$props->noResultsText',
  valueField: '$props->valueField',
};
");
    // Non-native selects are implemented with the Chosen javascript widget.
    if (!$props->native) {
      $this->addClass ('chosen-select');
      // Add this just once for all Chosens:
      $assets->addInlineScript (<<<JS
$(function() { //defer
  $ ('.chosen-select').each (function () {
    var id = $(this).prop('id');
    var props = selenia.ext.select.props[id]
      , master = $(this);
    master.chosen ({
      placeholder_text: props.emptyLabel,
      no_results_text: props.noResultsText
    });
    if (props.linkedSelector) {
      var slave = $('#' + props.linkedSelector);
      if (!slave.length)
        console.error ('linkedSelector \"' + props.linkedSelector + '\" was not found');
      master.change(function () {
        var v = master.val ();
        if (v !== '') {
          var url = props.linkedUrl.replace (/\\\$value/, v);
          $.get (url).then (function (data) {
            selenia.ext.select.loadData.call (slave, data);
            if (props.autoOpenLinked)
              $('#' + props.linkedSelector.replace(/\\-/, '_') + '_chosen').trigger('mousedown');
          });
        }
        else {
          slave.empty ();
          slave.trigger('chosen:updated');
        }
      });
    }
  });
  $ ('.chosen-container').add ('.search-field input').css ('width','');
});

selenia.ext.select.loadData = function (data) {
  var self = this
    , current = this.val ()
    , props = selenia.ext.select.props[this.prop('id')];
  this.empty ();
  if (props.emptySelection)
    this.append ('<option value=\"\">' + props.emptyLabel + '</option>');
  if (data.forEach)
    data.forEach (function (v) {
      self.append ('<option value=\"' + v[props.valueField] + '\"' 
      + (v == current ? ' selected' : '') 
      + '>' + v[props.labelField] + '</option>'); 
    });
  if (current)
    this.val (current);
  this.trigger('chosen:updated');
}
JS
        , 'init-select2');
    }
    // If required, add auto-add tag behavior to this Chosen.
    if ($props->autoTag && $props->multiple)
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
      ");
    // AJAX-powered dropdown
    if ($props->dataUrl) {
      $assets->addInlineScript ("
$.get ('$props->dataUrl').then(selenia.ext.select.loadData.bind($('#$props->id')));
");
    }
    if ($props->linkedSelector) {
      $assets->addInlineScript ("
      ", 'init-select3');
    }
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
            if ($first && !$prop->emptySelection && $prop->autoselectFirst &&
                !exists ($selValue)
            ) $prop->value = $selValue = $value;

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

