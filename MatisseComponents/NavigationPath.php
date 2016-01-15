<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Interfaces\Navigation\NavigationInterface;
use Selenia\Interfaces\Navigation\NavigationLinkInterface;
use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\type;

class NavigationPathProperties extends HtmlComponentProperties
{
  /**
   * @var NavigationInterface
   */
  public $navigation = type::data;
  /**
   * @var bool
   */
  public $showIcons = false;
}

class NavigationPath extends HtmlComponent
{
  protected static $propertiesClass = NavigationPathProperties::class;

  public $cssClassName = 'breadcrumb';
  /** @var NavigationPathProperties */
  public $props;

  protected $containerTag = 'ol';

  protected function render ()
  {
    $prop = $this->props;

    $this->beginContent ();

    $navigation = $prop->navigation;
    if (!$navigation) return;
    $path      = $navigation->getCurrentTrail ();
    $showIcons = $prop->showIcons;

    echo html (
      map ($path, function (NavigationLinkInterface $link) use ($showIcons) {
        $url = $link->isGroup () && !isset ($link->defaultURI) ? null : $link->url ();
        return [
          h ('li', [
            h ('a', [
              'href' => $url,
            ], [
              when ($link->icon () && $showIcons, h ('i.' . $link->icon ())),
              $link->title (),
            ]),
          ]),
        ];
      })
    );
  }

}


