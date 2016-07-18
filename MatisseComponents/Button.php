<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Plugins\Matisse\Components\Base\HtmlComponent;
use Electro\Plugins\Matisse\Properties\Base\HtmlComponentProperties;
use Electro\Plugins\Matisse\Properties\TypeSystem\is;
use Electro\Plugins\Matisse\Properties\TypeSystem\type;
use Electro\Plugins\MatisseComponents\Traits\UserInteraction;

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
  public $type = ['button', type::id, is::enum, ['button', 'submit']];
  /**
   * @var string
   */
  public $url = '';
}

class Button extends HtmlComponent
{
  use UserInteraction;

  const propertiesClass = ButtonProperties::class;

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
    if (!str_contains ($this->props->class, 'btn-'))
      $this->addClass ('btn-default');
    parent::preRender ();
  }

  protected function render ()
  {
    $prop = $this->props;

    if ($prop->disabled)
      $this->attr ('disabled', 'disabled');
    $this->attrIf ($prop->tabIndex, 'tabindex', $prop->tabIndex);
    $this->attr ('type', $prop->type);
    if (exists ($prop->action)) {
      $this->beginAttr ('onclick', null, ';');
      if ($prop->confirm) {
        $msg = str_encodeJavasciptStr ($prop->message, "'");
        $this->context->getAssetsService ()->addInlineScript ("function confirm_$prop->id()
{
  swal({
    title: '',
    text: $msg,
    type: 'warning',
    showCancelButton: true
  },
  function() {
    selenia.doAction('$prop->action','$prop->param');
  });
}", "confirm_$prop->id");
        $this->attrValue ("confirm_$prop->id()");
      }
      else $this->attrValue ("selenia." . ($prop->type == 'submit' ? 'set' : 'do') .
                             "Action('$prop->action','$prop->param')");
      $this->endAttr ();
    }
    else {
      if (exists ($prop->script))
        $this->attr ('onclick', $prop->script);
      else if (exists ($prop->url))
        $this->attr ('onclick', "selenia.go('$prop->url',event);");
    }
    if (exists ($prop->help))
      $this->attr ('title', $prop->help);

    $this->beginContent ();

    if (exists ($prop->icon)) {
      $this->tag ('i', [
        'class' => $prop->icon,
      ]);
    }
    $txt = trim ($prop->label);
    echo strlen ($txt) ? $txt : (exists ($prop->icon) ? '' : '&nbsp;');

  }
}
