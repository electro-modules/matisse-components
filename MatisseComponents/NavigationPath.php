<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Interfaces\Navigation\NavigationInterface;
use Electro\Interfaces\Navigation\NavigationLinkInterface;
use Electro\Plugins\Matisse\Components\Base\HtmlComponent;
use Electro\Plugins\Matisse\Properties\Base\HtmlComponentProperties;
use Electro\Plugins\Matisse\Properties\TypeSystem\type;

class NavigationPathProperties extends HtmlComponentProperties
{
  /**
   * @var NavigationInterface
   */
  public $navigation = type::data;
  /**
   * @var string HTML to be prependend to the navigation content.
   */
  public $prepend = type::content;
  /**
   * @var bool
   */
  public $showIcons = false;
}

class NavigationPath extends HtmlComponent
{
  const propertiesClass = NavigationPathProperties::class;

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
    $path      = $navigation->getVisibleTrail ();
    $showIcons = $prop->showIcons;

    echo html ([
      $prop->prepend ? $prop->prepend->run () : null,
      map ($path, function (NavigationLinkInterface $link) use ($showIcons) {
        $url = $link->isGroup () && !isset ($link->defaultURI) ? null : $link->url ();
        return [
          h ('li', [
            h ('a', [
              'href' => $url,
            ], [
              when ($link->icon () && $showIcons, h ('i', ['class' => $link->icon ()])),
              $link->title (),
            ]),
          ]),
        ];
      })
      ]
    );
  }

}


