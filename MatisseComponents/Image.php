<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;

class ImageProperties extends HtmlComponentProperties
{
  /**
   * @var string|null
   */
  public $align = [type::string, is::enum, ['left', 'center', 'right']];
  /**
   * Sets the background color of the image.
   * <p>It accepts the 140 color names supported by browsers, and also the following formats:
   *
   * - 3 digit RGB: `CCC`
   * - 4 digit ARGB (alpha): `5CCC`
   * - 6 digit RGB: `CCCCCC`
   * - 8 digit ARGB (alpha): `55CCCCCC`
   *
   * @var string|null
   */
  public $background = [type::string];
  /**
   * @var string The physical or virtual URL prefix for retrieving the image file.
   */
  public $baseUrl = 'files';
  /**
   * @var bool
   */
  public $cache = true;
  /**
   * @var string
   */
  public $description = '';
  /**
   * When set, resizes the image to fill the width and height boundaries and crops any excess image data.
   * ><p>**Note:** `'crop' == 'crop-center'`
   *
   * @var string
   */
  public $fit = [
    type::string, is::enum, [
      'crop', 'crop-top-left', 'crop-top', 'crop-top-right', 'crop-left', 'crop-center', 'crop-right',
      'crop-bottom-left', 'crop-bottom', 'crop-bottom-right',
    ],
  ];
  /**
   * Encodes the image to a specific format. Accepts jpg, pjpg (progressive jpeg), png or gif. Defaults to jpg.
   *
   * @var string
   */
  public $format = [type::string, is::enum, ['jpg', 'pjpg', 'png', 'gif']];
  /**
   * @var int
   */
  public $height = 0;
  /**
   * @var string
   */
  public $onClick = '';
  /**
   * @var string
   */
  public $onClickGo = '';
  /**
   * @var int|null
   */
  public $quality = [type::number];
  /**
   * @var string
   */
  public $value = '';
  /**
   * @var string
   */
//  public $watermark = '';
  /**
   * @var int
   */
//  public $watermarkOpacity = 0;
  /**
   * @var int
   */
//  public $watermarkPadding = 0;
  /**
   * @var int
   */
  public $width = 0;
}

class Image extends HtmlComponent
{
  protected static $propertiesClass = ImageProperties::class;

  /** @var ImageProperties */
  public $props;

  protected $containerTag = 'img';

  protected function postRender ()
  {
    if (isset($this->props->value))
      parent::postRender ();
  }

  protected function preRender ()
  {
    if (isset($this->props->value))
      parent::preRender ();
  }

  protected function render ()
  {
    $prop = $this->props;

    if (isset($prop->value)) {
      $align = $prop->align;
      switch ($align) {
        case 'left':
          $this->attr ('style', 'float:left');
          break;
        case 'right':
          $this->attr ('style', 'float:right');
          break;
        case 'center':
          $this->attr ('style', 'margin: 0 auto;display:block');
          break;
      }
      $desc = property ($prop, 'description');
      if (exists ($desc))
        $this->attr ('alt', $desc);
      $onclick = property ($prop, 'on_click');
      if (exists ($onclick))
        $this->attr ('onclick', $onclick);
      $onclick = property ($prop, 'on_click_go');
      if (exists ($onclick))
        $this->attr ('onclick', "location='$onclick'");
      $args  = [];
      $width = $prop->width;
      if (isset($width)) {
        $args[] = 'w=' . intval ($width);
//                if ($crop)
//                    $this->addAttribute('width',intval($width));
      }
      $height = $prop->height;
      if (isset($height)) {
        $args[] = 'h=' . intval ($height);
//                if ($crop)
//                    $this->addAttribute('height',intval($height));
      }
      $quality = $prop->quality;
      if (isset($quality))
        $args[] = 'q=' . $quality;
      if (isset($prop->fit))
        $args[] = 'fit=' . $prop->fit;
      if (isset($prop->format))
        $args[] = 'fm=' . $prop->format;
      if (!$prop->cache)
        $args[] = 'nc=1';
      if (isset($prop->background))
        $args[] = "bg=$prop->background";

      $params = $args ? '?' . implode ('&', $args) : '';
      $this->attr ('src', "$prop->baseUrl/$prop->value$params");
    }
  }

}

