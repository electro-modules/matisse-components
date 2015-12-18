<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\Type;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\VisualComponent;

class TabPageAttributes extends VisualComponentAttributes
{
  public $content;
  public $disabled;
  public $icon;
  public $id;
  public $index;
  public $label;
  public $lazyCreation = false;
  public $selected; //used by Tabs
  public $url;
  public $value;

  protected function typeof_content () { return Type::SRC; }

  protected function typeof_disabled () { return Type::BOOL; }

  protected function typeof_id () { return Type::ID; }

  protected function typeof_icon () { return Type::TEXT; }

  protected function typeof_index () { return Type::NUM; }

  protected function typeof_label () { return Type::TEXT; }

  protected function typeof_lazyCreation () { return Type::BOOL; }

  protected function typeof_selected () { return Type::BOOL; }

  protected function typeof_url () { return Type::TEXT; }

  protected function typeof_value () { return Type::TEXT; }
}

class TabPage extends VisualComponent
{

  protected $autoId = true;

  /**
   * Returns the component's attributes.
   * @return TabPageAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return TabPageAttributes
   */
  public function newAttributes ()
  {
    return new TabPageAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    if (!$this->parent || $this->parent->className != 'Tabs')
      throw new ComponentException($this, 'TabPages may only exist inside Tabs components.');
    $this->setChildren ($this->getChildren ('content'));
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
      $script = "var {$this->attrs()->id}Content='$html';";
      $this->page->addInlineScript ($script);
    }
    else {
      $this->beginContent ();
      $this->renderChildren ();
    }
  }

}
