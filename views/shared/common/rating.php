<div class="rating">
<?php
foreach ($display as $format):
    switch ($format):

        case 'score': ?>
    <div class="rateit-score">
        <span <?php
            $attributes = sprintf('data-record_type="%s" data-record_id="%s" ', get_class($record), $record->id);
            echo $attributes; ?> data-rateit-value="<?php echo $average_score; ?>" data-rateit-readonly="true" class="rateit rateit-average"></span>
        <span><?php echo __('(Rates: %s)', '</span><span class="rateit-count">' . $count_ratings . '</span><span>'); ?></span>
    </div>
        <?php break;

        case 'my rate':
            $attributes = sprintf('data-record_type="%s" data-record_id="%s" ', get_class($record), $record->id);
            if ($rating) {
                $attributes .= sprintf('data-rateit-value="%s" data-rateit-ispreset="true" ', $rating->score);
            } ?>
    <div class="rateit-myrate">
        <span><?php echo __('My Rate:');  ?></span>
        <span <?php echo $attributes; ?> data-rateit-step="0.01" class="rateit"></span>
    </div>
        <?php break;

        case 'score text': ?>
    <div class="rateit-score rateit-text"><?php
        echo __('Score: %s', '<span class="rateit-average">' . $average_score . '</span>');
    ?><br /><?php
        echo __('Rates: %s', '<span class="rateit-count">' . $count_ratings . '</span>');
    ?></div>
        <?php break;

        case 'my rate text': ?>
    <div class="rateit-myrate rateit-text"><?php
        if ($rating) :
            if (is_null($rating->score)):
                echo __('My Rate: %s', __('Cancelled'));
            else:
                echo __('My Rate: %s', $rating->score);
            endif;
        else:
                echo __('My Rate: Not rated');
        endif;
    ?></div>
        <?php break;

    endswitch;
endforeach; ?>
</div>
