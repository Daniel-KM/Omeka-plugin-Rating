<?php
/**
 * The Rating Ajax controller class.
 *
 * @package Rating
 */
class Rating_AjaxController extends Omeka_Controller_AbstractActionController
{
    protected $_minScore = 0;
    protected $_maxScore = 5;
    // See RateIt step.
    protected $_precision = 2;

    /**
     * Controller-wide initialization. Sets the underlying model to use.
     */
    public function init()
    {
        // Don't render the view script.
        $this->_helper->viewRenderer->setNoRender(true);

        $this->_helper->db->setDefaultModelName('Rating');
    }

    /**
     * Handle AJAX requests to rate a record.
     */
    public function addAction()
    {
        if (!$this->_checkAjax('add')) {
            return;
        }

        $record = $this->_checkAndGetRecord();
        if (!$record) {
            return;
        }

        $score = $this->_checkAndGetScore();
        if ($score === false) {
            return;
        }

        // User, record and score are checked, so process rating.
        try {
            // Check if a rating exists for the user or visitor (same id or ip).
            $rating = get_db()->getTable('Rating')->findByRecordAndCurrentUserOrIP($record);
            if (!$rating) {
                $rating = new Rating;
                $rating->setRecord($record);
            }

            // Add or update user (via Ajax, the current user is the real one).
            $rating->setCurrentUser();
            $rating->score = $score;
            $result = $rating->save(false);
            if ($result) {
                $this->getResponse()->setBody(json_encode(array(
                    'average_score' => $rating->getAverageScore(),
                    'count_ratings' => $rating->getCountRatings(),
                )));
            }
            else {
                $this->getResponse()->setHttpResponseCode(400);
                $this->getResponse()->setBody(__('Error. Retry later.'));
            }
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
        }
    }

    /**
     * Check AJAX requests.
     *
     * 400 Bad Request
     * 403 Forbidden
     * 500 Internal Server Error
     *
     * @param string $action
     */
    protected function _checkAjax($action)
    {
        // Only allow AJAX requests.
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            $this->getResponse()->setHttpResponseCode(403);
            return false;
        }

        // Allow only valid calls.
        if ($request->getControllerName() != 'ajax'
                || $request->getActionName() != $action
            ) {
            $this->getResponse()->setHttpResponseCode(400);
            return false;
        }

        // Allow only allowed users.
        if (!is_allowed('Rating_Rating', $action)) {
            $this->getResponse()->setHttpResponseCode(403);
            return false;
        }

        return true;
    }

    /**
     * Check and get record.
     *
     * @internal Right to access to record is checked via get_record_by_id().
     *
     * @return Record|boolean
     */
    protected function _checkAndGetRecord()
    {
        $recordType = $this->_getParam('record_type');
        if (!in_array($recordType, array('Item', 'File', 'Collection', 'SimplePage', 'Exhibit', 'ExhibitPage'))) {
            $this->getResponse()->setHttpResponseCode(400);
            return false;
        }

        $recordId = $this->_getParam('record_id');
        if (!$recordId) {
            $this->getResponse()->setHttpResponseCode(400);
            return false;
        }

        $record = get_record_by_id($recordType, $recordId);
        if (!$record) {
            $this->getResponse()->setHttpResponseCode(403);
            return false;
        }

        return $record;
    }

    /** Check and get score.
     *
     * @return integer|null|boolean
     */
    protected function _checkAndGetScore()
    {
        $score = $this->_getParam('score');
        if ($score === 'null') {
            $score = null;
        }
        else {
            $score = (float) $score;
            $score = round($score, $this->_precision);
            if ($score < $this->_minScore || $score > $this->_maxScore) {
                $this->getResponse()->setHttpResponseCode(400);
                return false;
            }
        }
        return $score;
    }
}
