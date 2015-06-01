<?php
namespace Selene\Matisse\Components;

use Selene\Matisse\AttributeType;
use Selene\Matisse\VisualComponentAttributes;
use Selene\Matisse\DataSet;
use Selene\Matisse\Exceptions\ComponentException;
use Selene\Matisse\VisualComponent;

class TabsAttributes extends VisualComponentAttributes
{
  public $selected_index    = 0; //-1 to not preselect any tab
  public $value;
  public $disabled          = false;
  public $pages;
  public $pageTemplate;
  public $data;
  public $valueField        = 'value';
  public $labelField        = 'label';
  public $lazyCreation      = false;
  public $tabAlign          = 'left';
  public $containerCssClass = '';

  protected function typeof_selectedIndex () { return AttributeType::NUM; }

  protected function typeof_value () { return AttributeType::TEXT; }

  protected function typeof_disabled () { return AttributeType::BOOL; }

  protected function typeof_pages () { return AttributeType::SRC; }

  protected function typeof_pageTemplate () { return AttributeType::SRC; }

  protected function typeof_data () { return AttributeType::DATA; }

  protected function typeof_valueField () { return AttributeType::TEXT; }

  protected function typeof_labelField () { return AttributeType::TEXT; }

  protected function typeof_lazyCreation () { return AttributeType::BOOL; }

  protected function typeof_tabAlign () { return AttributeType::TEXT; }

  protected function typeof_containerCssClass () { return AttributeType::TEXT; }
}

class TabsData
{
  public $id;
  public $value;
  public $label;
  public $url;
  public $icon;
  public $inactive;
  public $disabled;
}

class Tabs extends VisualComponent
{

  protected $autoId = true;

  /**
   * Indicates if the component contains tab-pages.
   * @var boolean
   */
  protected $hasPages;

  private $count = 0;
  private $selIdx;

  /**
   * Returns the component's attributes.
   * @return TabsAttributes
   */
  public function attrs ()
  {
    return $this->attrsObj;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return TabsAttributes
   */
  public function newAttributes ()
  {
    return new TabsAttributes($this);
  }

  protected function render ()
  {
    $this->selIdx = $this->attrs ()->selected_index;
    convertToInt ($selIdx);
    $pages = $this->getChildren ('pages');
    if (!empty($pages)) {
      //create data source for tabs from tab-pages defined on the source markup
      $data = [];
      foreach ($pages as $idx => $tabPage) {
        $t           = new TabsData();
        $t->id       = $tabPage->attrs ()->id;
        $t->value    = either ($tabPage->attrs ()->value, $idx);
        $t->label    = $tabPage->attrs ()->label;
        $t->icon     = $tabPage->attrs ()->icon;
        $t->inactive = $tabPage->inactive;
        $t->disabled = $tabPage->attrs ()->disabled;
        $t->url      = $tabPage->attrs ()->url;
        $data[]      = $t;
      }
      $data                = new DataSet($data);
      $propagateDataSource = false;
    }
    else {
      $data                = $this->attrs ()->data;
      $propagateDataSource = true;
    }
    if (!empty($data)) {
      $template = $this->attrs ()->pageTemplate;
      if (isset($template)) {
        if (isset($pages))
          throw new ComponentException($this,
            "You may not define both the <b>p:page-template</b> and the <b>p:pages</p> parameters.");
        $this->hasPages = true;
      }
      if ($propagateDataSource)
        $this->defaultDataSource = $data;
      $value = either ($this->attrs ()->value, $this->selIdx);
      foreach ($data as $idx => $record) {
        if (!get ($record, 'inactive')) {
          $isSel = get ($record, $this->attrs ()->valueField) === $value;
          if ($isSel)
            $this->selIdx = $this->count;
          ++$this->count;
          //create tab
          $tab               = new Tab($this->context, [
            'id'       => $this->attrs ()->id . 'Tab' . $idx,
            'name'     => $this->attrs ()->id,
            'value'    => get ($record, $this->attrs ()->valueField),
            'label'    => get ($record, $this->attrs ()->labelField),
            'url'      => get ($record, 'url'),
            //'class'         => $this->style()->tab_class,
            //'css_class'     => $this->style()->tab_css_class,
            'disabled' => get ($record, 'disabled') || $this->attrs ()->disabled,
            'selected' => false//$isSel
          ], [
            //'width'         => $this->style()->tab_width,
            //'height'        => $this->style()->tab_height,
            'icon' => get ($record, 'icon'),
            //'icon_align'    => $this->style()->tab_icon_align
          ]);
          $tab->container_id = $this->attrs ()->id;
          $this->addChild ($tab);
          //create tab-page
          $newTemplate = isset($template) ? clone $template : null;
          if (isset($template)) {
            $page = new TabPage($this->context, [
              'id'            => get ($record, 'id', $this->attrs ()->id . 'Page' . $idx),
              'label'         => get ($record, $this->attrs ()->labelField),
              'icon'          => get ($record, 'icon'),
              'content'       => $newTemplate,
              'lazy_creation' => $this->attrs ()->lazyCreation
            ]);
            $newTemplate->attachTo ($page);
            $this->addChild ($page);
          }
        }
      }
      if (!empty($pages)) {
        $this->addChildren ($pages);
        if ($this->selIdx >= 0)
          $pages[$this->selIdx]->attrs ()->selected = true;
        $this->setupSet ($pages);
        $this->hasPages = true;
      }
    }

    //--------------------------------

    $this->beginTag ('fieldset', [
      'class' => enum (' ', 'tabGroup', $this->attrs ()->tabAlign ? 'align_' . $this->attrs ()->tabAlign : '')
    ]);
    $this->beginContent ();
    $p = 0;
    if ($this->attrs ()->tabAlign == 'right') {
      $selIdx = $this->count - $this->selIdx - 1;
      for ($i = count ($this->children) - 1; $i >= 0; --$i) {
        $child = $this->children[$i];
        if ($child->className == 'Tab') {
          $s                         = $selIdx == $p++;
          $child->attrs ()->selected = $s;
          if ($s) $selName = $child->attrs ()->id;
          $child->doRender ();
        }
      }
    }
    else {
      $selIdx = $this->selIdx;
      foreach ($this->children as $child)
        if ($child->className == 'Tab') {
          $s                         = $selIdx == $p++;
          $child->attrs ()->selected = $s;
          if ($s) $selName = $child->attrs ()->id;
          $child->doRender ();
        }
    }
    $this->endTag ();

    if ($this->hasPages) {
      $this->beginTag ('div', [
        'id'    => $this->attrs ()->id . 'Pages',
        'class' => enum (' ', 'TabsContainer', $this->attrs ()->containerCssClass)
      ]);
      $this->beginContent ();
      $p      = 0;
      $selIdx = $this->selIdx;
      foreach ($this->children as $child)
        if ($child->className == 'TabPage') {
          $s                         = $selIdx == $p++;
          $child->attrs ()->selected = $s;
          if ($s) $sel = $child;
          $child->doRender ();
        }
      $this->endTag ();
      if (isset($sel))
        $this->addTag ('script', null, "Tab_change(\$f('{$selName}Field'),'{$this->attrs()->id}')");
    }
  }

}

