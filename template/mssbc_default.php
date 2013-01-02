<?php
/*  
    Copyright (c) 2011-2013 www.mysolrserver.com

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/

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
			echo '<div class="mssbc_browse_field_title">' . ucwords(str_replace('_', ' ', preg_replace('/_str$/i', '', $facetfield))) . "</div>";
			echo '<ul class="mssbc_browse_field_list">';
			foreach ($facet as $facetval => $facetcnt) {
				$aValues = explode ('^^', $facetval);
				$values = "";
				for ($i=0;$i<count($aValues);$i++) {
					if (trim($aValues[$i])!='') $values = $aValues[$i];
				}
				
				if (strpos($facetval, "\n")) continue;
				
				// To improve !!!
				$facetval_forjs = str_replace ("'", "\" + String.fromCharCode(39) + \"", str_replace("&amp;", "&amp;amp;", $facetval));
				
				//$facetval_forjs = preg_replace('/\n/', '\\n', $facetval_forjs);
				
				echo '<li class="mssbc_browse_field_list_item">';
				if ($this->isSelected($aFilters['filters'], $facetfield, $facetval)) {
					echo "<input type='checkbox' onClick='mss_browse(\"remove\", \"" . $facetfield . "\", \"" . $facetval_forjs . "\");' checked>";
					echo "&nbsp;<a href='javascript:void(0)' onClick='mss_browse(\"remove\", \"" . $facetfield . "\", \"" . $facetval_forjs . "\");'>" . $values . " (" . $facetcnt . ") </a>";
				}
				else {
					echo "<input type='checkbox' onClick='mss_browse(\"add\", \"" . $facetfield . "\", \"" . $facetval_forjs . "\");'>";
					echo "&nbsp;<a href='javascript:void(0)' onClick='mss_browse(\"add\", \"" . $facetfield . "\", \"" . $facetval_forjs . "\");'>" . $values . " (" . $facetcnt . ") </a>";
				}
				echo '</li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}
}
?>