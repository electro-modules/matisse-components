<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\Component;
use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Components\Internal\Metadata;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class FieldProperties extends HtmlComponentProperties
{
  /**
   * Bootstrap form field grouo addon
   *
   * @var string
   */
  public $append = type::content;
  /**
   * Overrides inherited `class` prop. with a default value.
   *
   * @var string
   */
  public $class = 'form-group';
  /**
   * @var string
   */
  public $controlClass = 'form-control';
  /**
   * @var Metadata|null
   */
  public $field = type::content;
  /**
   * @var string
   */
  public $groupClass = '';
  /**
   * @var string
   */
  public $label = ''; //allow 'field[]'
  /**
   * @var string
   */
  public $labelClass = '';
  /**
   * @var array
   */
  public $languages = type::data;
  /**
   * @var bool Multilanguage?
   */
  public $multilang = false;
  /**
   * @var string
   */
  public $name = '';
  /**
   * Bootstrap form field grouo addon
   *
   * @var Metadata|null
   */
  public $prepend = type::content;

}

/**
 * Wraps one or more form field components with HTML to create a formatted form field.
 *
 * <p>This is Bootstrap-compatible. It comes pre-configured to generated markup for a vertical form field layout.
 * <p>It is also compatible with other CSS frameworks, as long as it is properly configured.
 *
 * <p>This component also supports generating multi-language fields, where multiple inputs are generated for each field,
 * one for each language; only one of them is visible at one time.
 *
 * <p>Field has support for generating fields with add-ons. An add-on can be an icon, button, checkbox, etc, and it can
 * be left or right aligned.
 */
class Field extends HtmlComponent
{
  protected static $propertiesClass = FieldProperties::class;

  public $allowsChildren = true;
  /** @var FieldProperties */
  public $props;

  protected function render ()
  {
    $prop = $this->props;

    $inputFlds = $this->getChildren ();
    if (empty ($inputFlds))
      throw new ComponentException($this, "<b>field</b> parameter must define <b>one or more</b> component instances.",
        true);

    $name = $prop->get ('name'); // Name is NOT required.

    // Treat the first child component specially

    /** @var Component $input */
    $input   = $inputFlds[0];
    $append  = $this->getChildren ('append');
    $prepend = $this->getChildren ('prepend');

    $fldId = $input->props->get ('id', $name);

    if ($fldId) {
      foreach ($inputFlds as $counter => $c)
        if (!$c->getComputedPropValue ('hidden')) break;

      // Special case for the HtmlEditor component.

      if ($input->className == 'HtmlEditor') {
        $forId = $fldId . "-{$counter}_field";
        $click = "$('#{$fldId}-{$counter} .redactor_editor').focus()";
      }

      // All other component types with an ID set.
      else {
        $forId = $fldId . "-$counter";
        $click = null;
      }
    }
    else $forId = $click = null;

    if ($input->className == 'Input')
      switch ($input->props->type) {
        case 'date':
        case 'time':
        case 'datetime':
          $btn    = Button::create ($this, [
            'class'    => 'btn btn-default',
            'icon'     => 'glyphicon glyphicon-calendar',
            'script'   => "$('#{$name}0').data('DateTimePicker').show()",
            'tabIndex' => -1,
          ]);
          $append = [$btn];
//          $append = [Literal::from ($this->context, '<i class="glyphicon glyphicon-calendar"></i>')];
      }

    $this->beginContent ();

    // Output a LABEL

    $label = $prop->label;
    if (!empty($label))
      $this->tag ('label', [
        'class'   => $prop->labelClass,
        'for'     => $forId,
        'onclick' => $click,
      ], $label);

    // Output child components

    $hasGroup = $append || $prepend || $prop->groupClass;
    if ($hasGroup)
      $this->begin ('div', [
        'class' => enum (' ', when ($append || $prepend, 'input-group'), $prop->groupClass),
      ]);
    $this->beginContent ();

    if ($prepend) $this->renderAddOn ($prepend[0]);

    if ($prop->multilang)
      foreach ($inputFlds as $i => $input)
        foreach ($prop->languages as $lang)
          $this->outputField ($input, $i, $fldId, $name, $lang);
    else
      foreach ($inputFlds as $i => $input)
        $this->outputField ($input, $i, $fldId, $name);

    if ($append) $this->renderAddOn ($append[0]);

    if ($hasGroup)
      $this->end ();
  }

  private function outputField ($input, $i, $id, $name, $lang = '')
  {
    $_lang = $lang ? "_$lang" : '';
    /** @var InputProperties $prop */
    $prop = $input->props;

    // EMBEDDED COMPONENTS

    if ($input instanceof HtmlComponent) {
      /** @var HtmlComponent $input */

      // Special case for the Input[type=color] component.
      if (!($input instanceof Input && $prop->type == 'color'))
        $input->addClass ($this->props->controlClass);

      if ($id)
        $prop->id = "$id-$i";
      if ($name && $prop->defines ('name'))
        $prop->name = "$name$_lang";
      if (!$i)
        $input->originalCssClassName = $input->cssClassName;
      if ($lang)
        $input->htmlAttrs['lang'] = $lang;
    }
    $input->run ();
  }

  private function renderAddOn (Component $addOn)
  {
    switch ($addOn->getTagName ()) {
      case 'Text':
      case 'Literal':
      case 'Checkbox':
      case 'RadioButton':
        echo '<span class="input-group-addon">';
        $addOn->run ();
        echo '</span>';
        break;
      case 'Button':
        echo '<span class="input-group-btn">';
        $addOn->run ();
        echo '</span>';
        break;
    }

  }
}

