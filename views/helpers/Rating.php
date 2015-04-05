<?php
/**
 * Helper to get rating.
 */
class Rating_View_Helper_Rating extends Zend_View_Helper_Abstract
{
    protected $_table;

    /**
     * Load the hit table one time only.
     */
    public function __construct()
    {
        $this->_table = get_db()->getTable('Rating');
    }

    /**
     * Get the rating.
     *
     * @return This view helper.
     */
    public function rating()
    {
        return $this;
    }

    /**
     * Get the average score of a record as a simple value.
     *
     * @param Record|array $record If array, contains record type and record id.
     *
     * @return integer|null
     */
    public function score($record)
    {
        return $this->_table->getAverageScore($record);
    }

    /**
     * Get a user rate for a record as a simple value.
     *
     * @param Record|array $record If array, contains record type and record id.
     * @param User|integer $user. If null, current user.
     *
     * @return integer|null
     */
    public function rate($record, $user = null)
    {
        if (empty($user)) {
            $user = current_user();
        }

        $rating = $this->_table->findByRecordAndUserOrIP($record, $user);

        return $rating ? $rating->score : null;
    }

    /**
     * Get the rating widget, according to current user.
     *
     * @param Record|array $record If array, contains record type and record id.
     * @param User|integer $user. If null, current user.
     * @param array $display Options to display the widget (default depends of
     * user).
     *
     * @return string Html code from the theme.
     */
    public function widget($record, $user = null, $display = array())
    {
        // Check and get record.
        $record = $this->checkAndPrepareRecord($record);
        if (empty($record['record_type']) || empty($record['record_id'])) {
            return '';
        }

        if (empty($user)) {
            $user = current_user();
            $isCurrentUser = true;
        }
        else {
            $isCurrentUser = $this->_isCurrentUser($user);
        }

        if (in_array('score', $display)) {
            return $this->score($record);
        }
        elseif (in_array('rate', $display)) {
            return $this->score($record, $user);
        }

         // Set default display if needed.
        if (empty($display)) {
            $display = is_allowed('Rating_Rating', 'add')
                ? array('score text', 'rate visual')
                : array('score visual');
        }
        // Check rights to rate.
        else {
            if (!is_allowed('Rating_Rating', 'add')) {
                $display = array_diff($display, array('rate visual', 'rate text'));
            }
        }

        $rating = $this->_table->findByRecordAndCurrentUserOrIP($record);

        $params = array(
            'record' => $record,
            'rating' => $rating,
            'display' => $display,
            // This values are set to avoid multiple queries.
            'average_score' => $this->_table->getAverageScore($record),
            'count_ratings' => $this->_table->getCountRatings($record),
            'is_current_user' => $isCurrentUser,
        );

        return $this->view->partial('common/rating.php', $params);
    }

    /**
     * Helper to get params from a record. If no record, return empty record.
     *
     * This allows record to be an object or an array. This is useful to avoid
     * to fetch a record when it's not needed, in particular when it's called
     * from the theme.
     *
     * Recommended forms are object and associative array with 'record_type'
     * and 'record_id' as keys.
     *
     * @return array Associatie array with record type and record id.
     */
    public function checkAndPrepareRecord($record)
    {
        if (is_object($record)) {
            $recordType = get_class($record);
            $recordId = $record->id;
        }
        elseif (is_array($record)) {
            if (isset($record['record_type']) && isset($record['record_id'])) {
                $recordType = $record['record_type'];
                $recordId = $record['record_id'];
            }
            elseif (count($record) == 1) {
                $recordId = reset($record);
                $recordType = key($record);
            }
            elseif (count($record) == 2) {
                $recordType = array_shift($record);
                $recordId = array_shift($record);
            }
            else {
                return array(
                    'record_type' => '',
                    'record_id' => 0,
                );
            }
        }
        else {
            return array(
                'record_type' => '',
                'record_id' => 0,
            );
        }
        return array(
            'record_type' => $recordType,
            'record_id' => $recordId,
        );
    }

    /**
     * Get remote ip address. This check respects privacy settings.
     *
     * @return string
     */
    public function getRemoteIP()
    {
        $privacy = get_option('rating_privacy');
        if ($privacy == 'anonymous') {
            return '';
        }

        // Check if user is behind nginx.
        $server = Zend_Controller_Front::getInstance()->getRequest()->getServer();
        $ip = isset($server['HTTP_X_REAL_IP'])
            ? $server['HTTP_X_REAL_IP']
            : $server['REMOTE_ADDR'];

        switch ($privacy) {
            case 'clear': return $ip;
            case 'hashed': return md5($ip);
            case 'partial_3':
                $partial = explode('.', $ip);
                if (isset($partial[3])) {
                    unset($partial[3]);
                }
                return implode('.', $partial);
            case 'partial_2':
                $partial = explode('.', $ip);
                if (isset($partial[3])) {
                    unset($partial[3]);
                    unset($partial[2]);
                }
                return implode('.', $partial);
            case 'partial_3':
                $partial = explode('.', $ip);
                if (isset($partial[3])) {
                    unset($partial[3]);
                    unset($partial[2]);
                    unset($partial[1]);
                }
                return implode('.', $partial);
        }
    }

    /**
     * Check if a user is the current one.
     *
     * @param User|integer $user User or user id.
     *
     * @return boolean True if the user is the current one, else false.
     */
    protected function _isCurrentUser($user)
    {
        if (empty($user)) {
            return false;
        }

        $currentUser = current_user();
        if (empty($currentUser)) {
            return false;
        }

        $userId = is_object($user) ? $user->id : (integer) $user;
        return ($currentUser->id == $userId);
    }
}
