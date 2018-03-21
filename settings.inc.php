<?php
	
	

    //CURL class
	ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
	//error_reporting(E_ALL);
	
	if (get_magic_quotes_gpc()) 
	{
		$_REQUEST = array_map('stripslashes',$_REQUEST);
	}	
    require_once('curl.class.php');
	////turn on implicit flush in php.ini so we can flush status updates to the browser if desired
	ob_implicit_flush(); 
    ini_set('output_buffering',0); 
    
    //Length of time (in days) to cache a wikipedia page for before fetching it again
    $wikicachelength = 30;
    $wikisearchcachelength = 4;	//96 hour search cache length
    $useragentstring = 'DegreesOfWikipedia.com-' . $wikicachelength . 'dayartcache-' . $wikisearchcachelength . 'daysearchcache-contact:dave@lkdev.com-thankyou';
    $curltimeout = 5;	//curl timeout in seconds
    $usesqlcompression = false;
	$minautocompletechars = 2;	//minimum characters needed for the auto-complete jQuery function to kick off
	$submitbuttontimeoutseconds = 60 * 60 * 24 * 7;	//7 day submit button timeout, to keep search engines from beating the crap out of the server.
	$maxdatarowcount = 2000;
	$maxsearchcachecount = 2000;
	
	
	
	//default pages that will be used across all languages. 
	// [l] should be replaced with the 2-char language code,
	// [a] should be replaced with the name of the article being fetched
	$d_search 	= 'https://[l].wikipedia.org/w/api.php?action=opensearch&search=[a]&namespace=0&suggest=';
	$d_link 	= 'https://[l].wikipedia.org/wiki/[a]';
	$d_xml 		= 'https://[l].wikipedia.org/wiki/Special:Export/[a]';
	$d_api 		= 'https://[l].wikipedia.org/w/api.php?action=query&list=backlinks&bllimit=5000&blfilterredir=nonredirects&format=php&blredirect&blnamespace=0&bltitle=[a]';
	$d_rand 	= 'https://[l].wikipedia.org/w/api.php?action=query&list=random&rnnamespace=0&rnlimit=1&format=php';

		//English
		$l = 'en';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Link Two Articles on Wikipedia',
										'choose_language'	=> 'Choose a language',
										'choose_two' 		=> 'Enter two articles to link',
										'template_check'	=> 'Allow Links that appear in side [template] boxes - (yields faster results and shorter paths)',
										'random_path' 		=> 'Shuffle Paths - attempts an alternate route between articles and takes longer',
										'article_direction' => 'Reverse article direction',
										'skip' 				=> 'Skip articles (optional - one per line)',
										'shuffle' 			=> 'Random Path - attempts an alternate route between articles and takes longer ',
										'random_article' 	=> 'random',
										'additional_options'=> 'Additional options',
										'allowyears'		=> 'Allow 4 digit years (like 2008, 2009, etc)',
										
										'startinglinkingof' => 'Starting linking of',
										'deadend' => 'was a dead end (no connecting articles)',
										'peakmemory' => 'Peak memory usage',
										'pagegeneratedin' => 'Page generated in',
										'contact' => 'Contact Us',
										'about'=>'Degrees of Wikipedia is a multi-lingual tool for finding the distance between articles on a live copy of wikipedia.  To try it, enter two terms below, and click the button!',
										
										
									),
						'flags'=>array(
							'uk.png',
							'usa.png',
						),
					);
					
		
		//German
		$l = 'de';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Link Zwei Artikel auf Wikipedia',
										'choose_language'	=> 'Wählen Sie eine Sprache',
										'choose_two' 		=> 'Geben Sie zwei Artikel zu verbinden',
										'template_check' 	=> 'Lassen Sie, dass Links in der Seite werden angezeigt - (yeilds schnellere Ergebnisse und kürzere Verbindungen)',
										'article_direction' => 'Reverse artikel richtung',
										'skip' 				=> 'Skip beiträge (optional - eine pro zeile)',
										'shuffle' 			=> 'Versuche, eine alternative Route zwischen Artikeln (dauert länger)',
										'random_article' 	=> 'zufällig',
										'additional_options'=> 'Zusätzliche optionen',
										'allowyears'		=> 'Lassen Sie vierstellige Jahreszahlen (2008, 2009, etc)',
										
										'startinglinkingof' => 'Ab Verknüpfung von',
										'deadend' => 'war eine Sackgasse (keine Anschluss Artikel)',
										'peakmemory' => 'Peak-Speichernutzung',
										'pagegeneratedin' => 'Seite generiert',
										'contact' => 'Kontaktieren Sie uns',
										'about'=>'Grade der Wikipedia ist eine mehrsprachige Tool für die Suche nach den Abstand zwischen den Artikeln auf einem Live-Kopie der Wikipedia. Um es zu versuchen, geben Sie zwei Begriffe unten, und klicken Sie auf den Button klicken!',
									),
						'flags'=>array(
							'germany.png',
						),
					);
		
		
		//Spanish
		$l = 'es';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Vincular dos artículos en la Wikipedia',
										'choose_language'	=> 'Elija un idioma',
										'choose_two' 		=> 'Introduzca los dos artículos de vincular',
										'template_check' 	=> 'Permitir enlaces que aparecen en cuadros de lado - (produce resultados más rápidos y conexiones más cortas)',
										'article_direction' => 'Reverso artículo dirección',
										'skip' 				=> 'Pasar los artículos (opcional - uno por línea)',
										'shuffle' 			=> 'intentos de una ruta alternativa entre los artículos (tarda más)',
										'random_article' 	=> 'azar',
										'additional_options'=> 'Opciones adicionales',
										'allowyears'		=> 'Permitir cuatro años dígitos',
										
										'startinglinkingof' => 'A partir de la vinculación',
										'deadend' => 'era un callejón sin salida (hay artículos que conectan)',
										'peakmemory' => 'Uso de la memoria de pico',
										'pagegeneratedin' => 'Página generada en',
										'contact' => 'Contáctenos',
										'about'=>'Grados de Wikipedia es una herramienta multilingüe para encontrar la distancia entre los artículos sobre una copia viva de la wikipedia. Para probarlo, introducir dos términos a continuación y haga clic en el botón!',
										
									),
						'flags'=>array(
							'spain.png',
							'mexico.png',
						),
					);
		
					
		
		
		//French
		$l = 'fr';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Lien Deux articles sur Wikipedia',
										'choose_language'	=> 'Choisir une langue',
										'choose_two' 		=> 'Entrez deux articles de lier',
										'template_check' 	=> 'Autoriser les liens qui apparaissent dans les boîtes de côté - (donne des résultats plus rapides et plus courtes connexions)',
										'article_direction' => 'direction inversée article',
										'skip' 				=> 'Passer articles (optionnel - un par ligne)',
										'shuffle' 			=> 'tente un autre itinéraire entre les articles (prend plus de temps)',
										'random_article' 	=> 'aléatoire',
										'additional_options'=> 'Options supplémentaires',
										'allowyears'		=> 'Permettez-quatre années chiffres',
										
										'startinglinkingof' => 'À partir de liaison',
										'deadend' => 'était une impasse (pas de liaison articles)',
										'peakmemory' => 'Utilisation de la mémoire de pointe',
										'pagegeneratedin' => 'Page générée en',
										'contact' => 'Contactez-nous',
										'about'=>'Degrés de Wikipedia est un outil multilingue pour trouver la distance entre les articles sur une copie en direct de wikipedia. Pour l\'essayer, entre deux termes ci-dessous, et cliquez sur le bouton!',
									),
									
						'flags'=>array(
							'france.png',
						),
					);
		
		
		//Hindi
		$l = 'hi';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'विकिपीडिया पर लिंक दो लेख',
										'choose_language'	=> 'एक भाषा चुनें',
										'choose_two' 		=> 'लिंक करने के लिए दो लेख में प्रवेश',
										'template_check' 	=> 'अनुमति दें पक्ष [टेम्पलेट] बॉक्स में दिखाई देने वाली लिंक - (पैदावार तेजी से परिणाम और कम पथ)',
										'random_path' 		=> 'साधा पथ - लेख के बीच एक वैकल्पिक मार्ग का प्रयास करता है और अब लगता है',
										'article_direction' => 'रिवर्स लेख दिशा',
										'skip' 				=> 'लेख (- एक प्रति पंक्ति वैकल्पिक) करें',
										'shuffle' 			=> 'रैंडम पथ - लेख के बीच एक वैकल्पिक मार्ग का प्रयास करता है और अब लगता है',
										'random_article' 	=> 'यादृच्छिक',
										'additional_options'=> 'अतिरिक्त विकल्प',
										'allowyears'		=> 'अनुमति दें 4 अंकों वर्ष (जैसे 2008, 2009, आदि)',
										
										'startinglinkingof' => 'जोड़ने शुरू',
										'deadend' => 'एक मरा हुआ अंत (कोई जोड़ने लेख) था',
										'peakmemory' => 'पीक स्मृति उपयोग',
										'pagegeneratedin' => 'में उत्पन्न पेज',
										'contact' => 'हमसे संपर्क करें',
										'about'=>'विकिपीडिया की डिग्री विकिपीडिया का जीना नकल पर लेख के बीच की दूरी को खोजने के लिए एक बहुभाषी उपकरण है. , यह कोशिश नीचे दो शब्द दर्ज करें, और बटन क्लिक करें!',
									),
						'flags'=>array(
							'india.png',
						),
					);
		
		
		//Italian
		$l = 'it';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Link due articoli su Wikipedia',
										'choose_language'	=> 'Scegli una lingua',
										'choose_two' 		=> 'Inserisci il link a due articoli',
										'template_check' 	=> 'Consenti collegamenti che compaiono nelle caselle di lato - (risultati rese più veloci e più brevi collegamenti)',
										'article_direction' => 'Reverse articolo direzione',
										'skip' 				=> 'Passer articles (optionnel - un par ligne)',
										'shuffle' 			=> 'tenta un percorso alternativo tra gli articoli (richiede più tempo)',
										'random_article' 	=> 'casuale',
										'additional_options'=> 'Opzioni aggiuntive',
										'allowyears'		=> 'Lasciare quattro anni cifre',
										
										'startinglinkingof' => 'Collegamento a causa Articoli su Wikipedia',
										'deadend' => 'era un vicolo cieco (articoli di collegamento)',
										'peakmemory' => 'L\'utilizzo della memoria di picco',
										'pagegeneratedin' => 'Pagina generata in',
										'contact' => 'Contattaci',										
										'about'=>'Gradi di Wikipedia è uno strumento multilingue per trovare la distanza tra articoli su una copia diretta di wikipedia. Per provarlo, immettere due termini qui di seguito, e fare clic sul pulsante!',
									),
									
						'flags'=>array(
							'italy.png',
						),
					);
					
		//Japaneese
		$l = 'ja';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'リンクはウィキペディア上の2つの記事',
										'choose_language'	=> '言語を選択',
										'choose_two' 		=> 'リンクする2つの記事を入力してください',
										'template_check' 	=> 'リンクは、サイドボックスに表示さ - （高速な結果と短い接続を許可許可',
										'article_direction' => '逆資料の方向',
										'skip' 				=> 'ナビゲーション記事（オプション - 1行あたり）',
										'shuffle' 			=> '記事（時間がかかります）との間の代替ルートを試みます',
										'random_article' 	=> 'ランダム',
										'additional_options'=> 'その他のオプション',
										'allowyears'		=> '4桁の年を許可する',
										
										'startinglinkingof' => 'のリンクを開始',
										'deadend' => '行き止まり（NO接続記事）だった',
										'peakmemory' => 'ピーク時のメモリ使用量',
										'pagegeneratedin' => 'で生成されたページ',
										'contact' => 'お問い合わせ',
										'about'=>'ウィキペディアの程度は、ウィキペディアのライブコピー上の記事の間の距離を求めるための多言語ツールです。 、それを試して、以下の2条件を入力し、ボタンをクリックします！',
										
									),
						'flags'=>array(
							'japanese.png',
						),
					);
					
		//Dutch
		$l = 'nl';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Link twee artikelen op Wikipedia',
										'choose_language'	=> 'Kies een taal',
										'choose_two' 		=> 'Voer twee artikelen te koppelen',
										'template_check' 	=> 'Laat links die worden weergegeven in de zijwand van dozen - (de opbrengst snellere resultaten en kortere verbindingen)',
										'article_direction' => 'Omgekeerde richting artikel',
										'skip' 				=> 'Spring artikelen (optioneel - een per regel)',
										'shuffle' 			=> 'pogingen een alternatieve route tussen artikelen (duurt langer)',
										'random_article' 	=> 'willekeurige',
										'additional_options'=> 'Aanvullende opties',
										'allowyears'		=> 'Laat viercijferig jaar',
										
										
										'startinglinkingof' => 'Vanaf koppeling van',
										'deadend' => 'was een doodlopende weg (geen aansluiting van artikelen)',
										'peakmemory' => 'Peak geheugengebruik',
										'pagegeneratedin' => 'Pagina gegenereerd in',
										'contact' => 'Contacteer ons',
										
										'about'=>'Graden van Wikipedia is een meertalige tool voor het vinden van de afstand tussen de artikelen op een live-versie van wikipedia. Om het te proberen, voer twee termen hieronder, en klik op de knop!',
									),
						'flags'=>array(
							'netherlands.png',
						),
					);

		
		//Polish
		$l = 'pl';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Link dwóch artykułów w Wikipedii',
										'choose_language'	=> 'Wybierz język',
										'choose_two' 		=> 'Wpisz link do dwóch artykułów',
										'template_check' 	=> 'Pozwalają Linki, które pojawiają się w polach strony - (daje szybsze rezultaty i krótsze połączenia)',
										'article_direction' => 'Odwrotnym kierunku artykułu',
										'skip' 				=> 'Przejdź artykułów (opcjonalnie - po jednym w wierszu)',
										'shuffle' 			=> 'próby alternatywnej trasy między artykułami (trwa dłużej)',
										'random_article' 	=> 'przypadkowy',
										'additional_options'=> 'Opcje dodatkowe',
										'allowyears'		=> 'Pozwalają czterocyfrowy rok',
										
										'startinglinkingof' => 'Zaczynając powiązanie',
										'deadend' => 'był ślepy zaułek (Brak artykułów łączących)',
										'peakmemory' => 'Szczytowe wykorzystanie pamięci',
										'pagegeneratedin' => 'Strona wygenerowana w',
										'contact' => 'Kontakt z nami',
										'about'=>'Stopnie Wikipedia jest narzędziem wielojęzyczne za znalezienie dystansu pomiędzy artykułami na żywo kopią wikipedii. Spróbować, wprowadzić dwa warunki poniżej i kliknij przycisk!',
										
									),
						'flags'=>array(
							'poland.png',
						),
					);
		
		//Portuguese
		$l = 'pt';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Link Dois artigos na Wikipédia',
										'choose_language'	=> 'Escolha um idioma',
										'choose_two' 		=> 'Digite os dois artigos para link',
										'template_check' 	=> 'Permitir links que aparecem em caixas de lado - (produz resultados mais rápidos e conexões mais curtas)',
										'article_direction' => 'Odwrotnym kierunku artykułu',
										'skip' 				=> 'Artigos skip (opcional - um por linha)',
										'shuffle' 			=> 'tentativas de uma rota alternativa entre artigos (demora mais)',
										'random_article' 	=> 'acaso',
										'additional_options'=> 'Opções adicionais',
										'allowyears'		=> 'Permitir quatro anos dígito',
										
										'startinglinkingof' => 'A partir da ligação',
										'deadend' => 'era um beco sem saída (sem artigos de conexão)',
										'peakmemory' => 'Uso de memória de pico',
										'pagegeneratedin' => 'Página gerada em',
										'contact' => 'Fale Conosco',
										'about'=>'Graus de Wikipedia é uma ferramenta multi-lingual para encontrar a distância entre artigos sobre uma cópia viva de wikipedia. Para experimentá-lo, digite dois termos abaixo, e clique no botão!',
										
									),
						'flags'=>array(
							'portugal.png',
							'brazil.png',
						),
					);
			
		
					
		//Russian
		$l = 'ru';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Ссылка Две статьи в Википедии',
										'choose_language'	=> 'Выберите язык',
										'choose_two' 		=> 'Введите две статьи на ссылку',
										'template_check' 	=> 'Разрешить ссылки, которые появляются в стороне коробки - (дает быстрый результат и короткие соединения)',
										'article_direction' => 'Обратное направление статьи',
										'skip' 				=> 'Перейти статей (по желанию - по одному в каждой строке)',
										'shuffle' 			=> 'Попытки альтернативного маршрута между статьями (дольше)',
										'random_article' 	=> 'случайный',
										'additional_options'=> 'Дополнительные опции',
										'allowyears'		=> 'Разрешить четыре цифры года',
										
										'startinglinkingof' => 'Начиная увязки',
										'deadend' => 'был тупик (без соединительных статьи)',
										'peakmemory' => 'Использование Пик памяти',
										'pagegeneratedin' => 'Время генерации страницы',
										'contact' => 'связаться с нами',
										'about'=>'Степени Википедии является многоязычным инструментом для нахождения расстояния между статьями на живой копии википедии. Чтобы попробовать его, введите два срока ниже, и нажмите кнопку!',
										
									),
						'flags'=>array(
							'russia.png',
						),
					);
					
		



		//Swedish
		$l = 'sv';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Länk två artiklar på Wikipedia',
										'choose_language'	=> 'Välj ett språk',
										'choose_two' 		=> 'Ange två artiklar att länka',
										'template_check'	=> 'Tillåt länkar som visas i sidor [mall] rutor - (ger snabbare resultat och kortare vägar)',
										'random_path' 		=> 'Shuffle Paths - försöker en alternativ rutt mellan artiklar och tar längre tid',
										'article_direction' => 'Omvänd artikelriktning',
										'skip' 				=> 'Hoppa över artiklar (tillval - en per rad)',
										'shuffle' 			=> 'Slumpvis sökväg - försöker en alternativ rutt mellan artiklar och tar längre tid ',
										'random_article' 	=> 'slumpmässig',
										'additional_options'=> 'Ytterligare alternativ',
										'allowyears'		=> 'Tillåt fyrsiffriga år (som 2008, 2009, etc)',
										
										'startinglinkingof' => 'Börjar länkning av',
										'deadend' => 'Var ett dödsfall (inga anslutande artiklar)',
										'peakmemory' => 'Användning av hög minne',
										'pagegeneratedin' => 'Sidan genereras i',
										'contact' => 'Contact Us',
										'about'=>'Grader av Wikipedia är ett flerspråkigt verktyg för att hitta avståndet mellan artiklar på en live copy av wikipedia. För att prova det, skriv in två villkor nedan och klicka på knappen!',
										
										
									),
						'flags'=>array(
							'sweden.png',
						),
					);
		


		
		
		//Chinese
		$l = 'zh';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> '维基百科上的文章链接两个',
										'choose_language'	=> '選擇語言 - Xuǎnzé yǔyán',
										'choose_two' 		=> '輸入連接兩篇文章 - Shūrù liánjiē liǎng piān wénzhāng',
										'template_check' 	=> '允許鏈接出現在側箱 - Yǔnxǔ liànjiē chū xiànzài cè xiāng',
										'article_direction' => '逆向文章方向',
										'skip' 				=> '跳過文章（可選 - 每行一個）',
										'shuffle' 			=> '尝试1条之间（需要较长时间备用路由）',
										'random_article' 	=> '随机',
										'additional_options'=> '附加选项',
										'allowyears'		=> '允许四位数年',
										
										
										'startinglinkingof' => '开始的链接',
										'deadend' => '是死路一条（无连接篇）',
										'peakmemory' => '峰值内存使用量',
										'pagegeneratedin' => '页面生成',
										'contact' => '联系我们',
										'about'=>'维基百科的度是一个多语种的工具，用于发现在维基百科上的实时副本物品之间的距离。来试试吧，输入下面两个术语，并单击按钮！尝试另一种语言。',
										
										//'' => '',
										//'' => '',
										//'' => '',
										//'' => '',
										//'' => '',
									),
						'flags'=>array(
							'china.png',
						),
					);
	
		//Simple English
		$l = 'simple';
		$wikiurls[$l] = array(
						'search'=>	str_replace('[l]',$l,$d_search),
						'link'	=>	str_replace('[l]',$l,$d_link),
						'xml'	=>	str_replace('[l]',$l,$d_xml),
						'api'	=>	str_replace('[l]',$l,$d_api),
						'rand'	=>	str_replace('[l]',$l,$d_rand),
						'text' 	=>	array(
										'title'				=> 'Link Two Articles on Wikipedia',
										'choose_language'	=> 'Choose a language',
										'choose_two' 		=> 'Enter two articles to link',
										'template_check'	=> 'Allow Links that appear in the side (goes faster)',
										'random_path' 		=> 'Shuffle Paths - try different ways of connecting articles',
										'article_direction' => 'Go back the other way, or revserse reverse direction',
										'skip' 				=> 'Skip or Ignore articles (optional - put one article on each line)',
										'shuffle' 			=> 'Random Path - try a different path (may take longer) ',
										'random_article' 	=> 'random',
										'additional_options'=> 'Additional options',
										'allowyears'		=> 'Allow 4 digit years (like 2008, 2009, etc)',
										
										'startinglinkingof' => 'Starting linking of',
										'deadend' => 'was a dead end (no connecting articles)',
										'peakmemory' => 'Peak memory usage',
										'pagegeneratedin' => 'Page generated in',
										'contact' => 'Contact Us',
										'about'=>'SIMPLE ENGLISH SELECTED! Degrees of Wikipedia is a multi-language tool for finding the distance (or clicks) between articles on a live copy of simple wikipedia.  To try it, enter two terms below, and click the \'go\' button!',
										
										
									),
						'flags'=>array(
							'uk.png',
							'usa.png',
						),
					);
		
		
					
	
	$articlestoskip = array();
    
    //Database and server-specific Settings	
	$phpmailer_path = dirname(__FILE__) . '/zz_PHPMailer_v2.0.0/class.phpmailer.php';
	if(file_exists(dirname(__FILE__) . '/settings-prod.inc.php'))
	{		
		//use the settings-prod file, if needed.  Override the defaults below
		require_once(dirname(__FILE__) . '/settings-prod.inc.php');
	}
	else
	{
		//DEFAULT SETTINGS
		
		//Mail/Contact form settings
		$contact_email_address 	=	'';
		$phpmailer_smtp_host 	= 	'localhost';
		
		//set the error level
		error_reporting(0); 	// we will do our own error handling on the production server
		
		
		$supersecretkey = "changeme";
	}
	
    //**************************************	
	
	
	
	

	function getHTTP($url)
    {
        if($GLOBALS['dbg'])
		{
			echo "fetching $url<br/>";
		}
		
        $curl = new curlclass();
        $curl->setMethod('get');
        $curl->setURL($url);
        
        $params = array();
        $params[CURLOPT_USERAGENT] = $GLOBALS['useragentstring'];
        $params[CURLOPT_FOLLOWLOCATION] = true;
        $params[CURLOPT_MAXREDIRS] = 10;
        $params[CURLOPT_TIMEOUT] = $GLOBALS['curltimeout'];
		$params[CURLOPT_SSL_VERIFYPEER] = false;
		$params[CURLOPT_CERTINFO] = false;
        $curl->setAdditionalParameters($params);
        
        $data = $curl->exec();
		
		if($GLOBALS['dbg'])
		{
			echo "data returned within getHTTP($url): $data<br/>\n";
			echo $data;
		}	
		
		//echo $data;
		
        return $data;
    }
    
	
	//this function was taken from example code on php.net for the microtime function
	//it is used to clock the time it takes for the search to execute
	function getmicrotime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}


?>