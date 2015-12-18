<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Type;
use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\VisualComponent;

class LabelAttributes extends VisualComponentAttributes
{
  public $text;
  public $for;

  protected function typeof_text () { return Type::TEXT; }

  protected function typeof_for () { return Type::TEXT; }
}

class Label extends VisualComponent
{

  /** overriden */
  protected $containerTag = 'label';

  /**
   * Returns the component's attributes.
   * @return LabelAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return LabelAttributes
   */
  public function newAttributes ()
  {
    return new LabelAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $this->attr ('for', $attr->for);
    $this->setContent ($attr->text ? $attr->text : '&nbsp;');
  }
}
