<?php
/**
 * Пример файла. Необходимо переименовать, убрав суффикс «-sample»
 */

    $rubrics = [
        ["id" => 43600, "image" => imagePath("map.png", $config), "name" => "Юрга и район"],
        ["id" => 43601, "image" => imagePath("fav.png", $config), "name" => "Акции компаний"],
    ];

/**
 * Format path for image
 *
 * @param string $img image name
 * @param array $config array with config.ini params
 * @return string absolute
 */
    function imagePath($img, $config) {
        return "http://" . $_SERVER["HTTP_HOST"] . $config['global']['path'] . "assets/rubrics/" . $img;
    }