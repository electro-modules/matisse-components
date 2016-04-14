<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class TabProperties extends HtmlComponentProperties
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

class Tab extends HtmlComponent
{
  const propertiesClass = TabProperties::class;

  /**
   * The id of the containing Tabs component, if any.
   *
   * @var string
   */
  public $container_id;
  /** @var TabProperties */
  public $props;

  protected function render ()
  {
    $prop = $this->props;

    $this->begin ('div');
    $this->attr ('class',
      enum (' ', $prop->disabled ? 'disabled' : '', $prop->selected ? 'selected' : ''
      ));

    $this->begin ('input');
    $this->attr ('type', 'radio');
    $this->attr ('name', $prop->name);
    $this->attr ('value', $prop->value);
    if (!isset($prop->id))
      $prop->id = 'tab' . $this->getUniqueId ();
    $this->attr ('id', "{$this->props()->id}Field");
    $this->attrIf ($prop->disabled, 'disabled', 'disabled');
    $this->attrIf ($prop->selected, 'checked', 'checked');
    $this->end ();

    $this->begin ('label');
    $this->attr ('for', "{$this->props()->id}Field");
    $this->attr ('hidefocus', '1');
    $this->attr ('onclick', 'Tab_change(this' . (isset($this->container_id) ? ",'$this->container_id'" : '') .
                            (isset($prop->url) ? ",'{$this->props()->url}')" : ')'));

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
    $this->setContent ($prop->label);
    $this->end ();

    $this->end ();

    $this->end ();
  }

}

