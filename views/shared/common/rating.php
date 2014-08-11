<div class="rating">
<?php
foreach ($display as $format):
    switch ($format):

        case 'score visual': ?>
    <div class="rateit-score">
        <span <?php
            $attributes = sprintf('data-record_type="%s" data-record_id="%s" ', get_class($record), $record->id);
            echo $attributes; ?> data-rateit-value="<?php echo $average_score; ?>" data-rateit-readonly="true" class="rateit rateit-average"></span>
        <span><?php echo __('(Rates: %s)', '</span><span class="rateit-count">' . $count_ratings . '</span><span>'); ?></span>
    </div>
        <?php break;

        case 'my rate visual':
            $attributes = sprintf('data-record_type="%s" data-record_id="%s" ', get_class($record), $record->id);
            if ($rating) {
                $attributes .= sprintf('data-rateit-value="%s" data-rateit-ispreset="true" ', $rating->score);
            } ?>
    <div class="rateit-myrate">
        <span><?php echo __('My Rate:');  ?></span>
        <span <?php echo $attributes; ?> data-rateit-step="0.01" class="rateit rateit-myscore"></span>
    </div>
        <?php break;

        case 'score text': ?>
    <div class="rateit-score"><?php
        echo __('Score: %s', '<span class="rateit-average">' . $average_score . '</span>');
    ?><br /><?php
        echo __('Rates: %s', '<span class="rateit-count">' . $count_ratings . '</span>');
    ?></div>
        <?php break;

        case 'my rate text': ?>
    <div class="rateit-myrate"><?php
        if ($rating) :
            if (is_null($rating->score)):
                $myscore = __('Cancelled');
            else:
                $myscore = $rating->score;
            endif;
        else:
                $myscore = __('Not rated');
        endif;
        echo __('My Rate: %s', '<span class="rateit-myscore">' . $myscore . '</span>');
    ?></div>
        <?php break;

    endswitch;
endforeach; ?>
</div>
