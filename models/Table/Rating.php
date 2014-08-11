<?php

/**
 * @package Rating\models\Table
 */
class Table_Rating extends Omeka_Db_Table
{
    /**
     * Retrieve ratings associated with a record.
     *
     * @param Record|array $record If array, contains record type and record id.
     *
     * @return array
     */
    public function findByRecord($record)
    {
        $params = $this->_getParamsFromRecord($record);
        if (empty($params)) {
            return;
        }

        $result = $this->findBy($params);
        return $result;
    }

    /**
     * Find the rating of record of a user by id or ip (exclusive).
     *
     * @param Record|array $record If array, contains record type and record id.
     * @param integer|User $user User object or user id.
     * @param string $ip
     * @return Rating|null.
     */
    public function findByRecordAndUserOrIP($record, $user = null, $ip = null)
    {
        $params = $this->_getParamsFromRecord($record);
        if (empty($params)) {
            return;
        }

        if ($user) {
            $params['user_id'] = is_object($user) ? $user->id : $user;
        }
        elseif ($ip) {
            $params['user_id'] = 0;
            $params['ip'] = $ip;
        }
        else {
            return;
        }

        $result = $this->findBy($params, 1);
        return $result ? reset($result) : null;
    }

    /**
     * Find the rating of record of the current user by id or ip (exclusive).
     *
     * @param Record|array $record If array, contains record type and record id.
     * @return Rating|null.
     */
    public function findByRecordAndCurrentUserOrIP($record)
    {
        $user = current_user();
        $ip = get_view()->rating()->getRemoteIp();
        return $this->findByRecordAndUserOrIP();
    }

    /**
     * Get the average of all scores of the record.
     *
     * @param Record|array $record If array, contains record type and record id.
     *
     * @return numeric|null
     */
    public function getAverageScore($record)
    {
        $params = $this->_getParamsFromRecord($record);
        if (empty($params)) {
            return;
        }

        $db = get_db();
        $sql = "
        SELECT ROUND(AVG(score), 2)
        FROM `$db->Rating`
        WHERE record_type = ?
            AND record_id = ?
            AND score IS NOT NULL
        ";
        $bind = array(
            $params['record_type'],
            $params['record_id'],
        );
        $result = $db->fetchOne($sql, $bind);

        return $result;
    }

    /**
     * Get the average of all scores of the record (not cancelled ratings).
     *
     * @param Record|array $record If array, contains record type and record id.
     *
     * @return numeric|null
     */
    public function getCountRatings($record)
    {
        $params = $this->_getParamsFromRecord($record);
        if (empty($params)) {
            return;
        }

        $db = get_db();
        $sql = "
        SELECT COUNT(id)
        FROM `$db->Rating`
        WHERE record_type = ?
            AND record_id = ?
            AND score IS NOT NULL
        ";
        $bind = array(
            $params['record_type'],
            $params['record_id'],
        );
        $result = $db->fetchOne($sql, $bind);

        return $result;
    }

    /**
     *Get user score for a record. If no user is set, check via the ip, if any.
     *
     * @param Record|array $record If array, contains record type and record id.
     * @return numeric|null
     */
    public function getUserScore($record, $user = null, $ip = null)
    {
        $rating = $this->findByRecordAndUserOrIP($record, $user, $ip);
        return $rating ? $rating->score : null;
    }

    /**
     * Find all ratings of a user by id or ip (exclusive).
     *
     * @param integer|User $user User object or user id.
     * @param string $ip
     * @return array of ratings.
     */
    public function findByUserOrIP($user = null, $ip = null)
    {
        $params = array();

        if ($user) {
            $params['user_id'] = is_object($user) ? $user->id : $user;
        }
        elseif ($ip) {
            $params['user_id'] = 0;
            $params['ip'] = $ip;
        }
        else {
            return;
        }

        return $this->findBy($params);
    }

    /**
     * Find anonymous ratings.
     *
     * @return array of ratings.
     */
    public function findByAnonymous()
    {
        $params = array(
            'user_id' => 0,
        );
        return $this->findBy($params);
    }

    /**
     * Filter ratings by record.
     *
     * @todo As Omeka, manages only items.
     *
     * @see self::applySearchFilters()
     * @param Omeka_Db_Select
     * @param Record|array $record If array, contains record type and record id.
     */
    public function filterByRecord($select, $record)
    {
        $params = $this->_getParamsFromRecord($record);
        if (empty($params)) {
            return;
        }

        $alias = $this->getTableAlias();
        $select->where($alias . '.record_type = ?', $params['record_type']);
        $select->where($alias . '.record_id = ?', $params['record_id']);
    }

    /**
     * @param Omeka_Db_Select
     * @param array
     * @return void
     */
    public function applySearchFilters($select, $params)
    {
        $alias = $this->getTableAlias();
        $boolean = new Omeka_Filter_Boolean;
        foreach ($params as $key => $value) {
            if ($value === null || (is_string($value) && trim($value) == '')) {
                continue;
            }
            switch ($key) {
                case 'record':
                    $this->filterByRecord($select, $value);
                    break;
                case 'user_id':
                    $this->filterByUser($select, $value, 'user_id');
                    break;
                default:
                    parent::applySearchFilters($select, array($key => $value));
            }
        }

        // If we returning the data itself, we need to group by the record id.
        $select->group("$alias.id");
    }

    /**
     * Helper to get params from a record.
     *
     * This allows record to be an object or an array. This is useful to avoid
     * to fetch a record when it's not needed, in particular when it's called
     * from the theme.
     *
     * Recommended forms are object and associative array with 'record_type'
     * and 'record_id' as keys.
     *
     * @return null|array Associatie array with record type and record id.
     */
    protected function _getParamsFromRecord($record)
    {
        if (is_object($record)) {
            $recordType = get_class($record);
            $recordId = $record->id;
        }
        elseif (is_array($record)) {
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
        }
        else {
            return null;
        }
        return array(
            'record_type' => $recordType,
            'record_id' => $recordId,
        );
    }
}
