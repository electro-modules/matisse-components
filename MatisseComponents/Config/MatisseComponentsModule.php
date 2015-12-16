<?php
namespace Selenia\Plugins\MatisseComponents\Config;

use Selenia\Core\Assembly\Services\ModuleServices;
use Selenia\Interfaces\ModuleInterface;
use Selenia\Plugins\MatisseComponents\Button;
use Selenia\Plugins\MatisseComponents\Calendar;
use Selenia\Plugins\MatisseComponents\Checkbox;
use Selenia\Plugins\MatisseComponents\DataGrid;
use Selenia\Plugins\MatisseComponents\Field;
use Selenia\Plugins\MatisseComponents\FileUpload;
use Selenia\Plugins\MatisseComponents\HtmlEditor;
use Selenia\Plugins\MatisseComponents\Image;
use Selenia\Plugins\MatisseComponents\ImageField;
use Selenia\Plugins\MatisseComponents\Input;
use Selenia\Plugins\MatisseComponents\Label;
use Selenia\Plugins\MatisseComponents\Link;
use Selenia\Plugins\MatisseComponents\MainMenu;
use Selenia\Plugins\MatisseComponents\NavigationPath;
use Selenia\Plugins\MatisseComponents\Paginator;
use Selenia\Plugins\MatisseComponents\Radiobutton;
use Selenia\Plugins\MatisseComponents\Selector;
use Selenia\Plugins\MatisseComponents\Tab;
use Selenia\Plugins\MatisseComponents\TabPage;
use Selenia\Plugins\MatisseComponents\Tabs;

class MatisseComponentsModule implements ModuleInterface
{
  function boot () { }

  function configure (ModuleServices $module)
  {
    $module
      ->publishPublicDirAs ('modules/selenia-plugins/matisse-components')
      ->provideTemplates ()
      ->registerComponents ([
        'Button'         => Button::class,
        'Calendar'       => Calendar::class,
        'Checkbox'       => Checkbox::class,
        'DataGrid'       => DataGrid::class,
        'Field'          => Field::class,
        'FileUpload'     => FileUpload::class,
        'HtmlRditor'     => HtmlEditor::class,
        'Image'          => Image::class,
        'ImageField'     => ImageField::class,
        'Input'          => Input::class,
        'Label'          => Label::class,
        'Link'           => Link::class,
        'MainMenu'       => MainMenu::class,
        'NavigationPath' => NavigationPath::class,
        'Paginator'      => Paginator::class,
        'RadioButton'    => Radiobutton::class,
        'Selector'       => Selector::class,
        'Tab'            => Tab::class,
        'TabPage'        => TabPage::class,
        'Tabs'           => Tabs::class,
      ]);
  }
}
