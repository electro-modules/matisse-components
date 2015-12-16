<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\AttributeType;
use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\VisualComponent;

class LabelAttributes extends VisualComponentAttributes
{
  public $text;
  public $for;

  protected function typeof_text () { return AttributeType::TEXT; }

  protected function typeof_for () { return AttributeType::TEXT; }
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
    $this->addAttribute ('for', $this->attrs ()->for);
    $this->setContent ($this->attrs ()->text ? $this->attrs ()->text : '&nbsp;');
  }
}
