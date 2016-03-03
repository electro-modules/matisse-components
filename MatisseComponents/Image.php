<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;

class ImageProperties extends HtmlComponentProperties
{
  /**
   * Aligns the image on the page.
   *
   * @var string|null
   */
  public $align = [type::string, is::enum, ['left', 'center', 'right']];
  /**
   * @var string A textuad description of the image.
   */
  public $alt = '';
  /**
   * Sets the background color of the generated image.
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
   * When set, resizes the image to fill the width and height boundaries and crops any excess image data.
   *
   * <p>The transformations are performed server-side and an optimized image is generated, cached and downloaded.
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
   * Encodes the generated image to a specific format. Accepts jpg, pjpg (progressive jpeg), png or gif.
   * Defaults to jpg.
   *
   * @var string
   */
  public $format = [type::string, is::enum, ['jpg', 'pjpg', 'png', 'gif']];
  /**
   * Affects both the server-side and client-side images.
   *
   * @var int|null
   */
  public $height = [type::number];
  /**
   * @var string If set, when the image is clicked it triggers a navigation to the given URL.
   */
  public $href = '';
  /**
   * @var string
   */
  public $onClick = '';
  /**
   * Specifies how the image will be positioned inside the component's area.
   * <p>Valid values are the same as those for the CSS `background-position` property (ex: `20% center`).
   *
   * @var string
   */
  public $position = [type::string];
  /**
   * @var int|null
   */
  public $quality = [type::number];
  /**
   * Specifies how the image will be scaled client-side.
   *
   * <p>Valid values are the same as those for the CSS `background-size` property:
   * ```
   *   auto|length|cover|contain|initial|inherit
   * ```
   * `length` is a set of one or two values (units, percentage or `auto`).
   *
   * @var string
   */
  public $size = 'auto';
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
   * @var string The file URL.
   */
  public $value = '';
  /**
   * Affects both the server-side and client-side images.
   *
   * @var int|null
   */
  public $width = [type::number];
}

/**
 * Displays an image retrieved from the content repository, optionally applyibg transformations to it (ex: resizing).
 *
 * <p>The transformed image will be cached on the server.
 * <p>The component outputs the image via the CSS background of a DIV element, instead of using an IMG element, so that
 * it can take advantage of more advanced features not available on IMGs (like dynamic background stretching and
 * clipping).
 */
class Image extends HtmlComponent
{
  protected static $propertiesClass = ImageProperties::class;

  /** @var ImageProperties */
  public $props;

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

      if (exists ($prop->alt))
        $this->attr ('alt', $prop->alt);
      if (exists ($prop->onClick))
        $this->attr ('onclick', $prop->onClick);
      if (exists ($prop->href))
        $this->attr ('onclick', "location='$prop->href'");

      $args = [];
      if (isset($prop->width))
        $args[] = 'w=' . intval ($prop->width);
      if (isset($prop->height))
        $args[] = 'h=' . intval ($prop->height);
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

      $this->attr ('style', enum (';',
        "background-image:url($prop->baseUrl/$prop->value$params)",
        when (exists ($prop->size) && $prop->size != 'auto', "background-size:$prop->size"),
        when ($prop->position, "background-position:$prop->position")
      ));
    }
  }

}

