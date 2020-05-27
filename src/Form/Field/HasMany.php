<?php

namespace Mallto\Admin\Form\Field;

use Encore\Admin\Admin;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\NestedForm;

/**
 * Class HasMany.
 */
class HasMany extends Field\HasMany
{

    /**
     * Setup tab template script.
     *
     * @param string $templateScript
     *
     * @return void
     */
    protected function setupScriptForTabView($templateScript)
    {
        $removeClass = NestedForm::REMOVE_FLAG_CLASS;
        $defaultKey = NestedForm::DEFAULT_KEY_NAME;

        $script = <<<EOT

$('#has-many-{$this->column} > .nav').off('click', 'i.close-tab').on('click', 'i.close-tab', function(){

    var that=$(this);

    swal({ 
      title: '确定删除吗？', 
      text: '你将无法恢复它！', 
      type: 'warning',
      showCancelButton: true, 
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      cancelButtonText: '取消',
      confirmButtonText: '确定删除！', 
    }).then(function(result){
      console.log(result);
      if (result.value) {
           var \$navTab = that.siblings('a');
           var \$pane = $(\$navTab.attr('href'));
           if( \$pane.hasClass('new') ){
               \$pane.remove();
           }else{
               \$pane.removeClass('active').find('.$removeClass').val(1);
           }
           if(\$navTab.closest('li').hasClass('active')){
               \$navTab.closest('li').remove();
               $('#has-many-{$this->column} > .nav > li:nth-child(1) > a').tab('show');
           }else{
               \$navTab.closest('li').remove();
           }
           
           swal(
             '删除！',
             '你的文件已经被删除。',
             'success'
             );
       }else if(result.dismiss === Swal.DismissReason.cancel){
       }
    })

    


});

var index = 0;
$('#has-many-{$this->column} > .header').off('click', '.add').on('click', '.add', function(){
    index++;
    var navTabHtml = $('#has-many-{$this->column} > template.nav-tab-tpl').html().replace(/{$defaultKey}/g, index);
    var paneHtml = $('#has-many-{$this->column} > template.pane-tpl').html().replace(/{$defaultKey}/g, index);
    $('#has-many-{$this->column} > .nav').append(navTabHtml);
    $('#has-many-{$this->column} > .tab-content').append(paneHtml);
    $('#has-many-{$this->column} > .nav > li:last-child a').tab('show');
    {$templateScript}
});

if ($('.has-error').length) {
    $('.has-error').parent('.tab-pane').each(function () {
        var tabId = '#'+$(this).attr('id');
        $('li a[href="'+tabId+'"] i').removeClass('hide');
    });
    
    var first = $('.has-error:first').parent().attr('id');
    $('li a[href="#'+first+'"]').tab('show');
}
EOT;

        Admin::script($script);
    }
}
