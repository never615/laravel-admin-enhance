<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Base;


trait ModelForm
{
    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->currentId=$id;
        return $this->edit($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $this->currentId=$id;

        return $this->form()->update($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->currentId=$id;

        try {
            if ($this->form()->destroy($id)) {
                return response()->json([
                    'status'  => true,
                    'message' => trans('admin.delete_succeeded'),
                ]);
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => trans('admin.delete_failed'),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'status'  => false,
                'message' => "为了数据安全,暂时无法删除存在关联数据的数据",

            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        return $this->form()->store();
    }
}
