<?php namespace TippingCanoe\Phperclip\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class File extends Model{

	protected $table = 'phperclip_files';

	protected $fillable = [
		'clippable_id',
		'clippable_type',
		'slot',
		'mime_type'
	];

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function clippable() {
		return $this->morphTo();
	}

	/**
	 * Get the mimetype of the File
	 *
	 * @return mixed
	 */
	public function getMimeType(){
		return $this->mime_type;
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder|Builder $query
	 * @param string $type
	 * @param int $id
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeForClippable(Builder $query, $type, $id) {
		return $query
			->where('clippable_type', $type)
			->where('clippable_id', $id)
			;
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder|Builder $query
	 * @param string $slot
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeInSlot(Builder $query, $slot) {
		return $query->whereIn('slot', (array)$slot);
	}

	/**
	 * @param \Illuminate\Database\Eloquent\Builder|Builder $query
	 * @param string $slot
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeNotInSlot(Builder $query, $slot) {
		return $query->whereNotIn('slot', (array)$slot);
	}

	/**
	 * @param Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWithoutSlot(Builder $query) {
		return $query->whereNull('slot');
	}

	/**
	 * Modifies the query to only include files without clippables.
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeUnattached(Builder $query) {
		return $query
			->whereNull('clippable_id')
			->whereNull('clippable_type')
			;
	}

	/**
	 * Modifies the query to only include files attached to an clippable.
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeAttached(Builder $query) {
		return $query
			->whereNotNull('clippable_id')
			->whereNotNull('clippable_type')
			;
	}

	/**
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeRandom(Builder $query) {
		return $query->orderBy('RAND()');
	}

	/**
	 * Only retrieve files whose slots are integers.
	 *
	 * @param Builder $query
	 * @return Builder
	 */
	public function scopeInIntegerSlot(Builder $query) {
		return $query->whereRaw(sprintf('%s.slot REGEXP \'^[[:digit:]]+$\'', $query->getQuery()->from));
	}
}