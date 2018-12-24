<?php
//////////////////////////////////////////////////////////
//	PHP Index 1.3
//	Copyright (c) 2006, Christian Elmerot, Chreo.net
//	All rights reserved.
//////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////
//
//  Icon functions, Copyright (c) 2007, www.foks.se
//
//////////////////////////////////////////////////////////

if(!isset($_GET["ext"])||$_GET["ext"]=="")
{
	
	//////////////////////////////////////////////////////////
	//	Format/Layout Settings
	//////////////////////////////////////////////////////////

		// Sort by 'Name', 'Type', 'Size' or 'Date'
	$sort = $_GET['sort'];

	// Sort Folders First
	$list_folders_first = true;

	// Always Sort Folders First Regardless Of Other Sorting
	$list_folders_first_always = true;

	// List Files Not To Be Included In the Index-listing
	$skip_files = array('.' , '..' , 'index.php', '.htaccess', '.htpasswd');

	// Skip Files That Contain One Of These Keys
	$skip_files_containing = array('_vti_');

	// Link To CSS-file, Unset To Disable (Default)
	$css_file = '';

	// CSS-styles, Unset To '' To Disable
	$css_style = '
		body { color: 000000; background-color: #ffffff; margin: 40px; }
		td { font-family: "Arial", arial; font-size: 12px;  color: #000000; }
		hr { color: black; background-color: #000000; border: none; height: 1px; width: 100%; line-height: 0px; }
		h1 { color: #000000; }
		a:link { text-decoration: none; color: #000000; }
		a:visited { text-decoration: none; color: #000000; }
		a:active { text-decoration: none; color: #000000; }
		a:hover { text-decoration: underline; color: #000000; }';

//////////////////////////////////////////////////////////
//	Retrieve URL Data
//////////////////////////////////////////////////////////

	$url_host = $_SERVER['HTTP_HOST'];		// Server name, eg. www.domain.net
	$url_array = explode("?", $_SERVER['REQUEST_URI']);
	$url_path = dirname($url_array[0].'x');	// Add an extra character in case URL ends with /

	// If Used as 403 ErrorDoc Then $_GET['sort'] probably did not work
	if ( !isset($sort) and ( strlen($url_array[1]) > 4) ) $sort = substr($url_array[1], 5);

	$url_path_array_temp = explode("/", $url_path);
	$url_depth = sizeof($url_path_array_temp);
	$url_path_array = array();
	for ($i = 0; $i < $url_depth; $i++) {
		if ($url_path_array_temp[$i] != "") {
			array_push ($url_path_array, $url_path_array_temp[$i]);
		}
	}
	$url_depth = sizeof($url_path_array);
	$folder_real_path = realpath('../'.$url_path);
	if ($folder_real_path !== true) {
		$folder_real_path = realpath('.');
	}



//////////////////////////////////////////////////////////
//	Retrieve File/Folder Data
//////////////////////////////////////////////////////////

	// Create and populate arrays with filenames
	//$filenamelist = scandir($folder_real_path);
	$filenamelist = array();
	if ($folder_handle = opendir($folder_real_path)) {
		while (false !== ($file = readdir($folder_handle))) {
			if (!in_array($file, $skip_files) and
				!search_string_using_array($file, $skip_files_containing)) {
				array_push ($filenamelist, $file);
			}
		}
		closedir($folder_handle);
	}

	$filenamelength = array();

	$filetypelist = array();
	$simpletypelist = array();

	$filesizelist = array();
	$filetimelist = array();

	$max_filename_length = 0;

	if ($list_folders_first) {
		$folderlist = array();
		$filelist = array();
		for ($i = 0; $i < count($filenamelist); $i++) {
			$file_full_path = $folder_real_path.'/'.$filenamelist[$i];

			if (is_dir($file_full_path)) {
				array_push ($folderlist, $filenamelist[$i]);
			}
			else {
				array_push ($filelist, $filenamelist[$i]);
			}
		}
		$filenamelist = array_merge($folderlist, $filelist);
	}

	// Retrieve date, size and type for the files/folders
	for ($i = 0; $i < count($filenamelist); $i++) {
		$file_full_path = $folder_real_path.'/'.$filenamelist[$i];

		if (is_file($file_full_path)) {
			$filenamelength[$i] = strlen($filenamelist[$i]);
			$filetypelist[$i] = 'File';
			$simpletypelist[$i] = 'file';

			$filesizelist[$i] = filesize($file_full_path);
			$filetimelist[$i] = filemtime($file_full_path);
		}
		elseif (is_dir($file_full_path)) {
			$filenamelength[$i] = strlen($filenamelist[$i]);
			$filetypelist[$i] = 'Directory';
			$simpletypelist[$i] = 'dir';

			$filesizelist[$i] = 0;
			$filetimelist[$i] = filemtime($file_full_path);
		}
		else {
			$filenamelength[$i] = 0;
			$filetypelist[$i] = 'Unknown';
			$simpletypelist[$i] = 'unk';
			$filesizelist[$i] = -1;
			$filetimelist[$i] = -1;
		}
	}

//////////////////////////////////////////////////////////
//	Sort the arrays if requested (defaults lists the files by names)
//////////////////////////////////////////////////////////

	switch ($sort) {
		case "date":
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filetimelist, SORT_NUMERIC, SORT_ASC,
							$filenamelist,
							$filenamelength,
							$filetypelist,
							$filesizelist);
			}
			else array_multisort($filetimelist, SORT_NUMERIC, SORT_ASC,
							$filenamelist,
							$filenamelength,
							$filetypelist,
							$simpletypelist,
							$filesizelist);
			break;
		case "date_desc":
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filetimelist, SORT_NUMERIC, SORT_DESC,
							$filenamelist,
							$filenamelength,
							$filetypelist,
							$filesizelist);
			}
			else array_multisort($filetimelist, SORT_NUMERIC, SORT_DESC,
							$filenamelist,
							$filenamelength,
							$filetypelist,
							$simpletypelist,
							$filesizelist);
			$sorted_by_date_desc = 1;
			break;
		case "name":
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filenamelist, SORT_ASC,
							$filetypelist,
							$filenamelength,
							$filesizelist,
							$filetimelist);
			}
			else array_multisort($filenamelist, SORT_ASC,
							$filetypelist,
							$filenamelength,
							$filesizelist,
							$simpletypelist,
							$filetimelist);
			break;
		case "name_desc":
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filenamelist, SORT_DESC,
							$filetypelist,
							$filenamelength,
							$filesizelist,
							$filetimelist);
			}
			else array_multisort($filenamelist, SORT_DESC,
							$filetypelist,
							$simpletypelist,
							$filenamelength,
							$filesizelist,
							$filetimelist);
			$sorted_by_name_desc = 1;
			break;
		case "size":
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filesizelist, SORT_NUMERIC, SORT_ASC,
							$filenamelist,
							$filenamelength,
							$filetypelist,
							$filetimelist);
			}
			else array_multisort($filesizelist, SORT_NUMERIC, SORT_ASC,
							$filenamelist,
							$filenamelength,
							$filetypelist,
							$simpletypelist,
							$filetimelist);
			break;
		case "size_desc":
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filesizelist, SORT_NUMERIC, SORT_DESC,
							$filenamelist,
							$filenamelength,
							$filetypelist,
							$filetimelist);
			}
			else array_multisort($filesizelist, SORT_NUMERIC, SORT_DESC,
							$filenamelist,
							$filenamelength,
							$filetypelist,
							$simpletypelist,
							$filetimelist);
			$sorted_by_size_desc = 1;
			break;
		case "type":
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filetypelist, SORT_ASC,
							$filenamelist,
							$filenamelength,
							$filesizelist,
							$filetimelist);
			}
			else array_multisort($filetypelist, SORT_ASC,
							$filenamelist,
							$simpletypelist,
							$filenamelength,
							$filesizelist,
							$filetimelist);
			break;
		case "type_desc":
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filetypelist, SORT_DESC,
							$filenamelist,
							$filenamelength,
							$filesizelist,
							$filetimelist);
			}
			else array_multisort($filetypelist, SORT_DESC,
							$filenamelist,
							$simpletypelist,
							$filenamelength,
							$filesizelist,
							$filetimelist);
			$sorted_by_type_desc = 1;
			break;
		default:
			if ( $list_folders_first_always ) {
				array_multisort($simpletypelist, SORT_ASC,
							$filenamelist, SORT_ASC,
							$filetypelist,
							$filenamelength,
							$filesizelist,
							$filetimelist);
			}
			else {
				array_multisort($filenamelist, SORT_ASC,
							$filetypelist,
							$filenamelength,
							$filesizelist,
							$simpletypelist,
							$filetimelist);
			}
			break;
	}


//////////////////////////////////////////////////////////
//	Start Document Output
//////////////////////////////////////////////////////////
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>Index of <?php echo $url_host.$url_path; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-15" />
<?php
		if ($css_file !== '') {
			echo "\t".'<link rel="stylesheet" type="text/css" href="' . $css_file . '" />'."\n";
		}
?>
<?php
		if ($css_style !== '') {
			echo '	<style type="text/css">';
			print $css_style;
			echo "\n\t".'</style>'."\n";
		}
?>
</head>
<body>
<h1>Index of <?php
	echo '<a href="http://'.$url_host.'">'.$url_host.'</a>';
	for($i = 0; $i < $url_depth; $i++) {
		$temp_path = "";
		for ($j = 0; $j <= $i; $j++) {
			$temp_path = $temp_path . '/' .$url_path_array[$j];
		}
		echo '/<a href="'.$temp_path.'">'.$url_path_array[$i].'</a>';
	}
?></h1>

<table cellpadding="0" cellspacing="0" border=0>
<?php
	echo "\t<tr>\n";
	if ($sorted_by_name_desc)	echo "\t\t".'<td width=20></td><td><a href="?sort=name">Name</a></td>'."\n";
	else						echo "\t\t".'<td width=20></td><td><a href="?sort=name_desc">Name</a></td>'."\n";

	if ($sorted_by_date_desc)	echo "\t\t".'<td align="center">&nbsp;<a href="?sort=date">Date</a></td>'."\n";
	else						echo "\t\t".'<td align="center">&nbsp;<a href="?sort=date_desc">Date</a></td>'."\n";

	if ($sorted_by_size_desc)	echo "\t\t".'<td align="center">&nbsp;<a href="?sort=size">Size</a></td>'."\n";
	else						echo "\t\t".'<td align="center">&nbsp;<a href="?sort=size_desc">Size</a></td>'."\n";
	echo "\t</tr>\n";

	echo "\t<tr>\n\t\t".'<td colspan="4"><hr /></td>'."\n\t</tr>\n";

	for ($i = 0; $i < count($filenamelist); $i++) {

		echo "\t<tr height=20>\n";

		//check file extension
		if ($filetypelist[$i] == 'Directory')
		{
			$icon="directory";
		}
		else
		{
			$filename_array = explode(".",rawurlencode($filenamelist[$i]));
			$icon = $filename_array[count($filename_array)-1];
		}
		
		echo "\t\t".'<td width=20><img src=\'index.php?ext='.$icon.'\'></td><td><a href="'.rawurlencode($filenamelist[$i]).'">'
			.$filenamelist[$i] . '</a></td>'."\n";

		if ($filetypelist[$i] == 'File') {
			echo "\t\t".'<td>&nbsp;&nbsp;&nbsp;&nbsp;'
				.strftime("%Y-%b-%d %H:%M", $filetimelist[$i])
				.'&nbsp;&nbsp;&nbsp;&nbsp;</td>'."\n";
			echo "\t\t".'<td align="right">'.size_to_prefixed_size($filesizelist[$i]).'</td>'."\n";
		}
		elseif ($filetypelist[$i] == 'Directory') {
			echo "\t\t".'<td>&nbsp;&nbsp;&nbsp;&nbsp;'
					.strftime("%Y-%b-%d %H:%M", $filetimelist[$i])
					.'&nbsp;&nbsp;&nbsp;&nbsp;</td>'."\n";
			echo "\t\t".'<td align="right">-&nbsp;&nbsp;&nbsp;</td>'."\n";
		}

		echo "\t</tr>\n";
	}
?>
</table>
</body>
</html>





<?php
}

else
{
	//show icon
	$icon = strtolower($_GET["ext"]);
		
	switch($icon)
	{
		case "directory":
			$kod = "K|P kÃl8(È(7b()D[)Ct|9@tNU8»Õ;-nÊ^wt øQnu3Gy]Z·6:eE’ÀŸ√ÈﬁWtt ‡P‰iGBw_}·…h^ËKÃ⁄4LÕMpyŒ‚cnq3Ëpÿ[·xZU7»Œ€ºÈxBjxMs@”^CAvﬁZø‚2iF–O€Â‰ÊYvrÀRA√‘aGx]:·2:[;KN:LT|DkuL0b:[j((()zP((-H((√((9«È*Mc‘Ke…Jrr(H(.0X?c≈(rCH/ j)ﬂdzÃaA–4ÃV(6LKA”÷(ATX))—(K¬WÈu?≈paÈj/H(~€:3n6“El:]¬j.[:ÿ(8iÃ-G‰~ÃÁuxql1–÷N¡JM+b(Q[|È/@)V√(BT«+c≈(ZB«.„v(ﬂXj6_ŸDjV=8rÀ[MF(d:—p—yÀ»VÈxÈœ((–L2w«)cl(Y0(.“9ÿÿa„«.ørz›(2w|Icu‘PClL€z(qVR-Ë”~j–4Ë5«+gl]c04qK2.»]∆4N«)øÀ(ATz))À(O?*>S]*‹Xj6ﬂ()≈ÿ(Do«+Ux(8BHÈeAP⁄(fv@zs«k(B(7„LaÁYxÁﬂF∆Ê(U:-J@(√((D((+b)r[⁄3ÿ/j*P1(-H((√«(=;«*N((KG(1wH(Ntj()j((((B.X/d⁄(1]«(+√¿-~hocPt.@(G(((((((L(((«(((D(((Ë)8rj+/«((/(4((/f((1((((((:*jHA0((‰(((6vX(Sﬂj8Y8)‡((K=(/d]Ë1H«(+ÿ)n](-cÿ*/((G7vj+Ê∆(i…j/‰((Áj=.(.SF(.8(()‡(BË(.Jb)+kj7q…√J~‚(i@()2‰((71+ ƒ÷ﬂnlI8(((ÀCi^„;4Ëk{«/«((Ë~(AF‡)6:(((((ÿœ(À¿(Èf4((h(j(«(+ÿ((*H(()~pUƒ’ﬂ‰L:Áj(/‡()n(jgÿ((L(((20l1ΩÀ(:8X()‡(((;(0+≈((Èj((*z(v¿«*ZH(((((*qv(“È(1(0((J(5F[H(8ÀÈ(XX)1‡j(—((Èº2(4((()/⁄È((((((ø(((()((H(+l>(*È8DLTË4444wvN6.È8-È8;*2…+j»IÈ*m:r({{9)HsL]bph(@2NN-5…7(/1(.11”T|YH…HJ}X·A)P(ÈHHjznR24ËC3K482-x2mkl*lm—2B1u8ddX{@{9fhRZ√H@ËBDM?-ÈÈ-È¿¡Aÿ^()(k»)XAHQ@Mƒ‘«Mjbp’YB4È4*ËË3-w-(+(()»Hk kj√TTXÿb9(P4ËPG??@Ex)jj(ﬁ(";
			break;
		case "png":
		case "gif":
		case "jfif":
		case "jpg":
		case "jpeg":
		case "bmp":
		case "tif":
		case "tiff":
			$kod = "K|P kÃl8(È(7b((ÈE)-tYZsJ|1:QÕ;UDh]≈k…¡–”›c*q8|A|l5>WÕ}M~hFyjL3Qn€K3o⁄⁄·d≈K9œÕ;‘‰*;jqK¿–T÷K?x?{cZpaC‘»|=~*>jtÃd¿Ë÷i*p_8¿6BAAUÀ:5ÈÕ[¿sL¡Q‹„g7w?€–⁄‚QCPJ:‘‰’_¬jL3sÀ„M@v>;1…lC@ŒHYf4BMpyŒ„‡]y?Ët^;cÕx)3”O[º”t^zmÃ¬·~“^8rË;A—pA4ÕM⁄ÂftL|jJr0fs;>xË[s…⁄79UH9fÈ¡UΩkKSA4oK;s~€·‚ÊGB÷«|5ÀdYxmÀø·»(alH(r(((:((((((((*j(((((J((/0((r((/»D(›=(+ÈX*(((GDjÈ}x((nj(((((L(((((((((((((ÿ(„‡H*ør(p(((6H)5≈((*H(((((Z(.J‰) 2(*H(((](È4j/..(1G(((()Â((Gÿ(+Ê((i«8+8((Ÿj()8((((((X(((((((((+r((c«((r(((((7‡(yÁ(2∆ÿ*CÊ((:(2«() ((*H(()ÿ((Ãz((*((((((((«((;«(*(((G(/≈H(|Wj()H((((2;H/+Õ(1^((+√¿-Àho^‡t.Ë(G(((((((Ë(((z(((D(((Ë(zrj*q«((/(f((/(((1((((()T*jfc0(L‰(((6tX(Sÿj8Y8)‡((;=(/*]Ë1H«(+ÿ)n](-cÿ*/((G7y(+ÊU(i 8/‰((ÁjA.(-iF(-8(()‡(BË(.Jb)+kj7q…√*~‚(G@()2‰((/-) k»ÿnlI8(((‡CiZ“;4Ak{«/«((Ë](AF0{6:(((+8ÿœ/À¿:Ëf5‡(h((4«(+»((*H(()PNUmF/‰P:Áj(/‡()È((f9H(L(((A0l1hÀ(-8X()‡(((3((+l((Èj(((((gj(-8((((((/«()»((*H(((((.(+Ob(Àr(8a«)‡b(Z(ÿ*ÊJ(2G(()/⁄È((((((ø(((()((H(+l?ÿ+/)œ(ÿ{)0@8kX6.È=+.FË4D(oo-pk-O/È{«0IrH«»Hz’HXn]HlDpGRkj20jÈ2/08LÀZn{(*+t8«jL””Ÿ)0‡ﬁEKD3AD4K6JeÃ+mp.+lJJ«9*dBZ(Ÿ:NM8À0@v.|rk)FG54.5¿98SM[‹…»LK(8—rz11HTb·).((8@.JN>3dXiW--m0/ooNX;|»C|8HIA@(bœ8]+*6J[^o)æ88q-+’œj‡¬‚(kX80È(e";
			break;
		case "htm":		
		case "html":
		case "shtml":
		case "xml":
			$kod ="K|P kÃl8(È(7b((4W/5sŸ80Zx/;œÀ[^D:B|jKr1D÷^@xæÿr—…M;RŒÿ”È64upKtA~–a*o⁄Ÿ0xlcF÷À|ºvZGΩjJ—‡Èg=?uﬁYcB…cEVÃ}E”J;wjÀ3A~÷e4s}Xœld72QÀ:ÂÀF>wjÀd0‹sSÈp^;Q⁄ÊC9œM⁄T”≈a∆nÀuPv∆K(q98rdd;3PH9onNByvNCP‰¿^2p;⁄1:≈)0RK|^Ë—d∆y…@Q”“^Dyﬂ€¿’‚)8’Õ[w‰h^uoK1–4qQ@xa{1d2YCVN:ÃËdH|wM“0TQU0tﬂ}sÕﬁ;3N«Y‰ÀVB|jK“–‰G+-m|€Q¡⁄gF÷M:‘n*>øoÀ„P‰¿GÈn}ÿrÕ≈)8JIŸgË≈Y¬wOe·À·i4v|9@¡d)4J«z‘D2PºjK‡QT€^(pΩÿrZZC6QÕ[U√:=xn…¡0ËSW>uﬁ{@Ωd)8U»ŸË”J>}tM„¿ÈuS>sË€1…Ê1/Œ…{o4*:ΩqL—–”·g)sa:Q>ΩWD÷…[È4ΩTæwOCœ√iEFyæÿ0_xA9 Ã;wv|QyxÃ—¿È“G(r}Ÿ·>…17PL:=DJF|qÕ3bÈm9Go98ø…≈O=O ⁄5f≈c√lÀ¬øËwO8u>}1Ω‚iC‘À⁄o]ΩY√yŒSrLm[;s=Yø‚piG÷»9ƒf*D¬mJ—sË‘aGmzY0|dcDVÀ:ETBB~jKbøÈKSGn8z@≈Z=;‘«9À4dH}kK0øLC?4sﬁ8‡pd3/ŒJ{gÈ…Z¬nJt1f»[@vΩ{1h2;3ÕÕ[º‰.>æwŒu·n÷i2p}€c—ÊiF÷MZ‘n’e∆oJ––~“a7sº}–⁄‚YD÷Õ€w‹‚(i@()2‰((71+ ƒ÷ﬂnlI8(((ÀCi^„;4Ëk{«/«((Ë~(AF‡)6:(((((ÿœ(À¿(Èf4((h(j(«(+ÿ((*H(()~pUƒ’ﬂ‰L:Áj(/‡()n(jgÿ((L(((20l1ΩÀ(:8X()‡(((;(0+≈((Èj((*z(œA«1¡H(((((*k‡(“*(1(0()@(5/ H(0ÀÈ(XXÈ»‡j(¡((-=2(4((()/⁄È((((((ø(((()((H(+lGÿ)‘—*“2d¿‚H6l6zFÈg).uN√-kn|~8ur(ZJo‹…Ω@L‹Ë6{D40º4f·:vC?./AGO*R8UP«lS2j*p+¿PzQ„QB[1‰*d2_Tp>nJfmXΩ*zl¿;*ËX=>=}‹[Hn ][È@P9]wAQ6J3KË*PG2I12–ŒuŒkSQlo>@j}—:·€*⁄X≈(√6|dNxPd?Yu4w*>/qnæﬁ]:{|3k9}z{”s0Ar^oËtUUTF52N8=(w2È”K«“-vpt„:‚—œ*Q@»{hÈX8PRdzx60.53p}}ysl»@Ã…+‹Ã8⁄À€S]ÿƒ1h0‹ÃnLHGZ+UFT66”÷»+x{ LÃ2 :B¡?‹~vÃXrŸL6grÀ8Z4UUo∆*Juk«O8(+ø(((";
			break;
		case "mp2":
		case "mpeg":
		case "mp3":	
		case "avi":
		case "mpg":
		case "mid":
		case "wav":
		case "au":
		case "ra":
		case "ram":
		case "rm":
			$kod ="K|P kÃl8(È(7b((√W?=t›Xø¡≈^CU :=]ﬁeƒrLB0LaU1tﬂ€1tÕcD’I:-√xN}kKePfcOGyæ|·d≈+5TÕ;^‹’b¬sLtP]wg6q[}–ﬁ‚37”N}M~2E¡j CQT¬UCwﬁŸA*…eE÷LZE4⁄dƒtM2sÈkIGyæ€1l…7ËW»Ÿ›f>E¡j SPÀ[A-o›€c¡’K9PÕ;U”Õ[¿lLSø]eQ?u›€s…⁄M:–I⁄^‹hFywN¬PËg^>u^{c2…gFWO[º”|Q~yŒ„‡ngW*pΩ}≈paÈj/H(~€:3n6“El:]¬j.[:’(8iÃ-G‰~ÃÁuxql1–÷N¡JM+b(Q[|L/@)V√(BT«+c≈(ZB«.„v(ﬂXj6_ŸDjV=8rÀ[MF(d:—p—yÀ»VÈxÈœ((–Ltw«)cl(Y0(.“9ÿÿa„«.ørz›(2w|Icu‘PClL€z(qV>-Ë””j–4Ë5«+gl]c04qK2.»]∆4N«)øÀ(ATz))À(O?*>S]*‹dj6ﬂ@)≈–(DwX+T√(8A«ÈeMP⁄[fv8(s«k(B(7„LaÁYxÁﬂF∆Ê(U:MJ@(√((D((+b)r[⁄3ÿ/j*P1(-H((√«(=;«*N((KG(.|«)Gwj()j((((B.X/d⁄(1]«(+√¿-Thocøt.@(G(((((((L(((«(((D(((Ë)8rj+/«((/(4((/f((1((((((:*jHA0((‰(((6vX(Sﬂj8Y8)‡((K=(/d]Ë1H«(+ÿ)n](-cÿ*/((G7vj+Ê∆(i…j/‰((Áj3.(.aF(.8(()‡(BË(.Jb)+kj7q…√J~‚(i@()2‰((71+ ƒ÷ﬂnlI8(((ÀCi^„;4Ëk{«/«((Ë~(AF‡)6:(((((ÿœ(À¿(Èf4((h(j(œ(+ÿ((*H(()~pUg’ﬂ‰L:Áj(/‡()n(jgÿ((L(((20l1ΩÀ(:8X()‡(((;(0+≈((Èj((*z(^AÁ·‚KÊ((i«*j((“((1((((((54+H(ÊÀÈ6XX()‡j(>((È‘4(4((()/⁄È((((((ø(((()((H(+lAj+ﬂŸHœPzø0n””pNÈC--(2>>CqlpnqmÈpŸ«P–j{8ÿ‡sË-8rLP>DT:FE”1..(61È3+k~»*€ 9…8eOXÿ”8j8‡ËÕ:bD*(@003FFJln*m*npl8æk{»r8z8‡=»jTDÈ4ËËÈH6Ho3)5æ*¿+c-;]8…mΩ»}hÿ”ÿ‘ÀƒøÿTﬁB≈D08*J6EXp—Sp(’*-o)*Ë8(√tkDH–ŸÂT‡fD8Ë?4O*d?6.ru1«(wA)++ »dBZH«Ÿ](88≈≈≈√)8gJ9((ﬁ(";	
			break;
		case "php":
		case "asp":
		case "php3":
		case "php4":
		case "js":
			$kod = "K|P kƒl8(È(/b((T(((((j(8(IÁÁÁÁÃ⁄·dEVq[{…Ÿ‡‰gÕ€cº—›c√’a-l»W?uÃ¡‘_æSº—E6qAvÕZQËt?3o»8rK›‰ÊeExÕK9r≈W?j(((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((ÈK«(0)»ÿÿ(Hjjnnj84È0.*67--./((*+)l)kmmÿ8kY99P√(rXP@È@(>M1PG4+È-;0*c*IlKkkl)|LŸ‡X¿s«8PPo”x.XË.pZ8mZoq|}w+zlJ:{⁄]]€Ë~[”Á5Ëw4ËEDvE0YO^=S+YS-⁄XWWOOkU(jUjs»„@(zzu Œ…|r*n*((e";
			break;
		case "txt":
		case "doc":
		case "pdf":
		case "odt":
		case "ps":
			$kod = "K|P kÃl8(È(7b((√W?>u^}c≈’K9PÕ[Ev‚f≈tM2r‹eE:s]|1*lW?“Œ[ºÀ—^¡yŒ“·ËsM=t›Y‡ÊhA4ÕM⁄›f⁄b√rÀdA~–^Fya|sN|G7OÀ⁄^DlIzvN31”›eCwΩ€·ÊÊG18()¡*(@H(3D((Zj2ŒBÿ((ﬂj-I8(√(8BjF j(:[j((()zP((-H((√((9«È*Mc‘Ke…Jrr(H(.0X?c≈(rCH/ j)ﬂdzÃaA–4ÃV(6LKA”÷(ATX))—(K¬WÈu?≈paÈj/H(~€:3n6“El:]¬j.[:ÿ(8iÃ-G‰~ÃÁuxql1–÷N¡JM+b(Q[|È/@)V√(BT«+c≈(ZB«.„v(ﬂXj6_ŸDjV=8rÀ[MF(d:—p—yÀ»VÈxÈœ((–L2w«)cl(Y0(.“9ÿÿa„«.ørz›(2w|Icu‘PClL€z(qV@-Ë’Èj–3~5«+gl]c04qK2.»]∆4N«)øÀ(ATz))À(O?*>Sz*‹Zj6ﬂj)≈‹(Do«+Ux(8BHÈeAP⁄(fv@zs«k(B(7„LaÁYxÁﬂF∆Ê(U:-J@(√((D((+b)r[⁄3ÿ/j*P1(-H((√«(=;«*N((KG(4u((y«j()b((((B.X/d⁄(1]«(+√¿-Xhof0t.?(G(((((((X(((√(((D(((Ë)jrj*ﬂ«((/(4((/f((1((((((:*jHA0((‰(((6vX(Sﬂj8Y8)‡((K=(/d]Ë1H«(+ÿ)n](-cÿ*/((G7vj+Ê∆(i…j/‰((Áj4.(/+F(-ÿ(()‡(BË(.Jb)+kj7q…√J~‚(i@()2‰((71+ ƒ÷ﬂnlI8(((ÀCi^„;4Ëk{«/«((Ë~(AF‡)6:(((((ÿœ(À¿(Èf4((h(j(«(+ÿ((*H(()~pUƒ’ﬂ‰L:Áj(/‡()n(jgÿ((L(((20l1ΩÀ(:8X()‡(((;(0+≈((Èj((*z(À¿«È‚H(((((*q‡(“)j1(0((7(5G H(.ÀÈ(XX(g‡j(ø((.-2(3j(()/⁄È((((((ø(((()((H(+l;z)›ŸH@8@X(HX:]È>4È3)//6k.*)kmllmYm}kÿœXœH0v:Pf]0(4È:E+l;(*r().qsOaJz»k;[X»czrj(0f~‘(O*ËD7AÈ3C0LrM-m(NNPœ@Buz‡d(jj—PSø6]BË6|vx~}u1){*|Ωn‹À:ÿ[º}>z=›]~ƒ^]œEgø÷∆g841B7_46m«0l*ÿXXj(ÿH@0(Ej((((((";
			break;
		case "zip":
		case "rar";
		case "tar";
		case "gz":
			$kod = "K|P kÃl8(È(7b((È))>r8€1l…e?ÃL:5ÈxAjwM0‡vM;@s9[AxÕS6»O€n‰≈JnxŒe¿Da)Gv€;ABBS7»O€‘4≈InyŒ¡QÀ¿;5p}[A>B^B‘O€ºÈ…KoyŒ“·La+>rX€·pZK9PÀŸ~È’RrvLø¿Tg-Gx]:·6:iBÕÃ{‰DÊi}q ··√‘aGyﬂ⁄r⁄.(-H((√((9«È*L)‘K(*JsÀ(H(.0X/c≈(rCH/ j)ﬂdzÃaA–4ÃV(6LKA”÷(ATX))—(K¬WÈu?≈paÈj/H(~€:3n6“El:]¬j.[:⁄(8iÃ-G‰~ÃÁuxql1–÷N¡JM+b(Q[{À/@)V√(BT«+c≈(ZB«.„v(ﬂXj6_ŸDjV=8rÀ[MF(d:—p—yÀ»VÈxÈœ((–Ktw«)cl(Y0(.“9ÿÿa„«.ørz›(2w|Icu‘PClL€z(qV>-Ë“ÿj–3~5«+gl]c04qK2.»]∆4N«)øÀ(ATz))À(O?*>Sœ*‹]j6ﬂ()≈V(Dvÿ+U|(8AjÈb)P⁄bfv@zs«k(B(7„NOÁYŸGﬂFyÊ(e{ÃH((√((D((+b)r[ø3ÿ)‰*PDj-H((√«(=;«*N((KG()*«)kh(()b((((B.X/d⁄(1]«(+√¿-Thoa«t.?(G(((((((D(((√(((D(((Ë(ÿrj*ﬂ«((/(4((/f((1((((((:*jHA0((‰(((6vX(Sﬂj8Y8)‡((K=(/d]Ë1H«(+ÿ)n](-cÿ*/((G7vj+Ê∆(i…j/‰((Áj3.(-·F(-ÿ(()‡(BË(.Jb)+kj7q…√J~‚(i@()2‰((71+ ƒ÷ﬂnlI8(((ÀCi^„;4Ëk{«/«((Ë~(AF‡)6:(((((ÿœ(À¿(Èf4((h(j(ø(+ÿ((*H(()~pUÂ’ﬂ‰L:Áj(/‡()n(jgÿ((L(((20l1ΩÀ(:8X()‡(((;(0+≈((Èj((*z(0AÁ‰2KÊ((((*qH(“ﬂ(1)8((((55€H(√ÀÈ^ÿX*–‡jﬁΩ((mŸ0)3jËz)/⁄È((((((ø(((()((H(+l?j*√8DLTË4444IHÈ8Ë*Ë/È=r=n*È(oÈjIŸBbrH(X)HXXÈvÈÈ@F4P>Ë:ÈC(*)-kÈ/Õ2«lJII…{«mtT«œ0XÈ‡~V>]d-4/*È=>*È++n*((j-2:S“(»HHzœf√0ÿ28@:≈≈+º?y¡.È|(1.Q2)=J)*l)XÕI√=øP=‘œjz2TNF2È5Z;j( a+U+-Œn…ËYIkkkH«Y)(ËDL]PÈ(P?@A¡ﬁŒ+w2+2lPPø‚ejXz(+ø(((";
			break;
		default:
			$kod = "K|P kÃl8(È(7b((√W?>u^}c≈’K9PÕ[Ev‚f≈tM2r‹eE:s]|·h≈eEVN}U~Êh∆rL21ËsM=t›YrÕZW?SŒ;oÀpK{wNuQ‹„gËt^9‡‚hO;QÕ;-nﬁdƒwÕ“c‰Ái8r]:*H(G18()¡((@H(3D((Zj2ŒFn((n(-U@(√ﬂ8Bj6 j(:[j((()zP[j-HH(ƒΩ(9’‹*L)‘K(*Jr((H(.0iÁc∆ÁrCiﬂ jEﬂdzÃaA–4ÃV(6LKA”÷(ATX))—(K¬WÈu?≈paÈj/H(~€:3n6“El:]¬j.[:’(8iÃ-G‰~ÃÁuxql1–÷N¡JM+b(Q[|L/@)V√(BT«+c≈(ZB«.„v(ﬂXj6_ŸDjV=8rÀ[MF(d:—p—yÀ»VÈxÈœ((–Ltw«)cl(Y0(.“9ÿÿa„«.ørz›(2w|Icu‘PClL€z(qVh-Ë—fj–3~5«+gl]c04qK2.»]∆4N«)øÀ(ATz))À(O?*>Sf*‹^j6ﬁ0)≈’(Dvj+T√(8A«ÈeMP⁄[fv8(s«k(B(7„LaÁYxÁﬂF∆Ê(U:MJ@(√((D((+b)r[⁄3ÿ/j*P1(-H((√«(=;«*N((KG(1Œ«)⁄Oj()b((((B.X/d⁄(1]«(+√¿-‰ho]‡t.?(G(((((((8(((√(((D(((Ë(jrj*ﬂ«((/(4((/f((1((((((:*jHA0((‰(((6vX(Sﬂj8Y8)‡((K=(/d]Ë1H«(+ÿ)n](-cÿ*/((G7vj+Ê∆(i…j/‰((ÁjG.(-GF(-ÿ(()‡(BË(.Jb)+kj7q…√J~‚(i@()2‰((71+ ƒ÷ﬂnlI8(((ÀCi^„;4Ëk{«/«((Ë~(AF‡)6:(((((ÿœ(À¿(Èf4((h(j(œ(+ÿ((*H(()~pUg’ﬂ‰L:Áj(/‡()n(jgÿ((L(((20l1ΩÀ(:8X()‡(((;(0+≈((Èj((*z(xAÁÁ:KÊ((i«*j((“((1((((((54+H(ÊÀÈ6XX()‡j(h((*g4(3j(()/⁄È((((((ø(((()((H(+l:X)Ã«ÿœ@P0b@VVdÈË2È3)//5wol(k+*lm9YX{ÿrXœH00”8Lf8È08Ë:E+j*rÈs)//+*N IYkk(z:œ√XXÈ@8T~M*-YK:..+61¬√ÃNlMQl(Q0Q¬BAd8ttRdelhN2tΩ≈¡*ƒ≈lΩzænø~¿bcÿæºH{«ﬁºQ”n0ºbMr]b6(H_Aach)K6(jH)”((";
	}
	
	show_image("image/gif",$kod);
	
}


//////////////////////////////////////////////////////////
//	Helper Functions
//////////////////////////////////////////////////////////

	//////////////////////////////////////////////////////
	// Returns the prefixed byte size
	// Usage: size_to_prefixed_size(filesize($file));
	//////////////////////////////////////////////////////
	function size_to_prefixed_size($size) {
		$i=0;
		$unit = array(" B&nbsp;", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		while (($size / 1024) > 1) {
			$size = $size / 1024;
			$i++;
		}
		return substr($size, 0, strpos($size,'.')+2).$unit[$i];
	}

	//////////////////////////////////////////////////////
	// Returns true if a key in the array matches agains the string
	// Usage: search_string_using_array($string, $string_array);
	//////////////////////////////////////////////////////
	function search_string_using_array($string, $string_array) {
		for ($i = 0; $i < count($string_array); $i++) {
			if (strpos($string, $string_array[$i]) !== false)
				return true;
		}
		return false;
	}





function show_image($type,$code)
{
	header("Content-Type: ".$type);
	$kodarray = str_split_php4("()*+È-./0123456789:;Ë=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_abcdefghijklmnopqrstuvwxyz{|}~ºΩæø¿¡¬√ƒ≈∆«»… ÀÃÕŒœ–—“”‘’÷ÿŸ⁄€‹›ﬁﬂ‡·‚„‰ÂÊÁËÈ");

	$bin = "";
	$var_array = str_split_php4($code, 1);
	foreach($var_array as $var_temp)
	{
		$bin_temp = decbin(array_search($var_temp,$kodarray));
		$bin .= str_repeat("0", 7-strlen($bin_temp)).$bin_temp;	
	}
	$bin_array = str_split_php4($bin, 8);
	$result ="";
	foreach($bin_array as $bin_temp)
	{
		$result.= chr(bindec($bin_temp));
	}
	echo rtrim($result,"\0");
}
	
	

function str_split_php4($text, $split = 1)
{
   if (!is_string($text)) return false;
   if (!is_numeric($split) && $split < 1) return false;
   
   $len = strlen($text);
   
   $array = array();
   
   $i = 0;
  
   while ($i < $len)
   {
     $key = NULL;
     
     for ($j = 0; $j < $split; $j += 1)
     {
       $key .= $text{$i};
       $i++;   
     }
     $array[] = $key;
   }
   return $array;
}


?>