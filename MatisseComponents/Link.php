<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class LinkProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $action = type::id;
  /**
   * @var bool
   */
  public $active = false;
  /**
   * @var string
   */
  public $activeClass = 'active';
  /**
   * @var string
   */
  public $currentUrl = '';
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

class Link extends HtmlComponent
{
  protected static $propertiesClass = LinkProperties::class;

  /** @var LinkProperties */
  public $props;

  protected $containerTag = 'a';

  protected function preRender ()
  {
    $prop = $this->props;

    if ($prop->active || (exists ($prop->href) && $prop->currentUrl == $prop->href))
      $this->cssClassName = $prop->activeClass;

    if (!empty($prop->wrapper))
      $this->containerTag = $prop->wrapper;
    parent::preRender ();
  }

  protected function render ()
  {
    $prop = $this->props;

    if (!empty($prop->wrapper))
      $this->begin ('a');

    $script = $prop->action ? "doAction('$prop->action','$prop->param')"
      : $prop->script;

    if (exists ($prop->tooltip))
      $this->attr ('title', $prop->tooltip);
    $this->attr ('href', $prop->disabled
      ? 'javascript:void(0)'
      :
      (exists ($prop->href)
        ?
        $prop->href
        :
        "javascript:$script"
      )
    );
    $this->beginContent ();
    $this->setContent ($prop->label);

    if (!empty($prop->wrapper))
      $this->end ();
  }
}
