<?php
global $cfg;

if (!$info['title'])
    $info['title'] = __('Merge Tickets');

?>
<style>
    .ticket {
        max-width: 400px;
        margin: 0 auto;
    }

    .ticket-number {
        width: 30%;
        float: left;
        text-align: center;
        background-color: #BCBCBC;
        color: #fff;
        padding: 3px;
        border-radius: 5px;
        margin-right: 15px;
    }

    .ticket-details {
        padding: 3px;
        font-weight: bold;
        margin-left: calc(30% + 15px);
    }

    .tickets {
        padding: 10px;
        background-color: #FDE8D3;
    }

    .search-ticket {
        text-align: center;
        margin: 20px;
    }

    .merge-into p {
        text-align: center;
    }

    .related-tickets {
        overflow-y: scroll;
        height: 200px;
    }

    .sep {
        position: relative;
        text-align: center;
    }

    .sep span {
        background-color: #f8f8f8;
        width: 70px;
        display: block;
        margin: 0 auto;
        position: relative;
    }

    .sep hr {
        position: absolute;
        width: 100%;
        top: 0px;
    }

    .clearfix {
        clear: both;
    }
</style>
<h3 class="drag-handle"><?php echo $info['title']; ?></h3>
<b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
<div class="clear"></div>
<hr/>
<?php
if ($info['error']) {
    echo sprintf('<p id="msg_error">%s</p>', $info['error']);
} elseif ($info['warn']) {
    echo sprintf('<p id="msg_warning">%s</p>', $info['warn']);
} elseif ($info['msg']) {
    echo sprintf('<p id="msg_notice">%s</p>', $info['msg']);
} elseif ($info['notice']) {
   echo sprintf('<p id="msg_info"><i class="icon-info-sign"></i> %s</p>',
           $info['notice']);
}

$tid = $info['tid'];
$action = $info[':action'];

 ?>
<div style="display:block; margin:5px;">
<?php if (!$info['error']): ?>
<div class="tickets">
    <div class="ticket">
      <?php
        $t = Ticket::lookup($tid);
        ?>
       <div class="ticket-number"><?php echo $t->getNumber(); ?></div>
       <div class="ticket-details"><span><?php echo date('M d', $t->getOpenDate()); ?> </span><?php echo $t->getName(); ?><br><span class="faded"><?php echo $t->getSubject(); ?></span></div>
    </div>
    <div class="merge-into"></div>
</div>
<div class="search-ticket">
    <div class="attached input">
      <input type="number" class="basic-search" data-url="ajax.php/tickets/lookup" name="query" autofocus="" size="30" value="" autocomplete="off" autocorrect="off" autocapitalize="off">
      <span type="submit" class="attached button"><i class="icon-search"></i>
      </span>
    </div>
</div>
<div class="related-tickets">
<h2 class="sep"><hr><span>OR</span></h2>
<h5>Select one of the following:</h5>
<?php
    $u = User::lookup($t->getOwnerId());
    foreach($u->tickets as $ticket){
        $rt = Ticket::lookup($ticket->getId());

        if($rt->getNumber() != $t->getNumber()) {
        ?>
        <a id="<?php echo $rt->getNumber(); ?>" data-date="<?php echo date('M d', $rt->getOpenDate()); ?>" data-name="<?php echo $rt->getName(); ?>" class="ticket" style="width: calc(50% - 20px);float: left;margin: 10px;padding: 10px;border: 1px solid;">
           <div class="ticket-number"><?php echo $rt->getNumber(); ?></div>
           <div class="ticket-details"><span><?php echo date('M d', $rt->getOpenDate()); ?> </span><?php echo $rt->getName(); ?><br><span class="faded"><?php echo $rt->getSubject(); ?></span></div>
        </a>
        <?php
        }
    }
?>
</div>
<?php endif; ?>
</div>
<form class="mass-action" method="post"
    name="merge"
    id="merge"
    action="<?php echo $action; ?>">
    <input type="hidden" name="tid" value="<?php echo $info['tid']; ?>">

    <hr>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" name="cancel" class="close"
            value="<?php echo __('Cancel'); ?>">
        </span>
        <?php if(!$info['error']): ?>
        <span class="buttons pull-right">
            <input type="submit" class="red button" value="<?php
            echo $verb ?: __('Merge'); ?>">
        </span>
        <?php endif; ?>
     </p>
</form>

<script type="text/javascript">
$(function() {

    var last_req;
    $('input.basic-search').typeahead({
        source: function (typeahead, query) {
            if (last_req) last_req.abort();
            var $el = this.$element;
            var url = $el.data('url')+'?q='+encodeURIComponent(query);
            last_req = $.ajax({
                url: url,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            var $el = this.$element;
            var form = $('#merge');
            $el.val(obj.id);
            if (obj.id) {
                form.append($('<input type="hidden" name="number">').val(obj.id))
                $('.merge-into').html($('<p>Merge to:</p><div class="ticket"><div class="ticket-number">' + obj.id + '</div><div class="ticket-details">' + obj.info + '</div></div>'));
                //form.submit();
            }
        },
        property: "matches"
    });

    $('.related-tickets a').click(function(obj) {
        obj.preventDefault();
        obj.stopPropagation();

        var form = $('#merge');
        if (obj.currentTarget.id) {
                $('input.basic-search').val('');
                form.append($('<input type="hidden" name="number">').val(obj.currentTarget.id))
                $('.merge-into').html($('<p>Merge to:</p><div class="ticket"><div class="ticket-number">' + obj.currentTarget.id + '</div><div class="ticket-details">' + $(obj.currentTarget).data('date') + ' ' + $(obj.currentTarget).data('name') + '</div></div>'));
            }
    });

    $('#merge').on('submit', function(e) {
        if(!$('input[name=number]').length) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
</script>
