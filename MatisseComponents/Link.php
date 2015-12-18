<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\Base\VisualComponentAttributes;
use Selenia\Matisse\Attributes\DSL\type;
use Selenia\Matisse\Components\Base\VisualComponent;

class LinkAttributes extends VisualComponentAttributes
{
  /**
   * @var string
   */
  public $action = type::id;
  /**
   * @var string
   */
  public $activeClass = 'active';
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var string
   */
  public $href = '';
  /**
   * @var string
   */
  public $label = '';
  /**
   * @var string
   */
  public $param = '';
  /**
   * @var string
   */
  public $script = '';
  /**
   * @var string
   */
  public $tooltip = '';
  /**
   * @var string
   */
  public $wrapper = '';
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

    if (!empty($attr->wrapper))
      $this->containerTag = $attr->wrapper;
    parent::preRender ();
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    if (!empty($attr->wrapper))
      $this->begin ('a');

    $script = $attr->action ? "doAction('{$this->attrs()->action}','{$this->attrs()->param}')"
      : $attr->script;

    $this->attr ('title', $attr->tooltip);
    $this->attr ('href', $attr->disabled
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

    if (!empty($attr->wrapper))
      $this->end ();
  }
}
