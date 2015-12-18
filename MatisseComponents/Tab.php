<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\Base\VisualComponentAttributes;
use Selenia\Matisse\Attributes\DSL\type;
use Selenia\Matisse\Components\Base\VisualComponent;

class TabAttributes extends VisualComponentAttributes
{
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var string
   */
  public $label = '';
  /**
   * @var string
   */
  public $name = type::id;
  /**
   * @var bool
   */
  public $selected = false;
  /**
   * @var string
   */
  public $url = '';
  /**
   * @var string
   */
  public $value = '';
}

class Tab extends VisualComponent
{
  /**
   * The id of the containing Tabs component, if any.
   * @var string
   */
  public $container_id;

  /**
   * Returns the component's attributes.
   * @return TabAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return TabAttributes
   */
  public function newAttributes ()
  {
    return new TabAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $this->begin ('div');
    $this->attr ('class',
      enum (' ', $attr->disabled ? 'disabled' : '', $attr->selected ? 'selected' : ''
      ));

    $this->begin ('input');
    $this->attr ('type', 'radio');
    $this->attr ('name', $attr->name);
    $this->attr ('value', $attr->value);
    if (!isset($attr->id))
      $attr->id = 'tab' . $this->getUniqueId ();
    $this->attr ('id', "{$this->attrs()->id}Field");
    $this->attrIf ($attr->disabled, 'disabled', 'disabled');
    $this->attrIf ($attr->selected, 'checked', 'checked');
    $this->end ();

    $this->begin ('label');
    $this->attr ('for', "{$this->attrs()->id}Field");
    $this->attr ('hidefocus', '1');
    $this->attr ('onclick', 'Tab_change(this' . (isset($this->container_id) ? ",'$this->container_id'" : '') .
                            (isset($attr->url) ? ",'{$this->attrs()->url}')" : ')'));

    $this->begin ('span');
    $this->attr ('class', 'text');
    $this->attr ('unselectable', 'on');
    /*
        if (isset($this->style()->icon)) {
          $this->beginTag('img');
          switch ($this->style()->icon_align) {
            case NULL:
            case 'left':
              $this->addAttribute('class', 'icon icon_left');
              break;
            case 'right':
              $this->addAttribute('class', 'icon icon_right');
              break;
            default:
              $this->addAttribute('class', 'icon');
              break;
          }
          $this->addAttribute('src', $this->style()->propertyToImageURI('icon'));
          $this->endTag();
        }
    */
    $this->setContent ($attr->label);
    $this->end ();

    $this->end ();

    $this->end ();
  }

}

