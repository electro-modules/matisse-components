<?php
namespace Electro\Plugins\MatisseComponents\Config;

use Electro\ContentServer\Config\ContentServerSettings;
use Electro\Interfaces\DI\InjectorInterface;
use Electro\Interfaces\KernelInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Interfaces\ModuleInterface;
use Electro\Kernel\Lib\ModuleInfo;
use Electro\Plugins\Matisse\Config\MatisseSettings;
use Electro\Plugins\MatisseComponents as C;
use Electro\Plugins\MatisseComponents\Handlers\ImageFieldHandler;
use Electro\Plugins\MatisseComponents\Models\File;
use Electro\Profiles\WebProfile;
use League\Glide\Server;

class MatisseComponentsModule implements ModuleInterface
{
  static function getCompatibleProfiles ()
  {
    return [WebProfile::class];
  }

  static function startUp (KernelInterface $kernel, ModuleInfo $moduleInfo)
  {
    $kernel->onConfigure (
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
