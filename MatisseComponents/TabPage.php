<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\type;

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
  protected static $propertiesClass = TabPageProperties::class;

  protected $autoId = true;

  /**
   * Returns the component's attributes.
   * @return TabPageProperties
   */
  public function props ()
  {
    return $this->props;
  }

  protected function render ()
  {
    $attr = $this->props ();

    if (!$this->parent || $this->parent->className != 'Tabs')
      throw new ComponentException($this, 'TabPages may only exist inside Tabs components.');
    if ($attr->lazyCreation) {
      ob_start ();
      $this->renderChildren ();
      $html   = ob_get_clean ();
      $html   = str_replace ('\\', '\\\\', $html);
      $html   = str_replace ("'", "\\'", $html);
      $html   = str_replace (chr (0xE2) . chr (0x80) . chr (0xA8), '\n', $html);
      $html   = str_replace (chr (0xE2) . chr (0x80) . chr (0xA9), '\n', $html);
      $html   = str_replace ("\r", '', $html);
      $html   = str_replace ("\n", '\n', $html);
      $html   = str_replace ('</script>', "</s'+'cript>", $html);
      $script = "var {$this->props()->id}Content='$html';";
      $this->page->addInlineScript ($script);
    }
    else {
      $this->beginContent ();
      $this->renderChildren ();
    }
  }

}

