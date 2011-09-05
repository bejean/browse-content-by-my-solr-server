<?php
if ($results) {
	if ($bDebug) {
		echo "Solr: <br />";
		print_r($results);
		echo "<br /><br />";
	}
	if($results->facet_counts) {
		echo '<div class="mssbc_browse">';
		foreach ($results->facet_counts->facet_fields as $facetfield => $facet) {
			if (!get_object_vars($facet) ) continue;
			echo '<div class="mssbc_browse_field_title">' . ucwords(preg_replace('/_str$/i', '', $facetfield)) . "</div>";
			echo '<lu class="mssbc_browse_field_list">';
			foreach ($facet as $facetval => $facetcnt) {
				$aValues = explode ('^^', $facetval);
				$values = "";
				for ($i=0;$i<count($aValues);$i++) {
					if (trim($aValues[$i])!='') $values = $aValues[$i];
				}
				echo '<li class="mssbc_browse_field_list_item">';
				if ($this->isSelected($aFilters['filters'], $facetfield, $facetval)) {
					echo "<input type='checkbox' onClick='mss_browse(\"remove\", \"" . $facetfield . "\", \"" . $facetval . "\");' checked>";
					echo "<a href='javascript:void(0)' onClick='mss_browse(\"remove\", \"" . $facetfield . "\", \"" . $facetval . "\");'>" . $values . " (" . $facetcnt . ") </a>";
				}
				else {
					echo "<input type='checkbox' onClick='mss_browse(\"add\", \"" . $facetfield . "\", \"" . $facetval . "\");'>";
					echo "<a href='javascript:void(0)' onClick='mss_browse(\"add\", \"" . $facetfield . "\", \"" . $facetval . "\");'>" . $values . " (" . $facetcnt . ") </a>";
				}
				echo '</li>';
			}
			echo '</lu>';
		}
		echo '</div>';
	}
}
?>