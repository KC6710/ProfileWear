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
 * @property int $product_option_id
 * @property int $product_id
 * @property int $option_id
 * @property string $value
 * @property bool $required
 * @property string $vdate
 * @property string $vtime
 * @property string $vdatetime
 * 
 * @property Product $product
 * @property Option $option
 * @property OcmeFilterProperty $ocme_filter_property
 */

class ProductOption extends \Ocme\Database\Model {

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_option';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'product_option_id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'product_id', 'option_id', 'value', 'required', 'vdate', 'vtime', 'vdatetime',
	);
	// Functions ///////////////////////////////////////////////////////////////
	
	/**
	 * 
	 */
	public function reCreate() {
		/* @var $data array */
		$data = array(
			'vdate' => null,
			'vtime' => null,
			'vdatetime' => null,
		);
		
		if( in_array( $this->option->type, array( 'date', 'time', 'datetime' ) ) ) {
			$data['v' . $this->option->type] = $this->value;
		}
		
		$this->fill( $data )->save();
	}
	
	// Scopes //////////////////////////////////////////////////////////////////
	
	public function scopeMissing( \Illuminate\Database\Eloquent\Builder $query ) {
		$query
			->whereExists(function($q) {
				$q->select(ocme()->db()->raw(1))
					->from('option')
					->whereColumn('option.option_id', 'product_option.option_id')
					->where(function($q){
						$q->where(function($q){
							$q
								->whereIn('option.type', array('date', 'time', 'datetime'))
								->where(function($q){
									$q->whereNull('product_option.vdate')->whereNull('product_option.vtime')->whereNull('product_option.vdatetime');
								});
						})->orWhere(function($q){
							$q
								->whereNotIn('option.type', array('date', 'time', 'datetime'))
								->where(function($q){
									$q->whereNotNull('product_option.vdate')->orWhereNotNull('product_option.vtime')->orWhereNotNull('product_option.vdatetime');
								});
						});
					});
			});
	}
	
	public function scopeRedundant( \Illuminate\Database\Eloquent\Builder $query ) {
		$query
			->where(function($q){
				$q->whereNotExists(function($q) {
					$q->select(ocme()->db()->raw(1))
						->from('product')
						->whereColumn('product.product_id', 'product_option.product_id');
				})->orWhereNotExists(function($q) {
					$q->select(ocme()->db()->raw(1))
						->from('option')
						->whereColumn('option.option_id', 'product_option.option_id');
				});
			});
	}
	
	// Relationships ///////////////////////////////////////////////////////////
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product() {
		return $this->belongsTo('\Ocme\Model\Product', 'product_id', 'product_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function option() {
		return $this->belongsTo('\Ocme\Model\Option', 'option_id', 'option_id');
	}
	
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function ocme_filter_property() {
		return $this->belongsTo('\Ocme\Model\OcmeFilterProperty', 'option_id', 'option_id');
	}
	
}