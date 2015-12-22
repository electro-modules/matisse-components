<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

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
  protected static $propertiesClass = LabelProperties::class;

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

  protected function render ()
  {
    $attr = $this->props ();

    $this->attr ('for', $attr->for);
    $this->setContent ($attr->text ? $attr->text : '&nbsp;');
  }
}
