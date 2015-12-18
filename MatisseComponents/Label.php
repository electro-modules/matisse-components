<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\Base\VisualComponentAttributes;
use Selenia\Matisse\Attributes\DSL\type;
use Selenia\Matisse\Components\Base\VisualComponent;

class LabelAttributes extends VisualComponentAttributes
{
  /**
   * @var string
   */
  public $for = type::id;
  /**
   * @var string
   */
  public $text = '';
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
