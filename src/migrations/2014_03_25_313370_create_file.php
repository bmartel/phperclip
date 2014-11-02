<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;


class CreateFile extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::create('phperclip_files', function (Blueprint $table) {

			$table
				->increments('id')
				->unsigned()
			;

			$table
				->integer('clippable_id')
				->unsigned()
				->nullable()
			;

			$table
				->string('clippable_type')
				->nullable()
			;

			$table
				->string('slot')
				->nullable()
			;

			$table->string('mime_type');

			$table->timestamps();

			//
			// Indexes
			//

			$table->unique(['clippable_id', 'clippable_type', 'slot'], 'U_clippable_slot');

		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::drop('phperclip_files');
	}

}