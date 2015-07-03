<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 03.07.15
 * Time: 12:02
 */

class NewsCollection {

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Add news to collection
     * @param int $type Type of news (rubric)
     * @param string $header Header of news. Must be minimum 15 chars length
     * @param string $text Content of news. Must be minimum 100 chars length
     * @param array $images
     * @return int Id of added news
     * @throws AppException
     */
    public function addNews($type, $header, $text, array $images)
    {

        if (strlen($header) < 5)
        {
            throw new AppException('Header must be 15 chars minimum.');
        }
        if (strlen($text) < 100)
        {
            throw new AppException('Text is too short');
        }
        if (!sizeof($images))
        {
            throw new AppException('News must contains minimum one image.');
        }

        $query = "INSERT INTO `news` SET
                              `header` = :header,
                              `date` = NOW(),
                              `images` = :images,
                              `type` = :type
                              `text` = :text";

        $query = $this->db->_db->prepare($query);
        $query->execute(    array(  "header" => $header,
                                    "images" => json_encode($images),
                                    "type" => $type,
                                    "text" => $text )
                        );

        $newsId = $this->db->_db->lastInsertId();

        return $newsId;
    }



}