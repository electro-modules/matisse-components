<?php
namespace Electro\Plugins\MatisseComponents\Handlers;

use Electro\ContentServer\Config\ContentServerSettings;
use Electro\ContentServer\Lib\FileUtil;
use Electro\Exceptions\FlashMessageException;
use Electro\Exceptions\FlashType;
use Electro\Interfaces\ModelControllerExtensionInterface;
use Electro\Interfaces\ModelControllerInterface;
use Electro\Plugins\MatisseComponents\ImageField;
use Electro\Plugins\MatisseComponents\Models\File;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\UploadedFileInterface;

class ImageFieldHandler implements ModelControllerExtensionInterface
{
  /** @var string */
  private $fileArchivePath;

  public function __construct (ContentServerSettings $settings)
  {
    $this->fileArchivePath = $settings->fileArchivePath ();
  }

  /*
   * Detect if the request has fields that were generated by this component; if so, register an handler for saving
   * them.
   */
  function modelControllerExtension (ModelControllerInterface $modelController)
  {
    $request = $modelController->getRequest ();
    $files   = $request->getUploadedFiles ();
    $uploads = [];

    // Check if extension is applicable to the current request.
    foreach ($files as $fieldName => $file)
      if (str_endsWith ($fieldName, ImageField::FILE_FIELD_SUFFIX)) {
        // Note: slashes are converted to dots, which delimit path segments to nested fields. See the Field component.
        $fieldName           = str_replace ('/', '.', str_segmentsStripLast ($fieldName, '_'));
        $uploads[$fieldName] = $file;
      }

    if ($uploads)
      $modelController->onSave (-1, function () use ($uploads, $modelController) {
        /** @var UploadedFileInterface $file */
        foreach ($uploads as $fieldName => $file) {
          list ($targetModel, $prop) = $modelController->getTarget ($fieldName);
          $err = $file->getError ();
          if ($err == UPLOAD_ERR_OK)
            static::newUpload ($targetModel, $prop, $file);
          else if ($err == UPLOAD_ERR_NO_FILE)
            static::noUpload ($targetModel, $prop);
          else throw new FlashMessageException ("Error $err", FlashType::ERROR, "Error uploading file");
        }
      });
  }

  /**
   * Remove a physical file and the respective database file record.
   * ><p>Non-existing records or physical files are ignored.
   *
   * @param string $filePath A folder1/folder1/UID.ext path.
   * @throws \Exception If the file could not be deleted.
   */
  private function deleteFile ($filePath)
  {
    $id   = str_segmentsLast ($filePath, '/');
    $id   = str_segmentsStripLast ($id, '.');
    $file = File::find ($id);
    if ($file)
      $file->delete ();
  }

  /**
   * Handle the case where a file has been uploaded for a field, possibly replacing another already set on the field.
   *
   * @param Model                 $model
   * @param string                $fieldName
   * @param UploadedFileInterface $file
   */
  private function newUpload (Model $model, $fieldName, UploadedFileInterface $file)
  {
    $filename = $file->getClientFilename ();
    $ext      = strtolower (str_segmentsLast ($filename, '.'));
    $name     = str_segmentsStripLast ($filename, '.');
    $id       = uniqid ();
    $mime     = FileUtil::getUploadedFileMimeType ($file);
    $isImage  = FileUtil::isImageType ($mime);

    $fileModel = $model->files ()->create ([
      'id'    => $id,
      'name'  => $name,
      'ext'   => $ext,
      'mime'  => $mime,
      'image' => $isImage,
      'group' => str_segmentsLast ($fieldName, '.'),
    ]);

    // Save the uploaded file.
    $path = "$this->fileArchivePath/$fileModel->path";
    $dir  = dirname ($path);
    if (!file_exists ($dir))
      mkdir ($dir, 0777, true);
    $file->moveTo ($path);

    // Delete the previous file for this field, if one exists.
    $prevFilePath = $model->getOriginal ($fieldName);
    if (exists ($prevFilePath))
      $this->deleteFile ($prevFilePath);

    $model->$fieldName = $fileModel->path;
    $model->save ();
  }

  /**
   * Handle the case where no file has been uploaded for a field, but the field may have been cleared.
   *
   * @param Model  $model
   * @param string $fieldName
   */
  private function noUpload (Model $model, $fieldName)
  {
    $prevFilePath = $model->getOriginal ($fieldName);
    if (!exists ($model->$fieldName) && exists ($prevFilePath))
      $this->deleteFile ($prevFilePath);
  }

}
