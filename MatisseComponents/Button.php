<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;
use Selenia\Plugins\MatisseComponents\Traits\UserInteraction;

class ButtonProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $action = [type::id];
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
  public $script = [type::string];
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
  use UserInteraction;

  protected static $propertiesClass = ButtonProperties::class;

  public $cssClassName = 'btn';
  /** @var ButtonProperties */
  public $props;

  /** overriden */
  protected $containerTag = 'button';

  protected function init ()
  {
    $prop = $this->props;
    if ($prop->confirm) {
      $this->useInteraction ();
      $this->autoId = true;
    }
    parent::init ();
  }

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
    if (exists ($prop->action)) {
      if (isset($prop->param))
        $action = $prop->action . ':' . $prop->param;
      else $action = $prop->action;
      //if ($this->page->browserIsIE) $actionData = "<!--$action-->";
      //else $this->addAttribute('value',$action);

      $this->beginAttr ('onclick', null, ';');
      if ($prop->confirm) {
        $msg = str_encodeJavasciptStr ($prop->message, "'");
        $this->context->addInlineScript ("function confirm_$prop->id()
{
  swal({
    title: '',
    text: $msg,
    type: 'warning',
    showCancelButton: true
  },
  function() {
    doAction('$action');
  });
}");
        $this->attrValue ("confirm_$prop->id()");
      }
      else $this->attrValue ("doAction('$action')");
      $this->endAttr ();
    }
    else {
      if (exists ($prop->script))
        $this->attr ('onclick', $prop->script);
      else if (exists ($prop->url))
        $this->attr ('onclick', "go('$prop->url',event);");
    }
    if (exists ($prop->help))
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
