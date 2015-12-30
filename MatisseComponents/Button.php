<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;

class ButtonProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $action = [type::id, null];
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
  public $script = [type::string, null];
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

class Button extends HtmlComponent
{
  protected static $propertiesClass = ButtonProperties::class;

  public $cssClassName = 'btn';
  /** @var ButtonProperties */
  public $props;

  /** overriden */
  protected $containerTag = 'button';

  protected function preRender ()
  {
    if (exists ($this->props->icon))
      $this->addClass ('with-icon');
    parent::preRender ();
  }

  protected function render ()
  {
    $prop       = $this->props;
    $actionData = '';

    if ($prop->disabled)
      $this->attr ('disabled', 'disabled');
    $this->attrIf ($prop->tabIndex, 'tabindex', $prop->tabIndex);
    $this->attr ('type', $prop->type);
    if ($this->page->browserIsIE)
      $this->attr ('hideFocus', 'true');
    if (isset($prop->action)) {
      if (isset($prop->param))
        $action = $prop->action . ':' . $prop->param;
      else $action = $prop->action;
      //if ($this->page->browserIsIE) $actionData = "<!--$action-->";
      //else $this->addAttribute('value',$action);
      $this->beginAttr ('onclick', null, ';');
      if ($prop->confirm)
        $this->attrValue ("Button_onConfirm('{$action}','$prop->message')");
      else $this->attrValue ("doAction('" . $action . "')");

      $this->endAttr ();
    }
    else {
      if (isset($prop->script))
        $this->attr ('onclick', $prop->script);
      else if (isset($prop->url))
        $this->attr ('onclick', "go('$prop->url',event);");
    }
    if (exists($prop->help))
      $this->attr ('title', $prop->help);

    $this->beginContent ();

    if (exists ($prop->icon)) {
      $this->tag ('i', [
        'class' => $prop->icon,
      ]);
    }
    $txt = trim ($prop->label . $actionData);
    echo strlen ($txt) ? $txt : (exists ($prop->icon) ? '' : '&nbsp;');

  }
}
