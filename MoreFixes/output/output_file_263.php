 * <?php getStructureData('software', array('name'=>'Name', 'os'=>'Windows', 'price'=>10)); ?>
 *
 * @param 	string		$type				'blogpost', 'product', 'software', 'organization', 'qa',  ...
 * @param	array		$data				Array of data parameters for structured data
 * @return  string							HTML content
 */
function getStructuredData($type, $data = array())
{
	global $conf, $db, $hookmanager, $langs, $mysoc, $user, $website, $websitepage, $weblangs, $pagelangs; // Very important. Required to have var available when running inluded containers.

	$type = strtolower($type);

	if ($type == 'software') {
		$ret = '<!-- Add structured data for entry in a software annuary -->'."\n";
		$ret .= '<script type="application/ld+json">'."\n";
		$ret .= '{
			"@context": "https://schema.org",
			"@type": "SoftwareApplication",
			"name": "'.dol_escape_json($data['name']).'",
			"operatingSystem": "'.dol_escape_json($data['os']).'",
			"applicationCategory": "https://schema.org/'.dol_escape_json($data['applicationCategory']).'",';
		if (!empty($data['ratingcount'])) {
			$ret .= '
				"aggregateRating": {
					"@type": "AggregateRating",
					"ratingValue": "'.dol_escape_json($data['ratingvalue']).'",
					"ratingCount": "'.dol_escape_json($data['ratingcount']).'"
				},';
		}
		$ret .= '
			"offers": {
				"@type": "Offer",
				"price": "'.dol_escape_json($data['price']).'",
				"priceCurrency": "'.dol_escape_json($data['currency'] ? $data['currency'] : $conf->currency).'"
			}
		}'."\n";
		$ret .= '</script>'."\n";
	} elseif ($type == 'organization') {
		$companyname = $mysoc->name;
		$url = $mysoc->url;

		$ret = '<!-- Add structured data for organization -->'."\n";
		$ret .= '<script type="application/ld+json">'."\n";
		$ret .= '{
			"@context": "https://schema.org",
			"@type": "Organization",
			"name": "'.dol_escape_json($data['name'] ? $data['name'] : $companyname).'",
			"url": "'.dol_escape_json($data['url'] ? $data['url'] : $url).'",
			"logo": "'.($data['logo'] ? dol_escape_json($data['logo']) : '/wrapper.php?modulepart=mycompany&file=logos%2F'.urlencode($mysoc->logo)).'",
			"contactPoint": {
				"@type": "ContactPoint",
				"contactType": "Contact",
				"email": "'.dol_escape_json($data['email'] ? $data['email'] : $mysoc->email).'"
			}'."\n";
		if (is_array($mysoc->socialnetworks) && count($mysoc->socialnetworks) > 0) {
			$ret .= ",\n";
			$ret .= '"sameAs": [';
			$i = 0;
			foreach ($mysoc->socialnetworks as $key => $value) {
				if ($key == 'linkedin') {
					$ret .= '"https://www.'.$key.'.com/company/'.dol_escape_json($value).'"';
				} elseif ($key == 'youtube') {
					$ret .= '"https://www.'.$key.'.com/user/'.dol_escape_json($value).'"';
				} else {
					$ret .= '"https://www.'.$key.'.com/'.dol_escape_json($value).'"';
				}
				$i++;
				if ($i < count($mysoc->socialnetworks)) {
					$ret .= ', ';
				}
			}
			$ret .= ']'."\n";
		}
		$ret .= '}'."\n";
		$ret .= '</script>'."\n";
	} elseif ($type == 'blogpost') {
		if (!empty($websitepage->author_alias)) {
			//include_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
			//$tmpuser = new User($db);
			//$restmpuser = $tmpuser->fetch($websitepage->fk_user_creat);

			$pageurl = $websitepage->pageurl;
			$title = $websitepage->title;
			$image = $websitepage->image;
			$companyname = $mysoc->name;
			$description = $websitepage->description;

			$pageurl = str_replace('__WEBSITE_KEY__', $website->ref, $pageurl);
			$title = str_replace('__WEBSITE_KEY__', $website->ref, $title);
			$image = '/medias'.(preg_match('/^\//', $image) ? '' : '/').str_replace('__WEBSITE_KEY__', $website->ref, $image);
			$companyname = str_replace('__WEBSITE_KEY__', $website->ref, $companyname);
			$description = str_replace('__WEBSITE_KEY__', $website->ref, $description);

			$ret = '<!-- Add structured data for blog post -->'."\n";
			$ret .= '<script type="application/ld+json">'."\n";
			$ret .= '{
				  "@context": "https://schema.org",
				  "@type": "NewsArticle",
				  "mainEntityOfPage": {
				    "@type": "WebPage",
				    "@id": "'.dol_escape_json($pageurl).'"
				  },
				  "headline": "'.dol_escape_json($title).'",
				  "image": [
				    "'.dol_escape_json($image).'"
				   ],
				  "dateCreated": "'.dol_print_date($websitepage->date_creation, 'dayhourrfc').'",
				  "datePublished": "'.dol_print_date($websitepage->date_creation, 'dayhourrfc').'",
				  "dateModified": "'.dol_print_date($websitepage->date_modification, 'dayhourrfc').'",
				  "author": {
				    "@type": "Person",
				    "name": "'.dol_escape_json($websitepage->author_alias).'"
				  },
				  "publisher": {
				     "@type": "Organization",
				     "name": "'.dol_escape_json($companyname).'",
				     "logo": {
				        "@type": "ImageObject",
				        "url": "/wrapper.php?modulepart=mycompany&file=logos%2F'.urlencode($mysoc->logo).'"
				     }
				   },'."\n";
			if ($websitepage->keywords) {
				$ret .= '"keywords": [';
				$i = 0;
				$arrayofkeywords = explode(',', $websitepage->keywords);
				foreach ($arrayofkeywords as $keyword) {
					$ret .= '"'.dol_escape_json($keyword).'"';
					$i++;
					if ($i < count($arrayofkeywords)) {
						$ret .= ', ';
					}
				}
				$ret .= '],'."\n";
			}
			$ret .= '"description": "'.dol_escape_json($description).'"';
			$ret .= "\n".'}'."\n";
			$ret .= '</script>'."\n";
		} else {
			$ret .= '<!-- no structured data inserted inline inside blogpost because no author_alias defined -->'."\n";
		}
	} elseif ($type == 'product') {
		$ret = '<!-- Add structured data for product -->'."\n";
		$ret .= '<script type="application/ld+json">'."\n";
		$ret .= '{
				"@context": "https://schema.org/",
				"@type": "Product",
				"name": "'.dol_escape_json($data['label']).'",
				"image": [
					"'.dol_escape_json($data['image']).'",
				],
				"description": "'.dol_escape_json($data['description']).'",
				"sku": "'.dol_escape_json($data['ref']).'",
				"brand": {
					"@type": "Thing",
					"name": "'.dol_escape_json($data['brand']).'"
				},
				"author": {
					"@type": "Person",
					"name": "'.dol_escape_json($data['author']).'"
				}
				},
				"offers": {
					"@type": "Offer",
					"url": "https://example.com/anvil",
					"priceCurrency": "'.dol_escape_json($data['currency'] ? $data['currency'] : $conf->currency).'",
					"price": "'.dol_escape_json($data['price']).'",
					"itemCondition": "https://schema.org/UsedCondition",
					"availability": "https://schema.org/InStock",
					"seller": {
						"@type": "Organization",
						"name": "'.dol_escape_json($mysoc->name).'"
					}
				}
			}'."\n";
		$ret .= '</script>'."\n";
	} elseif ($type == 'qa') {
		$ret = '<!-- Add structured data for QA -->'."\n";
		$ret .= '<script type="application/ld+json">'."\n";
		$ret .= '{
				"@context": "https://schema.org/",
				"@type": "QAPage",
				"mainEntity": {
					"@type": "Question",
					"name": "'.dol_escape_json($data['name']).'",
					"text": "'.dol_escape_json($data['name']).'",
					"answerCount": 1,
					"author": {
						"@type": "Person",
						"name": "'.dol_escape_json($data['author']).'"
					}
					"acceptedAnswer": {
						"@type": "Answer",
						"text": "'.dol_escape_json(dol_string_nohtmltag(dolStripPhpCode($data['description']))).'",
						"author": {
							"@type": "Person",
							"name": "'.dol_escape_json($data['author']).'"
						}
					}
				}
			}'."\n";
		$ret .= '</script>'."\n";
	}
	return $ret;
}

/**
 * Return HTML content to add as header card for an article, news or Blog Post or home page.
 *
 * @param	array	$params					Array of parameters
 * @return  string							HTML content
 */
function getSocialNetworkHeaderCards($params = null)
{
	global $conf, $db, $hookmanager, $langs, $mysoc, $user, $website, $websitepage, $weblangs; // Very important. Required to have var available when running inluded containers.

	$out = '';

	if ($website->virtualhost) {
		$pageurl = $websitepage->pageurl;
		$title = $websitepage->title;
		$image = $websitepage->image;
		$companyname = $mysoc->name;
		$description = $websitepage->description;

		$pageurl = str_replace('__WEBSITE_KEY__', $website->ref, $pageurl);
		$title = str_replace('__WEBSITE_KEY__', $website->ref, $title);
		$image = '/medias'.(preg_match('/^\//', $image) ? '' : '/').str_replace('__WEBSITE_KEY__', $website->ref, $image);
		$companyname = str_replace('__WEBSITE_KEY__', $website->ref, $companyname);
		$description = str_replace('__WEBSITE_KEY__', $website->ref, $description);

		$shortlangcode = '';
		if ($websitepage->lang) {
			$shortlangcode = substr($websitepage->lang, 0, 2); // en_US or en-US -> en
		}
		if (empty($shortlangcode)) {
			$shortlangcode = substr($website->lang, 0, 2); // en_US or en-US -> en
		}

		$fullurl = $website->virtualhost.'/'.$websitepage->pageurl.'.php';
		$canonicalurl = $website->virtualhost.(($websitepage->id == $website->fk_default_home) ? '/' : (($shortlangcode != substr($website->lang, 0, 2) ? '/'.$shortlangcode : '').'/'.$websitepage->pageurl.'.php'));
		$hashtags = trim(join(' #', array_map('trim', explode(',', $websitepage->keywords))));

		// Open Graph
		$out .= '<meta name="og:type" content="website">'."\n";	// TODO If blogpost, use type article
		$out .= '<meta name="og:title" content="'.$websitepage->title.'">'."\n";
		if ($websitepage->image) {
			$out .= '<meta name="og:image" content="'.$website->virtualhost.$image.'">'."\n";
		}
		$out .= '<meta name="og:url" content="'.$canonicalurl.'">'."\n";

		// Twitter
		$out .= '<meta name="twitter:card" content="summary">'."\n";
		if (!empty($params) && !empty($params['twitter_account'])) {
			$out .= '<meta name="twitter:site" content="@'.$params['twitter_account'].'">'."\n";
			$out .= '<meta name="twitter:creator" content="@'.$params['twitter_account'].'">'."\n";
		}
		$out .= '<meta name="twitter:title" content="'.$websitepage->title.'">'."\n";
		if ($websitepage->description) {
			$out .= '<meta name="twitter:description" content="'.$websitepage->description.'">'."\n";
		}
		if ($websitepage->image) {
			$out .= '<meta name="twitter:image" content="'.$website->virtualhost.$image.'">'."\n";
		}
		//$out .= '<meta name="twitter:domain" content="'.getDomainFromURL($website->virtualhost, 1).'">';
		/*
		 $out .= '<meta name="twitter:app:name:iphone" content="">';
		 $out .= '<meta name="twitter:app:name:ipad" content="">';
		 $out .= '<meta name="twitter:app:name:googleplay" content="">';
		 $out .= '<meta name="twitter:app:url:iphone" content="">';
		 $out .= '<meta name="twitter:app:url:ipad" content="">';
		 $out .= '<meta name="twitter:app:url:googleplay" content="">';
		 $out .= '<meta name="twitter:app:id:iphone" content="">';
		 $out .= '<meta name="twitter:app:id:ipad" content="">';
		 $out .= '<meta name="twitter:app:id:googleplay" content="">';
		 */
	}

	return $out;
}

/**
 * Return HTML content to add structured data for an article, news or Blog Post.
 *
 * @return  string							HTML content
 */
function getSocialNetworkSharingLinks()
{
	global $conf, $db, $hookmanager, $langs, $mysoc, $user, $website, $websitepage, $weblangs; // Very important. Required to have var available when running inluded containers.

	$out = '<!-- section for social network sharing of page -->'."\n";

	if ($website->virtualhost) {
		$fullurl = $website->virtualhost.'/'.$websitepage->pageurl.'.php';
		$hashtags = trim(join(' #', array_map('trim', explode(',', $websitepage->keywords))));

		$out .= '<div class="dol-social-share">'."\n";

		// Twitter
		$out .= '<div class="dol-social-share-tw">'."\n";
		$out .= '<a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$fullurl.'" data-text="'.dol_escape_htmltag($websitepage->description).'" data-lang="'.$websitepage->lang.'" data-size="small" data-related="" data-hashtags="'.preg_replace('/^#/', '', $hashtags).'" data-count="horizontal">Tweet</a>';
		$out .= '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script>';
		$out .= '</div>'."\n";

		// Reddit
		$out .= '<div class="dol-social-share-reddit">'."\n";
		$out .= '<a href="https://www.reddit.com/submit" target="_blank" rel="noopener noreferrer external" onclick="window.location = \'https://www.reddit.com/submit?url='.$fullurl.'\'; return false">';
		$out .= '<span class="dol-social-share-reddit-span">Reddit</span>';
		$out .= '</a>';
		$out .= '</div>'."\n";

		// Facebook
		$out .= '<div class="dol-social-share-fbl">'."\n";
		$out .= '<div id="fb-root"></div>'."\n";
		$out .= '<script>(function(d, s, id) {
				  var js, fjs = d.getElementsByTagName(s)[0];
				  if (d.getElementById(id)) return;
				  js = d.createElement(s); js.id = id;
				  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0&amp;appId=dolibarr.org";
				  fjs.parentNode.insertBefore(js, fjs);
				}(document, \'script\', \'facebook-jssdk\'));</script>
				        <fb:like
				        href="'.$fullurl.'"
				        layout="button_count"
				        show_faces="false"
				        width="90"
				        colorscheme="light"
				        share="1"
				        action="like" ></fb:like>'."\n";
		$out .= '</div>'."\n";

		$out .= "\n</div>\n";
	} else {
		$out .= '<!-- virtual host not defined in CMS. No way to add sharing buttons -->'."\n";
	}
	$out .= '<!-- section end for social network sharing of page -->'."\n";

	return $out;
}

/**
 * Return list of containers object that match a criteria.
 * WARNING: This function can be used by websites.
 *
 * @param 	string		$type				Type of container to search into (Example: '', 'page', 'blogpost', 'page,blogpost', ...)
 * @param 	string		$algo				Algorithm used for search (Example: 'meta' is searching into meta information like title and description, 'content', 'sitefiles', or any combination 'meta,content,...')
 * @param	string		$searchstring		Search string
 * @param	int			$max				Max number of answers
 * @param	string		$sortfield			Sort Fields
 * @param	string		$sortorder			Sort order ('DESC' or 'ASC')
 * @param	string		$langcode			Language code ('' or 'en', 'fr', 'es', ...)
 * @param	array		$otherfilters		Other filters
 * @param	int			$status				0 or 1, or -1 for both
 * @return  string							HTML content
 */
function getPagesFromSearchCriterias($type, $algo, $searchstring, $max = 25, $sortfield = 'date_creation', $sortorder = 'DESC', $langcode = '', $otherfilters = 'null', $status = 1)
{
	global $conf, $db, $hookmanager, $langs, $mysoc, $user, $website, $websitepage, $weblangs; // Very important. Required to have var available when running inluded containers.

	$error = 0;
	$arrayresult = array('code'=>'', 'list'=>array());

	if (!is_object($weblangs)) {
		$weblangs = $langs;
	}

	if (empty($searchstring) && empty($type) && empty($langcode) && empty($otherfilters)) {
		$error++;
		$arrayresult['code'] = 'KO';
		$arrayresult['message'] = $weblangs->trans("EmptySearchString");
	} elseif ($searchstring && dol_strlen($searchstring) < 2) {
		$weblangs->load("errors");
		$error++;
		$arrayresult['code'] = 'KO';
		$arrayresult['message'] = $weblangs->trans("ErrorSearchCriteriaTooSmall");
	} else {
		$tmparrayoftype = explode(',', $type);
		/*foreach ($tmparrayoftype as $tmptype) {
			if (!in_array($tmptype, array('', 'page', 'blogpost'))) {
				$error++;
				$arrayresult['code'] = 'KO';
				$arrayresult['message'] = 'Bad value for parameter type';
				break;
			}
		}*/
	}

	$searchdone = 0;
	$found = 0;

	if (!$error && (empty($max) || ($found < $max)) && (preg_match('/meta/', $algo) || preg_match('/content/', $algo))) {
		$sql = 'SELECT wp.rowid FROM '.MAIN_DB_PREFIX.'website_page as wp';
		if (is_array($otherfilters) && !empty($otherfilters['category'])) {
			$sql .= ', '.MAIN_DB_PREFIX.'categorie_website_page as cwp';
		}
		$sql .= " WHERE wp.fk_website = ".((int) $website->id);
		if ($status >= 0) {
			$sql .= " AND wp.status = ".((int) $status);
		}
		if ($langcode) {
			$sql .= " AND wp.lang ='".$db->escape($langcode)."'";
		}
		if ($type) {
			$tmparrayoftype = explode(',', $type);
			$typestring = '';
			foreach ($tmparrayoftype as $tmptype) {
				$typestring .= ($typestring ? ", " : "")."'".$db->escape(trim($tmptype))."'";
			}
			$sql .= " AND wp.type_container IN (".$db->sanitize($typestring, 1).")";
		}
		$sql .= " AND (";
		$searchalgo = '';
		if (preg_match('/meta/', $algo)) {
			$searchalgo .= ($searchalgo ? ' OR ' : '')."wp.title LIKE '%".$db->escapeforlike($db->escape($searchstring))."%' OR wp.description LIKE '%".$db->escapeforlike($db->escape($searchstring))."%'";
			$searchalgo .= ($searchalgo ? ' OR ' : '')."wp.keywords LIKE '".$db->escapeforlike($db->escape($searchstring)).",%' OR wp.keywords LIKE '% ".$db->escapeforlike($db->escape($searchstring))."%'"; // TODO Use a better way to scan keywords
		}
		if (preg_match('/content/', $algo)) {
			$searchalgo .= ($searchalgo ? ' OR ' : '')."wp.content LIKE '%".$db->escapeforlike($db->escape($searchstring))."%'";
		}
		$sql .= $searchalgo;
		if (is_array($otherfilters) && !empty($otherfilters['category'])) {
			$sql .= ' AND cwp.fk_website_page = wp.rowid AND cwp.fk_categorie = '.((int) $otherfilters['category']);
		}
		$sql .= ")";
		$sql .= $db->order($sortfield, $sortorder);
		$sql .= $db->plimit($max);
		//print $sql;

		$resql = $db->query($sql);
		if ($resql) {
			$i = 0;
			while (($obj = $db->fetch_object($resql)) && ($i < $max || $max == 0)) {
				if ($obj->rowid > 0) {
					$tmpwebsitepage = new WebsitePage($db);
					$tmpwebsitepage->fetch($obj->rowid);
					if ($tmpwebsitepage->id > 0) {
						$arrayresult['list'][$obj->rowid] = $tmpwebsitepage;
					}
					$found++;
				}
				$i++;
			}
		} else {
			$error++;
			$arrayresult['code'] = $db->lasterrno();
			$arrayresult['message'] = $db->lasterror();
		}

		$searchdone = 1;
	}

	if (!$error && (empty($max) || ($found < $max)) && (preg_match('/sitefiles/', $algo))) {
		global $dolibarr_main_data_root;

		$pathofwebsite = $dolibarr_main_data_root.($conf->entity > 1 ? '/'.$conf->entity : '').'/website/'.$website->ref;
		$filehtmlheader = $pathofwebsite.'/htmlheader.html';
		$filecss = $pathofwebsite.'/styles.css.php';
		$filejs = $pathofwebsite.'/javascript.js.php';
		$filerobot = $pathofwebsite.'/robots.txt';
		$filehtaccess = $pathofwebsite.'/.htaccess';
		$filemanifestjson = $pathofwebsite.'/manifest.json.php';
		$filereadme = $pathofwebsite.'/README.md';

		$filecontent = file_get_contents($filehtmlheader);
		if ((empty($max) || ($found < $max)) && preg_match('/'.preg_quote($searchstring, '/').'/', $filecontent)) {
			$arrayresult['list'][] = array('type'=>'website_htmlheadercontent');
		}

		$filecontent = file_get_contents($filecss);
		if ((empty($max) || ($found < $max)) && preg_match('/'.preg_quote($searchstring, '/').'/', $filecontent)) {
			$arrayresult['list'][] = array('type'=>'website_csscontent');
		}

		$filecontent = file_get_contents($filejs);
		if ((empty($max) || ($found < $max)) && preg_match('/'.preg_quote($searchstring, '/').'/', $filecontent)) {
			$arrayresult['list'][] = array('type'=>'website_jscontent');
		}

		$filerobot = file_get_contents($filerobot);
		if ((empty($max) || ($found < $max)) && preg_match('/'.preg_quote($searchstring, '/').'/', $filecontent)) {
			$arrayresult['list'][] = array('type'=>'website_robotcontent');
		}

		$searchdone = 1;
	}

	if (!$error) {
		if ($searchdone) {
			$arrayresult['code'] = 'OK';
			if (empty($arrayresult['list'])) {
				$arrayresult['code'] = 'KO';
				$arrayresult['message'] = $weblangs->trans("NoRecordFound");
			}
		} else {
			$error++;
			$arrayresult['code'] = 'KO';
			$arrayresult['message'] = 'No supported algorithm found';
		}
	}

	return $arrayresult;
}

/**
 * Download all images found into page content $tmp.
 * If $modifylinks is set, links to images will be replace with a link to viewimage wrapper.
 *
 * @param 	Website	 	$object			Object website
 * @param 	WebsitePage	$objectpage		Object website page
 * @param 	string		$urltograb		URL to grab (exemple: http://www.nltechno.com/ or http://www.nltechno.com/dir1/ or http://www.nltechno.com/dir1/mapage1)
 * @param 	string		$tmp			Content to parse
 * @param 	string		$action			Var $action
 * @param	string		$modifylinks	0=Do not modify content, 1=Replace links with a link to viewimage
 * @param	int			$grabimages		0=Do not grab images, 1=Grab images
 * @param	string		$grabimagesinto	'root' or 'subpage'
 * @return	void
 */
function getAllImages($object, $objectpage, $urltograb, &$tmp, &$action, $modifylinks = 0, $grabimages = 1, $grabimagesinto = 'subpage')
{
	global $conf;

	$error = 0;

	dol_syslog("Call getAllImages with grabimagesinto=".$grabimagesinto);

	$alreadygrabbed = array();

	if (preg_match('/\/$/', $urltograb)) {
		$urltograb .= '.';
	}
	$urltograb = dirname($urltograb); // So urltograb is now http://www.nltechno.com or http://www.nltechno.com/dir1

	// Search X in "img...src=X"
	$regs = array();
	preg_match_all('/<img([^\.\/]+)src="([^>"]+)"([^>]*)>/i', $tmp, $regs);

	foreach ($regs[0] as $key => $val) {
		if (preg_match('/^data:image/i', $regs[2][$key])) {
			continue; // We do nothing for such images
		}

		if (preg_match('/^\//', $regs[2][$key])) {
			$urltograbdirrootwithoutslash = getRootURLFromURL($urltograb);
			$urltograbbis = $urltograbdirrootwithoutslash.$regs[2][$key]; // We use dirroot
		} else {
			$urltograbbis = $urltograb.'/'.$regs[2][$key]; // We use dir of grabbed file
		}

		$linkwithoutdomain = $regs[2][$key];
		$dirforimages = '/'.$objectpage->pageurl;
		if ($grabimagesinto == 'root') {
			$dirforimages = '';
		}

		// Define $filetosave and $filename
		$filetosave = $conf->medias->multidir_output[$conf->entity].'/image/'.$object->ref.$dirforimages.(preg_match('/^\//', $regs[2][$key]) ? '' : '/').$regs[2][$key];
		if (preg_match('/^http/', $regs[2][$key])) {
			$urltograbbis = $regs[2][$key];
			$linkwithoutdomain = preg_replace('/^https?:\/\/[^\/]+\//i', '', $regs[2][$key]);
			$filetosave = $conf->medias->multidir_output[$conf->entity].'/image/'.$object->ref.$dirforimages.(preg_match('/^\//', $linkwithoutdomain) ? '' : '/').$linkwithoutdomain;
		}
		$filename = 'image/'.$object->ref.$dirforimages.(preg_match('/^\//', $linkwithoutdomain) ? '' : '/').$linkwithoutdomain;

		// Clean the aa/bb/../cc into aa/cc
		$filetosave = preg_replace('/\/[^\/]+\/\.\./', '', $filetosave);
		$filename = preg_replace('/\/[^\/]+\/\.\./', '', $filename);

		//var_dump($filetosave);
		//var_dump($filename);
		//exit;

		if (empty($alreadygrabbed[$urltograbbis])) {
			if ($grabimages) {
				$tmpgeturl = getURLContent($urltograbbis, 'GET', '', 1, array(), array('http', 'https'), 0);
				if ($tmpgeturl['curl_error_no']) {
					$error++;
					setEventMessages('Error getting '.$urltograbbis.': '.$tmpgeturl['curl_error_msg'], null, 'errors');
					$action = 'create';
				} elseif ($tmpgeturl['http_code'] != '200') {
					$error++;
					setEventMessages('Error getting '.$urltograbbis.': '.$tmpgeturl['http_code'], null, 'errors');
					$action = 'create';
				} else {
					$alreadygrabbed[$urltograbbis] = 1; // Track that file was alreay grabbed.

					dol_mkdir(dirname($filetosave));

					$fp = fopen($filetosave, "w");
					fputs($fp, $tmpgeturl['content']);
					fclose($fp);
					if (!empty($conf->global->MAIN_UMASK)) {
						@chmod($filetosave, octdec($conf->global->MAIN_UMASK));
					}
				}
			}
		}

		if ($modifylinks) {
			$tmp = preg_replace('/'.preg_quote($regs[0][$key], '/').'/i', '<img'.$regs[1][$key].'src="'.DOL_URL_ROOT.'/viewimage.php?modulepart=medias&file='.$filename.'"'.$regs[3][$key].'>', $tmp);
		}
	}

	// Search X in "background...url(X)"
	preg_match_all('/background([^\.\/\(;]+)url\([\"\']?([^\)\"\']*)[\"\']?\)/i', $tmp, $regs);

	foreach ($regs[0] as $key => $val) {
		if (preg_match('/^data:image/i', $regs[2][$key])) {
			continue; // We do nothing for such images
		}

		if (preg_match('/^\//', $regs[2][$key])) {
			$urltograbdirrootwithoutslash = getRootURLFromURL($urltograb);
			$urltograbbis = $urltograbdirrootwithoutslash.$regs[2][$key]; // We use dirroot
		} else {
			$urltograbbis = $urltograb.'/'.$regs[2][$key]; // We use dir of grabbed file
		}

		$linkwithoutdomain = $regs[2][$key];

		$dirforimages = '/'.$objectpage->pageurl;
		if ($grabimagesinto == 'root') {
			$dirforimages = '';
		}

		$filetosave = $conf->medias->multidir_output[$conf->entity].'/image/'.$object->ref.$dirforimages.(preg_match('/^\//', $regs[2][$key]) ? '' : '/').$regs[2][$key];

		if (preg_match('/^http/', $regs[2][$key])) {
			$urltograbbis = $regs[2][$key];
			$linkwithoutdomain = preg_replace('/^https?:\/\/[^\/]+\//i', '', $regs[2][$key]);
			$filetosave = $conf->medias->multidir_output[$conf->entity].'/image/'.$object->ref.$dirforimages.(preg_match('/^\//', $linkwithoutdomain) ? '' : '/').$linkwithoutdomain;
		}

		$filename = 'image/'.$object->ref.$dirforimages.(preg_match('/^\//', $linkwithoutdomain) ? '' : '/').$linkwithoutdomain;

		// Clean the aa/bb/../cc into aa/cc
		$filetosave = preg_replace('/\/[^\/]+\/\.\./', '', $filetosave);
		$filename = preg_replace('/\/[^\/]+\/\.\./', '', $filename);

		//var_dump($filetosave);
		//var_dump($filename);
		//exit;

		if (empty($alreadygrabbed[$urltograbbis])) {
			if ($grabimages) {
				$tmpgeturl = getURLContent($urltograbbis, 'GET', '', 1, array(), array('http', 'https'), 0);
				if ($tmpgeturl['curl_error_no']) {
					$error++;
					setEventMessages('Error getting '.$urltograbbis.': '.$tmpgeturl['curl_error_msg'], null, 'errors');
					$action = 'create';
				} elseif ($tmpgeturl['http_code'] != '200') {
					$error++;
					setEventMessages('Error getting '.$urltograbbis.': '.$tmpgeturl['http_code'], null, 'errors');
					$action = 'create';
				} else {
					$alreadygrabbed[$urltograbbis] = 1; // Track that file was alreay grabbed.

					dol_mkdir(dirname($filetosave));

					$fp = fopen($filetosave, "w");
					fputs($fp, $tmpgeturl['content']);
					fclose($fp);
					if (!empty($conf->global->MAIN_UMASK)) {
						@chmod($filetosave, octdec($conf->global->MAIN_UMASK));
					}
				}
			}
		}

		if ($modifylinks) {
			$tmp = preg_replace('/'.preg_quote($regs[0][$key], '/').'/i', 'background'.$regs[1][$key].'url("'.DOL_URL_ROOT.'/viewimage.php?modulepart=medias&file='.$filename.'")', $tmp);
		}
	}
}

