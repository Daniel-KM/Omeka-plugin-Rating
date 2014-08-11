<?php
/**
 * Helper to get some public stats.
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
     * Get the rating widget, according to current user.
     *
     * @param Record|array $record If array, contains record type and record id.
     * @param array $display Options to display the widget.
     *
     * @return string Html code from the theme.
     */
    public function widget($record, $display = array())
    {
        // Check and get record.
        $record = $this->_checkAndGetRecord($record);
        if (empty($record)) {
            return '';
        }

        $rating = $this->_table->findByRecordAndCurrentUserOrIP($record);

        // Set default display if needed.
        if (empty($display)) {
            $display = is_allowed('Rating_Rating', 'add')
                ? array('score text', 'my rate')
                : array('score');
        }
        // Check rights to rate.
        else {
            if (!is_allowed('Rating_Rating', 'add')) {
                $display = array_diff($display, array('my rate', 'my rate text'));
            }
        }

        $params = array(
            'record' => $record,
            'rating' => $rating,
            'display' => $display,
            // This values are set to avoid multiple queries.
            'average_score' => $this->_table->getAverageScore($record),
            'count_ratings' => $this->_table->getCountRatings($record),
        );

        return $this->view->partial('common/rating.php', $params);
    }

    /**
     * Helper to allow record param to be an object or an array.
     *
     * This is useful to avoid to fetch a record when it's not needed, in
     * particular when it's called from the theme.
     *
     * Recommended forms are object and associative array with 'record_type'
     * and 'record_id' as keys.
     *
     * @return null|Record
     */
    protected function _checkAndGetRecord($record)
    {
        if (is_object($record)) {
            return $record;
        }

        if (is_array($record)) {
            if (isset($record['record_type']) && isset($record['record_type'])) {
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
                return null;
            }
            return get_record_by_id($recordType, $recordId);
        }

        return null;
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
        $server = $this->_getRequest()->getServer();
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
}
