<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\type;

class LinkProperties extends HtmlComponentProperties
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

class Link extends HtmlComponent
{
  protected static $propertiesClass = LinkProperties::class;

  /** overriden */
  protected $containerTag = 'a';

  protected function preRender ()
  {
    global $application;
    $attr = $this->props ();

    if ($application->VURI == $attr->href)
      $this->cssClassName = $attr->activeClass;

    if (!empty($attr->wrapper))
      $this->containerTag = $attr->wrapper;
    parent::preRender ();
  }

  /**
   * Returns the component's attributes.
   * @return LinkProperties
   */
  public function props ()
  {
    return $this->props;
  }

  protected function render ()
  {
    $attr = $this->props ();

    if (!empty($attr->wrapper))
      $this->begin ('a');

    $script = $attr->action ? "doAction('{$this->props()->action}','{$this->props()->param}')"
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
