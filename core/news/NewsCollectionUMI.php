<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 07.07.15
 * Time: 10:39
 */

class NewsCollectionUMI implements NewsCollectionInterface {

    /**
     * Gets news with some Id
     *
     * @param int $id Id of news
     * @return mixed Associated array with news data
     * @throws APIException
     */
    public function get($id)
    {
        $element = umiHierarchy::getInstance()->getElement($id);

        if (!$element)
        {
            throw new APIException("Can't find news with id " . $id);
        }

        $images = array();

        $mainImage = $element->getValue("anons_pic");
        if ($mainImage)
        {
            $mainImage = "http://" . $_SERVER['HTTP_HOST'] . $mainImage;
            $images[] = $mainImage;
        }

        $cleaner = new TextCleaner();

        $text = $element->getValue("content");
        $text = $cleaner->clearText($text);

        $imagesFromText = $cleaner->getPhotos($text);
        $images = array_merge($images, $imagesFromText);

        $item = array(
            "id" => $id,
            "header" => $element->getValue("h1"),
            "text" => $text,
            "images" => $images,
            "date" => $element->getValue("publish_time")->getFormattedDate("d.m.Y"),
            "original_link" => "http://" . $_SERVER['HTTP_HOST'] . umiHierarchy::getInstance()->getPathById($id)
        );

        return $item;
    }

    /**
     * Gets news with specified type. Uses paging.
     *
     * @param int $type Id of type (rubric)
     * @param int $limit Limit for output
     * @param int $page Current page for calculate offset
     * @return array
     */
    public function getList($type, $limit, $page = 1)
    {
        $config = parse_ini_file("./config/config.ini", true);
        $page -=1;

        $hierarchyTypeId = umiHierarchyTypesCollection::getInstance()->getTypeByName("news", "item")->getId();

        $objectTypeId = umiObjectTypesCollection::getInstance()->getBaseType("news", "item");
        $objectType = umiObjectTypesCollection::getInstance()->getType($objectTypeId);
        $publishTimeFieldId = $objectType->getFieldId('publish_time');
        $publishToAppFieldId = $objectType->getFieldId('publish_to_app');

        $sel = new umiSelection;
        $sel->addElementType($hierarchyTypeId);
        $sel->addHierarchyFilter($type, 0, true);

        if ($config["umi"]["ignore-publish-flag"] != 1) {
            $sel->addPropertyFilterEqual($publishToAppFieldId, true);
        }

        $sel->setOrderByProperty($publishTimeFieldId, false);
        $sel->addLimit($limit, $page);

        $result = umiSelectionsParser::runSelection($sel);

        $items = array();
        $size = sizeof($result);

        for($i = 0; $i < $size; $i++)
        {
            $elementId = $result[$i];
            $element = umiHierarchy::getInstance()->getElement($elementId);
            if(!$element) continue;

            $items[] = array(
                "id" => $elementId,
                "header" => $element->getValue("h1"),
                "date" => $element->getValue("publish_time")->getFormattedDate("d.m.Y"),
                "image" => "http://" . $_SERVER['HTTP_HOST'] . $element->getValue("anons_pic"),
                "original_link" => "http://" . $_SERVER['HTTP_HOST'] . umiHierarchy::getInstance()->getPathById($elementId)
            );
        }

        return $items;

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
        return $type;
    }

    /**
     * Delete news with specified Id
     *
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        return false;
    }
}