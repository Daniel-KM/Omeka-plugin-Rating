<script type ="text/javascript">
jQuery('.rating .rateit').bind('rated reset', function(e) {
    var ri = jQuery(this);
    var value = ri.rateit('value');
    if (e.type == 'reset') { value = 'null'; };
    jQuery.ajax({
        type: 'POST',
        url: '<?php echo url('/rating/ajax/add'); ?>',
        data: {
            record_type: ri.data('record_type'),
            record_id: ri.data('record_id'),
            score: value
        },
        success: function (data) {
            var result = jQuery.parseJSON(data);
            var riw = ri.closest('.rating');
            riw.find('.rateit-average.rateit').rateit('value', result.average_score);
            riw.find('.rateit-average:not(.rateit)').text(result.average_score);
            riw.find('.rateit-count').text(result.count_ratings);
        },
        error: function (jxhr, msg, err) {
            ri.parent().append('<span class="rateit-error">' + jxhr.responseText + '</span>');
        }
    });
});
</script>
