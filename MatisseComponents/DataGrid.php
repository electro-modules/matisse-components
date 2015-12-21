<?php
namespace Selenia\Plugins\MatisseComponents;

use Selenia\Matisse\Components\Base\HtmlComponent;
use Selenia\Matisse\Components\Internal\ContentProperty;
use Selenia\Matisse\Properties\Base\HtmlComponentProperties;
use Selenia\Matisse\Properties\Types\is;
use Selenia\Matisse\Properties\Types\type;

/**
 * A dataGrid component, using the DataTables.net jQuery widget.
 *
 * Note: if responsive problems occur, try: $( $.fn.dataTable.tables(true) ).DataTable().responsive.recalc();
 */
class DataGridProperties extends HtmlComponentProperties
{
  /**
   * @var string
   */
  public $action = '';
  /**
   * @var bool
   */
  public $ajax = false;
  /**
   * @var string
   */
  public $as = '';
  /**
   * @var bool
   */
  public $clickable = false;
  /**
   * Attributes for each column:
   * - type="row-selector|action|input". Note: if set, clicks on the column have no effect.
   * - align="left|center|right"
   * - title="t" (t is text)
   * - width="n|n%" (n is a number)
   * @var ContentProperty[]
   */
  public $column = type::collection;
  /**
   * @var mixed
   */
  public $data = type::data;
  /**
   * @var string
   */
  public $detailUrl = '';
  /**
   * @var bool
   */
  public $info = true;
  /**
   * @var string
   */
  public $initScript = '';
  /**
   * @var string
   */
  public $lang = 'en-US';
  /**
   * @var bool
   */
  public $lengthChange = true;
  /**
   * @var string
   */
  public $lengthChangeScript = '';
  /**
   * @var string A string representation of an array of number of rows to display.
   */
  public $lengthMenu = '[5,10,15,20,50,100]';
  /**
   * @var string
   */
  public $onClick = '';
  /**
   * @var string
   */
  public $onClickGoTo = '';
  /**
   * @var bool
   */
  public $ordering = true;
  /**
   * @var string Number of rows to display.
   * It may be a numeric constant or a javascript expression.
   */
  public $pageLength = '10';
  /**
   * @var bool
   */
  public $paging = true;
  /**
   * @var string
   */
  public $pagingType = ['simple_numbers', is::enum, ['simple', 'simple_numbers', 'full', 'full_numbers']];
  /**
   * @var ContentProperty|null
   */
  public $plugins = type::content;
  /**
   * @var string
   */
  public $responsive = 'false';
  /**
   * @var bool
   */
  public $searching = true;
}

class DataGrid extends HtmlComponent
{
  const PUBLIC_URI = 'modules/selenia-plugins/matisse-components';

  protected static $MIN_PAGE_ITEMS = [
    'simple'         => 0, // n/a
    'full'           => 0, // n/a
    'simple_numbers' => 3,
    'full_numbers'   => 5,
  ];

  public    $cssClassName = 'box';
  protected $autoId       = true;

  private $enableRowClick = false;

  /**
   * Returns the component's attributes.
   * @return DataGridProperties
   */
  public function props ()
  {
    return $this->props;
  }

  /**
   * Creates an instance of the component's attributes.
   * @return DataGridProperties
   */
  public function newProperties ()
  {
    return new DataGridProperties($this);
  }

  protected function render ()
  {
    $attr                  = $this->props ();
    $this->contextualModel = [];

    $this->page->addInlineScript (<<<JAVASCRIPT
function check(ev,id,action) {
    action = action || 'check';
    ev.stopPropagation();
    $.post(location.href, { _action: action, id: id });
}
JAVASCRIPT
      , 'datagridInit');
    $id          = $attr->id;
    $minPagItems = self::$MIN_PAGE_ITEMS [$attr->pagingType];
    $PUBLIC_URI  = self::PUBLIC_URI;
    $language    = $attr->lang != 'en-US'
      ? "language:     { url: '$PUBLIC_URI/js/datatables/{$attr->lang}.json' }," : '';

    $this->setupColumns ($attr->column);
    $this->enableRowClick = $this->isAttributeSet ('onClick') || $this->isAttributeSet ('onClickGoTo');
    $paging               = boolToStr ($attr->paging);
    $searching            = boolToStr ($attr->searching);
    $ordering             = boolToStr ($attr->ordering);
    $info                 = boolToStr ($attr->info);
    $responsive           = $attr->responsive;
    $lengthChange         = boolToStr ($attr->lengthChange);
    $this->beginCapture ();
    $this->renderChildren ('plugins');
    $plugins = ob_get_clean ();

    // AJAX MODE

    if ($attr->ajax) {
      $url                  = $_SERVER['REQUEST_URI'];
      $action               = $attr->action;
      $detailUrl            = $attr->detailUrl;
      $this->enableRowClick = $attr->clickable;
      $this->page->addInlineDeferredScript (<<<JavaScript
$('#$id table').dataTable({
  serverSide:   true,
  paging:       $paging,
  lengthChange: $lengthChange,
  searching:    $searching,
  ordering:     $ordering,
  info:         $info,
  autoWidth:    false,
  responsive:   $responsive,
  pageLength:   $attr->pageLength,
  lengthMenu:   $attr->lengthMenu,
  pagingType:   '$attr->pagingType',
  $language
  $plugins
  ajax: {
     url: '$url',
     type: 'POST',
     data: {
        _action: '$action'
    }
   },
  initComplete: function() {
    $attr->initScript
    $('#$id .col-sm-6').attr('class', 'col-xs-6');
    $('#$id').show();
  }
}).on ('length.dt', function (e,cfg,len) {
  $attr->lengthChangeScript
}).on ('click', 'tbody tr', function () {
    location.href = '$detailUrl' + $(this).attr('rowid');
});
JavaScript
      );
    }
    else {

      // IMMEDIATE MODE

      $this->page->addInlineDeferredScript (<<<JavaScript
$('#$id table').dataTable({
  paging:       $paging,
  lengthChange: $lengthChange,
  searching:    $searching,
  ordering:     $ordering,
  info:         $info,
  autoWidth:    false,
  responsive:   $responsive,
  pageLength:   $attr->pageLength,
  lengthMenu:   $attr->lengthMenu,
  pagingType:   '$attr->pagingType',
  $language
  $plugins
  initComplete: function() {
    $attr->initScript
    $('#$id .col-sm-6').attr('class', 'col-xs-6');
    $('#$id').show();
  },
  drawCallback: function() {
    var p = $('#$id .pagination');
    p.css ('display', p.children().length <= $minPagItems ? 'none' : 'block');
  }
}).on ('length.dt', function (e,cfg,len) {
  $attr->lengthChangeScript
});
JavaScript
      );
      if (isset($attr->data)) {
        $dataIter = iterator ($attr->data);
        $dataIter->rewind ();
        $valid = $dataIter->valid ();
      }
      else $valid = false;
      if ($valid) {
        $this->parseIteratorExp ($attr->as, $idxVar, $itVar);
        $columnsCfg = $attr->column;
        $this->begin ('table', [
          'class' => enum (' ', 'table table-striped', $this->enableRowClick ? 'table-clickable' : ''),
        ]);
        $this->beginContent ();
        $this->renderHeader ($columnsCfg);
        if (!$attr->ajax) {
          $idx = 0;
          foreach ($dataIter as $i => $v) {
            if ($idxVar)
              $this->contextualModel[$idxVar] = $i;
            $this->contextualModel[$itVar] = $v;
            $this->renderRow ($idx++, $columnsCfg);
          }
        }
        $this->end ();
      }
      else $this->renderSet ($this->getChildren ('no_data'));
    }
  }

  private function renderHeader (array $columns)
  {
    $id = $this->props ()->id;
    foreach ($columns as $k => $col) {
      $w = $col->props ()->width;
      if (strpos ($w, '%') === false && $this->page->browserIsIE)
        $w -= 3;
      $this->tag ('col', isset($w) ? ['width' => $w] : null);
    }
    $this->begin ('thead');
    foreach ($columns as $k => $col) {
      $al = $col->props ()->get ('header_align', $col->props ()->align);
      if (isset($al))
        $this->page->addInlineCss ("#$id .h$k{text-align:$al}");
      $this->begin ('th');
      $this->setContent ($col->props ()->title);
      $this->end ();
    }
    $this->end ();
  }

  private function renderRow ($idx, array $columns)
  {
    $this->begin ('tr');
    $this->attr ('class', 'R' . ($idx % 2));
    if ($this->enableRowClick) {
      if ($this->isAttributeSet ('onClickGoTo')) {
        $onclick = $this->evaluateAttr ('onClickGoTo');
        $onclick = "go('$onclick',event)";
      }
      else $onclick = $this->evaluateAttr ('onClick');
      $onclick = "if (!$(event.target).closest('[data-nck]').length) $onclick";
      $this->attr ('onclick', $onclick);
    }
    foreach ($columns as $k => $col) {
      $col->databind ();
      $colAttrs = $col->props ();
      $colType  = property ($colAttrs, 'type', '');
      $al       = property ($colAttrs, 'align');;
      $isText = empty($colType);
      $this->begin ('td');
      if ($colType != '')
        $this->attr ('class', enum (' ', "ta-$al",
          $colType == 'row-selector' ? 'rh' : '',
          $colType == 'field' ? 'field' : ''
        ));
      if ($isText) {
        $this->beginContent ();
        $col->renderChildren ();
      }
      else {
        if ($this->enableRowClick)
          $this->attr ('data-nck');
        $this->beginContent ();
        $col->renderChildren ();
      }
      $this->end ();
    }
    $this->end ();
  }

  private function setupColumns (array $columns)
  {
    $id     = $this->props ()->id;
    $styles = '';
    foreach ($columns as $k => $col) {
      $al = $col->props ()->align;
      if (isset($al))
        $styles .= "#$id .c$k{text-align:$al}";
      $al = $col->props ()->header_align;
      if (isset($al))
        $styles .= "#$id .h$k{text-align:$al}";
    }
    $this->page->addInlineCss ($styles);
  }

}
