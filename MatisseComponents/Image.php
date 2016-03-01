<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;

class ImageProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $align = ['left', is::enum, ['left', 'center', 'right']];
  /**
   * @var string The physical or virtual URL prefix for retrieving the image file.
   */
  public $baseUrl = 'files';
  /**
   * @var string
   */
  public $bckColor = '';
  /**
   * @var bool
   */
  public $cache = false;
  /**
   * @var string
   */
  public $crop = '';
  /**
   * @var string
   */
  public $description = '';
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
   * @var string
   */
  public $quality = '';
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
      $crop  = $prop->crop;
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
      $args  = '';
      $width = $prop->width;
      if (isset($width)) {
        $args .= '&amp;w=' . intval ($width);
//                if ($crop)
//                    $this->addAttribute('width',intval($width));
      }
      $height = $prop->height;
      if (isset($height)) {
        $args .= '&amp;h=' . intval ($height);
//                if ($crop)
//                    $this->addAttribute('height',intval($height));
      }
      $quality = $prop->quality;
      if (isset($quality)) $args .= '&amp;q=' . $quality;
      $args .= '&amp;c=' . $crop;
      if (isset($prop->cache) && $prop->cache == '0') $args .= '&amp;nc=1';
//      if (isset($prop->watermark)) {
//        $args .= '&amp;wm=' . ($prop->watermark);
//        if (isset($prop->watermarkOpacity))
//          $args .= '&amp;a=' . $prop->watermarkOpacity;
//        if (isset($prop->watermarkPadding))
//          $args .= '&amp;wmp=' . $prop->watermarkPadding;
//      }
      $bck_color = $prop->bckColor;
      if (isset($bck_color)) $args .= '&amp;bg=' . substr ($bck_color, 1);

      $this->attr ('src', "$prop->baseUrl/$prop->value$args");
    }
  }

}

