<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */


namespace Mallto\Admin\Jobs;

interface ImportHandlerInterface
{
    public function handle($record);

    public function fail($record, $exception = null);

}
