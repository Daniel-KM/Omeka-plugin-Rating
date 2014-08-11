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
