<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;

class SwitchProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $checked = false;
  /**
   * @var string
   */
  public $color = [type::string, is::enum, ['red', 'green', 'blue', 'grey'], 'red'];
  /**
   * @var string
   */
  public $labelOff = 'No';
  /**
   * @var string
   */
  public $labelOn = 'Yes';
  /**
   * @var string
   */
  public $name = '';
}

class Switch_ extends HtmlComponent
{
  const CSS_CLASS = 'Switch';
  static $COLORS = [
    'red'   => '#f35f42',
    'green' => '#13BF11',
    'blue'  => '#1fc1c8',
    'grey'  => '#c7c7c7',
  ];

  protected static $propertiesClass = SwitchProperties::class;

  /** @var SwitchProperties */
  public $props;

  /** @var bool */
  protected $autoId = true;

  protected function init ()
  {
    $this->context->addStylesheet ('modules/selenia-plugins/matisse-components/css/switch.css');

    $class = self::CSS_CLASS;
    $color = $this->props->color;

    $value = self::$COLORS[$color];
    $this->context->addInlineCss ("
.$class-$color + .$class-label{
  /*box-shadow*/
  -webkit-box-shadow:inset 0 0 0 0px $value,0 0 0 2px #dddddd;
  -moz-box-shadow:inset 0 0 0 0px $value,0 0 0 2px #dddddd;
  box-shadow:inset 0 0 0 0px $value,0 0 0 2px #dddddd;
}
.$class-$color:checked + .$class-label{
  /*box-shadow*/
  -webkit-box-shadow:inset 0 0 0 18px $value,0 0 0 2px $value;
  -moz-box-shadow:inset 0 0 0 18px $value,0 0 0 2px $value;
  box-shadow:inset 0 0 0 18px $value,0 0 0 2px $value;
}
.$class-$color:checked + .$class-label:after{
  color:$value;
}
", "init-$class-$color");
  }

  protected function preRender ()
  {
    $this->props->containerId = $this->props->id . 'Container';
    parent::preRender ();
  }


  protected function render ()
  {
    $prop  = $this->props;
    $class = self::CSS_CLASS;

    $this->beginContent();
    echo html ([
      h ('input', [
        'type'    => 'checkbox',
        'id'      => $prop->id,
        'name'    => either ($prop->name, $prop->id),
        'class'   => enum (' ',
          "$class-checkbox",
          "$class-$prop->color"
        ),
        'checked' => $prop->checked,
      ]),
      h ('label', [
        'for'      => $prop->id,
        'class'    => "$class-label",
        'data-off' => $prop->labelOff,
        'data-on'  => $prop->labelOn,
      ]),
    ]);
  }

}

