<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class QuantityType extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'id' => [
                'type'  => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 50,
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
		]);
		$this->forge->addKey('id', true);
		$this->forge->addUniqueKey('name');
        $this->forge->createTable('quantity_type');
    }

    public function down()
    {
        $this->forge->dropTable('quantity_type');
    }
}
