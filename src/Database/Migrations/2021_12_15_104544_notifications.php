<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->boolean('read')->default(0);
            $table->morphs('notifiable');
            $table->timestamps();
            $table->integer('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        DB::statement('
            CREATE VIEW notification_types AS
                SELECT
                    w.id,
                    w.status,
                    s.customer_id,
                    s.start_at as created_at,
                    "writable" AS notifiable_type
                FROM
                    write_programs AS w
                    JOIN wp_subscriptions AS s ON w.wp_subscriptions_id = s.id
                UNION ALL
                SELECT
                    id,
                    status,
                    customer_id,
                    created_at,
                    "order" AS notifiable_type
                FROM
                    orders
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_types');
    }
};
