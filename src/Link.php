<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\ComponentAttributes;
use Selene\Matisse\VisualComponent;

class LinkAttributes extends ComponentAttributes
{
  public $action;
  public $activeClass = 'active';
  public $disabled = false;
  public $label;
  public $param;
  public $script;
  public $tooltip;
  public $href;

  protected function typeof_action () { return AttributeType::ID; }

  protected function typeof_active_class () { return AttributeType::TEXT; }

  protected function typeof_disabled () { return AttributeType::BOOL; }

  protected function typeof_label () { return AttributeType::TEXT; }

  protected function typeof_param () { return AttributeType::TEXT; }

  protected function typeof_script () { return AttributeType::TEXT; }

  protected function typeof_tooltip () { return AttributeType::TEXT; }

  protected function typeof_href () { return AttributeType::TEXT; }
}

class Link extends VisualComponent
{

  /** overriden */
  protected $containerTag = 'a';

  /**
   * Returns the component's attributes.
   * @return LinkAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return LinkAttributes
   */
  public function newAttributes ()
  {
    return new LinkAttributes($this);
  }

  protected function preRender ()
  {
    global $application;
    $attr = $this->attrs ();

    if ($application->VURI == $attr->href)
      $this->cssClassName = $attr->activeClass;

    parent::preRender();
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $script = $attr->action ? "doAction('{$this->attrs()->action}','{$this->attrs()->param}')"
      : $attr->script;

    $this->addAttribute ('title', $attr->tooltip);
    $this->addAttribute ('href', $attr->disabled
      ? '#'
      :
      (isset($attr->href)
        ?
        $attr->href
        :
        "javascript:$script"
      )
    );
    $this->beginContent ();
    $this->setContent ($attr->label);
  }
}
