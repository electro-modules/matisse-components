<?php
namespace Electro\Plugins\MatisseComponents;

use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\type;

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
  const propertiesClass = LabelProperties::class;

  /** @var LabelProperties */
  public $props;

  protected $containerTag = 'label';

  protected function render ()
  {
    $prop = $this->props;

    $this->attr ('for', $prop->for);
    $this->setContent ($prop->text ? $prop->text : '&nbsp;');
  }
}
