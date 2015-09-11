<?php
namespace Selenia\Plugins\MatisseWidgets;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\AttributeType;
use Selenia\Matisse\Component;
use Selenia\Matisse\Components\Literal;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\VisualComponent;

class FieldAttributes extends VisualComponentAttributes
{
  public $name;
  public $label;
  public $field;
  public $labelWidth = 'col-sm-2';
  public $width      = 'col-sm-10';
  /**
   * Bootstrap form field grouo addon
   * @var string
   */
  public $prepend;
  /**
   * Bootstrap form field grouo addon
   * @var string
   */
  public $append;

  protected function typeof_name () { return AttributeType::ID; }

  protected function typeof_label () { return AttributeType::TEXT; }

  protected function typeof_field () { return AttributeType::SRC; }

  protected function typeof_width () { return AttributeType::TEXT; }

  protected function typeof_labelWidth () { return AttributeType::TEXT; }

  protected function typeof_prepend () { return AttributeType::SRC; }

  protected function typeof_append () { return AttributeType::SRC; }
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
    $inputFlds = $this->children;
    if (empty ($inputFlds))
      throw new ComponentException($this, "<b>field</b> parameter must define <b>one or more</b> component instances.",
        true);

    $name = $this->attrs ()->get ('name'); // Name is NOT required.

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
//          $btn       = self::create ($this->context, 'button', ['class' => 'btn btn-default', 'icon' => 'glyphicon glyphicon-calendar']);
//          $btn->page = $this->page;
//          $append = [$btn];
          $append = [Literal::from ($this->context, '<i class="glyphicon glyphicon-calendar"></i>')];
      }

    $this->beginContent ();

    // Output a LABEL

    $label = $this->attrs ()->label;
    if (!empty($label))
      $this->addTag ('label', [
        'class'   => 'control-label ' . $this->attrs ()->labelWidth,
        'for'     => $forId,
        'onclick' => $click
      ], $label);

    // Output child components

    $this->beginTag ('div', [
      'class' => enum (' ', when ($append || $prepend, 'input-group'), $this->attrs ()->width)
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
      $input->doRender ();
    }

    if ($append) $this->renderAddOn ($append[0]);

    $this->endTag ();
  }

  private function renderAddOn (Component $addOn)
  {
    switch ($addOn->getTagName ()) {
      case 'Literal':
      case 'Checkbox':
      case 'Radiobutton':
        echo '<span class="input-group-addon">';
        $addOn->doRender ();
        echo '</span>';
        break;
      case 'Button':
        echo '<span class="input-group-btn">';
        $addOn->doRender ();
        echo '</span>';
        break;
    }

  }
}

