<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\ComponentAttributes;
use Selene\Matisse\VisualComponent;

class ImageAttributes extends ComponentAttributes
{
  public $value;
  public $cache;
  public $absoluteUrl;
  public $description;
  public $onClick;
  public $onClickGo;
  public $unstyled = false;
  public $width;
  public $height;
  public $quality;
  public $crop;
  public $bckColor;
  public $align;
  public $watermark;
  public $watermarkOpacity;
  public $watermarkPadding;

  protected function typeof_value () { return AttributeType::TEXT; }

  protected function typeof_cache () { return AttributeType::BOOL; }

  protected function typeof_absoluteUrl () { return AttributeType::BOOL; }

  protected function typeof_description () { return AttributeType::TEXT; }

  protected function typeof_onClick () { return AttributeType::TEXT; }

  protected function typeof_onClickGo () { return AttributeType::TEXT; }

  protected function typeof_unstyled () { return AttributeType::BOOL; }

  protected function typeof_width () { return AttributeType::NUM; }

  protected function typeof_height () { return AttributeType::NUM; }

  protected function typeof_quality () { return AttributeType::NUM; }

  protected function typeof_crop () { return AttributeType::TEXT; }

  protected function typeof_bckColor () { return AttributeType::TEXT; }

  protected function typeof_align () { return AttributeType::TEXT; }

  protected function enum_align () { return ['left', 'center', 'right']; }

  protected function typeof_watermark () { return AttributeType::TEXT; }

  protected function typeof_watermarkOpacity () { return AttributeType::NUM; }

  protected function typeof_watermarkPadding () { return AttributeType::NUM; }
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

  protected function preRender ()
  {
    if (isset($this->attrs ()->value))
      parent::preRender ();
  }

  protected function postRender ()
  {
    if (isset($this->attrs ()->value))
      parent::postRender ();
  }

  protected function render ()
  {
    global $application, $FRAMEWORK;
    if (isset($this->attrs ()->value)) {
      $crop  = $this->attrs ()->crop;
      $align = $this->attrs ()->align;
      switch ($align) {
        case 'left':
          $this->addAttribute ('style', 'float:left');
          break;
        case 'right':
          $this->addAttribute ('style', 'float:right');
          break;
        case 'center':
          $this->addAttribute ('style', 'margin: 0 auto;display:block');
          break;
      }
      $desc = property ($this->attrs (), 'description');
      if (exists ($desc))
        $this->addAttribute ('alt', $desc);
      $onclick = property ($this->attrs (), 'on_click');
      if (exists ($onclick))
        $this->addAttribute ('onclick', $onclick);
      $onclick = property ($this->attrs (), 'on_click_go');
      if (exists ($onclick))
        $this->addAttribute ('onclick', "location='$onclick'");
      $args  = '';
      $width = $this->attrs ()->width;
      if (isset($width)) {
        $args .= '&amp;w=' . intval ($width);
//                if ($crop)
//                    $this->addAttribute('width',intval($width));
      }
      $height = $this->attrs ()->height;
      if (isset($height)) {
        $args .= '&amp;h=' . intval ($height);
//                if ($crop)
//                    $this->addAttribute('height',intval($height));
      }
      $quality = $this->attrs ()->quality;
      if (isset($quality)) $args .= '&amp;q=' . $quality;
      $args .= '&amp;c=' . $crop;
      if (isset($this->attrs ()->cache) && $this->attrs ()->cache == '0') $args .= '&amp;nc=1';
      if (isset($this->attrs ()->watermark)) {
        $args .= '&amp;wm=' . ($this->attrs ()->watermark);
        if (isset($this->attrs ()->watermarkOpacity))
          $args .= '&amp;a=' . $this->attrs ()->watermarkOpacity;
        if (isset($this->attrs ()->watermarkPadding))
          $args .= '&amp;wmp=' . $this->attrs ()->watermarkPadding;
      }
      $bck_color = $this->attrs ()->bckColor;
      if (isset($bck_color)) $args .= '&amp;bg=' . substr ($bck_color, 1);
      $uri = "$FRAMEWORK/image.php?id={$this->attrs()->value}$args";
      $url =
        $this->attrs ()->absoluteUrl ? $application->toURL ("$application->baseURI/$uri") : $application->toURI ($uri);
      $this->addAttribute ('src', $url);
    }
  }

}

