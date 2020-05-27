<?php
namespace Electro\Plugins\MatisseComponents;

use Electro\Interfaces\Navigation\NavigationInterface;
use Electro\Interfaces\Navigation\NavigationLinkInterface;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\TypeSystem\type;

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
  /**
   * @var string
   */
  public $urlPrefix = '';
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
        map ($path, function (NavigationLinkInterface $link) use ($showIcons, $prop) {
          $url = $link->isGroup () && !isset ($link->defaultURI)
            ? null : (empty($prop->urlPrefix) ? $link->url () : "$prop->urlPrefix/" . $link->url ());
          return [
            h ('li', [
              'class' => when ($link->isCurrent (), 'active'),
            ], [
              h ('a', [
                'href' => $url,
              ], [
                when ($link->icon () && $showIcons, h ('i', ['class' => $link->icon ()])),
                $link->title (),
              ]),
            ]),
          ];
        }),
      ]
    );
  }

}


