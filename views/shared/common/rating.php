<div class="rating">
<?php
foreach ($display as $format):
    switch ($format):
        case 'score text': ?>
    <div class="rateit-score"><?php
        echo __('Score: %s', '<span class="rateit-average">' . $average_score . '</span>');
    ?><br /><?php
        echo __('Rates: %s', '<span class="rateit-count">' . $count_ratings . '</span>');
    ?></div>
        <?php break;

        case 'score visual': ?>
    <div class="rateit-score">
        <span <?php
            $attributes = sprintf('data-record_type="%s" data-record_id="%s" ', $record['record_type'], $record['record_id']);
            echo $attributes; ?> data-rateit-value="<?php echo $average_score; ?>" data-rateit-readonly="true" class="rateit rateit-average"></span>
        <span><?php echo __('(Rates: %s)', '</span><span class="rateit-count">' . $count_ratings . '</span><span>'); ?></span>
    </div>
        <?php break;

        case 'rate text': ?>
    <div class="rateit-rate"><?php
            if ($rating) :
                if (is_null($rating->score)):
                    $userscore = __('Cancelled');
                else:
                    $userscore = $rating->score;
                endif;
            else:
                $userscore = __('Not rated');
            endif;
            if ($is_current_user):
                echo __('My Rate: %s', '<span class="rateit-userscore">' . $userscore . '</span>');
            else:
                echo __('User Rate: %s', '<span class="rateit-userscore">' . $userscore . '</span>');
            endif;
    ?></div>
        <?php break;

        case 'rate visual':
            $attributes = sprintf('data-record_type="%s" data-record_id="%s" ', $record['record_type'], $record['record_id']);
            if ($rating) {
                $attributes .= $is_current_user
                    ? sprintf('data-rateit-value="%s" data-rateit-ispreset="true" ', $rating->score)
                    : sprintf('data-rateit-value="%s" data-rateit-readonly="true" ', $rating->score);
            } ?>
    <div class="rateit-rate">
        <span><?php echo $is_current_user ? __('My Rate:') : __('User Rate:'); ?></span>
        <span <?php echo $attributes; ?>data-rateit-step="0.01" class="rateit rateit-userscore"></span>
    </div>
        <?php break;
    endswitch;
endforeach; ?>
</div>
