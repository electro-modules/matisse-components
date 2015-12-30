<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Components\Internal\Metadata;
use Selenia\Matisse\Exceptions\ComponentException;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\TypeSystem\is;
use Selenia\Matisse\Properties\TypeSystem\type;

class TabsProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $containerCssClass = '';
  /**
   * @var string
   */
  public $data = type::data;
  /**
   * @var bool
   */
  public $disabled = false;
  /**
   * @var string
   */
  public $labelField = 'label';
  /**
   * @var bool
   */
  public $lazyCreation = false;
  /**
   * @var Metadata|null
   */
  public $pageTemplate = type::content;
  /**
   * @var Metadata|null
   */
  public $pages = type::content;
  /**
   * > **Note:** -1 to not preselect any tab.
   * @var int
   */
  public $selected_index = 0;
  /**
   * @var string
   */
  public $tabAlign = ['left', is::enum, ['left', 'center', 'right']];
  /**
   * @var string
   */
  public $value = '';
  /**
   * @var string
   */
  public $valueField = 'value';
}

class TabsData
{
  public $disabled;
  public $icon;
  public $id;
  public $inactive;
  public $label;
  public $url;
  public $value;
}

class Tabs extends HtmlComponent
{
  protected static $propertiesClass = TabsProperties::class;

  /** @var TabsProperties */
  public $props;

  protected $autoId = true;
  /**
   * Indicates if the component contains tab-pages.
   * @var boolean
   */
  protected $hasPages;

  private $count = 0;
  private $selIdx;

  protected function render ()
  {
    $prop = $this->props;

    $this->selIdx = $prop->selected_index;
    $pages        = $this->getChildren ('pages');
    if (!empty($pages)) {
      //create data source for tabs from tab-pages defined on the source markup
      $data = [];
      /** @var TabPage $tabPage */
      foreach ($pages as $idx => $tabPage) {
        $t           = new TabsData();
        $t->id       = $tabPage->props->id;
        $t->value    = either ($tabPage->props->value, $idx);
        $t->label    = $tabPage->props->label;
        $t->icon     = $tabPage->props->icon;
        $t->inactive = $tabPage->inactive;
        $t->disabled = $tabPage->props->disabled;
        $t->url      = $tabPage->props->url;
        $data[]      = $t;
      }
      $propagateDataSource = false;
    }
    else {
      $data                = $prop->data;
      $propagateDataSource = true;
    }
    if (!empty($data)) {
      $template = $prop->pageTemplate;
      if (isset($template)) {
        if (isset($pages))
          throw new ComponentException($this,
            "You may not define both the <b>p:page-template</b> and the <b>p:pages</p> parameters.");
        $this->hasPages = true;
      }
      if ($propagateDataSource)
        $this->contextualModel = $data;
      $value = either ($prop->value, $this->selIdx);
      foreach ($data as $idx => $record) {
        if (!get ($record, 'inactive')) {
          $isSel = get ($record, $prop->valueField) === $value;
          if ($isSel)
            $this->selIdx = $this->count;
          ++$this->count;
          //create tab
          $tab               = new Tab($this->context, [
            'id'       => $prop->id . 'Tab' . $idx,
            'name'     => $prop->id,
            'value'    => get ($record, $prop->valueField),
            'label'    => get ($record, $prop->labelField),
            'url'      => get ($record, 'url'),
            //'class'         => $this->style()->tab_class,
            //'css_class'     => $this->style()->tab_css_class,
            'disabled' => get ($record, 'disabled') || $prop->disabled,
            'selected' => false//$isSel
          ], [
            //'width'         => $this->style()->tab_width,
            //'height'        => $this->style()->tab_height,
            'icon' => get ($record, 'icon'),
            //'icon_align'    => $this->style()->tab_icon_align
          ]);
          $tab->container_id = $prop->id;
          $this->addChild ($tab);
          //create tab-page
          $newTemplate = isset($template) ? clone $template : null;
          if (isset($template)) {
            $page = new TabPage($this->context, [
              'id'            => get ($record, 'id', $prop->id . 'Page' . $idx),
              'label'         => get ($record, $prop->labelField),
              'icon'          => get ($record, 'icon'),
              'content'       => $newTemplate,
              'lazy_creation' => $prop->lazyCreation,
            ]);
            $newTemplate->attachTo ($page);
            $this->addChild ($page);
          }
        }
      }
      if (!empty($pages)) {
        $this->addChildren ($pages);
        if ($this->selIdx >= 0)
          $pages[$this->selIdx]->props->selected = true;
        $this->setupSet ($pages);
        $this->hasPages = true;
      }
    }

    //--------------------------------

    $this->begin ('fieldset', [
      'class' => enum (' ', 'tabGroup', $prop->tabAlign ? 'align_' . $prop->tabAlign : ''),
    ]);
    $this->beginContent ();
    $p = 0;
    if ($prop->tabAlign == 'right') {
      $selIdx   = $this->count - $this->selIdx - 1;
      $children = $this->getChildren ();
      for ($i = count ($children) - 1; $i >= 0; --$i) {
        $child = $children[$i];
        if ($child->className == 'Tab') {
          $s                         = $selIdx == $p++;
          $child->props->selected = $s;
          if ($s) $selName = $child->props->id;
          $child->run ();
        }
      }
    }
    else {
      $selIdx = $this->selIdx;
      foreach ($this->getChildren () as $child)
        if ($child->className == 'Tab') {
          $s                         = $selIdx == $p++;
          $child->props->selected = $s;
          if ($s) $selName = $child->props->id;
          $child->run ();
        }
    }
    $this->end ();

    if ($this->hasPages) {
      $this->begin ('div', [
        'id'    => $prop->id . 'Pages',
        'class' => enum (' ', 'TabsContainer', $prop->containerCssClass),
      ]);
      $this->beginContent ();
      $p      = 0;
      $selIdx = $this->selIdx;
      foreach ($this->getChildren () as $child)
        if ($child->className == 'TabPage') {
          $s                         = $selIdx == $p++;
          $child->props->selected = $s;
          if ($s) $sel = $child;
          $child->run ();
        }
      $this->end ();
      if (isset($sel))
        $this->tag ('script', null, "Tab_change(\$f('{$selName}Field'),'{$this->props()->id}')");
    }
  }

}

