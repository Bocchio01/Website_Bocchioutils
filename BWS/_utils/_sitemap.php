<?php

include_once "../../_setting.php";
require_once "../../_isAdmin.php";

if ($login) {
    $xml = file_get_contents(HOST_URL . "/sitemap.xml" . '?' . mt_rand());
    // $xml = file_get_contents(UTILS_SITE . "/sitemap.xml" . '?' . mt_rand());

    $document = new DOMDocument;
    $document->loadXML($xml);

    $xpath = new DOMXpath($document);
    $xpath->registerNameSpace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    $xpath->registerNameSpace('x', 'http://www.w3.org/1999/xhtml');

    foreach ($xpath->evaluate('//s:url') as $url) {
        $data = [
            'lastmod' => $xpath->evaluate('string(s:lastmod)', $url),
            'en' => str_replace(HOST_URL, '', $xpath->evaluate('string(x:link[@hreflang="en"]/@href)', $url)),
            'it' => str_replace(HOST_URL, '', $xpath->evaluate('string(x:link[@hreflang="it"]/@href)', $url)),
        ];


        $id_page = Query("SELECT id_page FROM BWS_Translations WHERE (it, en) = ('$data[it]', '$data[en]')")->fetch_array(MYSQLI_ASSOC)['id_page'];

        if (!$id_page) {
            Query("INSERT INTO BWS_Pages (name) VALUES ('$data[en]')");
            $id_page = Query("SELECT LAST_INSERT_ID() AS id_page")->fetch_array(MYSQLI_ASSOC)['id_page'];
            Query("INSERT INTO BWS_Translations (id_page, it, en) VALUES ($id_page, '$data[it]', '$data[en]')");
            Query("INSERT INTO BWS_Interactions (id_page) VALUES ($id_page)");

            echo "New page:\t" . $id_page . " - " . $data['en'] . "\n";
        }


        if ($data['lastmod'] && strpos($data['lastmod'], date("Y-m-d")) === false) {
            $sqlDate = date('Y-m-d h:i:s', strtotime($data['lastmod']));
            Query("UPDATE BWS_Pages SET last_modify = '$sqlDate' WHERE id_page = $id_page");
        }
    }
}
