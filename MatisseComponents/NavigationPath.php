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
}

class NavigationPath extends HtmlComponent
{
  protected static $propertiesClass = NavigationPathProperties::class;

  public $cssClassName = 'breadcrumb';

  protected $containerTag = 'ol';

  protected function render ()
  {
    $attr = $this->props;

    $this->beginContent ();

    $navigation = $attr->navigation;
    if (!$navigation) return;
    $path = $navigation->getCurrentTrail ();

    echo html (
      map ($path, function (NavigationLinkInterface $link) {
        $url = $link->isGroup () && !isset ($link->defaultURI) ? null : $link->url ();
        return [
          h ('li', [
            h ('a', [
              'href' => $url,
            ], [
              when ($link->icon (), h ('i.' . $link->icon ())),
              $link->title (),
            ]),
          ]),
        ];
      })
    );
  }

}


