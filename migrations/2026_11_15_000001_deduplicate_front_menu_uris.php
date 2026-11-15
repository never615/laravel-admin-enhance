<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $connection = DB::connection(config('admin.database.connection') ?: config('database.default'));

        $connection->transaction(function () use ($connection): void {
            // 多副本启动时也必须先完成合并和建索引，再允许菜单或角色关联写入。
            if ($connection->getDriverName() === 'pgsql') {
                $connection->statement('LOCK TABLE front_menus, admin_role_front_menu IN SHARE ROW EXCLUSIVE MODE');
            }

            do {
                $groups = $connection->table('front_menus')
                    ->whereNotNull('uri')->where('uri', '<>', '')
                    ->select('parent_id', 'uri')
                    ->groupBy('parent_id', 'uri')->havingRaw('COUNT(*) > 1')->get();

                foreach ($groups as $group) {
                    $ids = $connection->table('front_menus')
                        ->where('parent_id', $group->parent_id)->where('uri', $group->uri)
                        ->orderBy('id')->pluck('id')->all();
                    $canonicalId = array_shift($ids);

                    foreach ($ids as $duplicateId) {
                        foreach ($connection->table('admin_role_front_menu')->where('menu_id', $duplicateId)->get() as $pivot) {
                            $exists = $connection->table('admin_role_front_menu')
                                ->where('menu_id', $canonicalId)->where('role_id', $pivot->role_id)->exists();
                            if (! $exists) {
                                $attributes = (array) $pivot;
                                $attributes['menu_id'] = $canonicalId;
                                $connection->table('admin_role_front_menu')->insert($attributes);
                            }
                        }
                        $connection->table('admin_role_front_menu')->where('menu_id', $duplicateId)->delete();

                        $connection->table('front_menus')->where('parent_id', $duplicateId)
                            ->update(['parent_id' => $canonicalId]);
                        foreach ($connection->table('front_menus')->where('path', 'like', '%.' . $duplicateId . '.%')->get(['id', 'path']) as $child) {
                            $connection->table('front_menus')->where('id', $child->id)->update([
                                'path' => str_replace('.' . $duplicateId . '.', '.' . $canonicalId . '.', $child->path),
                            ]);
                        }

                        // 不调用 ModelTree::delete()，避免递归删除已迁移的子菜单。
                        $connection->table('front_menus')->where('id', $duplicateId)->delete();
                    }
                }
                // 父节点合并后，原先分属两棵子树的同 URI 子节点也可能成为重复兄弟。
            } while ($groups->isNotEmpty());

            // 空 URI 的分组菜单及不同父级下的链接仍可分别存在。
            $connection->statement("CREATE UNIQUE INDEX IF NOT EXISTS front_menus_parent_uri_unique ON front_menus (parent_id, uri) WHERE uri IS NOT NULL AND uri <> ''");
        });
    }

    public function down(): void
    {
        DB::connection(config('admin.database.connection') ?: config('database.default'))
            ->statement('DROP INDEX IF EXISTS front_menus_parent_uri_unique');
        // 合并后的菜单、角色关联和子节点在回滚时保留。
    }
};
