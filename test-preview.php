<?php
$ch = curl_init("https://seo-project.ddev.site/admin/site/9/page/4/preview-content");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
if(curl_errno($ch)){
    echo "Curl error: " . curl_error($ch);
} else {
    echo $response;
}
curl_close($ch);
