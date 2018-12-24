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
			$kod = "K|P�k�l8(�(7b()D[)Ct|9@tNU8��;-n�^wtʿQnu3Gy]Z�6:eE������Wtt��P�iGBw_}��h^�K��4L�Mpy��cnq3�p�[�xZU7��ۼ�xBjxMs@�^CAv�Z��2iF�O����Yvr�RA��aGx]:�2:[;KN:LT|DkuL0b:[j((()zP((-H((�((9��*Mc�Ke�Jrr(H(.0X?c�(rCH/�j)�dz�aA�4�V(6LKA��(ATX))�(K�W�u?�pa�j/H(~�:3n6�El:]�j.[:�(8i�-G�~��uxql1��N�JM+b(Q[|�/@)V�(BT�+c�(ZB�.�v(�Xj6_�DjV=8r�[MF(d:�p�y��V�x��((�L2w�)cl(Y0(.�9��a��.�rz�(2w|Icu�PClL�z(qVR-��~j�4�5�+gl]c04qK2.�]�4N�)��(ATz))�(O?*>S]*�Xj6�()��(Do�+Ux(8BH�eAP�(fv@zs�k(B(7�La�Yx��F��(U:-J@(�((D((+b)r[�3�/j*P1(-H((��(=;�*N((KG(1wH(Ntj()j((((B.X/d�(1]�(+��-~hocPt.@(G(((((((L(((�(((D(((�)8rj+/�((/(4((/f((1((((((:*jHA0((�(((6vX(S�j8Y8)�((K=(/d]�1H�(+�)n](-c�*/((G7vj+��(i�j/�((�j=.(.SF(.8(()�(B�(.Jb)+kj7q��J~�(i@()2�((71+����nlI8(((�Ci^�;4�k{�/�((�~(AF�)6:(((((��(��(�f4((h(j(�(+�((*H(()~pU����L:�j(/�()n(jg�((L(((20l1��(:8X()�(((;(0+�((�j((*z(v��*ZH(((((*qv(��(1(0((J(5F[H(8��(XX)1�j(�((�2(4((()/��((((((�(((()((H(+l>(*�8DLT�4444wvN6.�8-�8;*2�+j�I�*m:r({{9)HsL]bph(@2NN-5�7(/1(.11�T|YH�HJ}X�A)P(�HHjznR24�C3K482-x2mkl*lm�2B1u8ddX{@{9fhRZ�H@�BDM?-��-���A�^()(k�)XAHQ@M���Mjbp�YB4�4*��3-w-(+(()�Hk�kj�TTX�b9(P4�PG??@Ex)jj(�(";
			break;
		case "png":
		case "gif":
		case "jfif":
		case "jpg":
		case "jpeg":
		case "bmp":
		case "tif":
		case "tiff":
			$kod = "K|P�k�l8(�(7b((�E)-tYZsJ|1:Q�;UDh]�k�����c*q8|A|l5>W�}M~hFyjL3Qn�K3o���d�K9��;��*;jqK��T�K?x?{cZpaC��|=~*>jt�d���i*p_8�6BAAU�:5��[�sL�Q��g7w?����QCPJ:���_�jL3s��M@v>;1�lC@�HYf4BMpy���]y?�t^;c�x)3�O[��t^zm���~�^8r�;A�pA4�M��ftL|jJr0fs;>x�[s��79UH9f��U�kKSA4oK;s~����GB��|5�dYxm˿��(alH(r(((:((((((((*j(((((J((/0((r((/�D(�=(+�X*(((GDj�}x((nj(((((L(((((((((((((�(��H*�r(p(((6H)5�((*H(((((Z(.J�)�2(*H(((](�4j/..(1G(((()�((G�(+�((i�8+8((�j()8((((((X(((((((((+r((c�((r(((((7�(y�(2��*C�((:(2�()�((*H(()�((�z((*((((((((�((;�(*(((G(/�H(|Wj()H((((2;H/+�(1^((+��-�ho^�t.�(G(((((((�(((z(((D(((�(zrj*q�((/(f((/(((1((((()T*jfc0(L�(((6tX(S�j8Y8)�((;=(/*]�1H�(+�)n](-c�*/((G7y(+�U(i�8/�((�jA.(-iF(-8(()�(B�(.Jb)+kj7q��*~�(G@()2�((/-)�k��nlI8(((�CiZ�;4Ak{�/�((�](AF0{6:(((+8��/��:�f5�(h((4�(+�((*H(()PNUmF/�P:�j(/�()�((f9H(L(((A0l1h�(-8X()�(((3((+l((�j(((((gj(-8((((((/�()�((*H(((((.(+Ob(�r(8a�)�b(Z(�*�J(2G(()/��((((((�(((()((H(+l?�+/)�(�{)0@8kX6.�=+.F�4D(oo-pk-O/�{�0IrH��Hz�HXn]HlDpGRkj20j�2/08L�Zn{(*+t8�jL���)0��EKD3AD4K6Je�+mp.+lJJ�9*dBZ(�:NM8�0@v.|rk)FG54.5�98SM[���LK(8�rz11HTb�).((8@.JN>3dXiW--m0/ooNX;|�C|8HIA@(b�8]+*6J[^o)�88q-+��j���(kX80�(e";
			break;
		case "htm":		
		case "html":
		case "shtml":
		case "xml":
			$kod ="K|P�k�l8(�(7b((4W/5s�80Zx/;��[^D:B|jKr1D�^@x��r��M;R����64upKtA~�a*o��0xlcF��|�vZG�jJ���g=?u�YcB�cEV�}E�J;wj�3A~�e4s}X�ld72Q�:��F>wj�d0�sS�p^;Q��C9�M�T��a�n�uPv�K(q98rdd;3PH9onNByvNCP��^2p;�1:�)0RK|^��d�y�@Q��^Dy�����)8��[w�h^uoK1�4qQ@xa{1d2YCVN:��dH|wM�0TQU0t�}s��;3N�Y��VB|jK���G+-m|�Q��gF�M:�n*>�o��P��G�n}�r��)8JI�g��Y�wOe���i4v|9@�d)4J�z�D2P�jK�QT�^(p��rZZC6Q�[U�:=xn��0�SW>u�{@�d)8U����J>}tM���uS>s��1��1/��{o4*:�qL����g)sa:Q>�WD��[�4�T�wOC��iEFy��0_xA9��;wv|Qyx�����G(r}��>�17PL:=DJF|q�3b�m9Go98���O=O��5f�c�l�¿�wO8u>}1��iC���o]�Y�y�SrLm[;s=Y��piG��9�f*D�mJ�s��aGmzY0|dcDV�:ETBB~jKb��KSGn8z@�Z=;��9�4dH}kK0�LC?4s�8�pd3/�J{g��Z�nJt1f�[@v�{1h2;3��[��.>�w�u�n�i2p}�c��iF�MZ�n�e�oJ��~�a7s�}���YD���w��(i@()2�((71+����nlI8(((�Ci^�;4�k{�/�((�~(AF�)6:(((((��(��(�f4((h(j(�(+�((*H(()~pU����L:�j(/�()n(jg�((L(((20l1��(:8X()�(((;(0+�((�j((*z(�A�1�H(((((*k�(�*(1(0()@(5/�H(0��(XX���j(�((-=2(4((()/��((((((�(((()((H(+lG�)��*�2d��H6l6zF�g).uN�-kn|~8ur(ZJo�ɽ@L��6{D40�4f�:vC?./AGO*R8UP�lS2j*p+�PzQ�QB[1�*d2_Tp>nJfmX�*zl�;*�X=>=}�[Hn�][�@P9]wAQ6J3K�*PG2I12��u�kSQlo>@j}�:��*�X�(�6|dNxPd?Yu4w*>/qn��]:{|3k9}z{�s0Ar^o�tUUTF52N8=(w2��K��-vpt�:���*Q@�{h�X8PRdzx60.53p}}ysl�@��+��8���S]��1h0��nLHGZ+UFT66���+x{�L�2�:B�?�~v�Xr�L6gr�8Z4UUo�*Juk�O8(+�(((";
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
			$kod ="K|P�k�l8(�(7b((�W?=t�X���^CU�:=]�e�rLB0LaU1t��1t�cD�I:-�xN}kKePfcOGy�|�d�+5T�;^��b�sLtP]wg6q[}���37�N}M~2E�j�CQT�UCw��A*�eE�LZE4�d�tM2s�kIGy��1l�7�W���f>E�j�SP�[A-o��c��K9P�;U��[�lLS�]eQ?u��s��M:�I�^�hFywN�P�g^>u^{c2�gFWO[��|Q~y���ngW*p�}�pa�j/H(~�:3n6�El:]�j.[:�(8i�-G�~��uxql1��N�JM+b(Q[|L/@)V�(BT�+c�(ZB�.�v(�Xj6_�DjV=8r�[MF(d:�p�y��V�x��((�Ltw�)cl(Y0(.�9��a��.�rz�(2w|Icu�PClL�z(qV>-���j�4�5�+gl]c04qK2.�]�4N�)��(ATz))�(O?*>S]*�dj6�@)��(DwX+T�(8A��eMP�[fv8(s�k(B(7�La�Yx��F��(U:MJ@(�((D((+b)r[�3�/j*P1(-H((��(=;�*N((KG(.|�)Gwj()j((((B.X/d�(1]�(+��-Thoc�t.@(G(((((((L(((�(((D(((�)8rj+/�((/(4((/f((1((((((:*jHA0((�(((6vX(S�j8Y8)�((K=(/d]�1H�(+�)n](-c�*/((G7vj+��(i�j/�((�j3.(.aF(.8(()�(B�(.Jb)+kj7q��J~�(i@()2�((71+����nlI8(((�Ci^�;4�k{�/�((�~(AF�)6:(((((��(��(�f4((h(j(�(+�((*H(()~pUg���L:�j(/�()n(jg�((L(((20l1��(:8X()�(((;(0+�((�j((*z(^A���K�((i�*j((�((1((((((54+H(���6XX()�j(>((��4(4((()/��((((((�(((()((H(+lAj+��H�Pz�0n��pN�C--(2>>Cqlpnqm�p��P�j{8��s�-8rLP>DT:FE�1..(61�3+k~�*��9�8eOX��8j8���:bD*(@003FFJln*m*npl8�k{�r8z8�=�jTD�4���H6Ho3)5�*�+c-;]8�m��}h�����Ŀ�T�B�D08*J6EXp�Sp(�*-o)*�8(�tkDH���T�fD8�?4O*d?6.ru1�(wA)++��dBZH��](88����)8gJ9((�(";	
			break;
		case "php":
		case "asp":
		case "php3":
		case "php4":
		case "js":
			$kod = "K|P�k�l8(�(/b((T(((((j(8(I�������dEVq[{����g��c���c��a-l�W?u���_�S��E6qAv�ZQ�t?3o�8rK���eEx�K9r�W?j(((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((((�K�(0)���(Hjjnnj84�0.*67--./((*+)l)kmm�8kY99P�(rXP@�@(>M1PG4+�-;0*c*IlKkkl)|L��X�s�8PPo�x.X�.pZ8mZoq|}w+zlJ:{�]]��~[��5�w4�EDvE0YO^=S+YS-�XWWOOkU(jUjs��@(zzu���|r*n*((e";
			break;
		case "txt":
		case "doc":
		case "pdf":
		case "odt":
		case "ps":
			$kod = "K|P�k�l8(�(7b((�W?>u^}c��K9P�[Ev�f�tM2r�eE:s]|1*lW?��[���^�y����sM=t�Y��hA4�M��f�b�r�dA~�^Fya|sN|G7O��^DlIzvN31��eCw�����G18()�*(@H(3D((Zj2�B�((�j-I8(�(8BjF�j(:[j((()zP((-H((�((9��*Mc�Ke�Jrr(H(.0X?c�(rCH/�j)�dz�aA�4�V(6LKA��(ATX))�(K�W�u?�pa�j/H(~�:3n6�El:]�j.[:�(8i�-G�~��uxql1��N�JM+b(Q[|�/@)V�(BT�+c�(ZB�.�v(�Xj6_�DjV=8r�[MF(d:�p�y��V�x��((�L2w�)cl(Y0(.�9��a��.�rz�(2w|Icu�PClL�z(qV@-���j�3~5�+gl]c04qK2.�]�4N�)��(ATz))�(O?*>Sz*�Zj6�j)��(Do�+Ux(8BH�eAP�(fv@zs�k(B(7�La�Yx��F��(U:-J@(�((D((+b)r[�3�/j*P1(-H((��(=;�*N((KG(4u((y�j()b((((B.X/d�(1]�(+��-Xhof0t.?(G(((((((X(((�(((D(((�)jrj*��((/(4((/f((1((((((:*jHA0((�(((6vX(S�j8Y8)�((K=(/d]�1H�(+�)n](-c�*/((G7vj+��(i�j/�((�j4.(/+F(-�(()�(B�(.Jb)+kj7q��J~�(i@()2�((71+����nlI8(((�Ci^�;4�k{�/�((�~(AF�)6:(((((��(��(�f4((h(j(�(+�((*H(()~pU����L:�j(/�()n(jg�((L(((20l1��(:8X()�(((;(0+�((�j((*z(�����H(((((*q�(�)j1(0((7(5G�H(.��(XX(g�j(�((.-2(3j(()/��((((((�(((()((H(+l;z)��H@8@X(HX:]�>4�3)//6k.*)kmllmYm}k��X�H0v:Pf]0(4�:E+l;(*r().qsOaJz�k;[X�czrj(0f~�(O*�D7A�3C0LrM-m(NNP�@Buz�d(jj�PS�6]B�6|vx~}u1){*|�n��:�[�}>z=�]~�^]�Eg���g841B7_46m�0l*�XXj(�H@0(Ej((((((";
			break;
		case "zip":
		case "rar";
		case "tar";
		case "gz":
			$kod = "K|P�k�l8(�(7b((�))>r8�1l�e?�L:5�xAjwM0�vM;@s9[Ax�S6�O�n��Jnx�e�Da)Gv�;ABBS7�O��4�Iny��Q��;5p}[A>B^B�Oۼ��Koy���La+>rX��pZK9P��~��RrvL��Tg-Gx]:�6:iB��{�D�i}q�����aGy��r�.(-H((�((9��*L)�K(*Js�(H(.0X/c�(rCH/�j)�dz�aA�4�V(6LKA��(ATX))�(K�W�u?�pa�j/H(~�:3n6�El:]�j.[:�(8i�-G�~��uxql1��N�JM+b(Q[{�/@)V�(BT�+c�(ZB�.�v(�Xj6_�DjV=8r�[MF(d:�p�y��V�x��((�Ktw�)cl(Y0(.�9��a��.�rz�(2w|Icu�PClL�z(qV>-���j�3~5�+gl]c04qK2.�]�4N�)��(ATz))�(O?*>S�*�]j6�()�V(Dv�+U|(8Aj�b)P�bfv@zs�k(B(7�NO�Y�G�Fy�(e{�H((�((D((+b)r[�3�)�*PDj-H((��(=;�*N((KG()*�)kh(()b((((B.X/d�(1]�(+��-Thoa�t.?(G(((((((D(((�(((D(((�(�rj*��((/(4((/f((1((((((:*jHA0((�(((6vX(S�j8Y8)�((K=(/d]�1H�(+�)n](-c�*/((G7vj+��(i�j/�((�j3.(-�F(-�(()�(B�(.Jb)+kj7q��J~�(i@()2�((71+����nlI8(((�Ci^�;4�k{�/�((�~(AF�)6:(((((��(��(�f4((h(j(�(+�((*H(()~pU����L:�j(/�()n(jg�((L(((20l1��(:8X()�(((;(0+�((�j((*z(0A��2K�((((*qH(��(1)8((((55�H(���^�X*��j޽((m�0)3j�z)/��((((((�(((()((H(+l?j*�8DLT�4444IH�8�*�/�=r=n*�(o�jI�BbrH(X)HXX�v��@F4P>�:�C(*)-k�/�2�lJII�{�mtT��0X��~V>]d-4/*�=>*�++n*((j-2:S�(�HHz�f�0�28@:��+�?y�.�|(1.Q2)=J)*l)X�I�=�P=��jz2TNF2�5Z;j(�a+U+-�n��YIkkkH�Y)(�DL]P�(P?@A���+w2+2lPP��ejXz(+�(((";
			break;
		default:
			$kod = "K|P�k�l8(�(7b((�W?>u^}c��K9P�[Ev�f�tM2r�eE:s]|�h�eEVN}U~�h�rL21�sM=t�Yr�ZW?S�;o�pK{wNuQ��g�t^9��hO;Q�;-n�d�w��c��i8r]:*H(G18()�((@H(3D((Zj2�Fn((n(-U@(��8Bj6�j(:[j((()zP[j-HH(Ľ(9��*L)�K(*Jr((H(.0i�c��rCi��jE�dz�aA�4�V(6LKA��(ATX))�(K�W�u?�pa�j/H(~�:3n6�El:]�j.[:�(8i�-G�~��uxql1��N�JM+b(Q[|L/@)V�(BT�+c�(ZB�.�v(�Xj6_�DjV=8r�[MF(d:�p�y��V�x��((�Ltw�)cl(Y0(.�9��a��.�rz�(2w|Icu�PClL�z(qVh-��fj�3~5�+gl]c04qK2.�]�4N�)��(ATz))�(O?*>Sf*�^j6�0)��(Dvj+T�(8A��eMP�[fv8(s�k(B(7�La�Yx��F��(U:MJ@(�((D((+b)r[�3�/j*P1(-H((��(=;�*N((KG(1��)�Oj()b((((B.X/d�(1]�(+��-�ho]�t.?(G(((((((8(((�(((D(((�(jrj*��((/(4((/f((1((((((:*jHA0((�(((6vX(S�j8Y8)�((K=(/d]�1H�(+�)n](-c�*/((G7vj+��(i�j/�((�jG.(-GF(-�(()�(B�(.Jb)+kj7q��J~�(i@()2�((71+����nlI8(((�Ci^�;4�k{�/�((�~(AF�)6:(((((��(��(�f4((h(j(�(+�((*H(()~pUg���L:�j(/�()n(jg�((L(((20l1��(:8X()�(((;(0+�((�j((*z(xA��:K�((i�*j((�((1((((((54+H(���6XX()�j(h((*g4(3j(()/��((((((�(((()((H(+l:X)����@P0b@VVd��2�3)//5wol(k+*lm9YX{�rX�H00�8Lf8�08�:E+j*r�s)//+*N�IYkk(z:��XX�@8T~M*-YK:..+61���NlMQl(Q0Q�BAd8ttRdelhN2t���*��l�z�n�~�bcؾ�H{�޼Q�n0�bMr]b6(H_Aach)K6(jH)�((";
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
	$kodarray = str_split_php4("()*+�-./0123456789:;�=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_abcdefghijklmnopqrstuvwxyz{|}~���������������������������������������������");

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