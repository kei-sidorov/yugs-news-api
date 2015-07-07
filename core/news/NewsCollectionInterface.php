<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 07.07.15
 * Time: 10:35
 */

interface NewsCollectionInterface {

    /**
     * Gets news with some Id
     *
     * @param int $id Id of news
     * @return mixed Associated array with news data
     */
    public function get($id);

    /**
     * Gets news with specified type. Uses paging.
     *
     * @param int $type Id of type (rubric)
     * @param int $limit Limit for output
     * @param int $page Current page for calculate offset
     * @return array
     */
    public function getList($type, $limit, $page = 1);

    /**
     * Add news to collection
     *
     * @param int $type Type of news (rubric)
     * @param string $header Header of news. Must be minimum 15 chars length
     * @param string $text Content of news. Must be minimum 100 chars length
     * @param array $images
     * @param int $date Timestamp of publish date
     * @return int Id of added news
     * @throws APIException
     */
    public function add($type, $header, $text, array $images, $date = 0);

    /**
     * Delete news with specified Id
     *
     * @param $id
     * @return bool
     */
    public function delete($id);
}