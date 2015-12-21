<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\type;

class LabelProperties extends HtmlComponentProperties
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

class Label extends HtmlComponent
{

  /** overriden */
  protected $containerTag = 'label';

  /**
   * Returns the component's attributes.
   * @return LabelProperties
   */
  public function props ()
  {
    return $this->props;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return LabelProperties
   */
  public function newProperties ()
  {
    return new LabelProperties($this);
  }

  protected function render ()
  {
    $attr = $this->props ();

    $this->attr ('for', $attr->for);
    $this->setContent ($attr->text ? $attr->text : '&nbsp;');
  }
}
