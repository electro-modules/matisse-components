<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Plugins\MatisseComponents\Traits\UserInteraction;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\is;
use Matisse\Properties\TypeSystem\type;

class ButtonProperties extends HtmlComponentProperties
{
  /**
   * @var string If set, when clicked the button will invoke the specified server-side action.
   */
  public $action = type::id;
  /**
   * @var string When set, a confirmation prompt with the given message will be displayed before the action is
   *      performed.
   */
  public $confirm = '';
  /**
   * @var string
   */
  public $help = '';
  /**
   * @var string A list of space-delimited CSS class names for an optional icon to be displayed inside the button.
   */
  public $icon = '';
  /**
   * @var string The button label.
   */
  public $label = '';
  /**
   * If set, when clicked the button will display the modal dialog with the given CSS selector.
   * > Ex: `'#myModal'`
   *
   * @var string
   */
  public $modal = '';
  /**
   * @var string An optional argument for the remote call specified by the `action` property.
   */
  public $param = '';
  /**
   * @var string If set, when clicked the button will perform the specified client-side Javascript.
   */
  public $script = '';
  /**
   * @var int
   */
  public $tabIndex = 0;
  /**
   * @var string
   */
  public $type = ['button', type::id, is::enum, ['button', 'submit']];
  /**
   * @var string If set, when clicked the button will navigate the browser to the specified URL.
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
        $msg = str_encodeJavasciptStr ($prop->confirm, "'");
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
      elseif (exists ($prop->url))
        $this->attr ('onclick', "selenia.go('$prop->url',event);");
      elseif (exists ($prop->modal))
        $this->addAttrs ([
          'data-toggle' => 'modal',
          'data-target' => $prop->modal,
        ]);
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
