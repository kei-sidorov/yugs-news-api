<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 03.07.15
 * Time: 13:19
 */

class TextCleaner {

    private $siteUrl;

    /**
     * Constructor. Load configs.
     */
    function __construct()
    {
        $config = parse_ini_file("config.ini", true);

        $this->siteUrl = $config["cleaner"]["images-site-url"];
        if (substr($this->siteUrl, -1, 1) != '/')
        {
            $this->siteUrl .= '/';
        }
    }

    /**
     * Clean text from styles and images
     *
     * @param $text
     * @return string
     */
    public function clearText($text) {

        $safeTags = array('<br>', '<br />', '<br/>', '</p>', '</P>', "\t", "\r", "\n");
        $replaceVal = array('__BR__', '__BR__', '__BR__', '__P__', '__P__', '', '', '');
        $replaceValUnique = array('__BR__', '__P__');
        $backTags = array('<br />', '</p><p>');

        $text = str_replace($safeTags, $replaceVal, $text);
        $text = strip_tags( html_entity_decode( $text ) );
        $text = preg_replace("#[\s]{2,}#iu", "", $text);
        $text = str_replace($replaceValUnique, $backTags, $text);

        $text = "<p>" . $text . "</p>";
        $text = str_replace("<p></p>", "", $text);

        return $text;
    }


    /**
     * Extract photos from text
     *
     * @param string $data Text
     * @return array Array of images with full URL
     */
    public function getPhotos($data) {

        $pattern = "#<img.*?src=(.*?)\s#i";
        $result = array();

        preg_match_all($pattern, $data, $matches);
        foreach($matches[1] as $imageUrl)
        {
            $imageUrl = str_replace(array('"', "'"), "", $imageUrl);
            if (substr($imageUrl, 0, 11) == '/autothumbs')
            {
                $imageUrl = $this->clearOffAutothumbs($imageUrl);
            }
            if (substr($imageUrl, 0, 7) != 'http://')
            {
                if (substr($imageUrl, 0, 1) == '/')
                {
                    $imageUrl = substr($imageUrl, 1);
                }
                $imageUrl = $this->siteUrl . $imageUrl;
            }


            $result[] = $imageUrl;
        }

        return $result;
    }

    /**
     * Extract original file path from UMI autothumbs handler
     *
     * @param string $url Relative URL from autothumbs handler
     * @return bool|string Relative image file path
     */
    private function clearOffAutothumbs($url) {

        $pattern = "#^/autothumbs\.php\?img=(.*?)_[0-9]+_[0-9]+\.([a-zA-Z]+)$#i";
        if (preg_match($pattern, $url, $matches))
        {
            $result = $matches[1] . '.' . $matches[2];
            return $result;
        }

        return false;

    }

}