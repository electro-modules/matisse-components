<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Attributes\Base\VisualComponentAttributes;
use Selenia\Matisse\Attributes\DSL\is;
use Selenia\Matisse\Components\Base\VisualComponent;

class ImageAttributes extends VisualComponentAttributes
{
  /**
   * @var bool
   */
  public $absoluteUrl = false;
  /**
   * @var string
   */
  public $align = ['left', is::enum, ['left', 'center', 'right']];
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
   * @var bool
   */
  public $unstyled = false;
  /**
   * @var string
   */
  public $value = '';
  /**
   * @var string
   */
  public $watermark = '';
  /**
   * @var int
   */
  public $watermarkOpacity = 0;
  /**
   * @var int
   */
  public $watermarkPadding = 0;
  /**
   * @var int
   */
  public $width = 0;
}

class Image extends VisualComponent
{

  protected $containerTag = 'img';

  /**
   * Returns the component's attributes.
   * @return ImageAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return ImageAttributes
   */
  public function newAttributes ()
  {
    return new ImageAttributes($this);
  }

  protected function postRender ()
  {
    if (isset($this->attrs ()->value))
      parent::postRender ();
  }

  protected function preRender ()
  {
    if (isset($this->attrs ()->value))
      parent::preRender ();
  }

  protected function render ()
  {
    global $application;
    $attr = $this->attrs ();

    if (isset($attr->value)) {
      $crop  = $attr->crop;
      $align = $attr->align;
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
      $desc = property ($attr, 'description');
      if (exists ($desc))
        $this->attr ('alt', $desc);
      $onclick = property ($attr, 'on_click');
      if (exists ($onclick))
        $this->attr ('onclick', $onclick);
      $onclick = property ($attr, 'on_click_go');
      if (exists ($onclick))
        $this->attr ('onclick', "location='$onclick'");
      $args  = '';
      $width = $attr->width;
      if (isset($width)) {
        $args .= '&amp;w=' . intval ($width);
//                if ($crop)
//                    $this->addAttribute('width',intval($width));
      }
      $height = $attr->height;
      if (isset($height)) {
        $args .= '&amp;h=' . intval ($height);
//                if ($crop)
//                    $this->addAttribute('height',intval($height));
      }
      $quality = $attr->quality;
      if (isset($quality)) $args .= '&amp;q=' . $quality;
      $args .= '&amp;c=' . $crop;
      if (isset($attr->cache) && $attr->cache == '0') $args .= '&amp;nc=1';
      if (isset($attr->watermark)) {
        $args .= '&amp;wm=' . ($attr->watermark);
        if (isset($attr->watermarkOpacity))
          $args .= '&amp;a=' . $attr->watermarkOpacity;
        if (isset($attr->watermarkPadding))
          $args .= '&amp;wmp=' . $attr->watermarkPadding;
      }
      $bck_color = $attr->bckColor;
      if (isset($bck_color)) $args .= '&amp;bg=' . substr ($bck_color, 1);
//      $uri = "$FRAMEWORK/image.php?id={$this->attrs()->value}$args";
      $uri = "$application->frameworkURI/image?id={$this->attrs()->value}$args";
      $url =
        $attr->absoluteUrl ? $application->toURL ("$application->baseURI/$uri") : $application->toURI ($uri);
      $this->attr ('src', $url);
    }
  }

}

