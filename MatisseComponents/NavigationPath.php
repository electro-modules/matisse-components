<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Interfaces\Navigation\NavigationInterface;
use Selenia\Interfaces\Navigation\NavigationLinkInterface;
use Selenia\Matisse\Attributes\Base\VisualComponentAttributes;
use Selenia\Matisse\Attributes\DSL\type;
use Selenia\Matisse\Components\Base\VisualComponent;

class NavigationPathAttributes extends VisualComponentAttributes
{
  /**
   * @var NavigationInterface
   */
  public $navigation = type::data;
}

class NavigationPath extends VisualComponent
{
  public $cssClassName = 'breadcrumb';

  protected $containerTag = 'ol';

  /**
   * @return NavigationPathAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * @return NavigationPathAttributes
   */
  public function newAttributes ()
  {
    return new NavigationPathAttributes($this);
  }

  protected function render ()
  {
    $attr = $this->attrs ();

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


