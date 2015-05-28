<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\ComponentAttributes;
use Selene\Matisse\VisualComponent;
use Selene\Routing\AbstractRoute;
use Selene\Routing\RouteGroup;

class MainMenuAttributes extends ComponentAttributes
{
  /** @var  Parameter */
  public $header;
  /** @var  string */
  public $expandIcon;
  /** @var int */
  public $depth = 99;

  protected function typeof_header () { return AttributeType::SRC; }

  protected function typeof_expandIcon () { return AttributeType::TEXT; }

  protected function typeof_depth () { return AttributeType::NUM; }
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
    global $application;
    $attr = $this->attrs ();

    $this->beginContent ();
    $this->runSet ($this->getChildren ('header'));
    $xi = $attr->get ('expand_icon');

    if (!empty($application->routingMap->routes))
      echo html (
        map ($application->routingMap->routes, function ($route) use ($xi) {
          if (!$route->onMenu) return null;
          $active = $route->selected ? '.active' : '';
          $sub    = $route->hasSubNav ? '.sub' : '';
          $url    = $route instanceof RouteGroup && !isset ($route->defaultURI) ? 'javascript:void(0)' : $route->URL;
          return [
            h ("li$active$sub", [
              h ("a$active", [
                'href' => $url
              ], [
                when ($route->icon, [h ('i.' . $route->icon), ' ']),
                either ($route->subtitle, $route->title),
                iftrue (isset($xi) && $route->hasSubNav, h ("span.$xi"))
              ]),
              when ($route->hasSubNav, $this->renderMenuItem ($route->routes, $xi, $route->matches))
            ])
          ];
        })
      );

    else echo html (
      map ($application->routingMap->groups, function ($grp) use ($xi) {
        return [
          h ('li.header', [
            h ('a', [
              when ($grp->icon, [h ('i.' . $grp->icon), ' ']),
              $grp->title
            ])
          ]),
          map ($grp->routes, function ($route) use ($xi) {
            if (!$route->onMenu) return null;
            $active = $route->selected ? '.active' : '';
            $sub    = $route->hasSubNav ? '.sub' : '';
            return [
              h ("li.treeview$active$sub", [
                h ("a$active", ['href' => $route->URL], [
                  when ($route->icon, [h ('i.' . $route->icon), ' ']),
                  either ($route->subtitle, $route->title),
                  iftrue (isset($xi) && $route->hasSubNav, h ("span.$xi"))
                ]),
                when ($route->hasSubNav, $this->renderMenuItem ($route->routes, $xi, $route->matches))
              ])
            ];
          })
        ];
      })
    );
  }

  private function renderMenuItem ($pages, $xi, $parentIsActive, $depth = 1)
  {
    if (!$pages || $depth >= $this->attrs ()->depth)
      return null;
    return h ('ul.nav.collapse.' . $this->depthClass[$depth],
      map ($pages, function ($route) use ($xi, $depth, $parentIsActive) {
        if (!$route->onMenu) return null;
        if (isset($route->menu))
          return array_map (function ($url, $title) use ($route) {
            global $application;
            /** @var AbstractRoute $route */
            return h ("li", [
              'class' => $url == $application->VURI ? 'active current' : ''
            ], [
              h ("a", [
                'href'  => $url,
                'class' => $url == $application->VURI ? 'active' : ''
              ], $title)
            ]);
          }, $route->menu, array_keys ($route->menu));
        $active  = $route->selected ? '.active' : '';
        $sub     = $route->hasSubNav ? '.sub' : '';
        $current = $route->matches ? '.current' : '';
        // Disable submenus that require parameters not yet available
//        $disabled = $parentIsActive && !$route->matches && strpos($route->URL, '{') !== false;
        $disabled      = strpos ($route->URL, '{') !== false;
        $url           = $disabled || ($route instanceof RouteGroup) ? 'javascript:void(0)' : $route->URL;
        $disabledClass = $disabled ? '.disabled' : '';
        return
          h ("li.$active$sub$current", [
            h ("a$active$disabledClass", [
              'href' => $url,
            ], [
              when ($route->icon, [h ('i.' . $route->icon), ' ']),
              either ($route->subtitle, $route->title),
              iftrue (isset($xi) && $route->hasSubNav, h ("span.$xi"))
            ]),
            when ($route->hasSubNav, $this->renderMenuItem ($route->routes, $xi, $route->matches, $depth + 1))
          ]);
      })
    );
  }

}


