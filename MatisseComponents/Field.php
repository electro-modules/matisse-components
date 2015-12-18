<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\Base\VisualComponentAttributes;
use Selenia\Matisse\Attributes\DSL\type;
use Selenia\Matisse\Components\Base\Component;
use Selenia\Matisse\Components\Base\VisualComponent;
use Selenia\Matisse\Components\Internal\Parameter;
use Selenia\Matisse\Exceptions\ComponentException;

class FieldAttributes extends VisualComponentAttributes
{
  /**
   * Bootstrap form field grouo addon
   * @var string
   */
  public $append = type::parameter;
  /**
   * @var Parameter|null
   */
  public $field = type::parameter;
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
  public $name = type::id;
  /**
   * Bootstrap form field grouo addon
   * @var Parameter|null
   */
  public $prepend = type::parameter;
  /**
   * @var string
   */
  public $width = 'col-sm-10';
}

class Field extends VisualComponent
{
  public $allowsChildren = true;

  public $cssClassName = 'form-group';

  /**
   * Returns the component's attributes.
   * @return FieldAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return FieldAttributes
   */
  public function newAttributes ()
  {
    return new FieldAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $inputFlds = $this->getChildren ();
    if (empty ($inputFlds))
      throw new ComponentException($this, "<b>field</b> parameter must define <b>one or more</b> component instances.",
        true);

    $name = $attr->get ('name'); // Name is NOT required.

    // Treat the first child component specially

    /** @var Component $input */
    $input   = $inputFlds[0];
    $append  = $this->getChildren ('append');
    $prepend = $this->getChildren ('prepend');

    $fldId = $input->attrs ()->get ('id', $name);

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
      switch ($input->attrs ()->type) {
        case 'date':
        case 'datetime':
          $btn       = self::create ($this->context, $this, 'Button', [
            'class'    => 'btn btn-default',
            'icon'     => 'glyphicon glyphicon-calendar',
            'script'   => "$('#{$name}0').data('DateTimePicker').show()",
            'tabIndex' => -1,
          ]);
          $btn->page = $this->page;
          $append    = [$btn];
//          $append = [Literal::from ($this->context, '<i class="glyphicon glyphicon-calendar"></i>')];
      }

    $this->beginContent ();

    // Output a LABEL

    $label = $attr->label;
    if (!empty($label))
      $this->tag ('label', [
        'class'   => 'control-label ' . $attr->labelWidth,
        'for'     => $forId,
        'onclick' => $click,
      ], $label);

    // Output child components

    $this->begin ('div', [
      'class' => enum (' ', when ($append || $prepend, 'input-group'), $attr->width),
    ]);
    $this->beginContent ();

    if ($prepend) $this->renderAddOn ($prepend[0]);

    foreach ($inputFlds as $i => $input) {

      // EMBEDDED COMPONENTS

      if ($input instanceof VisualComponent) {
        $input->addClass ('form-control');
        if ($fldId)
          $input->attrsObj->id = "$fldId$i";
        if ($name && $input->attrs ()->defines ('name'))
          $input->attrsObj->name = $name;
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
      case 'Radiobutton':
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

