<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;

class ImageFieldProperties extends HtmlComponentProperties
{
  /**
   * @var bool
   */
  public $crop = true;
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var int
   */
  public $imageHeight = 0;
  /**
   * @var int
   */
  public $imageWidth = 160;
  /**
   * @var array
   */
  public $name = ''; //allow 'field[]'
  /**
   * @var bool
   */
  public $noClear = false;
  /**
   * @var bool
   */
  public $sortable = false;
  /**
   * @var string
   */
  public $value = '';
}

class ImageField extends HtmlComponent
{
  protected static $propertiesClass = ImageFieldProperties::class;

  /** @var ImageFieldProperties */
  public $props;

  protected $autoId = true;

  protected function render ()
  {
    $prop = $this->props;

    $this->page->enableFileUpload = true;

    $this->begin ('input');
    $this->attr ('type', 'hidden');
    $this->attr ('id', "{$prop->id}Field");
    if (isset($prop->name))
      $this->attr ('name', $prop->name);
    else $this->attr ('name', $prop->id);
    $this->attr ('value', $prop->value);
    $this->end ();

    if (isset($prop->value)) {
      $image = new Image($this->context, [
        'value' => $prop->value,
        'class' => 'img-thumbnail',
      ], [
        'width'  => $prop->imageWidth,
        'height' => $prop->imageHeight,
        'crop'   => $prop->crop,
      ]);
      $this->attachAndRender ($image);
    }
    else $this->tag ('div', [
      'class' => 'emptyImg',
      'style' => enum (';',
        "width:{$prop->imageWidth}px",
        isset($prop->imageHeight) ? "height:{$prop->imageHeight}px" : ''
      ),
    ]);

    $this->begin ('div');
    $this->attr ('class', 'buttons');

    $this->begin ('div');
    $this->attr ('class', 'fileBtn');
    $this->beginContent ();

    $button = new Button($this->context, [
      'disabled' => $prop->disabled,
      'class'    => 'btn-default glyphicon glyphicon-picture',
    ]);
    $this->attachAndRender ($button);

    $this->tag ('input', [
      'id'        => "{$prop->id}File",
      'type'      => 'file',
      'class'     => 'fileBtn',
      'size'      => 1,
      'tabindex'  => -1,
      'onchange'  => "ImageField_onChange('{$prop->id}')",
      'name'      => isset($prop->name) ? $prop->name . '_file' : 'file',
      'hidefocus' => $this->page->browserIsIE ? 'true' : null,
    ]);

    $this->end ();

    if (!$prop->noClear) {
      $button = new Button($this->context, [
        'id'       => "{$prop->id}Clear",
        'script'   => "ImageField_clear('{$prop->id}')",
        'disabled' => $prop->disabled || !isset($prop->value),
        'class'    => 'btn-default glyphicon glyphicon-remove',
      ]);
      $this->attachAndRender ($button);
    }
    if ($prop->sortable) {
      $button = new Button($this->context, [
        'action'   => 'down',
        'param'    => $prop->value,
        'disabled' => $prop->disabled || !isset($prop->value),
        'class'    => 'ImageField_next',
      ]);
      $this->attachAndRender ($button);

      $button = new Button($this->context, [
        'action'   => 'up',
        'param'    => $prop->value,
        'disabled' => $prop->disabled || !isset($prop->value),
        'class'    => 'ImageField_prev',
      ]);
      $this->attachAndRender ($button);
    }
    echo '</div><div class="end"></div>';
  }
}

