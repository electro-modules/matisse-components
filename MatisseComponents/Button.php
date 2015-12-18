<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\Type;
use Selenia\Matisse\VisualComponent;

class ButtonAttributes extends VisualComponentAttributes
{
  protected static $ENUMS = [
    'type' => ['button', 'submit'],
  ];
  protected static $TYPES = [
    'action'   => Type::ID,
    'param'    => Type::TEXT,
    'script'   => Type::TEXT,
    'url'      => Type::TEXT,
    'label'    => Type::TEXT,
    'message'  => Type::TEXT,
    'confirm'  => Type::BOOL,
    'help'     => Type::TEXT,
    'tabIndex' => Type::NUM,
    'icon'     => Type::TEXT,
    'type'     => Type::TEXT,
  ];

  public $action;
  public $confirm = false;
  public $help;
  public $icon;
  public $label;
  public $message;
  public $param;
  public $script;
  public $tabIndex;
  public $type    = 'button';
  public $url;

  protected function enum_type () { return ['button', 'submit']; }

  protected function typeof_action () { return Type::ID; }

  protected function typeof_confirm () { return Type::BOOL; }

  protected function typeof_help () { return Type::TEXT; }

  protected function typeof_icon () { return Type::TEXT; }

  protected function typeof_label () { return Type::TEXT; }

  protected function typeof_message () { return Type::TEXT; }

  protected function typeof_param () { return Type::TEXT; }

  protected function typeof_script () { return Type::TEXT; }

  protected function typeof_tabIndex () { return Type::NUM; }

  protected function typeof_type () { return Type::TEXT; }

  protected function typeof_url () { return Type::TEXT; }
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
