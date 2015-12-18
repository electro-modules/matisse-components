<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\Base\VisualComponentAttributes;
use Selenia\Matisse\Attributes\DSL\is;
use Selenia\Matisse\Attributes\DSL\type;
use Selenia\Matisse\Components\Base\VisualComponent;

class ButtonAttributes extends VisualComponentAttributes
{
  /**
   * @var string
   */
  public $action = type::id;
  /**
   * @var bool
   */
  public $confirm = false;
  /**
   * @var string
   */
  public $help = '';
  /**
   * @var string
   */
  public $icon = '';
  /**
   * @var string
   */
  public $label = '';
  /**
   * @var string
   */
  public $message = '';
  /**
   * @var string
   */
  public $param = '';
  /**
   * @var string
   */
  public $script = '';
  /**
   * @var int
   */
  public $tabIndex = 0;
  /**
   * @var string
   */
  public $type = [type::id, 'button', is::enum, ['button', 'submit']];
  /**
   * @var string
   */
  public $url = '';
}

class Button extends VisualComponent
{

  public $cssClassName = 'btn';

  /** overriden */
  protected $containerTag = 'button';

  /**
   * Returns the component's attributes.
   * @return ButtonAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return ButtonAttributes
   */
  public function newAttributes ()
  {
    return new ButtonAttributes($this);
  }

  protected function preRender ()
  {
    if (isset($this->attrs ()->icon))
      $this->addClass ('with-icon');
    parent::preRender ();
  }

  protected function render ()
  {
    $attr       = $this->attrs ();
    $actionData = '';

    if ($attr->disabled)
      $this->attr ('disabled', 'disabled');
    $this->attrIf ($attr->tabIndex, 'tabindex', $attr->tabIndex);
    $this->attr ('type', $attr->type);
    if ($this->page->browserIsIE)
      $this->attr ('hideFocus', 'true');
    if (isset($attr->action)) {
      if (isset($attr->param))
        $action = $attr->action . ':' . $attr->param;
      else $action = $attr->action;
      //if ($this->page->browserIsIE) $actionData = "<!--$action-->";
      //else $this->addAttribute('value',$action);
      $this->beginAttr ('onclick', null, ';');
      if ($attr->confirm)
        $this->attrValue ("Button_onConfirm('{$action}','{$this->attrs()->message}')");
      else $this->attrValue ("doAction('" . $action . "')");

      $this->endAttr ();
    }
    else {
      if (isset($attr->script))
        $this->attr ('onclick', $attr->script);
      else if (isset($attr->url))
        $this->attr ('onclick', "go('{$this->attrs()->url}',event);");
    }
    if (isset($attr->help))
      $this->attr ('title', $attr->help);

    $this->beginContent ();

    if (isset($attr->icon)) {
      $this->tag ('i', [
        'class' => $attr->icon,
      ]);
    }
    $txt = trim ($attr->label . $actionData);
    echo strlen ($txt) ? $txt : (isset($attr->icon) ? '' : '&nbsp;');

  }
}
