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
