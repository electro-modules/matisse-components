<?php
namespace Electro\Plugins\MatisseComponents\Models;

use Electro\Plugins\IlluminateDatabase\BaseModel;

/**
 * Represents a media file managed by the framework.
 *
 * When creating a new record, the model will automatically compute the `path` and `sort` fields.
 *
 * When deleting a record, the associated physical file will also be delete. Note: the observer for deletion events is
 * external to the class; you can find it on the module's service provider.
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
    'path',
    'group',
    'metadata',
  ];

  protected static function boot ()
  {
    parent::boot ();

    static::creating (function (self $model) {
      // if it's a class name, convert the namespace to a file path.
      $owner       = str_replace ('\\', '/', $model->owner_type);
      $model->path = "$owner/$model->owner_id/$model->id.$model->ext";
    });
  }

  /**
   * Get all of the owning models.
   */
  public function owner ()
  {
    return $this->morphTo ();
  }

}
