<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProductQuantityUnit extends Migration
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
			'product_id' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
				'null' => false,
            ],
			'quantity_type_id' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
				'null' => false,
            ],
			'unit_id' => [
                'type' => 'VARCHAR',
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
		]);
		$this->forge->addKey('id', true);
        $this->forge->createTable('product_quantity_unit');
    }

    public function down()
    {
        $this->forge->dropTable('product_quantity_unit');
    }
}
