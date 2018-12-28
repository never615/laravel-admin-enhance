@if($errors->hasBag('exception'))
    <?php $error = $errors->getBag('exception');?>
    <div class="alert alert-warning alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4>
            <i class="icon fa fa-warning"></i>
            <i style="border-bottom: 1px dotted #fff;cursor: pointer;">{!! $error->get('message')[0] !!}</i>
        </h4>
    </div>
@endif