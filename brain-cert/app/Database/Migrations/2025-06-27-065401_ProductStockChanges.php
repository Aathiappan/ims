<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProductStockChanges extends Migration
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
                'type'  => 'INT',
                'constraint' => 10,
				'null'       => false,
			],
			'change_type' => [
				'type'       => 'ENUM',
				'constraint' => ['add', 'remove', 'adjust', 'return', 'sale', 'transfer'],
				'null'       => false,
			],
			'operation' => [
				'type' => 'ENUM',
				'constraint' => ['increament', 'decrement'],
				'null'       => false,
			],
			'change_quantity' => [
				'type' => 'DECIMAL',
				'constraint' => '10,2',
				'null'       => false,
			],
			'current_quantity' => [
				'type' => 'DECIMAL',
				'constraint' => '10,2',
				'null'       => false,
			],
			'note' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => true,
			],
			'changed_by' => [
				'type' => 'VARCHAR',
				'constraint' => '10',
				'null' => false,
			],
			'changed_at' => [
				'type' => 'INT',
				'constraint' => 10,
				'null' => false,
			],
		]);
		$this->forge->addKey('id', true);
        $this->forge->createTable('product_stock_changes');
    }

    public function down()
    {
        $this->forge->dropTable('product_stock_changes');
    }
}
