<?php
$ch = curl_init("https://seo-project.ddev.site/admin/site/9/page/4/preview-content");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Curl error: " . curl_error($ch);
} else {
    if (strpos($response, "<footer") !== false) {
        echo "Footer found\n";
        // Find footer content
        $footerStart = strpos($response, "<footer");
        $footerEnd = strpos($response, "</footer>", $footerStart) + strlen("</footer>");
        $footerContent = substr($response, $footerStart, $footerEnd - $footerStart);
        echo "Footer content:\n" . $footerContent . "\n";
    } else {
        echo "Footer not found";
    }
}
curl_close($ch);
