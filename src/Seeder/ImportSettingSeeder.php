<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Seeder;

use Illuminate\Database\Seeder;

class ImportSettingSeeder extends Seeder
{

    use ImportSettingSeederMaker;

    public function run()
    {
        //导入配置的填充写在这里
        $file_url_prefix = $this->getFileUrlPrefix();

        //主体导入
        $this->UpdateOrCreate('subjects',
            'Mallto\Admin\Domain\Import\SubjectImport',
            $file_url_prefix . 'subject.xlsx', mt_trans('subject_id') . '模块');
        $this->UpdateOrCreate('admin_users',
            'Mallto\Admin\Domain\Import\AdminUserImport',
            $file_url_prefix . 'admin_user.xlsx', '账号模块');
    }

}
