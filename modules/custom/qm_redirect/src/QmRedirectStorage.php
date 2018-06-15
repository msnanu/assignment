<?php

namespace Drupal\qm_redirect;

class QmRedirectStorage {

  /**
   * Query to get all records.
   */
  static function getAll() {

    $result = db_query('SELECT * FROM {qm_redirect} order by rid desc')->fetchAllAssoc('rid');
    return $result;
  }

  /**
   * Query to filter records.
   */
  static function getByFilter($arg = Null, $sort_header = NUll) {
    $count = 10;
    if (isset($_GET['sort']))
      $sort_order = $_GET['sort'];
    else
      $sort_order = 'desc';

    if (isset($_GET['order']))
      $sort_field = $sort_header[$_GET['order']];
    else
      $sort_field = 'source';

    $query = db_select('qm_redirect', 'q');

    if ($arg) {
      $query->condition('source', '%' . db_like($arg) . '%', 'LIKE');
    }
    $count_query = clone $query;
    $count_query->addExpression('Count(q.rid)');

    $paged_query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender');

    $paged_query->limit($count);
    $paged_query->setCountQuery($count_query);

    $result = $paged_query
        ->fields('q')
        ->orderBy($sort_field, $sort_order)
        ->execute()
        ->fetchAllAssoc('rid');

    return $result;
  }

  static function exists($id) {
    return (bool) $this->get($id);
  }

  /**
   * Query to records for perticular rid.
   */
  static function get($id) {
    $result = db_query('SELECT * FROM {qm_redirect} WHERE rid = :id', array(':id' => $id))->fetchAllAssoc('rid');
    if ($result) {
      return $result[$id];
    }
    else {
      return FALSE;
    }
  }

  /**
   * Query to add records.
   */
  static function add($questionmarkredirect) {

    $questionmarkredirect = (array) $questionmarkredirect;
    db_insert('qm_redirect')->fields(array(
      'hash' => $questionmarkredirect['hash'],
      'source' => $questionmarkredirect['source'],
      'source_options' => serialize($questionmarkredirect['source_options']),
      'type' => $questionmarkredirect['type'],
      'questionmarkredirect' => $questionmarkredirect['questionmarkredirect'],
      'questionmarkredirect_options' => serialize($questionmarkredirect['questionmarkredirect_options']),
      'language' => $questionmarkredirect['language'],
      'chkdiable' => $questionmarkredirect['chkdiable'],
      'diable_url' => $questionmarkredirect['diable_url'],
    ))->execute();
  }

  /**
   * Query to update records.
   */
  static function edit($questionmarkredirect) {

    $questionmarkredirect = (array) $questionmarkredirect;

    db_update('qm_redirect')->fields(array(
          'hash' => $questionmarkredirect['hash'],
          'source' => $questionmarkredirect['source'],
          'source_options' => serialize($questionmarkredirect['source_options']),
          'type' => $questionmarkredirect['type'],
          'questionmarkredirect' => $questionmarkredirect['questionmarkredirect'],
          'questionmarkredirect_options' => serialize($questionmarkredirect['questionmarkredirect_options']),
          'language' => $questionmarkredirect['language'],
          'chkdiable' => $questionmarkredirect['chkdiable'],
          'diable_url' => $questionmarkredirect['diable_url'],
        ))
        ->condition('rid', $questionmarkredirect['rid'])
        ->execute();
  }

  /**
   * Query to delete records.
   */
  static function delete($id) {
    db_delete('qm_redirect')->condition('rid', $id)->execute();
  }

  static function DeleteAll($ids) {
    foreach ($ids as $id => $rid) {
      db_delete('qm_redirect')->condition('rid', $rid)->execute();
    }
  }

  /**
   * Query to file record.
   */
  static function getFid($id) {
    $result = db_query('SELECT * FROM {file_managed} WHERE fid = :id', array(':id' => $id))->fetchAllAssoc('fid');
    if ($result) {
      return $result[$id];
    }
    else {
      return FALSE;
    }
  }

  /**
   * Query to check source is present in list.
   */
  static function checkEntry($source) {
    $result = db_query('SELECT rid FROM {qm_redirect} WHERE source = :source', array(':source' => $source))->fetchassoc('rid');

    if ($result) {
      return $result['rid'];
    }
    else {
      return FALSE;
    }
  }

}
