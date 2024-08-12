<?php namespace Ocme\Model;

/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 * 
 * @property int $language_id
 * @property string $name
 * @property string $code
 * @property string $locale
 * @property string $image
 * @property string $directory
 * @property int $sort_order
 * @property bool $status
 * 
 * @property-read string $image_path
 */

class Language extends \Ocme\Database\Model {
	
	use \Ocme\Database\Attribute\Status;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'language';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'language_id';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = array(
		'sort_order' => 0,
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'name', 'code', 'locale', 'image', 'directory', 'sort_order', 'status',
	);
	
	// Accessors ///////////////////////////////////////////////////////////////
	
	public function getImagePathAttribute() {
		return 'language/' . $this->code . '/' . $this->code . '.png';
	}
	
}