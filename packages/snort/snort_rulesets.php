<?php
/* $Id$ */
/*
	snort_rulesets.php
	Copyright (C) 2006 Scott Ullrich
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/

require("guiconfig.inc");
require("/usr/local/pkg/snort.inc");

if($_POST) {
	$enabled_items = "";
	$isfirst = true;
	foreach($_POST['toenable'] as $toenable) {
		if(!$isfirst)
			$enabled_items .= "||";
		$enabled_items .= "{$toenable}";
		$isfirst = false;
	}
	$config['installedpackages']['snort']['rulesets'] = $enabled_items;
	write_config();
	create_snort_conf();
	$savemsg = "The snort ruleset selections have been saved.";
}

$enabled_rulesets = $config['installedpackages']['snort']['rulesets'];
if($enabled_rulesets)
	$enabled_rulesets_array = split("\|\|", $enabled_rulesets);

$pgtitle = "Snort: Snort Rulesets";
include("head.inc");

?>

<body link="#000000" vlink="#000000" alink="#000000">
<?php include("fbegin.inc"); ?>

<p class="pgtitle"><?=$pgtitle?></font></p>

<form action="snort_rulesets.php" method="post" name="iform" id="iform">
<script src="/row_toggle.js" type="text/javascript"></script>
<script src="/javascript/sorttable.js" type="text/javascript"></script>
<?php if ($savemsg) print_info_box($savemsg); ?>
<table width="99%" border="0" cellpadding="0" cellspacing="0">
   <tr>
   		<td>
<?php
	$tab_array = array();
	$tab_array[] = array(gettext("Snort Settings"), false, "/pkg_edit.php?xml=snort.xml&id=0");
	$tab_array[] = array(gettext("Snort Rules Update"), false, "/snort_download_rules.php");
	$tab_array[] = array(gettext("Snort Rulesets"), true, "/snort_rulesets.php");
	$tab_array[] = array(gettext("Snort Blocked"), false, "/snort_blocked.php");
	display_top_tabs($tab_array);
?>
  		</td>
  </tr>
  <tr>
    <td>
		<div id="mainarea">
			<table id="maintable" class="tabcont" width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<table id="sortabletable1" class="sortable" width="100%" border="0" cellpadding="0" cellspacing="0">
						    <tr id="frheader">
								<td width="5%" class="listhdrr">Enabled</td>
								<td class="listhdrr">Ruleset</td>
								<!-- <td class="listhdrr">Description</td> -->
							</tr>
<?php
	$dir = "/usr/local/etc/snort/rules/";
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
   		$files[] = $filename;
	}
	sort($files);
	foreach($files as $file) {
		if(!stristr($file, ".rules"))
			continue;
		echo "<tr>";
		echo "<td align=\"center\" valign=\"top\">";
		if(is_array($enabled_rulesets_array))
			if(in_array($file, $enabled_rulesets_array)) {
				$CHECKED = " checked=\"checked\"";
			} else {
				$CHECKED = "";
			}
		else
			$CHECKED = "";
		echo "	<input type='checkbox' name='toenable[]' value='$file' {$CHECKED} />";
		echo "</td>";
		echo "<td>";
		echo "<a target='_new' href='edit.php?submit=Load&savetopath=" . urlencode($file) . "'>{$file}</a>";
		echo "</td>";
		//echo "<td>";
		//echo "description";
		//echo "</td>";
	}

?>
						</table>
		    		</td>
		  		</tr>
		  		<tr><td>&nbsp;</td></tr>
		  		<tr><td>Check the rulesets that you would like Snort to load at startup.</td></tr>
		  		<tr><td>&nbsp;</td></tr>
		  		<tr><td><input value="Save" type="submit" name="save" id="save" /></td></tr>
			</table>
		</div>
	</td>
  </tr>
</table>

</form>

<?php include("fend.inc"); ?>

</body>
</html>

<?php

	function get_snort_rule_file_description($filename) {
		$filetext = file_get_contents($filename);

	}

?>