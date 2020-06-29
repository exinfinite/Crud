<?php
namespace Exinfinite;

class DBALCrud {
    private $conn;
    function __construct(\Doctrine\DBAL\Connection $conn) {
        $this->conn = $conn;
    }
    function getConn() {
        return $this->conn;
    }
    function queryBuilder() {
        return $this->conn->createQueryBuilder();
    }
    function selectColsStmt($table, $cols = '*', Array $orders = []) {
        $stmt = $this->queryBuilder()->select($cols)->from($table);
        foreach ($orders as $by => $order) {
            $stmt->addOrderBy($by, $order);
        }
        return $stmt;
    }
    function selectAllStmt($table, Array $orders = []) {
        return $this->selectColsStmt($table, '*', $orders);
    }
    function selectAll($table, Array $orders = []) {
        return $this->selectAllStmt($table, $orders)->execute()->fetchAll();
    }
    function selectByOffsetStmt($table, Array $orders = [], $start, $offset) {
        return $this->selectAllStmt($table, $orders)->setFirstResult($start)->setMaxResults($offset);
    }
    function selectByOffset($table, Array $orders = [], $start, $offset) {
        return $this->selectByOffsetStmt($table, $orders, $start, $offset)->execute()->fetchAll();
    }
    function selectCols($table, $cols = '*', Array $orders = []) {
        return $this->selectColsStmt($table, $cols, $orders)->execute()->fetchAll();
    }
    function selectColsByOffset($table, $cols = '*', Array $orders = [], $start = 0, $offset) {
        return $this->selectColsStmt($table, $cols, $orders)->setFirstResult($start)->setMaxResults($offset)->execute()->fetchAll();
    }
    function countTotal($table) {
        return $this->queryBuilder()->select('COUNT(*)')->from($table)->execute()->fetchColumn();
    }
    function create($table, Array $datas) {
        if (count($datas) <= 0) {
            return;
        }
        $stmt = $this->queryBuilder()->insert($table);
        $cols = array_keys($datas);
        $stmt->values(array_fill_keys($cols, '?'));
        foreach ($cols as $idx => $pt) {
            $stmt->setParameter($idx, $datas[$pt]);
        }
        return $stmt->execute();
    }
    function updateStmt($table, Array $datas, $uid, $uid_col) {
        if (count($datas) <= 0) {
            return false;
        }
        $qb = $this->queryBuilder();
        $uids = (array) $uid;
        $stmt = $qb->update($table);
        foreach (array_keys($datas) as $idx => $pt) {
            $stmt->set($pt, '?');
            $stmt->setParameter($idx, $datas[$pt]);
        }
        return $stmt->andWhere(
            $qb->expr()->in($uid_col, $uids)
        );
    }
    function update($table, Array $datas, $uid, $uid_col) {
        return $this->updateStmt($table, $datas, $uid, $uid_col)->execute();
    }
    function deleteStmt($table, $uid, $uid_col) {
        $qb = $this->queryBuilder();
        $uids = (array) $uid;
        return $qb->delete($table)->andWhere(
            $qb->expr()->in($uid_col, $uids)
        );
    }
    function delete($table, $uid, $uid_col) {
        return $this->deleteStmt($table, $uid, $uid_col)->execute();
    }
    function callProcStmt($procedure) {
        return $this->conn->executeQuery("Call {$procedure}");
    }
    function callProc($procedure) {
        return $this->callProcStmt($procedure)->fetchAll();
    }
    function transaction(callable $call, callable $exception = null) {
        $this->conn->beginTransaction();
        try {
            $rst = call_user_func($call);
            $this->conn->commit();
            return $rst;
        } catch (Exception $e) {
            $this->conn->rollback();
            if (!is_null($exception)) {
                return call_user_func($call, $e);
            }
        }
    }
}
?>