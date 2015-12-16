<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Interfaces\Navigation\NavigationLinkInterface;
use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\AttributeType;
use Selenia\Matisse\Components\Parameter;
use Selenia\Matisse\VisualComponent;

class MainMenuAttributes extends VisualComponentAttributes
{
  /** @var int */
  public $depth = 99;
  /** @var  string */
  public $expandIcon;
  /** @var  Parameter */
  public $header;
  public $menu;

  protected function typeof_depth () { return AttributeType::NUM; }

  protected function typeof_expandIcon () { return AttributeType::TEXT; }

  protected function typeof_header () { return AttributeType::SRC; }

  protected function typeof_menu () { return AttributeType::DATA; }
}

class MainMenu extends VisualComponent
{
  protected $containerTag = 'ul';

  protected $depthClass = ['', 'nav-second-level', 'nav-third-level', 'nav-fourth-level', 'nav-fifth-level'];

  /**
   * @return MainMenuAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * @return MainMenuAttributes
   */
  public function newAttributes ()
  {
    return new MainMenuAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

    $this->beginContent ();
    $this->runSet ($this->getChildren ('header'));

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
    if (!$links->valid () || $depth >= $this->attrs ()->depth)
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


