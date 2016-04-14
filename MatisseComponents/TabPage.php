<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class TabPageProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var string
   */
  public $icon = '';
  /**
   * @var string
   */
  public $id = type::id;
  /**
   * @var int
   */
  public $index = 0;
  /**
   * @var string
   */
  public $label = '';
  /**
   * @var bool
   */
  public $lazyCreation = false;
  /**
   * > **Note:** used by Tabs
   *
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

class TabPage extends HtmlComponent
{
  const propertiesClass = TabPageProperties::class;

  /** @var TabPageProperties */
  public $props;

  protected $autoId = true;

  protected function render ()
  {
    $prop = $this->props;

    if (!$this->parent || $this->parent->className != 'Tabs')
      throw new ComponentException($this, 'TabPages may only exist inside Tabs components.');
    if ($prop->lazyCreation) {
      ob_start ();
      $this->runChildren ();
      $html   = ob_get_clean ();
      $html   = str_replace ('\\', '\\\\', $html);
      $html   = str_replace ("'", "\\'", $html);
      $html   = str_replace (chr (0xE2) . chr (0x80) . chr (0xA8), '\n', $html);
      $html   = str_replace (chr (0xE2) . chr (0x80) . chr (0xA9), '\n', $html);
      $html   = str_replace ("\r", '', $html);
      $html   = str_replace ("\n", '\n', $html);
      $html   = str_replace ('</script>', "</s'+'cript>", $html);
      $script = "var {$prop->id}Content='$html';";
      $this->context->addInlineScript ($script);
    }
    else {
      $this->beginContent ();
      $this->runChildren ();
    }
  }

}

