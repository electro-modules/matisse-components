<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\Attributes\VisualComponentAttributes;
use Selene\Matisse\VisualComponent;

class PaginatorAttributes extends VisualComponentAttributes
{

  public $page;
  public $total;
  public $uri;
  public $pageCount;

  protected function typeof_page () { return AttributeType::NUM; }

  protected function typeof_total () { return AttributeType::NUM; }

  protected function typeof_uri () { return AttributeType::TEXT; }

  protected function typeof_pageCount () { return AttributeType::NUM; }
}

class Paginator extends VisualComponent
{

  /**
   * Returns the component's attributes.
   * @return PaginatorAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return PaginatorAttributes
   */
  public function newAttributes ()
  {
    return new PaginatorAttributes($this);
  }

  protected function preRender ()
  {
    if ($this->attrs ()->total > 1)
      parent::preRender ();
  }

  protected function postRender ()
  {
    if ($this->attrs ()->total > 1)
      parent::postRender ();
  }

  protected function render ()
  {
    $SIZE  = floor (($this->attrs ()->pageCount - 1) / 2);
    $page  = $this->attrs ()->page;
    $total = $this->attrs ()->total;
    if ($total < 2) return;
    $uri   = $this->attrs ()->uri;
    $start = $page - $SIZE;
    $end   = $start + 2 * $SIZE;
    if ($start < 1) {
      $d = -$start + 1;
      $start += $d;
      $end += $d;
    }
    if ($end > $total) {
      $d = $end - $total;
      $end -= $d;
      $start -= $d;
      if ($start < 1) $start = 1;
    }
    $this->beginTag ('div');
    if ($start > 1) {
      $st = $start - 1;
      if ($st < 1) $st = 1;
      $this->addTag ('a', ['href' => "$uri&p=$st", 'class' => 'prev']);
      $this->beginContent ();
      echo '<span>...</span>';
    }
    for ($n = $start; $n <= $end; ++$n)
      $this->addTag ('a', ['href' => "$uri&p=$n", 'class' => $n == $page ? 'selected' : ''], $n);
    if ($end < $total) {
      $this->beginContent ();
      echo '<span>...</span>';
      $ed = $end + 1;
      if ($ed > $total) $ed = $total;
      $this->addTag ('a', ['href' => "$uri&p=$ed", 'class' => 'next']);
    }
    $this->endTag ();
  }

}

