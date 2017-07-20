<form>
    Word:<br>
    <input type="text" name="word"><br>
</form> 

<?php
include_once 'simple_html_dom.php';

if (isset($_GET['word'])) {

    $slug = $_GET['word'];

    $browsers = [
        "standard" => "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36",
        "mobile" => "Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30"
    ];

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, "https://www.google.pl/search?q=$slug");
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_USERAGENT, $browsers['standard']);
    $result = curl_exec($c);
    if ($result) {
        $myfile = fopen("content.html", "w") or die("Unable to open file!");
        fwrite($myfile, $result);
        fclose($myfile);
    } else {
        echo 'Error: ' . curl_error($c);
    }
    curl_close($c);

    $html = file_get_html('content.html');

    $result = preg_split('/resultStats">Około/', $html);
    if (count($result) > 1) {
        $result_split = explode(' ', $result[1]);
        $scoreNumber = $result_split[1];
    }

    $linksObj = $html->find('h3._DM a, h3.r a');
    $fileCsv = fopen('file.csv', 'w');
    $arrayScoreNumber = array('Results nr', $scoreNumber);
    fputcsv($fileCsv, $arrayScoreNumber, $delimiter = ';');
    $arrayColumnName = array('Position', 'Keyword', 'URL destination', 'Title');
    fputcsv($fileCsv, $arrayColumnName, $delimiter = ';');
    $counter = 1;

    foreach ($linksObj as $obj) {

        if ($counter < 11) {
            $title = trim($obj->plaintext);
            $link = trim($obj->href);

            if (!preg_match('/^https?/', $link) && preg_match('/q=(.+)&amp;sa=/U', $link, $matches) && preg_match('/^https?/', $matches[1])) {
                $link = $matches[1];
            } else if (!preg_match('/^https?/', $link)) {
                $link = 'https://google.com' . $link;
            }

            echo $counter . '<br>';
            echo $link . '<br>';
            echo $title . '<br><br>';

            $array = array($counter, $slug, $link, $title);
            fputcsv($fileCsv, $array, $delimiter = ';');
        }
        $counter++;
    }
    fclose($fileCsv);
}


