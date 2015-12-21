<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Interfaces\Navigation\NavigationLinkInterface;
use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Components\Internal\ContentProperty;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\type;

class MainMenuProperties extends HtmlComponentProperties
{
  /**
   * @var int
   */
  public $depth = 99;
  /**
   * @var string
   */
  public $expandIcon = '';
  /**
   * @var ContentProperty|null
   */
  public $header = type::content;
  /**
   * @var mixed
   */
  public $menu = type::data;
}

class MainMenu extends HtmlComponent
{
  protected $containerTag = 'ul';

  protected $depthClass = ['', 'nav-second-level', 'nav-third-level', 'nav-fourth-level', 'nav-fifth-level'];

  /**
   * @return MainMenuProperties
   */
  public function props ()
  {
    return $this->props;
  }

  /**
   * @return MainMenuProperties
   */
  public function newProperties ()
  {
    return new MainMenuProperties($this);
  }

  protected function render ()
  {
    $attr = $this->props ();

    $this->beginContent ();
    $this->renderChildren ('header');

    $xi    = $attr->get ('expandIcon');
    $links = $attr->menu;
    if (!$links) return;

    echo html (
      map ($links, function (NavigationLinkInterface $link) use ($xi) {
        if (!$link->isActuallyVisible ()) return null;
        $children = $link->getMenu ();
        $children->rewind ();
        $active  = $link->isActive () ? '.active' : '';
        $sub     = $children->valid () ? '.sub' : '';
        $current = $link->isCurrent () ? '.current' : '';
        $url     = $link->isGroup () && !isset ($link->defaultURI) ? null : $link->url ();
        return [
          h ("li$active$sub$current", [
            h ("a$active", [
              'href' => $url,
            ], [
              when ($link->icon (), [h ('i.' . $link->icon ()), ' ']),
              $link->title (),
              when (isset($xi) && $sub, h ("span.$xi")),
            ]),
            when ($sub, $this->renderMenuItem ($children, $xi, false /*$link->matches*/)),
          ]),
        ];
      })
    );
  }

  private function renderMenuItem (\Iterator $links, $xi, $parentIsActive, $depth = 1)
  {
    $links->rewind ();
    if (!$links->valid () || $depth >= $this->props ()->depth)
      return null;
    return h ('ul.nav.collapse.' . $this->depthClass[$depth],
      map ($links, function (NavigationLinkInterface $link) use ($xi, $depth, $parentIsActive) {
        if (!$link->isActuallyVisible ()) return null;
        $children = $link->getMenu ();
        $children->rewind ();
        $active        = $link->isActive () ? '.active' : '';
        $sub           = $children->valid () ? '.sub' : '';
        $current       = $link->isCurrent () ? '.current' : '';
        $disabled      = !$link->isActuallyEnabled ();
        $url           =
          $disabled || ($link->isGroup () && !isset ($link->defaultURI)) ? null : $link->url ();
        $disabledClass = $disabled ? '.disabled' : '';
        return [
          h ("li$active$sub$current", [
            h ("a$active$disabledClass", [
              'href' => $url,
            ], [
              when ($link->icon (), [h ('i.' . $link->icon ()), ' ']),
              $link->title (),
              when (isset($xi) && $sub, h ("span.$xi")),
            ]),
            when ($sub, $this->renderMenuItem ($children, $xi, false /*$link->matches*/, $depth + 1)),
          ]),
        ];
      })
    );
  }

}


