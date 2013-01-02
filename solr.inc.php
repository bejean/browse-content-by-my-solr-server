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



function mssbc_get_solr($ping = FALSE) {
	// get the connection options

	$plugin_s4w_settings = get_option('plugin_mss_settings');
	$host = $plugin_s4w_settings['mss_solr_host'];
	$port = $plugin_s4w_settings['mss_solr_port'];
	$path = $plugin_s4w_settings['mss_solr_path'];
	if (!$host && !$port && !$path) {

		$plugin_s4w_settings = get_option('plugin_s4w_settings');
		$host = $plugin_s4w_settings['s4w_solr_host'];
		$port = $plugin_s4w_settings['s4w_solr_port'];
		$path = $plugin_s4w_settings['s4w_solr_path'];

		if (!$host && !$port && !$path) {
			$host = get_option('s4w_solr_host');
			$port = get_option('s4w_solr_port');
			$path = get_option('s4w_solr_path');
		}
	}

	// double check everything has been set
	if ( ! ($host and $port and $path) ) {
		return NULL;
	}

	// create the solr service object
	require_once("SolrPhpClient/Apache/Solr/HttpTransport/Curl.php");
	$httpTransport = new Apache_Solr_HttpTransport_Curl();
	$solr = new Apache_Solr_Service($host, $port, $path, $httpTransport);
	
	// if we want to check if the server is alive, ping it
	if ($ping) {
		if ( ! $solr->ping() ) {
			$solr = NULL;
		}
	}

	return $solr;
}

function mssbc_query($aFilters, $facet_fields) {
	$solr = mssbc_get_solr();
	$response = NULL;

	$fq=array();
	for ($i=0; $i<count($aFilters); $i++) {
		//$fq .= '+' . $aFilters[$i]['facetfield'] . ':"' . $aFilters[$i]['facetval'] . '"';
		$fq[] = $aFilters[$i]['facetfield'] . ':"' . $aFilters[$i]['facetval'] . '"';
	}

	if ( $solr ) {
		$params = array();

		$bUseDismax = false;
		if ($bUseDismax) {
			$params['defType'] = 'dismax';
			$params['qf'] = 'content';
			$query="";
			$params['q.alt'] = '*:*';
		}
		else {
			$query="*:*";
		}

		$params['fl'] = '*,score';
		$params['fq'] = $fq;
		$params['facet'] = 'true';
		$params['facet.field'] = $facet_fields;
		$params['facet.mincount'] = '1';
		$response = $solr->search( $query, 0, 10, $params);
		if ( ! $response->getHttpStatus() == 200 ) {
			$response = NULL;
		}
	}
	//echo $response->getRawResponse();
	return $response;
}
?>