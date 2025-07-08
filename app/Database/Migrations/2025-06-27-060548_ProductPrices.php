<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProductPrices extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'price_id' => [
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
			'currency_id' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
				'null' => false,
            ],
			'price' => [
				'type' => 'DECIMAL',
				'constraint' => '10,2',
				'null'       => false,
			],
			'effective_from' => [
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
		]);
		$this->forge->addKey('price_id', true); 
        $this->forge->createTable('product_prices');
    }

    public function down()
    {
        $this->forge->dropTable('product_prices');
    }
}
