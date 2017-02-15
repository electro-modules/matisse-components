<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Interfaces\ContentRepositoryInterface;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\is;
use Matisse\Properties\TypeSystem\type;

class ImageProperties extends HtmlComponentProperties
{
  /**
   * Aligns the image on the page.
   *
   * @var string|null
   */
  public $align = [type::string, is::enum, ['', 'left', 'center', 'right']];
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
      '', 'crop', 'crop-top-left', 'crop-top', 'crop-top-right', 'crop-left', 'crop-center', 'crop-right',
      'crop-bottom-left', 'crop-bottom', 'crop-bottom-right',
    ],
  ];
  /**
   * Encodes the generated image to a specific format. Accepts jpg, pjpg (progressive jpeg), png or gif.
   * Defaults to jpg.
   *
   * @var string
   */
  public $format = ['jpg', type::string, is::enum, ['jpg', 'pjpg', 'png', 'gif']];
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
  public $position = 'center';
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
  /**
   * @var string|null HTML5 itemprop attribute
   */
  public $itemprop = [type::string, null];
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
  const propertiesClass = ImageProperties::class;

  /** @var ContentRepositoryInterface */
  public $contentRepo;
  /** @var ImageProperties */
  public $props;

  public function __construct (ContentRepositoryInterface $contentRepo)
  {
    parent::__construct ();
    $this->contentRepo = $contentRepo;
  }

  protected function postRender ()
  {
    if (isset($this->props->value))
      parent::postRender ();
  }

  protected function preRender ()
  {
    $prop = $this->props;
    if (!isset($prop->width) || !isset($prop->height))
      $this->containerTag = 'img';
    if (isset($prop->value))
      parent::preRender ();
  }

  protected function render ()
  {
    $prop = $this->props;

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

    if (exists ($prop->value)) {

      $url = $this->contentRepo->getImageUrl ($prop->value, [
        'w'   => when (isset($prop->width), $prop->width),
        'h'   => when (isset($prop->height), $prop->height),
        'q'   => when (isset($prop->quality), $prop->quality),
        'fit' => when (isset($prop->fit), $prop->fit),
        'fm'  => when (isset($prop->format), $prop->format),
        'nc'  => when (!$prop->cache, '1'),
        'bg'  => when (isset($prop->background), $prop->background),
      ]);

      if ($this->containerTag == 'img')
        $this->addAttrs ([
          'src'    => $url,
          'width'  => $prop->width,
          'height' => $prop->height,
          'itemprop' => $prop->itemprop
        ]);
      else $this->attr ('style', enum (';',
        "background-image:url($url)",
        "background-repeat:no-repeat",
        when (exists ($prop->size) && $prop->size != 'auto', "background-size:$prop->size"),
        when ($prop->position, "background-position:$prop->position"),
        when ($prop->width, "width:{$prop->width}px"),
        when ($prop->height, "height:{$prop->height}px")
      ));
    }
  }

}

