<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Currency extends Migration
{
    public function up()
    {
        $this->forge->addField([
			'currency_id' => [
                'type'  => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'auto_increment' => true,
            ],
			'code' => [
				'type' => 'VARCHAR',
                'constraint' => 4,
				'null' => false,
			],
			'symbol' => [
				'type' => 'VARCHAR',
                'constraint' => 10,
				'null' => false,
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
		$this->forge->addKey('currency_id', true);
		$this->forge->addUniqueKey('code');
        $this->forge->createTable('currency');
    }

    public function down()
    {
        $this->forge->dropTable('currency');
    }
}
