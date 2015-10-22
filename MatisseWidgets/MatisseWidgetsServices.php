<?php
namespace Selenia\Plugins\MatisseWidgets;

use Selenia\Interfaces\InjectorInterface;
use Selenia\Interfaces\ServiceProviderInterface;

class MatisseWidgetsServices implements ServiceProviderInterface
{
  function boot () { }

  function register (InjectorInterface $injector)
  {
    ModuleOptions (dirname (__DIR__), [
      'public'     => 'modules/selenia-plugins/matisse-components',
      'components' => [
        'Button'      => 'Selenia\Plugins\MatisseWidgets\Button',
        'Calendar'    => 'Selenia\Plugins\MatisseWidgets\Calendar',
        'Checkbox'    => 'Selenia\Plugins\MatisseWidgets\Checkbox',
        'DataGrid'    => 'Selenia\Plugins\MatisseWidgets\DataGrid',
        'Field'       => 'Selenia\Plugins\MatisseWidgets\Field',
        'FileUpload'  => 'Selenia\Plugins\MatisseWidgets\FileUpload',
        'HtmlRditor'  => 'Selenia\Plugins\MatisseWidgets\HtmlEditor',
        'Image'       => 'Selenia\Plugins\MatisseWidgets\Image',
        'ImageField'  => 'Selenia\Plugins\MatisseWidgets\ImageField',
        'Input'       => 'Selenia\Plugins\MatisseWidgets\Input',
        'Label'       => 'Selenia\Plugins\MatisseWidgets\Label',
        'Link'        => 'Selenia\Plugins\MatisseWidgets\Link',
        'MainMenu'    => 'Selenia\Plugins\MatisseWidgets\MainMenu',
        'Paginator'   => 'Selenia\Plugins\MatisseWidgets\Paginator',
        'RadioButton' => 'Selenia\Plugins\MatisseWidgets\Radiobutton',
        'Selector'    => 'Selenia\Plugins\MatisseWidgets\Selector',
        'Tab'         => 'Selenia\Plugins\MatisseWidgets\Tab',
        'TabPage'     => 'Selenia\Plugins\MatisseWidgets\TabPage',
        'Tabs'        => 'Selenia\Plugins\MatisseWidgets\Tabs',
      ],
    ]);
  }

}
