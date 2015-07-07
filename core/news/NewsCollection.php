<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 03.07.15
 * Time: 12:02
 */

class NewsCollection implements NewsCollectionInterface {

    private $db;

    /**
     * Constructor. Init database.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

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
    public function add($type, $header, $text, array $images, $date = 0)
    {

        if (strlen($header) < 5)
        {
            throw new APIException('Header must be 15 chars minimum.');
        }
        if (strlen($text) < 100)
        {
            throw new APIException('Text is too short');
        }
        if (!sizeof($images))
        {
            throw new APIException('News must contains minimum one image.');
        }

        if ($date == 0) {
            $date = time();
        }

        $query = "INSERT INTO `news` SET
                              `header` = :header,
                              `date` =:date,
                              `images` = :images,
                              `type` = :type,
                              `text` = :text";

        $query = $this->db->_db->prepare($query);
        $query->execute(    array(  "header" => $header,
                                    "images" => json_encode($images),
                                    "type" => $type,
                                    "text" => $text,
                                    "date" => date('Y-m-d', $date))
                        );

        $newsId = $this->db->_db->lastInsertId();

        return $newsId;
    }

    /**
     * Gets news with some Id
     *
     * @param int $id Id of news
     * @return mixed Associated array with news data
     */
    public function get($id)
    {
        $query = $this->db->_db->prepare("SELECT * FROM `news` WHERE `id` = :id LIMIT 1");
        $query->execute(array("id" => $id));

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Gets news with specified type. Uses paging.
     *
     * @param int $type Id of type (rubric)
     * @param int $limit Limit for output
     * @param int $page Current page for calculate offset
     * @return array
     */
    public function getList($type, $limit, $page = 1) {
        $page -= 1;
        $limit = (int) $limit;
        $offset = $page * $limit;

        $query = $this->db->_db->prepare("SELECT * FROM `news` JOIN
                                          (SELECT id FROM `news` WHERE `type` = :type ORDER BY `id` DESC LIMIT :offset, :limit)
                                          AS `b` ON `b`.`id` = `news`.`id`");
        $query->execute(
            array(  "type" => $type,
                    "offset" => $offset,
                    "limit" => $limit
            )
        );

        $result = [];
        while ($row = $query->fetch(PDO::FETCH_ASSOC))
        {
            $data = $row;

            $images = json_decode($row['images'], true);
            $data["preview"] = $images[0];
            $data['images'] = $images;

            $result[] = $data;
        }

        return $result;
    }

    /**
     * Delete news with specified Id
     *
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $query = $this->db->_db->prepare("DELETE FROM `news` WHERE `id` = :id");
        $query->execute(
            array( "id" => $id )
        );

        return true;
    }



}