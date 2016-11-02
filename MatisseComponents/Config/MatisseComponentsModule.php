<?php
namespace Electro\Plugins\MatisseComponents\Config;

use Electro\ContentServer\Config\ContentServerSettings;
use Electro\Core\Assembly\ModuleInfo;
use Electro\Core\Assembly\Services\Bootstrapper;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Plugins\Matisse\Config\MatisseSettings;
use Electro\Plugins\MatisseComponents as C;
use Electro\Plugins\MatisseComponents\Handlers\ImageFieldHandler;
use Electro\Plugins\MatisseComponents\Models\File;
use League\Glide\Server;
use const Electro\Core\Assembly\Services\CONFIGURE;

class MatisseComponentsModule implements ModuleInterface
{
  static function bootUp (Bootstrapper $bootstrapper, ModuleInfo $moduleInfo)
  {
    $bootstrapper->on (CONFIGURE,
      function (MatisseSettings $matisseSettings, ModelControllerInterface $modelController,
                InjectorInterface $injector, ContentServerSettings $contentServerSettings)
      use ($moduleInfo) {
        $matisseSettings
          ->registerMacros ($moduleInfo)
          ->registerComponents ([
            'Button'         => C\Button::class,
            'Checkbox'       => C\Checkbox::class,
            'DataGrid'       => C\DataGrid::class,
            'Dropzone'       => C\Dropzone::class,
            'Field'          => C\Field::class,
            'FileUpload'     => C\FileUpload::class,
            'HtmlEditor'     => C\HtmlEditor::class,
            'Image'          => C\Image::class,
            'ImageField'     => C\ImageField::class,
            'Input'          => C\Input::class,
            'Label'          => C\Label::class,
            'Link'           => C\Link::class,
            'MainMenu'       => C\MainMenu::class,
            'NavigationPath' => C\NavigationPath::class,
            'Paginator'      => C\Paginator::class,
            'RadioButton'    => C\RadioButton::class,
            'Select'         => C\Select::class,
            'Switch'         => C\Switch_::class,
            'Tab'            => C\Tab::class,
            'TabPage'        => C\TabPage::class,
            'Tabs'           => C\Tabs::class,
          ])
          ->registerAssets ($moduleInfo, [
            'dist/components.css',
          ]);

        $modelController
          ->registerExtension ($injector->makeFactory (ImageFieldHandler::class));

        File::deleting (function (File $model) use ($contentServerSettings, $injector) {
          if (exists ($model->path)) {
            $path = "{$contentServerSettings->fileArchivePath()}/$model->path";
            if (file_exists ($path))
              unlink ($path);
            $glideServer = $injector->make (Server::class);
            $glideServer->deleteCache ($model->path);
          }
        });

      });
  }

}
