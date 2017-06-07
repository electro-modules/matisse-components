<?php

namespace Electro\Plugins\MatisseComponents\Models;

use Electro\ContentRepository\Lib\FileUtil;
use Electro\Plugins\IlluminateDatabase\BaseModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;

/**
 * Represents a media file managed by the framework.
 *
 * <p>When creating a new record, the model will automatically compute the `path` and `sort` fields.
 *
 * <p>When deleting a record, the associated physical file will also be deleted if an observer is watching for deletion
 * events, which is NOT provided by this class.<br>
 * > The {@see ImageFieldHandler} class provides such an observer.
 *
 * <p>When deleting a file without an observer, you should call
 *       $repository->deleteFile ($model->path);
 * where `$repository` is an instance of {@see ContentRepositoryInterface}.
 *
 * <p>Instances SHOULD be immutable (except for metadata).<br>
 * If you want to replace a file on another model's field, delete the previous file and then create a new File model.
 *
 * @property string $id
 * @property string $name
 * @property string $ext
 * @property string $mime
 * @property bool   $image
 * @property string $path
 * @property string $group
 * @property string $metadata A JSON encoded value.
 */
class File extends BaseModel
{
  public $incrementing = false;
  public $timestamps   = true;

  protected $casts = [
    'metadata' => 'array',
    'image'    => 'boolean',
  ];

  protected $fillable = [
    'id',
    'name',
    'ext',
    'mime',
    'image',
    'path',   // This field is precomputed by the Model when a new record is inserted into the database.
    'group',
    'metadata',
  ];

  static function getFileData ($filename, $filePath, $fieldName = null)
  {
    $ext     = strtolower (str_segmentsLast ($filename, '.'));
    $name    = str_segmentsStripLast ($filename, '.');
    $mime    = FileUtil::getMimeType ($filePath, $ext); // Note: file paths of uploaded files do not have an extension.
    $isImage = FileUtil::isImageType ($mime);
    return [
      'id'    => uniqid (),
      'name'  => $name,
      'ext'   => $ext,
      'mime'  => $mime,
      'image' => $isImage,
      'group' => $fieldName ? str_segmentsLast ($fieldName, '.') : null,
    ];
  }

  protected static function boot ()
  {
    parent::boot ();

    static::creating (function (self $model) {
      // if it's a class name, convert the namespace to a file path.
      $owner       = str_replace ('\\', '/', $model->owner_type);
	  if (!$model->path)
		  $model->path = "$owner/$model->owner_id/$model->id.$model->ext";
    });
  }

  /**
   * Get all of the owning models.
   *
   * @return MorphTo
   */
  public function owner ()
  {
    return $this->morphTo ();
  }

  /**
   * A query scope that restricts results to files bound to a specific field on the owner model.
   *
   * @param Builder $query
   * @param string  $fieldName
   * @return mixed
   */
  public function scopeOfField ($query, $fieldName)
  {
    $fieldName = str_segmentsLast ($fieldName, '.');
    return exists ($fieldName) ? $query->where ('group', $fieldName) : $query;
  }

}
