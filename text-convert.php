<?php

function post_request($url, array $params) {
$postdata = http_build_query(
    $params
);

$opts = array(
    'ssl' => array(
        'verify_peer' => true,
        'verify_peername' => true
    ),
    'http' => array(
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded'."\r\n",
        'content' => $postdata
    )
);

$context = stream_context_create($opts);

return file_get_contents($url, false, $context);
}

function deleteOlderFiles($path,$days) {
  if ($handle = opendir($path)) {
    while (false !== ($file = readdir($handle))) {
      $filelastmodified = filemtime($path . $file);
      if((time() - $filelastmodified) > $days*24*3600)
      {
        if(is_file($path . $file)) {
          unlink($path . $file);
        }
      }
    }
    closedir($handle);
  }
}

deleteOlderFiles('uploads/', 7);

$target_file_to = "./uploads/download_".uniqid().".txt";
if(isset($_POST['txt']) || isset($_POST['txtfile'])) {

$txt = $_POST['txt'];
if(isset($_FILES['txtfile'])) {
$target_file = "./uploads/download_".uniqid().".txt";
if (move_uploaded_file($_FILES['txtfile']["tmp_name"], $target_file))
	$txt = file_get_contents($target_file);
$txtfile=$target_file;
$txtTofile=$target_file_to;
}

$from = $_POST['from'];
$to = $_POST['to'];
$CLIENT_ID = getenv('WHATS_MATE_CLIENT_ID') ?: '';
$CLIENT_SECRET = getenv('WHATS_MATE_CLIENT_SECRET') ?: '';
$txtTo ="";

if ($CLIENT_ID === '' || $CLIENT_SECRET === '') {
    $txtTo = 'Translation credentials are not configured on this server.';
} else {

$output = str_split($_POST['txt'], 2400);

foreach($output as $txttmp) {
// Specify your translation requirements here:
$postData = array(
  'fromLang' => $_POST['from'],
  'toLang' => $_POST['to'],
  'text' => $txttmp
);

$headers = array(
  'Content-Type: application/json',
  'X-WM-CLIENT-ID: '.$CLIENT_ID,
  'X-WM-CLIENT-SECRET: '.$CLIENT_SECRET
);


$url = 'https://api.whatsmate.net/v1/translation/translate';
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

$txtTo .= curl_exec($ch);

curl_close($ch);
}
}

file_put_contents($target_file_to, $txtTo);

$speech_arr = array('ar' => 'ar-eg',
'bg' => 'bg-bg',
'ca' => 'ca-es',
'zh-CN' => 'zh-cn',
'hr' => 'hr-hr',
'cs' => 'cs-cz',
'da' => 'da-dk',
'nl' => 'nl-be',
'en' => 'en-us',
'fi' => 'fi-fi',
'fr' => 'fr-fr',
'de' => 'de-de',
'el' => 'el-gr',
'hi' => 'hi-in',
'hu' => 'hu-hu',
'id' => 'id-id',
'it' => 'it-it',
'ja' => 'ja-jp',
'ko' => 'ko-kr',
'ms' => 'ms-my',
'nb' => 'nb-no', 
'pl' => 'pl-pl',
'pt' => 'pt-pt',
'ro' => 'ro-ro',
'ru' => 'ru-ru',
'sk' => 'sk-sk',
'sl' => 'sl-si',
'es' => 'es-es',
'sv' => 'sv-se',
'ta' => 'ta-in',
'th' => 'th-th',
'tr' => 'tr-tr',
'vi' => 'vi-vn');

}

$tesseract_arr = array('ar' => 'ar-eg',
'bg' => 'bul',
'ca' => 'cat',
'zh-CN' => 'chi_sim',
'hr' => 'hrv',
'cs' => 'ces',
'da' => 'dan',
'nl' => 'nld',
'en' => 'eng',
'fi' => 'fin',
'fr' => 'fra',
'de' => 'deu',
'el' => 'ell',
'hi' => 'hin',
'hu' => 'hun',
'id' => 'ind',
'it' => 'ita',
'ja' => 'jpn',
'ko' => 'kor',
'ms' => 'msa',
'nb' => 'nor', 
'po' => 'pol',
'pt' => 'por',
'ro' => 'ron',
'ru' => 'rus',
'sk' => 'slk',
'sl' => 'slv',
'es' => 'spa',
'sv' => 'swe',
'ta' => 'tam',
'th' => 'tha',
'tr' => 'tur',
'vi' => 'vie');

if(isset($_FILES['pdffile']["name"])) {
$target_dir = "uploads/";
$target_file = $target_dir . uniqid().'_'.basename($_FILES["pdffile"]["name"]);
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
$target_file = $target_dir . uniqid().'.'.$imageFileType;
if (move_uploaded_file($_FILES["pdffile"]["tmp_name"], $target_file))
{
$Url      = "https://text-konvertierung.bilke-projects.com/convert_file.php?lang=".$tesseract_arr[$_POST['from']]."&fileToUpload=".$target_file; 
$txt = file_get_contents($Url);
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text-Konvertierung - Moderne OCR & Sprachkonvertierung</title>
    <meta name="description" content="Konvertieren Sie Ihre Texte mit Leichtigkeit mit unseren modernen OCR-, Übersetzungs- und Text-zu-Sprache-Tools.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Modern Header -->
    <header class="modern-header">
        <div class="header-container">
            <a href="#home" class="logo">
                <i class="fas fa-language"></i> Text-Konvertierung
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="#home" class="nav-link" data-de="Startseite" data-en="Home">Startseite</a></li>
                    <li><a href="#ocr" class="nav-link" data-de="OCR" data-en="OCR">OCR</a></li>
                    <li><a href="#translation" class="nav-link" data-de="Übersetzung" data-en="Translation">Übersetzung</a></li>
                    <li><a href="#speech" class="nav-link" data-de="Sprache" data-en="Speech">Sprache</a></li>
                    <li><a href="#about" class="nav-link" data-de="Über uns" data-en="About">Über uns</a></li>
                </ul>
            </nav>
            <div class="language-switcher">
                <button class="lang-btn active" data-lang="de">DE</button>
                <button class="lang-btn" data-lang="en">EN</button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content fade-in-up">
            <h1 data-de="Konvertieren Sie Ihre Texte mit Leichtigkeit" data-en="Convert Your Texts with Ease">Konvertieren Sie Ihre Texte mit Leichtigkeit</h1>
            <p data-de="Transformieren Sie Dokumente, übersetzen Sie Sprachen und konvertieren Sie Text in Sprache mit unseren leistungsstarken KI-gestützten Tools. Professionell, schnell und präzise." data-en="Transform documents, translate languages, and convert text to speech with our powerful AI-powered tools. Professional, fast, and accurate.">Transformieren Sie Dokumente, übersetzen Sie Sprachen und konvertieren Sie Text in Sprache mit unseren leistungsstarken KI-gestützten Tools. Professionell, schnell und präzise.</p>
            <div class="hero-buttons">
                <a href="#ocr" class="btn btn-primary">
                    <i class="fas fa-camera"></i> <span data-de="OCR starten" data-en="Start OCR">OCR starten</span>
                </a>
                <a href="#translation" class="btn btn-secondary">
                    <i class="fas fa-language"></i> <span data-de="Jetzt übersetzen" data-en="Translate Now">Jetzt übersetzen</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-container">
            <div class="section-header fade-in-up">
                <h2 data-de="Leistungsstarke Konvertierungstools" data-en="Powerful Conversion Tools">Leistungsstarke Konvertierungstools</h2>
                <p data-de="Alles, was Sie für die Textverarbeitung benötigen, an einem Ort" data-en="Everything you need for text processing in one place">Alles, was Sie für die Textverarbeitung benötigen, an einem Ort</p>
            </div>
            <div class="features-grid">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3 data-de="OCR zu Text" data-en="OCR to Text">OCR zu Text</h3>
                    <p data-de="Extrahieren Sie Text aus Bildern, PDFs und gescannten Dokumenten mit hoher Genauigkeit durch fortschrittliche OCR-Technologie." data-en="Extract text from images, PDFs, and scanned documents with high accuracy using advanced OCR technology.">Extrahieren Sie Text aus Bildern, PDFs und gescannten Dokumenten mit hoher Genauigkeit durch fortschrittliche OCR-Technologie.</p>
                </div>
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-language"></i>
                    </div>
                    <h3 data-de="Mehrsprachige Übersetzung" data-en="Multi-Language Translation">Mehrsprachige Übersetzung</h3>
                    <p data-de="Übersetzen Sie Text zwischen über 30 Sprachen mit professioneller Genauigkeit und natürlicher Sprachverarbeitung." data-en="Translate text between 30+ languages with professional-grade accuracy and natural language processing.">Übersetzen Sie Text zwischen über 30 Sprachen mit professioneller Genauigkeit und natürlicher Sprachverarbeitung.</p>
                </div>
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-volume-up"></i>
                    </div>
                    <h3 data-de="Text zu Sprache" data-en="Text to Speech">Text zu Sprache</h3>
                    <p data-de="Konvertieren Sie jeden Text in natürlich klingende Sprache mit mehreren Stimmenoptionen und Sprachunterstützung." data-en="Convert any text into natural-sounding speech with multiple voice options and language support.">Konvertieren Sie jeden Text in natürlich klingende Sprache mit mehreren Stimmenoptionen und Sprachunterstützung.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- OCR Section -->
    <section id="ocr" class="conversion-section">
        <div class="conversion-container">
            <div class="section-header fade-in-up">
                <h2 data-de="OCR Texterkennung" data-en="OCR Text Recognition">OCR Texterkennung</h2>
                <p data-de="Laden Sie Ihre Dokumente und Bilder hoch, um bearbeitbaren Text zu extrahieren" data-en="Upload your documents and images to extract editable text">Laden Sie Ihre Dokumente und Bilder hoch, um bearbeitbaren Text zu extrahieren</p>
            </div>
            <form method="post" action="#translation" class="conversion-form fade-in-up" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label" data-de="Datei auswählen" data-en="Select File">Datei auswählen</label>
                    <div class="file-upload">
                        <input type="file" name="pdffile" id="pdffile" accept=".jpg, .jpeg, .png, .gif, .pdf" required>
                        <label for="pdffile" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt fa-2x"></i><br>
                            <strong data-de="Klicken zum Hochladen" data-en="Click to upload">Klicken zum Hochladen</strong> <span data-de="oder ziehen und ablegen" data-en="or drag and drop">oder ziehen und ablegen</span><br>
                            <small data-de="Unterstützt: JPG, PNG, GIF, PDF" data-en="Supports: JPG, PNG, GIF, PDF">Unterstützt: JPG, PNG, GIF, PDF</small>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="from" class="form-label" data-de="Dokumentensprache" data-en="Document Language">Dokumentensprache</label>
                    <select id="from" name="from" class="form-select" required>
                        <option value="de">German - Deutsch</option>
                        <option value="en">English</option>
                        <option value="fr">French - français</option>
                        <option value="es">Spanish - español</option>
                        <option value="it">Italian - italiano</option>
                        <option value="pt">Portuguese - português</option>
                        <option value="ru">Russian - русский</option>
                        <option value="zh-CN">Chinese - 中文</option>
                        <option value="ja">Japanese - 日本語</option>
                        <option value="ko">Korean - 한국어</option>
                        <option value="ar">Arabic - العربية</option>
                        <option value="hi">Hindi - हिन्दी</option>
                        <option value="bg">Bulgarian - български</option>
                        <option value="ca">Catalan - català</option>
                        <option value="hr">Croatian - hrvatski</option>
                        <option value="cs">Czech - čeština</option>
                        <option value="da">Danish - dansk</option>
                        <option value="nl">Dutch - Nederlands</option>
                        <option value="fi">Finnish - suomi</option>
                        <option value="el">Greek - Ελληνικά</option>
                        <option value="hu">Hungarian - magyar</option>
                        <option value="id">Indonesian - Indonesia</option>
                        <option value="ms">Malay - Bahasa Melayu</option>
                        <option value="nb">Norwegian - norsk</option>
                        <option value="pl">Polish - polski</option>
                        <option value="ro">Romanian - română</option>
                        <option value="sk">Slovak - slovenčina</option>
                        <option value="sl">Slovenian - slovenščina</option>
                        <option value="sv">Swedish - svenska</option>
                        <option value="ta">Tamil - தமிழ்</option>
                        <option value="th">Thai - ไทย</option>
                        <option value="tr">Turkish - Türkçe</option>
                        <option value="vi">Vietnamese - Tiếng Việt</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> <span data-de="Text erkennen" data-en="Recognize Text">Text erkennen</span>
                </button>
            </form>
        </div>
    </section>

    <!-- Translation Section -->
    <section id="translation" class="conversion-section">
        <div class="conversion-container">
            <div class="section-header fade-in-up">
                <h2 data-de="Textübersetzung" data-en="Text Translation">Textübersetzung</h2>
                <p data-de="Übersetzen Sie Ihren Text zwischen mehreren Sprachen mit professioneller Genauigkeit" data-en="Translate your text between multiple languages with professional accuracy">Übersetzen Sie Ihren Text zwischen mehreren Sprachen mit professioneller Genauigkeit</p>
            </div>
            <form method="post" action="#speech" class="conversion-form fade-in-up" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="txt" class="form-label" data-de="Text zum Übersetzen eingeben" data-en="Enter Text to Translate">Text zum Übersetzen eingeben</label>
                    <textarea id="txt" name="txt" class="form-textarea" data-placeholder-de="Geben Sie hier Ihren Text ein oder fügen Sie ihn ein..." data-placeholder-en="Enter or paste your text here..." placeholder="Geben Sie hier Ihren Text ein oder fügen Sie ihn ein..." required><?php echo isset($txt) ? htmlspecialchars($txt) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="from" class="form-label" data-de="Ausgangssprache" data-en="From Language">Ausgangssprache</label>
                    <select id="from" name="from" class="form-select" required>
                        <option value="de"<?php if(isset($from) && $from=="de") echo " selected"; ?>>German - Deutsch</option>
                        <option value="en"<?php if(isset($from) && $from=="en") echo " selected"; ?>>English</option>
                        <option value="fr"<?php if(isset($from) && $from=="fr") echo " selected"; ?>>French - français</option>
                        <option value="es"<?php if(isset($from) && $from=="es") echo " selected"; ?>>Spanish - español</option>
                        <option value="it"<?php if(isset($from) && $from=="it") echo " selected"; ?>>Italian - italiano</option>
                        <option value="pt"<?php if(isset($from) && $from=="pt") echo " selected"; ?>>Portuguese - português</option>
                        <option value="ru"<?php if(isset($from) && $from=="ru") echo " selected"; ?>>Russian - русский</option>
                        <option value="zh-CN"<?php if(isset($from) && $from=="zh-CN") echo " selected"; ?>>Chinese - 中文</option>
                        <option value="ja"<?php if(isset($from) && $from=="ja") echo " selected"; ?>>Japanese - 日本語</option>
                        <option value="ko"<?php if(isset($from) && $from=="ko") echo " selected"; ?>>Korean - 한국어</option>
                        <option value="ar"<?php if(isset($from) && $from=="ar") echo " selected"; ?>>Arabic - العربية</option>
                        <option value="hi"<?php if(isset($from) && $from=="hi") echo " selected"; ?>>Hindi - हिन्दी</option>
                        <option value="bg"<?php if(isset($from) && $from=="bg") echo " selected"; ?>>Bulgarian - български</option>
                        <option value="ca"<?php if(isset($from) && $from=="ca") echo " selected"; ?>>Catalan - català</option>
                        <option value="hr"<?php if(isset($from) && $from=="hr") echo " selected"; ?>>Croatian - hrvatski</option>
                        <option value="cs"<?php if(isset($from) && $from=="cs") echo " selected"; ?>>Czech - čeština</option>
                        <option value="da"<?php if(isset($from) && $from=="da") echo " selected"; ?>>Danish - dansk</option>
                        <option value="nl"<?php if(isset($from) && $from=="nl") echo " selected"; ?>>Dutch - Nederlands</option>
                        <option value="fi"<?php if(isset($from) && $from=="fi") echo " selected"; ?>>Finnish - suomi</option>
                        <option value="el"<?php if(isset($from) && $from=="el") echo " selected"; ?>>Greek - Ελληνικά</option>
                        <option value="hu"<?php if(isset($from) && $from=="hu") echo " selected"; ?>>Hungarian - magyar</option>
                        <option value="id"<?php if(isset($from) && $from=="id") echo " selected"; ?>>Indonesian - Indonesia</option>
                        <option value="ms"<?php if(isset($from) && $from=="ms") echo " selected"; ?>>Malay - Bahasa Melayu</option>
                        <option value="nb"<?php if(isset($from) && $from=="nb") echo " selected"; ?>>Norwegian - norsk</option>
                        <option value="pl"<?php if(isset($from) && $from=="pl") echo " selected"; ?>>Polish - polski</option>
                        <option value="ro"<?php if(isset($from) && $from=="ro") echo " selected"; ?>>Romanian - română</option>
                        <option value="sk"<?php if(isset($from) && $from=="sk") echo " selected"; ?>>Slovak - slovenčina</option>
                        <option value="sl"<?php if(isset($from) && $from=="sl") echo " selected"; ?>>Slovenian - slovenščina</option>
                        <option value="sv"<?php if(isset($from) && $from=="sv") echo " selected"; ?>>Swedish - svenska</option>
                        <option value="ta"<?php if(isset($from) && $from=="ta") echo " selected"; ?>>Tamil - தமிழ்</option>
                        <option value="th"<?php if(isset($from) && $from=="th") echo " selected"; ?>>Thai - ไทย</option>
                        <option value="tr"<?php if(isset($from) && $from=="tr") echo " selected"; ?>>Turkish - Türkçe</option>
                        <option value="vi"<?php if(isset($from) && $from=="vi") echo " selected"; ?>>Vietnamese - Tiếng Việt</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="to" class="form-label" data-de="Zielsprache" data-en="To Language">Zielsprache</label>
                    <select id="to" name="to" class="form-select" required>
                        <option value="en"<?php if(isset($to) && $to=="en") echo " selected"; ?>>English</option>
                        <option value="de"<?php if(isset($to) && $to=="de") echo " selected"; ?>>German - Deutsch</option>
                        <option value="fr"<?php if(isset($to) && $to=="fr") echo " selected"; ?>>French - français</option>
                        <option value="es"<?php if(isset($to) && $to=="es") echo " selected"; ?>>Spanish - español</option>
                        <option value="it"<?php if(isset($to) && $to=="it") echo " selected"; ?>>Italian - italiano</option>
                        <option value="pt"<?php if(isset($to) && $to=="pt") echo " selected"; ?>>Portuguese - português</option>
                        <option value="ru"<?php if(isset($to) && $to=="ru") echo " selected"; ?>>Russian - русский</option>
                        <option value="zh-CN"<?php if(isset($to) && $to=="zh-CN") echo " selected"; ?>>Chinese - 中文</option>
                        <option value="ja"<?php if(isset($to) && $to=="ja") echo " selected"; ?>>Japanese - 日本語</option>
                        <option value="ko"<?php if(isset($to) && $to=="ko") echo " selected"; ?>>Korean - 한국어</option>
                        <option value="ar"<?php if(isset($to) && $to=="ar") echo " selected"; ?>>Arabic - العربية</option>
                        <option value="hi"<?php if(isset($to) && $to=="hi") echo " selected"; ?>>Hindi - हिन्दी</option>
                        <option value="bg"<?php if(isset($to) && $to=="bg") echo " selected"; ?>>Bulgarian - български</option>
                        <option value="ca"<?php if(isset($to) && $to=="ca") echo " selected"; ?>>Catalan - català</option>
                        <option value="hr"<?php if(isset($to) && $to=="hr") echo " selected"; ?>>Croatian - hrvatski</option>
                        <option value="cs"<?php if(isset($to) && $to=="cs") echo " selected"; ?>>Czech - čeština</option>
                        <option value="da"<?php if(isset($to) && $to=="da") echo " selected"; ?>>Danish - dansk</option>
                        <option value="nl"<?php if(isset($to) && $to=="nl") echo " selected"; ?>>Dutch - Nederlands</option>
                        <option value="fi"<?php if(isset($to) && $to=="fi") echo " selected"; ?>>Finnish - suomi</option>
                        <option value="el"<?php if(isset($to) && $to=="el") echo " selected"; ?>>Greek - Ελληνικά</option>
                        <option value="hu"<?php if(isset($to) && $to=="hu") echo " selected"; ?>>Hungarian - magyar</option>
                        <option value="id"<?php if(isset($to) && $to=="id") echo " selected"; ?>>Indonesian - Indonesia</option>
                        <option value="ms"<?php if(isset($to) && $to=="ms") echo " selected"; ?>>Malay - Bahasa Melayu</option>
                        <option value="nb"<?php if(isset($to) && $to=="nb") echo " selected"; ?>>Norwegian - norsk</option>
                        <option value="pl"<?php if(isset($to) && $to=="pl") echo " selected"; ?>>Polish - polski</option>
                        <option value="ro"<?php if(isset($to) && $to=="ro") echo " selected"; ?>>Romanian - română</option>
                        <option value="sk"<?php if(isset($to) && $to=="sk") echo " selected"; ?>>Slovak - slovenčina</option>
                        <option value="sl"<?php if(isset($to) && $to=="sl") echo " selected"; ?>>Slovenian - slovenščina</option>
                        <option value="sv"<?php if(isset($to) && $to=="sv") echo " selected"; ?>>Swedish - svenska</option>
                        <option value="ta"<?php if(isset($to) && $to=="ta") echo " selected"; ?>>Tamil - தமிழ்</option>
                        <option value="th"<?php if(isset($to) && $to=="th") echo " selected"; ?>>Thai - ไทย</option>
                        <option value="tr"<?php if(isset($to) && $to=="tr") echo " selected"; ?>>Turkish - Türkçe</option>
                        <option value="vi"<?php if(isset($to) && $to=="vi") echo " selected"; ?>>Vietnamese - Tiếng Việt</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-language"></i> <span data-de="Text übersetzen" data-en="Translate Text">Text übersetzen</span>
                </button>
            </form>
            
            <?php if(isset($txtTo) && $txtTo): ?>
            <div class="results fade-in-up">
                <h3>Translation Result</h3>
                <div class="result-text"><?php echo htmlspecialchars($txtTo); ?></div>
                <div class="download-links">
                    <a href="<?php echo $target_file_to; ?>" download="Translation.txt" class="btn btn-outline">
                        <i class="fas fa-download"></i> Download Translation
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Speech Section -->
    <section id="speech" class="conversion-section">
        <div class="conversion-container">
            <div class="section-header fade-in-up">
                <h2 data-de="Text-zu-Sprache-Konvertierung" data-en="Text to Speech Conversion">Text-zu-Sprache-Konvertierung</h2>
                <p data-de="Hören Sie Ihren Text in mehreren Sprachen mit natürlich klingenden Stimmen" data-en="Listen to your text in multiple languages with natural-sounding voices">Hören Sie Ihren Text in mehreren Sprachen mit natürlich klingenden Stimmen</p>
            </div>
            
            <?php if (isset($_POST['txt']) && $_POST['txt']): ?>
            <div class="results fade-in-up">
                <h3>Audio Conversion</h3>
                
                <?php
                $urlFrom = post_request('https://www.text-konvertierung.de/audio_download.php', array("hl"=>$speech_arr[$from], "src"=>strip_tags($txt)));
                $urlTo = post_request('https://www.text-konvertierung.de/audio_download.php', array("hl"=>$speech_arr[$to], "src"=>strip_tags($txtTo)));
                ?>
                
                <div class="audio-player">
                    <h4>Original Text (<?php echo $from; ?>)</h4>
                    <audio controls>
                        <source src="<?php echo $urlFrom; ?>" type="audio/wav">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                
                <div class="audio-player">
                    <h4>Translated Text (<?php echo $to; ?>)</h4>
                    <audio controls>
                        <source src="<?php echo $urlTo; ?>" type="audio/wav">
                        Your browser does not support the audio element.
                    </audio>
                </div>
                
                <div class="download-links">
                    <a href="<?php echo $urlFrom ?>" class="btn btn-outline" download="Audio_<?php echo $from ?>.wav">
                        <i class="fas fa-download"></i> Download <?php echo strtoupper($from); ?> Audio
                    </a>
                    <a href="<?php echo $urlTo ?>" class="btn btn-outline" download="Audio_<?php echo $to ?>.wav">
                        <i class="fas fa-download"></i> Download <?php echo strtoupper($to); ?> Audio
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Steps Section -->
    <section id="about" class="steps">
        <div class="steps-container">
            <div class="section-header fade-in-up">
                <h2 data-de="So funktioniert es" data-en="How It Works">So funktioniert es</h2>
                <p data-de="Einfache Schritte, um Ihre Texte mit professionellen Ergebnissen zu konvertieren" data-en="Simple steps to convert your texts with professional results">Einfache Schritte, um Ihre Texte mit professionellen Ergebnissen zu konvertieren</p>
            </div>
            <div class="steps-grid">
                <div class="step-card fade-in-up">
                    <div class="step-number">1</div>
                    <h3 data-de="Text hochladen oder eingeben" data-en="Upload or Enter Text">Text hochladen oder eingeben</h3>
                    <p data-de="Laden Sie Ihre Dokumente oder Bilder hoch oder geben Sie einfach den Text ein, den Sie konvertieren möchten." data-en="Upload your documents, images, or simply type the text you want to convert.">Laden Sie Ihre Dokumente oder Bilder hoch oder geben Sie einfach den Text ein, den Sie konvertieren möchten.</p>
                </div>
                <div class="step-card fade-in-up">
                    <div class="step-number">2</div>
                    <h3 data-de="Konvertierungstyp wählen" data-en="Choose Conversion Type">Konvertierungstyp wählen</h3>
                    <p data-de="Wählen Sie zwischen OCR-Texterkennung, Sprachübersetzung oder Text-zu-Sprache-Konvertierung." data-en="Select between OCR text recognition, language translation, or text-to-speech conversion.">Wählen Sie zwischen OCR-Texterkennung, Sprachübersetzung oder Text-zu-Sprache-Konvertierung.</p>
                </div>
                <div class="step-card fade-in-up">
                    <div class="step-number">3</div>
                    <h3 data-de="Sprachen auswählen" data-en="Select Languages">Sprachen auswählen</h3>
                    <p data-de="Wählen Sie Ihre Quell- und Zielsprachen aus unserem umfangreichen Sprachsupport." data-en="Choose your source and target languages from our extensive language support.">Wählen Sie Ihre Quell- und Zielsprachen aus unserem umfangreichen Sprachsupport.</p>
                </div>
                <div class="step-card fade-in-up">
                    <div class="step-number">4</div>
                    <h3 data-de="Ergebnisse erhalten" data-en="Get Results">Ergebnisse erhalten</h3>
                    <p data-de="Laden Sie Ihre konvertierten Text- oder Audiodateien sofort mit professioneller Qualität herunter." data-en="Download your converted text or audio files instantly with professional quality.">Laden Sie Ihre konvertierten Text- oder Audiodateien sofort mit professioneller Qualität herunter.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="testimonials-container">
            <div class="section-header fade-in-up">
                <h2 data-de="Was unsere Nutzer sagen" data-en="What Our Users Say">Was unsere Nutzer sagen</h2>
                <p data-de="Vertraut von Fachleuten weltweit für genaue Textkonvertierung" data-en="Trusted by professionals worldwide for accurate text conversion">Vertraut von Fachleuten weltweit für genaue Textkonvertierung</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card fade-in-up">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">JD</div>
                        <div class="testimonial-info">
                            <h4>John Doe</h4>
                            <span>CEO, Company ABC</span>
                        </div>
                    </div>
                    <p class="testimonial-text" data-de="„Die Text-zu-Sprache-Funktion ist ein Game-Changer für uns. Sie hilft uns, Audio-Versionen unserer Inhalte einfach und professionell zu erstellen."" data-en=""The text to speech feature is a game-changer for us. It helps us create audio versions of our content easily and professionally."">„Die Text-zu-Sprache-Funktion ist ein Game-Changer für uns. Sie hilft uns, Audio-Versionen unserer Inhalte einfach und professionell zu erstellen."</p>
                </div>
                <div class="testimonial-card fade-in-up">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">JS</div>
                        <div class="testimonial-info">
                            <h4>Jane Smith</h4>
                            <span>Marketing Manager, XYZ Inc.</span>
                        </div>
                    </div>
                    <p class="testimonial-text" data-de="„Als Autorin muss ich oft Texte übersetzen, und dieses Tool hat den Prozess so viel reibungsloser gemacht. Sehr empfehlenswert!"" data-en=""As a writer, I often need to translate texts, and this tool has made the process so much smoother. Highly recommended!"">„Als Autorin muss ich oft Texte übersetzen, und dieses Tool hat den Prozess so viel reibungsloser gemacht. Sehr empfehlenswert!"</p>
                </div>
                <div class="testimonial-card fade-in-up">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">DJ</div>
                        <div class="testimonial-info">
                            <h4>David Johnson</h4>
                            <span>Freelance Writer</span>
                        </div>
                    </div>
                    <p class="testimonial-text" data-de="„Ich verwende die OCR-Funktion häufig für meine Forschungsprojekte, und sie hat mir viel Zeit gespart. Vielen Dank für dieses erstaunliche Tool!"" data-en=""I use the OCR feature frequently for my research projects, and it has saved me a lot of time. Thank you for this amazing tool!"">„Ich verwende die OCR-Funktion häufig für meine Forschungsprojekte, und sie hat mir viel Zeit gespart. Vielen Dank für dieses erstaunliche Tool!"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Text-Konvertierung</h3>
                    <p data-de="Professionelle Textkonvertierungstools für OCR, Übersetzung und Sprachsynthese. Machen Sie die Textverarbeitung einfach und effizient." data-en="Professional text conversion tools for OCR, translation, and speech synthesis. Making text processing simple and efficient.">Professionelle Textkonvertierungstools für OCR, Übersetzung und Sprachsynthese. Machen Sie die Textverarbeitung einfach und effizient.</p>
                </div>
                <div class="footer-section">
                    <h3 data-de="Dienstleistungen" data-en="Services">Dienstleistungen</h3>
                    <p><a href="#ocr" data-de="OCR Texterkennung" data-en="OCR Text Recognition">OCR Texterkennung</a></p>
                    <p><a href="#translation" data-de="Sprachübersetzung" data-en="Language Translation">Sprachübersetzung</a></p>
                    <p><a href="#speech" data-de="Text zu Sprache" data-en="Text to Speech">Text zu Sprache</a></p>
                </div>
                <div class="footer-section">
                    <h3 data-de="Kontakt" data-en="Contact">Kontakt</h3>
                    <p><a href="mailto:info@dominic-bilke.de">info@dominic-bilke.de</a></p>
                    <p><a href="https://www.dominic-bilke.de" target="_blank">www.dominic-bilke.de</a></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Bilke Web and Software Development. <span data-de="Alle Rechte vorbehalten." data-en="All rights reserved.">Alle Rechte vorbehalten.</span></p>
                <p>
                    <a href="https://www.dominic-bilke.de/en/privacy-policy" target="_blank" data-de="Datenschutz" data-en="Privacy Policy">Datenschutz</a> | 
                    <a href="https://www.dominic-bilke.de/en/terms-of-service" target="_blank" data-de="Nutzungsbedingungen" data-en="Terms of Service">Nutzungsbedingungen</a> | 
                    <a href="https://www.dominic-bilke.de/en/imprint" target="_blank" data-de="Impressum" data-en="Imprint">Impressum</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Modern JavaScript -->
    <script src="assets/js/modern.js"></script>
</body>
</html>
