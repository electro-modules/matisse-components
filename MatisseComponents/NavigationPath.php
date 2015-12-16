<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Interfaces\Navigation\NavigationInterface;
use Selenia\Interfaces\Navigation\NavigationLinkInterface;
use Selenia\Matisse\Attributes\VisualComponentAttributes;
use Selenia\Matisse\AttributeType;
use Selenia\Matisse\VisualComponent;

class NavigationPathAttributes extends VisualComponentAttributes
{
  /** @var NavigationInterface */
  public $navigation;

  protected function typeof_navigation () { return AttributeType::DATA; }
}

class NavigationPath extends VisualComponent
{
  protected $containerTag = 'ol';

  public $cssClassName = 'breadcrumb';

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


