<?php
namespace Electro\Plugins\MatisseComponents;

use Matisse\Components\Base\CompositeComponent;
use Matisse\Components\Base\HtmlComponent;
use Matisse\Components\DocumentFragment;
use Matisse\Components\Macro\Macro;
use Matisse\Components\Metadata;
use Matisse\Exceptions\ComponentException;
use Matisse\Properties\Base\HtmlComponentProperties;
use Matisse\Properties\Base\MetadataProperties;
use Matisse\Properties\TypeSystem\is;
use Matisse\Properties\TypeSystem\type;

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
   * @var Metadata|null
   */
  public $actions = type::content;
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
   * - headerAlign="left|center|right"
   * - title="t" (t is text)
   * - width="n|n%" (n is a number)
   * - icon="CSS class list"
   * - notSortable
   * - if={bool exp}. Note: rhe column is displayed when the value of this property is <kbd>true</kbd> or
   * <kbd>null</kbd> (not set).
   *
   * @var Metadata[]
   */
  public $column = [type::collection, is::of, type::content];
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
   * @var Metadata|null
   */
  public $initScript = [type::content];
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
   * @var bool
   */
  public $multiSearch = false;
  /**
   * @var Metadata|null
   */
  public $noData = type::content;
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
  public $pageLength = '15';
  /**
   * @var bool
   */
  public $paging = true;
  /**
   * @var string
   */
  public $pagingType = ['simple_numbers', is::enum, ['simple', 'simple_numbers', 'full', 'full_numbers']];
  /**
   * @var \Matisse\Components\Metadata|null
   */
  public $plugins = type::content;
  /**
   * @var string
   */
  public $responsive = 'false';
  /**
   * @var string route for a controller
   */
  public $rowReorder = 'false';
  /**
   * @var int Set to true if a column displaying the row number should be prepended to the table's columns.
   */
  public $rowSelector = false;
  /**
   * @var int The width of the row selector column.
   */
  public $rowSelectorWidth = 54;
  /**
   * @var bool
   */
  public $searching = true;
  /**
   * One or more CSS classes to add to the rendered table.
   * > <p>**Note:** for Bootstrap, the following classes are supported: `table table-striped table-bordered`
   *
   * @var string
   */
  public $tableClass = 'table table-striped table-bordered';
}

class DataGrid extends HtmlComponent
{
  const PUBLIC_URI      = 'modules/electro-modules/matisse-components';
  const propertiesClass = DataGridProperties::class;
  protected static $MIN_PAGE_ITEMS = [
    'simple'         => 0, // n/a
    'full'           => 0, // n/a
    'simple_numbers' => 3,
    'full_numbers'   => 5,
  ];
  /** @var DataGridProperties */
  public $props;

  protected $autoId = true;
  /** @var Metadata[]|null */
  private $cachedColumns = null;
  private $enableRowClick = false;

  protected function init ()
  {
    parent::init ();
    $this->context
      ->getAssetsService ()
      ->addStylesheet ('lib/datatables.net-bs/css/dataTables.bootstrap.min.css', true)
      ->addStylesheet ('lib/datatables.net-responsive-bs/css/responsive.bootstrap.min.css')
      ->addStylesheet ('lib/datatables.net-buttons-bs/css/buttons.bootstrap.min.css')
      ->addStylesheet ('https://cdn.datatables.net/rowreorder/1.2.3/css/rowReorder.dataTables.min.css')
      ->addScript ('lib/datatables.net/js/jquery.dataTables.min.js')
      ->addScript ('lib/datatables.net-bs/js/dataTables.bootstrap.min.js')
      ->addScript ('lib/datatables.net-responsive/js/dataTables.responsive.min.js')
      ->addScript ('lib/datatables.net-buttons/js/dataTables.buttons.min.js')
      ->addScript ('lib/datatables.net-buttons-bs/js/buttons.bootstrap.min.js')
      ->addScript ('lib/datatables.net-buttons/js/buttons.print.min.js')
      ->addScript ('https://cdn.datatables.net/rowreorder/1.2.3/js/dataTables.rowReorder.min.js');
  }

  protected function render ()
  {
    $prop      = $this->props;
    $context   = $this->context;
    $viewModel = $this->getViewModel ();

    $context->getAssetsService ()->addInlineScript (<<<JS
function check(ev,id,action) {
  action = action || 'check';
  ev.stopPropagation();
  $.post(location.href, { _action: action, id: id });
}
$.extend(true, $.fn.dataTable.Buttons.defaults, {
  dom: {
    button: {
      className: 'btn'
    },
  }
});
function dataGridMultiSearch (ev) {
  var table = $($(ev.target).parents('table')[0]).DataTable()
    , value = ev.target.value
    , idx = ev.target.getAttribute('data-col');
  table.column(idx).search(value).draw();
}
JS
      , 'datagridInit');
    $id          = $prop->id;
    $minPagItems = self::$MIN_PAGE_ITEMS [$prop->pagingType];
    $PUBLIC_URI  = self::PUBLIC_URI;
    $language    = $prop->lang != 'en-US'
      ? "language:     { url: '$PUBLIC_URI/js/datatables/{$prop->lang}.json' }," : '';

    $this->setupColumns ();
    $this->enableRowClick = $this->isPropertySet ('onClick') || $this->isPropertySet ('onClickGoTo');
    $paging               = boolToStr ($prop->paging);
    $searching            = boolToStr ($prop->searching);
    $ordering             = boolToStr ($prop->ordering);
    $info                 = boolToStr ($prop->info);
    $responsive           = $prop->responsive;
    $rowReorder           = boolToStr (($prop->rowReorder == 'false') ? false : true);

    if ($responsive)
      $responsive = "{
  details: {
    type: 'inline'
  }
}";
    $lengthChange = boolToStr ($prop->lengthChange);

    ob_start ();
    $this->runChildren ('plugins');
    $plugins = ob_get_clean ();

    $this->beginContent ();

    $layout  =
      "<'row'<'col-xs-4'f><'col-xs-8'<'dataTables_buttons'B>>><'row'<'col-xs-12'tr>><'row'<'col-xs-7'li><'col-xs-5'p>>";
    $buttons = '';
    if ($prop->actions) {
      $btns = [];
      $prop->actions->preRun ();
      foreach ($prop->actions->getChildren () as $btn) {
        if (!$btn instanceof Button) {
          if ($btn instanceof CompositeComponent) {
            $btn->preRun ();
            $b = $btn->provideShadowDOM ()->getFirstChild ();
            if ($b instanceof DocumentFragment)
              $b = $b->getFirstChild ();
            if ($b instanceof Macro) {
              $ch = $b->getChildren ();
              foreach ($ch as $b)
                if ($b instanceof Button) break;
            }
            if ($b instanceof Button) {
              $b->preRun ();
              $btn = $b;
              goto addBtn;
            }
          }
          throw new ComponentException($this, "Invalid content for the <kbd>actions</kbd> property.
<p>You can only use Button instances or components whose skin contains a button component as the first child", true);
        }
        addBtn:
        $bp = $btn->props;
        if ($bp->action) $action = "selenia.doAction('$bp->action')";
        elseif ($bp->script) $action = $bp->script;
        elseif ($v = $btn->getComputedPropValue ('url')) $action = "location.href='$v'";
        else $action = '';
        $class  = enum (' ', $bp->class,
          $bp->icon ? 'with-icon' : ''
        );
        $bLabel = $btn->getComputedPropValue ('label');
        $label  = $bp->icon ? "<i class=\"$bp->icon\"></i>$bLabel" : $bLabel;
        $btns[] = sprintf ("{className:'%s',text:'%s',action:function(e,dt,node,config){%s}}",
          $class, $label, $action);
      }
      $print   = "{extend: 'print', text: 'Print', autoPrint: false, className: 'printBtn hidden'}";
      $buttons = 'buttons:[' . implode (',', $btns) . ',' . $print . '],';
    }

    $initScript = '';

    if ($prop->multiSearch) {
      $initScript = <<<JS
var tfoot = this.find('tfoot')
, r = tfoot.find ('tr');
this.find('thead').append(r);
JS;
    }

    $notSortable = [];
    foreach ($this->getColumns () as $i => $col) {
      if ($col->props->notSortable)
        $notSortable[] = $i + ($this->props->rowSelector ? 1 : 0);
    }
    $columns = "columnDefs: [";
    if ($notSortable)
      $columns .= sprintf ('{ "orderable": false, targets: [%s] }', implode (',', $notSortable));
    $columns .= "],";

    if ($prop->initScript)
      $initScript .= (string)$prop->initScript;

    // AJAX MODE

    if ($prop->ajax) {
      $url                  = $_SERVER['REQUEST_URI'];
      $action               = $prop->action;
      $detailUrl            = $prop->detailUrl;
      $this->enableRowClick = $prop->clickable;
      $context->getAssetsService ()->addInlineScript (<<<JS
$('#$id table').dataTable({
  serverSide:   true,
  paging:       $paging,
  lengthChange: $lengthChange,
  searching:    $searching,
  ordering:     $ordering,
  info:         $info,
  autoWidth:    false,
  responsive:   $responsive,
  pageLength:   $prop->pageLength,
  lengthMenu:   $prop->lengthMenu,
  pagingType:   '$prop->pagingType',
  dom:          "$layout",
  $columns
  $language
  $plugins
  $buttons
  ajax: {
     url: '$url',
     type: 'POST',
     data: {
        _action: '$action'
    }
   },
  initComplete: function() {
    $initScript
    $('#$id').show();
  }
}).on ('length.dt', function (e,cfg,len) {
  $prop->lengthChangeScript
}).on ('click', 'tbody tr', function () {
    location.href = '$detailUrl' + $(this).attr('rowid');
});
JS
      );
      $valid = true;
    }
    else {

      // IMMEDIATE MODE

      $context->getAssetsService ()->addInlineScript (<<<JS
var tbl = $('#$id table').dataTable({
  paging:       $paging,
  lengthChange: $lengthChange,
  searching:    $searching,
  ordering:     $ordering,
  rowReorder:   $rowReorder,
  info:         $info,
  autoWidth:    false,
  responsive:   $responsive,
  pageLength:   $prop->pageLength,
  lengthMenu:   $prop->lengthMenu,
  pagingType:   '$prop->pagingType',
  dom:          "$layout",
  $columns
  $language
  $plugins
  $buttons
  initComplete: function() {
    $initScript
    $('#$id').show();
  },
  drawCallback: function() {
    var p = $('#$id .pagination');
    p.css ('display', p.children().length <= $minPagItems ? 'none' : 'block');
  }
}).on ('length.dt', function (e,cfg,len) {
  $prop->lengthChangeScript
});

function printFunction(){
    $('.printBtn').trigger('click')
};
JS
      );

      if ($rowReorder == 'true') {

        $this->context->getAssetsService ()->addInlineScript (<<<JS
      $(document).ready(function()
      {        
        var arr = [];
        $('#$id table').on( 'row-reorder.dt', function ( e, diff, edit ) {
          $.each(diff, function( index, value ) {
            var id = value.node.cells[1].innerHTML;
            arr.push({
              id: id,
              position: value.newData
            });
          });
          
          $.post({
            url: '$prop->rowReorder',
            data: {
              param: arr
            },
            success: function(html){
              console.log(html);
            }
          });
        });
 });        
JS
        );
      }

      if (isset($prop->data)) {
        /** @var \Iterator $dataIter */
        $dataIter = iterator ($prop->data);
        $dataIter->rewind ();
        $valid = $dataIter->valid ();
      }
      else $valid = false;
    }
    if ($valid) {
      $this->parseIteratorExp ($prop->as, $idxVar, $itVar);
      $columnsCfg = $this->getColumns ();
      foreach ($columnsCfg as &$col) {
        $col->databind ();
        $col->applyPresetsOnSelf ();
      }
      $this->begin ('table', [
        'class' => enum (' ', $prop->tableClass, $this->enableRowClick ? 'table-clickable' : ''),
      ]);
      $this->beginContent ();
      $this->renderHeader ($columnsCfg);
      if (!$prop->ajax) {
        $idx = 0;
        /** @noinspection PhpUndefinedVariableInspection */
        foreach ($dataIter as $i => $v) {
          if ($idxVar)
            $viewModel[$idxVar] = $i;
          $viewModel[$itVar] = $v;
          $this->renderRow ($idx++, $columnsCfg);
        }
      }
      $this->end ();
    }
    else {
      $this->renderSet ($this->getChildren ('noData'));
      $this->context->getAssetsService ()->addInlineScript (<<<JS
        $('#$id').show();
JS
      );
    }
  }

  /**
   * @return Metadata[]
   */
  private function getColumns ()
  {
    if (isset($this->cachedColumns))
      return $this->cachedColumns;
    return $this->cachedColumns = filter ($this->props->column, function ($col) {
      $col->databind ();
      return $col->props->if !== false;
    });
  }

  private function renderHeader (array $columns)
  {
    if ($this->props->rowSelector)
      $this->tag ('col', ['width' => $this->props->rowSelectorWidth]);
    $id = $this->props->id;
    foreach ($columns as $k => $col) {
      $w = $col->props->width;
      $this->tag ('col', isset($w) ? ['width' => $w] : null);
    }
    $this->begin ('thead');
    $this->begin ('tr');
    if ($this->props->rowSelector)
      $this->tag ('th');
    foreach ($columns as $k => $col) {
      /** @var MetadataProperties $props */
      $props = $col->props;
      $al    = $props->get ('headerAlign', $props->align);
      $this->begin ('th');
      $this->attr ('class', enum (' ', $al ? "ta-$al" : '', property ($props, 'class')));
      $this->beginContent ();
      if ($props->has ('icon')) {
        echo "<i class='{$props->icon}'></i>";
        if ($props->has ('title'))
          echo " ";
      }
      echo $props->get ('title');
      $this->end ();
    }
    $this->end ();
    $this->end ();
    if ($this->props->multiSearch) {
      $this->begin ('tfoot');
      $this->begin ('tr', ['class' => 'multiSearch']);
      if ($this->props->rowSelector)
        $this->tag ('th');
      foreach ($columns as $k => $col) {
        $type = $col->props->get ('type');
        $this->begin ('th');
        $this->tag ('input', [
          'type'     => 'text',
          'data-col' => $k,
          'oninput'  => 'dataGridMultiSearch(event)',
          'readonly' => (bool)$type,
        ]);
        $this->end ();
      }
      $this->end ();
      $this->end ();
    }
  }

  /**
   * @param int        $idx
   * @param Metadata[] $columns
   * @throws \Matisse\Exceptions\ComponentException
   */
  private function renderRow ($idx, array $columns)
  {
    $this->begin ('tr');
    $this->attr ('class', 'R' . ($idx % 2));
    if ($this->enableRowClick) {
      if ($this->isPropertySet ('onClickGoTo')) {
        $onclick = $this->getComputedPropValue ('onClickGoTo');
        $onclick = "selenia.go('$onclick',event)";
      }
      else $onclick = $this->getComputedPropValue ('onClick');
      $onclick = "if (!$(event.target).closest('[data-nck]').length) $onclick";
      $this->attr ('onclick', $onclick);
    }
    if ($this->props->rowSelector)
      $this->tag ('td', ['class' => 'rh', 'data-nck' => true], $idx + 1);
    foreach ($columns as $k => $col) {
      $col->preRun ();
      $colAttrs = $col->props;
      $colType  = property ($colAttrs, 'type', '');
      $al       = property ($colAttrs, 'align');
      $isText   = empty($colType);
      $this->begin ('td');
      $this->attr ('class', enum (' ',
        property ($colAttrs, 'class'),
        "ta-$al",
        $colType == 'row-selector' ? 'rh' : '',
        $colType == 'field' ? 'field' : ''
      ));
      if ($isText) {
        $this->beginContent ();
        $col->runChildren ();
      }
      else {
        if ($this->enableRowClick)
          $this->attr ('data-nck');
        $this->beginContent ();
        $col->runChildren ();
      }
      $this->end ();
    }
    $this->end ();
  }

  private function setupColumns ()
  {
    $id     = $this->props->id;
    $styles = '';
    foreach ($this->getColumns () as $k => $col) {
      $al = $col->props->align;
      if (isset($al))
        $styles .= "#$id .c$k{text-align:$al}";
      $al = $col->props->header_align;
      if (isset($al))
        $styles .= "#$id .h$k{text-align:$al}";
    }
    $this->context->getAssetsService ()->addInlineCss ($styles);
  }

}
