<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class SwitchProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $autofocus = false;
  /**
   * @var bool
   */
  public $checked = false;
  /**
   * A color name.
   *
   * <p>The predefined values are:
   * > `red | green | cyan | blue | purple | grey | black`
   *
   * @var string
   */
  public $color = 'black';
  /**
   * @var string If specified, a #RRGGBB hexadecimal color value; the `color` prop. will be ignored.
   */
  public $customColor = type::string;
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
  /**
   * @var string
   */
  public $script = type::string;
  /**
   * @var string
   */
  public $testValue = type::string;
  /**
   * @var string
   */
  public $tooltip = '';
  /**
   * @var string The value to be submitted.
   */
  public $value = '1';
}

/**
 * A pure CSS3 IOS7-style switch widget.
 */
class Switch_ extends HtmlComponent
{
  const CSS_CLASS = 'Switch';

  const propertiesClass = SwitchProperties::class;

  /** @var SwitchProperties */
  public $props;

  /** @var bool */
  protected $autoId = true;

  protected function init ()
  {
    parent::init ();

    // Support custom colors by dynamically generating the required CSS rules.

    $class = self::CSS_CLASS;
    $color = $this->props->customColor;
    if ($color) {
      if ($color[0] != '#')
        throw new ComponentException($this, "Invalid color value for <kbd>customColor</kbd>");
      $value = $color;
      $color = substr ($color, 1);

      $this->context->getAssetsService ()->addInlineCss ("
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
    $name  = either ($prop->name, $prop->id);

    $this->beginContent ();
    echo html ([
      // Output an additional hidden checkbox that will submit an empty value if the main checkbox is not checked.
      // Does not apply to checkboxes of array fields.

      when (!str_endsWith ($prop->name, '[]'),
        h ('input', [
          'type'    => 'checkbox',
          'name'    => $name,
          'value'   => '',
          'checked' => true,
          'style'   => 'display:none',
        ])
      ),

      // The main (invisible) checkbox that receives focus, clicks and keyboard input.

      h ('input', [
        'type'      => 'checkbox',
        'id'        => $prop->id,
        'name'      => $name,
        'class'     => enum (' ',
          "$class-checkbox",
          "$class-" . ($prop->customColor ? substr ($prop->customColor, 1) : $prop->color)
        ),
        'value'     => $prop->value,
        'checked'   => $prop->checked || (isset($prop->testValue) && $prop->value === $prop->testValue),
        'disabled'  => $prop->disabled,
        'autofocus' => $prop->autofocus,
        'onclick'   => $prop->script,
      ]),

      // The visible widget.

      h ('label', [
        'for'      => $prop->id,
        'class'    => "$class-label",
        'data-off' => $prop->labelOff,
        'data-on'  => $prop->labelOn,
        'title'    => $prop->tooltip,
      ]),

    ]);
  }

}

