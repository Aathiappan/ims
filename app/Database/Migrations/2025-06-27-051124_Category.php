<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Category extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'category_id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
			'description' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => true,
			],
			'parent_id' => [
				'type' => 'INT',
				'constraint' => 10,
				'null'       => true,
			],
			'created_by' => [
				'type' => 'VARCHAR',
				'constraint' => '10',
				'null' => false,
			],
			'created_at' => [
				'type' => 'INT',
				'constraint' => 10,
				'null' => false,
			],
		]);
		$this->forge->addKey('category_id', true); 
        $this->forge->createTable('category');
    }

    public function down()
    {
        $this->forge->dropTable('category');
    }
}
