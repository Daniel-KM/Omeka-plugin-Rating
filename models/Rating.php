<?php

/**
 * @package Rating\models
 */
class Rating extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
    public $record_type;
    public $record_id;
    public $score;
    public $user_id;
    public $ip;
    public $user_agent;
    public $added;

    /**
     * Records related to a rating.
     *
     * @var array
     */
    protected $_related = array(
        'Record' => 'getRecord',
    );

    protected function _initializeMixins()
    {
        $this->_mixins[] = new Mixin_Owner($this);
    }

    /**
     * Get the record object.
     *
     * @return Record
     */
    public function getRecord()
    {
        // Manage the case where record type has been removed.
        if (class_exists($this->record_type)) {
            return $this->getTable($this->record_type)->find($this->record_id);
        }
    }

    /**
     * Get the user object.
     *
     * @return User
     */
    public function getAddedByUser()
    {
        if ($this->user_id) {
            return $this->getTable('User')->find($this->user_id);
        }
    }

    /**
     * Get the average of all scores of the record.
     *
     * @return numeric|null
     */
    public function getAverageScore()
    {
        return $this->getTable('Rating')->getAverageScore(array(
            'record_type' => $this->record_type,
            'record_id' => $this->record_id,
        ));
    }

    /**
     * Get the count of ratings of the record.
     *
     * @return integer|null
     */
    public function getCountRatings()
    {
        return $this->getTable('Rating')->getCountRatings(array(
            'record_type' => $this->record_type,
            'record_id' => $this->record_id,
        ));
    }

    /**
     * Set record type and record id of the rating.
     *
     * @param Record $record
     */
    public function setRecord($record)
    {
        $this->record_type = get_class($record);
        $this->record_id = $record->id;
    }

    /**
     * Set current user (id, ip and user agent) of the rating.
     */
    public function setCurrentUser()
    {
        $user = current_user();
        $this->user_id = is_object($user) ? $user->id : 0;
        $this->ip = $this->_getRemoteIP();
        $this->user_agent = $this->_getUserAgent();
    }

    /**
     * Get remote ip address. This check respects privacy settings.
     *
     * @todo Consolidate this function (see Rating model, Ajax controller, getRatingWidget).
     *
     * @return string
     */
    protected function _getRemoteIP()
    {
        $privacy = get_option('rating_privacy');
        if ($privacy == 'anonymous') {
            return '';
        }

        // Check if user is behind nginx.
        $ip = isset($_SERVER['HTTP_X_REAL_IP'])
            ? $_SERVER['HTTP_X_REAL_IP']
            : $_SERVER['REMOTE_ADDR'];

        return $privacy == 'clear'
            ? $ip
            : md5($ip);
    }

    /**
     * Get user agent.
     */
    protected function _getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Simple validation.
     */
    protected function _validate()
    {
        if (empty($this->record_type) || empty($this->record_id)) {
            $this->addError('record_id', __('Record is not correct.'));
        }
    }

    public function getProperty($property)
    {
        switch($property) {
            case 'record':
                return $this->getRecord();
            case 'average_score':
                return $this->getAverageScore();
            case 'count_ratings':
                return $this->getCountRatings();
            case 'added_username':
                $user = $this->getAddedByUser();
                return $user
                    ? $user->username
                    : __('Anonymous');
            default:
                return parent::getProperty($property);
        }
    }

    public function getResourceId()
    {
        return 'Rating';
    }
}
