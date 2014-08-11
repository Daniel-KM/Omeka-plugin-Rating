<fieldset id="fieldset-rating-form"><legend><?php echo __('Rating Widget'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('rating_add_to_items_show', __('Add To items/show Page')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formCheckbox(
                'rating_add_to_items_show', true,
                array('checked' => (boolean) get_option('rating_add_to_items_show'))); ?>
            <p class="explanation"><?php echo
                __('If checked, the rating widget will be added to all item show pages via the hook.');
            ?></p>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('rating_add_to_items_browse', __('Add To items/browse Page')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formCheckbox(
                'rating_add_to_items_browse', true,
                array('checked' => (boolean) get_option('rating_add_to_items_browse'))); ?>
            <p class="explanation"><?php echo
                __('If checked, the rating widget will be added to each record on the items/browse page via the hook.');
            ?></p>
        </div>
    </div>
</fieldset>
<fieldset id="fieldset-rating-rights"><legend><?php echo __('Rights and Roles'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('rating_public_allow_rate', __('Allow Public to Rate')); ?>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formCheckbox(
                'rating_public_allow_rate', true,
                array('checked'=>(boolean) get_option('rating_public_allow_rate'))); ?>
            <p class="explanation">
                <?php echo __('Allow everybody to rate or not.'); ?>
            </p>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $view->formLabel('rating_add_roles', __('Roles that can Rate')); ?>
        </div>
        <div class="inputs five columns omega">
            <div class="input-block">
                <?php
                    $currentRoles = unserialize(get_option('rating_add_roles'));
                    $userRoles = get_user_roles();
                    unset($userRoles['super']);
                    echo '<ul>';
                    foreach ($userRoles as $role => $label) {
                        echo '<li>';
                        echo $view->formCheckbox('rating_add_roles[]', $role,
                            array('checked' => in_array($role, $currentRoles) ? 'checked' : ''));
                        echo $label;
                        echo '</li>';
                    }
                    echo '</ul>';
                ?>
            </div>
        </div>
    </div>
</fieldset>
<fieldset id="fieldset-rating-privacy"><legend><?php echo __('Privacy'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <label><?php echo __("Level of Privacy"); ?></label>
        </div>
        <div class="inputs five columns omega">
            <?php echo get_view()->formRadio('rating_privacy',
                get_option('rating_privacy'),
                null,
                array(
                    'anonymous' => __('Anonymous'),
                    'hashed' => __('Hashed IP'),
                    'partial_1' => __('Partial IP (first hex)'),
                    'partial_2' => __('Partial IP (first 2 hexs)'),
                    'partial_3' => __('Partial IP (first 3 hexs)'),
                    'clear' => __('Clear IP'),
                )); ?>
            <p class="explanation">
                <?php echo __('Choose the level of privacy (default: hashed IP).')
                . ' ' . __('If anonymous, no check will be done when an unidentified visitor rates a record multiple times.')
                . ' ' . __('A change applies only to new ratings.');
                ?>
            </p>
        </div>
    </div>
</fieldset>
