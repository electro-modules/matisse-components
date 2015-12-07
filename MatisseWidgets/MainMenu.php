<?php
namespace Selenia\Plugins\MatisseWidgets;

use Selenia\Interfaces\Navigation\NavigationLinkInterface;
use Selenia\Matisse\AttributeType;
use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\VisualComponent;
use Selenia\Routing\AbstractRoute;
use Selenia\Routing\RouteGroup;

class MainMenuAttributes extends VisualComponentAttributes
{
  /** @var  Parameter */
  public $header;
  /** @var  string */
  public $expandIcon;
  /** @var int */
  public $depth = 99;

  public $menu;

  protected function typeof_header () { return AttributeType::SRC; }

  protected function typeof_expandIcon () { return AttributeType::TEXT; }

  protected function typeof_depth () { return AttributeType::NUM; }

  protected function typeof_menu () { return AttributeType::DATA; }
}

class MainMenu extends VisualComponent
{
  protected $containerTag = 'ul';

  protected $depthClass = ['', 'nav-second-level', 'nav-third-level', 'nav-fourth-level', 'nav-fifth-level'];

  /**
   * Returns the component's attributes.
   * @return MainMenuAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
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
    $xi = $attr->get ('expandIcon');

    $links = $attr->menu;

    if (!$links) return;

      echo html (
        map ($links, function (NavigationLinkInterface $link) use ($xi) {
          if (!$link->isActuallyVisible()) return null;
          $children = $link->getMenu();
          $children->rewind();
          $active = true /*$link->isSelected()*/ ? '.active' : '';
          $sub    = $children->valid() ? '.sub' : '';
          $url    = $link->isGroup() && !isset ($link->defaultURI) ? 'javascript:void(0)' : $link->url();
          return [
            h ("li$active$sub", [
              h ("a$active", [
                'href' => $url
              ], [
                when ($link->icon(), [h ('i.' . $link->icon()), ' ']),
                $link->title(),
                when (isset($xi) && $sub, h ("span.$xi"))
              ]),
              when ($sub, $this->renderMenuItem ($children, $xi, false /*$link->matches*/))
            ])
          ];
        })
      );

//    else echo html (
//      map ($application->routingMap->groups, function ($grp) use ($xi) {
//        return [
//          h ('li.header', [
//            h ('a', [
//              when ($grp->icon, [h ('i.' . $grp->icon), ' ']),
//              $grp->title
//            ])
//          ]),
//          map ($grp->routes, function ($route) use ($xi) {
//            if (!$route->onMenu) return null;
//            $active = $route->selected ? '.active' : '';
//            $sub    = $route->hasSubNav ? '.sub' : '';
//            return [
//              h ("li.treeview$active$sub", [
//                h ("a$active", ['href' => $route->URL], [
//                  when ($route->icon, [h ('i.' . $route->icon), ' ']),
//                  either ($route->subtitle, $route->title),
//                  when (isset($xi) && $route->hasSubNav, h ("span.$xi"))
//                ]),
//                when ($route->hasSubNav, $this->renderMenuItem ($route->routes, $xi, $route->matches))
//              ])
//            ];
//          })
//        ];
//      })
//    );
  }

  private function renderMenuItem ($links, $xi, $parentIsActive, $depth = 1)
  {
    $links->rewind();
    if (!$links->valid() || $depth >= $this->attrs ()->depth)
      return null;
    return h ('ul.nav.collapse.' . $this->depthClass[$depth],
      map ($links, function (NavigationLinkInterface $link) use ($xi, $depth, $parentIsActive) {
        if (!$link->isActuallyVisible()) return null;
        $children = $link->getMenu();
        $children->rewind();
        $active = true /*$link->isSelected()*/ ? '.active' : '';
        $sub    = $children->valid() ? '.sub' : '';
        $url    = $link->isGroup() && !isset ($link->defaultURI) ? 'javascript:void(0)' : $link->url();
        $current = true /*$link->matches*/ ? '.current' : '';
        $disabled      = !$link->isActuallyEnabled();
        $url           = $disabled || ($link->isGroup()) ? 'javascript:void(0)' : $link->url();
        $disabledClass = $disabled ? '.disabled' : '';
        return [
          h ("li$active$sub$current", [
            h ("a$active$disabledClass", [
              'href' => $url
            ], [
              when ($link->icon(), [h ('i.' . $link->icon()), ' ']),
              $link->title(),
              when (isset($xi) && $sub, h ("span.$xi"))
            ]),
            when ($sub, $this->renderMenuItem ($children, $xi, false /*$link->matches*/, $depth + 1))
          ])
        ];

//        if (!$link->isActuallyVisible()) return null;
//        $active  = $link->selected ? '.active' : '';
//        $sub     = $link->hasSubNav ? '.sub' : '';
//        $current = $link->matches ? '.current' : '';
//        // Disable submenus that require parameters not yet available
////        $disabled = $parentIsActive && !$route->matches && strpos($route->URL, '{') !== false;
//        $disabled      = strpos ($link->URL, '{') !== false;
//        $url           = $disabled || ($link instanceof RouteGroup) ? 'javascript:void(0)' : $link->URL;
//        $disabledClass = $disabled ? '.disabled' : '';
//        return
//          h ("li.$active$sub$current", [
//            h ("a$active$disabledClass", [
//              'href' => $url,
//            ], [
//              when ($link->icon, [h ('i.' . $link->icon), ' ']),
//              either ($link->subtitle, $link->title),
//              when (isset($xi) && $link->hasSubNav, h ("span.$xi"))
//            ]),
//            when ($link->hasSubNav, $this->renderMenuItem ($link->routes, $xi, $link->matches, $depth + 1))
//          ]);
      })
    );
  }

}


