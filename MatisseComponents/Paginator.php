<?php

namespace Electro\Plugins\MatisseComponents;

use Electro\Interfaces\Http\Shared\CurrentRequestInterface;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Properties\Base\HtmlComponentProperties;

class PaginatorProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $itemClass = '';
  /**
   * @var string
   */
  public $linkClass = '';
  /**
   * @var string A sprintf-compatible format string, where the first parameter is the link URL, the second is either a
   *             '&' or a '?', and the third is the page number.
   */
  public $linkTemplate = '%s%sp=%d';
  /**
   * @var int
   */
  public $page = 0;
  /**
   * @var int
   */
  public $pageCount = 0;
  /**
   * @var bool Show &lt;&lt; and >> buttons even when disabled?
   */
  public $showDisabled = false;
  /**
   * @var int
   */
  public $total = 0;
}

class Paginator extends HtmlComponent
{
  const propertiesClass = PaginatorProperties::class;

  /** @var PaginatorProperties */
  public $props;

  protected $containerTag = 'nav';
  /**
   * @var CurrentRequestInterface
   */
  private $request;

  public function __construct (CurrentRequestInterface $request)
  {
    parent::__construct ();
    $this->request = $request;
  }

  protected function postRender ()
  {
    if ($this->props->total > 1)
      parent::postRender ();
  }

  protected function preRender ()
  {
    if ($this->props->total > 1)
      parent::preRender ();
  }

  protected function render ()
  {
    $prop = $this->props;

    $linkTpl = function ($uri, $sep, $page) {
      return sprintf ($this->props->linkTemplate, $uri, $sep, $page);
    };
    $SIZE    = floor (($prop->pageCount - 1) / 2);
    $page    = $prop->page;
    $total   = $prop->total;
    if ($total < 2) return;
    $uri   = preg_replace ('/&p=\d+/', '', $this->request->getAttribute('originalUri'));
    $sep   = strpos ($uri, '?') === false ? '?' : '&';
    $start = $page - $SIZE;
    $end   = $start + 2 * $SIZE;
    if ($start < 1) {
      $d     = -$start + 1;
      $start += $d;
      $end   += $d;
    }
    if ($end > $total) {
      $d     = $end - $total;
      $end   -= $d;
      $start -= $d;
      if ($start < 1) $start = 1;
    }
    $this->begin ('ul');
    $this->attr ('class', 'pagination');
    if ($start > 1 || $prop->showDisabled) {
      $st = $start - 1;
      if ($st < 1) $st = 1;
      $this->begin ('li', ['class' => ($start > 1 ? '' : 'disabled ') . $prop->itemClass]);
      $this->tag ('a',
        ['href' => $start > 1 ? $linkTpl($uri, $sep, $st) : 'javascript:void(0)', 'class' => "prev $prop->linkClass"],
        '&laquo;');
      $this->end ();
      $this->beginContent ();
    }
    for ($n = $start; $n <= $end; ++$n) {
      $this->begin ('li', ['class' => ($n == $page ? 'active' : '') . ' ' . $prop->itemClass]);
      $this->tag ('a', ['href' => $linkTpl($uri, $sep, $n), 'class' => $prop->linkClass], $n);
      $this->end ();
    }
    if ($end < $total || $prop->showDisabled) {
      $this->beginContent ();
      $ed = $end + 1;
      if ($ed > $total) $ed = $total;
      $this->begin ('li', ['class' => ($end < $total ? '' : 'disabled ') . $prop->itemClass]);
      $this->tag ('a',
        ['href'  => $end < $total ? $linkTpl($uri, $sep, $ed) : 'javascript:void(0)', 'class' => "next $prop->linkClass",
        ],
        '&raquo;');
      $this->end ();
    }
    $this->end ();
  }

}

