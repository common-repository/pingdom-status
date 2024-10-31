<div class="wrap">
<h2>Edit Templates</h2>

<div style="width: 100%">

<script type="text/javascript">
	jQuery(function() {
		<?php if (!empty($_POST["index"])) { ?>
			jQuery("#pingdomstatus-tabs").tabs().tabs('select', <?php echo $_POST["index"] - 1?>);
		<?php } else { ?>
			jQuery("#pingdomstatus-tabs").tabs();
		<?php } ?>
	});
</script>

<?php
	/*
		List of files in templates dir that will be excluded from admin view
	*/
	$exclude = array("PingdomStatus_status.php",
					 "PingdomStatus_charts.php",
					 "PingdomStatus_month_selector.php",
					 "PingdomStatus_widget.php");

	$path = dirname(__FILE__) . "/../templates";

	if (isset($_POST["submit"]) && isset($_POST["filename"])) {
		$file = $_POST["filename"];

		if (!is_writable("$path/$file")) {
			echo "Error: You need to make $path/$file writable first.";
		} else if (strpos($_POST["submit"], "Restore") !== FALSE) {
			if (copy("$path/defaults/$file", "$path/$file")) {
				echo "Restored $file to the default version.";
			} else {
				echo "Error restoring $file to the default version.";
			}
		} else if (strpos($_POST["submit"], "Save") !== FALSE) {
			if (!$handle = fopen("$path/$file", 'w')) {
				echo "Error opening $path/$file for writing.";
			} else {
                                // WordPress adds slashes to GET and POST variables in wp_magic_quotes()
				$content = stripslashes($_POST["content"]);

				if (fwrite($handle, $content) === FALSE) {
					echo "Error writing to $path/$file.";
				} else {
					echo "$file saved successfully.";
				}
			}
		}

		echo "<br />\n";
	}
?>

<div id="pingdomstatus-tabs"">
	<?php
		$templates = array();

		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				if ($file[0] != "." && is_file("$path/$file") && !in_array($file, $exclude)) {
					$templates[] = $file;
				}
			}

    			closedir($handle);
		}

		sort($templates);

		echo "<ul>\n";
		$idx = 1;
		foreach ($templates as $file) {
			echo "<li><a href='#pingdomstatus-tabs-$idx'>$file</a></li>\n";
			$idx++;
		}
		echo "</ul>\n";

		$idx = 1;
		foreach ($templates as $file) {
			echo "<div id='pingdomstatus-tabs-$idx'>\n";
			echo '<form method="post">';
			echo "<input type='hidden' name='filename' value='$file' />\n";
			echo "<input type='hidden' name='index' value='$idx' />\n";
			echo '<textarea name="content" rows="30" cols="128">';
			$template = file_get_contents("$path/$file");
			echo htmlentities($template, ENT_NOQUOTES, 'UTF-8');
			echo '</textarea><p class="submit"><input type="submit" name="submit" value="Save &raquo;" />' . "\n";
			echo '<input type="submit" name="submit" value="Restore &raquo;" /></p>';
			echo '</form></div>';
			$idx++;
		}
	?>
</div>

</div>
</div>
