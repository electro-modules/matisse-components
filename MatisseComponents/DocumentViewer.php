<?php
namespace Electro\Plugins\MatisseComponents;

use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\type;

class DocumentViewerProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $url = '';
}

class DocumentViewer extends HtmlComponent
{
  const propertiesClass = DocumentViewerProperties::class;

  /** @var DocumentViewerProperties */
  public $props;

  protected function render ()
  {
    $prop = $this->props;

    echo html(h('.modal.fade',['tabindex'=>-1,'role']));
  }
}
