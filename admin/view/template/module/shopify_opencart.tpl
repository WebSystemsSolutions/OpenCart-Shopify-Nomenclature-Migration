<!-- admin/view/template/module/recent_products.tpl -->
<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-recent-products" data-toggle="tooltip"
                        title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data"
                      id="form-recent-products" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-name"><?php echo $entry_name; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="name" value="<?php echo $name; ?>"
                                   placeholder="<?php echo $entry_name; ?>" id="input-name" class="form-control"/>
                            <?php if ($error_name) { ?>
                                <div class="text-danger"><?php echo $error_name; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-limit"><?php echo $entry_token; ?></label>
                        <div class="col-sm-10">
                            <input type="password" name="token" value="<?php echo $token; ?>"
                                   placeholder="<?php echo $entry_token; ?>" id="input-limit" class="form-control"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php if ($created) { ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"> <?php echo $text_shopify_opencart; ?></h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <form class="form_trigger_from" action="<?php echo $action; ?>" method="post">
                                <input type="hidden" name="productsShopifyToOpencart" value="1">
                                <button class="btn"><?php echo $button_shopify_to_opencart ?></button>
                            </form>

                        </div>
                        <div class="col-sm-3">
                            <form class="form_trigger_to" action="<?php echo $action; ?>" method="post">
                                <input type="hidden" name="productsOpencartToShopify" value="1">
                                <button class="btn"><?php echo $button_opencart_to_shopify ?></button>
                            </form>
                        </div>
                        <div class="col-sm-3">
                            <form class="form_trigger_cache" action="<?php echo $action; ?>" method="post">
                                <input type="hidden" name="delete_cache" value="1">
                                <button class="btn">Delete cache</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Trigger the modal with a click -->
            <input type="hidden" type="button" id="input_trigger" data-toggle="modal" data-target="#myModal"></input>

            <!-- Modal -->
            <div id="myModal" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Load Processing</h4>
                        </div>
                        <div class="modal-body">
                            <p>Running</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>

                </div>
            </div>
            <script>
                $('.form_trigger_to').submit(function (e) {
                    e.preventDefault();
                    $(e.currentTarget[1]).html('Running');
                    $('#input_trigger').click();
                    console.time('someFunction');
                    var url = e.currentTarget.action;
                    var data = $(e.currentTarget).serialize();
                    //var name = e.currentTarget[0].name;
                    //var value = e.currentTarget[0].value;
                    function test() {
                        $.ajax({
                            url: url,
                            method: "POST",
                            data: data,
                            dataType: "json"
                        }).done(function (callback) {
                            if (callback.add_count_products < callback.all_count_products) {
                                setTimeout(test, 1000);
                            }

                            $('#myModal .modal-body p').html(callback.add_count_products+'/'+callback.all_count_products+' progress');

                            if (callback.add_count_products == callback.all_count_products) {
                                console.timeEnd('someFunction');
                                $(e.currentTarget[1]).html('<?php echo $button_opencart_to_shopify ?>');
                            }
                        }).fail(function() {
                            setTimeout(test, 3000);
                        });
                    }
                    test();

                    return false;
                });
                $('.form_trigger_from').submit(function (e) {
                    e.preventDefault();
                    $(e.currentTarget[1]).html('Running');
                    //$('#input_trigger').click();
                    console.time('someFunction');
                    var url = e.currentTarget.action;
                    var data = $(e.currentTarget).serialize();
                    //var name = e.currentTarget[0].name;
                    //var value = e.currentTarget[0].value;
                    function test() {
                        $.ajax({
                            url: url,
                            method: "POST",
                            data: data,
                            dataType: "json"
                        }).done(function (callback) {
                            console.timeEnd('someFunction');
                            $(e.currentTarget[1]).html('<?php echo $button_shopify_to_opencart ?> [all sync]');
                        });
                    }
                    test();

                    return false;
                });
                $('.form_trigger_cache').submit(function (e) {
                    e.preventDefault();
                    var url = e.currentTarget.action;
                    var data = $(e.currentTarget).serialize();
                    //var name = e.currentTarget[0].name;
                    //var value = e.currentTarget[0].value;
                    function test() {
                        $.ajax({
                            url: url,
                            method: "POST",
                            data: data,
                            dataType: "json"
                        }).done(function (callback) {
                            console.log(callback);
                        });
                    }
                    test();

                    return false;
                });
            </script>
        <?php } ?>
    </div>
</div>
<?php echo $footer; ?>