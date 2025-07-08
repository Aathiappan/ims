<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Product extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
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
				'constraint' => 1000,
				'null'       => true,
			],
			'sku_id' => [
				'type' => 'VARCHAR',
				'constraint' => '30',
				'null'       => false,
			],
			'status' => ['type' => 'tinyint', 'constraint' => 1, 'null' => 0, 'default' => 1],
			'deleted_at' => [
				'type' => 'INT',
				'constraint' => 10,
				'null' => false,
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
			'updated_at' => [
				'type' => 'INT',
				'constraint' => 10,
				'null' => false,
			],
		]);
		
		$this->forge->addKey('id', true);
		$this->forge->addUniqueKey('sku_id');
        $this->forge->createTable('product');
    }

    public function down()
    {
        $this->forge->dropTable('product');
    }
}
