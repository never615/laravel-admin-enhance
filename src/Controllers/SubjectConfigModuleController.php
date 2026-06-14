<?php

namespace Mallto\Admin\Controllers;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Mallto\Admin\Domain\SubjectConfig\SubjectConfigModuleForm;
use Throwable;

class SubjectConfigModuleController extends Controller
{
    public function index(Request $request, SubjectConfigModuleForm $form)
    {
        $subjectId = $request->filled('subject_id') ? (int)$request->get('subject_id') : null;
        $snapshot = $form->snapshot($subjectId);

        return Admin::content(function (Content $content) use ($snapshot) {
            $content->header($snapshot['title']);
            $content->description($snapshot['description']);
            $content->body($this->renderHtml($snapshot));
        });
    }

    public function save(Request $request, SubjectConfigModuleForm $form)
    {
        $subjectId = (int)$request->get('subject_id');

        try {
            $form->save($request->all());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            admin_toastr($exception->getMessage(), 'error');

            return back()->withInput();
        }

        admin_toastr('项目动态配置已保存，运行期快照已发布；需要长驻进程生效时请执行发布与重启。');

        return redirect()->route('subject_configs.form', ['subject_id' => $subjectId]);
    }

    private function renderHtml(array $snapshot): string
    {
        $selector = $this->renderSubjectSelector($snapshot);
        $errors = $this->renderErrors();
        $notice = $this->renderNotice($snapshot);

        if (empty($snapshot['subject_id'])) {
            return <<<HTML
<style>{$this->style()}</style>
{$errors}
{$selector}
{$notice}
HTML;
        }

        $action = route('subject_configs.form.save');
        $publishRestartUrl = route('new_configs.publish_restart');
        $legacyUrl = route('subject_configs.index');
        $filters = $this->renderModuleFilters($snapshot);
        $rows = $this->renderRows($snapshot['rows'] ?? [], 'values');
        $historyRows = $this->renderRows($snapshot['history_rows'] ?? [], 'history');
        $temporaryRows = $this->renderTemporaryStationRows($snapshot);
        $filterScript = $this->renderFilterScript();

        return <<<HTML
<style>{$this->style()}</style>
{$errors}
{$selector}
{$notice}
<div class="subject-config-module-panel">
    <form method="POST" action="{$this->escape($action)}">
        {$this->csrfField()}
        <input type="hidden" name="subject_id" value="{$this->escape((string)$snapshot['subject_id'])}">
        {$filters}
        <div class="table-responsive">
            <table class="subject-config-module-table">
                <thead>
                    <tr>
                        <th class="config-name">配置项</th>
                        <th class="config-value">当前值</th>
                        <th class="config-meta">说明</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                    {$historyRows}
                    {$temporaryRows}
                </tbody>
            </table>
        </div>
        <div class="subject-config-module-actions">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> 保存项目动态配置</button>
            <a class="btn btn-warning" href="{$this->escape($publishRestartUrl)}"><i class="fa fa-refresh"></i> 发布与重启</a>
            <a class="btn btn-default" href="{$this->escape($legacyUrl)}"><i class="fa fa-list"></i> 传统动态配置</a>
        </div>
    </form>
</div>
{$filterScript}
HTML;
    }

    private function renderSubjectSelector(array $snapshot): string
    {
        $action = route('subject_configs.form');
        $subjectId = (string)($snapshot['subject_id'] ?? '');
        $options = '<option value="">请选择项目</option>';
        foreach (($snapshot['subjects'] ?? []) as $id => $label) {
            $selected = (string)$id === $subjectId ? ' selected' : '';
            $options .= '<option value="' . $this->escape((string)$id) . '"' . $selected . '>'
                . $this->escape((string)$label) . '</option>';
        }

        return <<<HTML
<div class="subject-config-module-panel">
    <form method="GET" action="{$this->escape($action)}" class="subject-config-subject-form">
        <label class="subject-config-subject-label">项目</label>
        <select name="subject_id" class="form-control input-sm subject-config-subject-select">{$options}</select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> 查看配置</button>
    </form>
</div>
HTML;
    }

    private function renderNotice(array $snapshot): string
    {
        $subjectName = $snapshot['subject'] ? $snapshot['subject']->name : '未选择项目';
        $limit = (int)($snapshot['temporary_station_limit'] ?? 10);

        return '<div class="alert alert-info subject-config-module-notice">'
            . '当前项目：<strong>' . $this->escape((string)$subjectName) . '</strong>。'
            . '保存后会发布运行期快照，但 LaravelS/Horizon 长驻进程需要“发布与重启”后读取新值。'
            . '临时基站 sa_lo_st_ 配置最多 ' . $this->escape((string)$limit) . ' 个。'
            . '</div>';
    }

    private function renderModuleFilters(array $snapshot): string
    {
        $counts = [];
        foreach (array_merge($snapshot['rows'] ?? [], $snapshot['history_rows'] ?? []) as $row) {
            $module = (string)($row['module'] ?? 'history');
            $counts[$module] = ($counts[$module] ?? 0) + 1;
        }
        $counts['temporary_station'] = count($snapshot['temporary_station_rows'] ?? []) + 3;

        $total = array_sum($counts);
        $html = '<div class="subject-config-module-filters">'
            . '<button type="button" class="btn btn-primary btn-xs subject-config-module-filter active" data-module-filter="all">'
            . '全部 <span class="badge">' . $this->escape((string)$total) . '</span></button>';

        foreach (($snapshot['modules'] ?? []) as $module => $meta) {
            if (!isset($counts[$module])) {
                continue;
            }

            $html .= '<button type="button" class="btn btn-default btn-xs subject-config-module-filter" data-module-filter="'
                . $this->escape((string)$module) . '">'
                . $this->escape((string)($meta['title'] ?? $module))
                . ' <span class="badge">' . $this->escape((string)$counts[$module]) . '</span></button>';
        }

        return $html . '</div>';
    }

    private function renderRows(array $rows, string $inputGroup): string
    {
        $html = '';
        foreach ($rows as $row) {
            $module = (string)($row['module'] ?? 'history');
            $encodedKey = (string)$row['encoded_key'];
            $name = $inputGroup . '[' . $this->escape($encodedKey) . ']';
            $familyBadge = $this->renderFamilyBadge($row);
            $historyBadge = !empty($row['history']) ? '<span class="label label-warning subject-config-module-badge">历史</span>' : '';

            $html .= '<tr class="subject-config-module-row" data-module="' . $this->escape($module) . '">'
                . '<td class="config-name"><strong>' . $this->escape((string)$row['name']) . '</strong>'
                . '<span class="label label-default subject-config-module-badge">' . $this->escape((string)$row['module_label']) . '</span>'
                . $familyBadge . $historyBadge
                . '<div class="subject-config-module-help subject-config-module-code">' . $this->escape((string)$row['key']) . '</div>'
                . '<div class="subject-config-module-help">类型: ' . $this->escape((string)$row['subject_config_type']) . ' / 来源: ' . $this->escape((string)$row['source']) . '</div></td>'
                . '<td class="config-value">' . $this->renderInput($row, $name)
                . '<div class="subject-config-module-help">默认值: <span class="subject-config-module-code">' . $this->escape((string)$row['default_value']) . '</span></div></td>'
                . '<td class="config-meta">' . $this->escape((string)$row['remark']) . '</td>'
                . '</tr>';
        }

        return $html;
    }

    private function renderTemporaryStationRows(array $snapshot): string
    {
        $rows = $snapshot['temporary_station_rows'] ?? [];
        for ($i = 0; $i < 3; $i++) {
            $rows[] = [
                'key' => '',
                'value' => '',
                'remark' => '',
                'type' => 'private',
            ];
        }

        $html = '';
        foreach ($rows as $index => $row) {
            $prefix = 'temporary_station[' . $index . ']';
            $html .= '<tr class="subject-config-module-row" data-module="temporary_station">'
                . '<td class="config-name"><strong>临时基站保存开关</strong>'
                . '<span class="label label-default subject-config-module-badge">临时基站</span>'
                . '<div class="subject-config-module-help subject-config-module-code">sa_lo_st_{mac}</div>'
                . '<div class="subject-config-module-help">类型: ' . $this->escape((string)($row['type'] ?? 'private')) . '</div></td>'
                . '<td class="config-value">'
                . '<input class="form-control input-sm subject-config-station-key" type="text" name="' . $prefix . '[key]" value="' . $this->escape((string)$row['key']) . '" placeholder="例如 sa_lo_st_F8:59:08:E8:85:EE">'
                . '<select class="form-control input-sm subject-config-station-value" name="' . $prefix . '[value]">'
                . $this->option('0', '关闭', (string)$row['value'])
                . $this->option('1', '开启', (string)$row['value'])
                . '</select>'
                . '<input class="form-control input-sm subject-config-station-remark" type="text" name="' . $prefix . '[remark]" value="' . $this->escape((string)($row['remark'] ?? '')) . '" placeholder="备注">'
                . '</td>'
                . '<td class="config-meta">仅用于少量、短期、按设备/MAC 展开的临时基站保存开关。超过限制会拒绝保存。</td>'
                . '</tr>';
        }

        return $html;
    }

    private function renderInput(array $row, string $name): string
    {
        $value = (string)$row['value'];
        $type = (string)($row['type'] ?? 'string');
        $placeholder = $this->placeholderAttribute($row);

        if ($type === 'boolean') {
            return '<select class="form-control input-sm" name="' . $name . '">'
                . $this->option('0', '关闭', $value)
                . $this->option('1', '开启', $value)
                . '</select>';
        }

        if ($type === 'integer') {
            return '<input class="form-control input-sm" type="number" step="1" name="' . $name . '" value="' . $this->escape($value) . '"' . $placeholder . '>';
        }

        if ($type === 'float') {
            return '<input class="form-control input-sm" type="number" step="0.001" name="' . $name . '" value="' . $this->escape($value) . '"' . $placeholder . '>';
        }

        if (($row['ui'] ?? '') === 'textarea') {
            return '<textarea class="form-control" name="' . $name . '"' . $placeholder . '>' . $this->escape($value) . '</textarea>';
        }

        return '<input class="form-control input-sm" type="text" name="' . $name . '" value="' . $this->escape($value) . '"' . $placeholder . '>';
    }

    private function renderFamilyBadge(array $row): string
    {
        $familyLabel = (string)($row['family_label'] ?? '');
        if ($familyLabel === '') {
            return '';
        }

        return '<span class="label label-info subject-config-module-badge">' . $this->escape($familyLabel) . '</span>';
    }

    private function renderFilterScript(): string
    {
        return <<<HTML
<script>
(function () {
    var buttons = document.querySelectorAll('.subject-config-module-filter');
    var rows = document.querySelectorAll('.subject-config-module-row');
    if (!buttons.length || !rows.length) {
        return;
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            var module = button.getAttribute('data-module-filter') || 'all';

            buttons.forEach(function (item) {
                item.classList.remove('active', 'btn-primary');
                item.classList.add('btn-default');
            });
            button.classList.add('active', 'btn-primary');
            button.classList.remove('btn-default');

            rows.forEach(function (row) {
                var rowModule = row.getAttribute('data-module') || '';
                row.style.display = module === 'all' || rowModule === module ? '' : 'none';
            });
        });
    });
})();
</script>
HTML;
    }

    private function renderErrors(): string
    {
        $errors = session('errors');
        if (!$errors || !$errors->any()) {
            return '';
        }

        $html = '<div class="alert alert-danger"><ul style="margin-bottom:0">';
        foreach ($errors->all() as $error) {
            $html .= '<li>' . $this->escape((string)$error) . '</li>';
        }

        return $html . '</ul></div>';
    }

    private function style(): string
    {
        return <<<CSS
    .subject-config-module-panel { background:#fff; border:1px solid #d8dde6; border-radius:4px; padding:14px 16px; margin-bottom:14px; }
    .subject-config-subject-form { display:flex; align-items:center; flex-wrap:wrap; gap:8px; margin:0; }
    .subject-config-subject-label { margin:0; color:#5f6b7a; font-weight:600; }
    .subject-config-subject-select { max-width:360px; }
    .subject-config-module-notice { margin-bottom:14px; }
    .subject-config-module-table { width:100%; border-collapse:collapse; font-size:13px; }
    .subject-config-module-table th, .subject-config-module-table td { border-bottom:1px solid #edf0f5; padding:8px; vertical-align:top; text-align:left; }
    .subject-config-module-table th { background:#fafbfc; color:#5f6b7a; font-weight:600; white-space:nowrap; }
    .subject-config-module-table .config-name { min-width:210px; }
    .subject-config-module-table .config-value { min-width:320px; }
    .subject-config-module-table .config-meta { min-width:300px; color:#667085; font-size:12px; }
    .subject-config-module-code { font-family:Menlo, Consolas, monospace; word-break:break-all; }
    .subject-config-module-help { color:#8a94a6; font-size:12px; margin-top:4px; }
    .subject-config-module-actions { display:flex; align-items:center; flex-wrap:wrap; gap:8px; margin-top:14px; }
    .subject-config-module-filters { display:flex; align-items:center; flex-wrap:wrap; gap:7px; margin-bottom:12px; }
    .subject-config-module-filter { border-radius:3px; }
    .subject-config-module-filter.active { background:#3c8dbc; border-color:#367fa9; color:#fff; box-shadow:none; }
    .subject-config-module-filter.active .badge { background:rgba(255,255,255,.92); color:#3c8dbc; }
    .subject-config-module-badge { display:inline-block; margin-left:6px; font-weight:400; vertical-align:middle; }
    .subject-config-module-table textarea { min-height:110px; font-family:Menlo, Consolas, monospace; font-size:12px; }
    .subject-config-station-key { margin-bottom:6px; font-family:Menlo, Consolas, monospace; }
    .subject-config-station-value { max-width:120px; margin-bottom:6px; }
CSS;
    }

    private function placeholderAttribute(array $row): string
    {
        $placeholder = (string)($row['placeholder'] ?? '');
        if ($placeholder === '') {
            return '';
        }

        return ' placeholder="' . $this->escape($placeholder) . '"';
    }

    private function option(string $value, string $label, string $current): string
    {
        $selected = $value === $current ? ' selected' : '';

        return '<option value="' . $this->escape($value) . '"' . $selected . '>' . $this->escape($label) . '</option>';
    }

    private function csrfField(): string
    {
        return csrf_field();
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
