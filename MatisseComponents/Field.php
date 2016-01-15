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
   * @var Metadata|null
   */
  public $field = type::content;
  /**
   * @var string
   */
  public $label = '';
  /**
   * @var string
   */
  public $labelWidth = 'col-sm-2';
  /**
   * @var string
   */
  public $name = ''; //allow 'field[]'
  /**
   * Bootstrap form field grouo addon
   *
   * @var Metadata|null
   */
  public $prepend = type::content;
  /**
   * @var string
   */
  public $width = 'col-sm-10';
}

class Field extends HtmlComponent
{
  protected static $propertiesClass = FieldProperties::class;

  public $allowsChildren = true;
  public $cssClassName   = 'form-group';
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
      if ($input->className == 'HtmlEditor') {
        $forId = $fldId . '0_field';
        $click = "$('#{$fldId}0 .redactor_editor').focus()";
      }
      else {
        $forId = $fldId . '0';
        $click = null;
      }
    }
    else $forId = $click = null;

    if ($input->className == 'Input')
      switch ($input->props->type) {
        case 'date':
        case 'datetime':
          $btn       = Button::create ($this, [
            'class'    => 'btn btn-default',
            'icon'     => 'glyphicon glyphicon-calendar',
            'script'   => "$('#{$name}0').data('DateTimePicker').show()",
            'tabIndex' => -1,
          ]);
          $append    = [$btn];
//          $append = [Literal::from ($this->context, '<i class="glyphicon glyphicon-calendar"></i>')];
      }

    $this->beginContent ();

    // Output a LABEL

    $label = $prop->label;
    if (!empty($label))
      $this->tag ('label', [
        'class'   => 'control-label ' . $prop->labelWidth,
        'for'     => $forId,
        'onclick' => $click,
      ], $label);

    // Output child components

    $this->begin ('div', [
      'class' => enum (' ', when ($append || $prepend, 'input-group'), $prop->width),
    ]);
    $this->beginContent ();

    if ($prepend) $this->renderAddOn ($prepend[0]);

    foreach ($inputFlds as $i => $input) {

      // EMBEDDED COMPONENTS

      if ($input instanceof HtmlComponent) {
        /** @var HtmlComponent $input */
        $input->addClass ('form-control');
        if ($fldId)
          $input->props->id = "$fldId$i";
        if ($name && $input->props->defines ('name'))
          $input->props->name = $name;
      }
      $input->run ();
    }

    if ($append) $this->renderAddOn ($append[0]);

    $this->end ();
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

