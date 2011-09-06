<?php
function mssbc_get_solr($ping = FALSE) {
	# get the connection options
	$host = get_option('s4w_solr_host');
	$port = get_option('s4w_solr_port');
	$path = get_option('s4w_solr_path');

	# double check everything has been set
	if ( ! ($host and $port and $path) ) {
		return NULL;
	}

	# create the solr service object
	$solr = new Apache_Solr_Service($host, $port, $path);

	# if we want to check if the server is alive, ping it
	if ($ping) {
		if ( ! $solr->ping() ) {
			$solr = NULL;
		}
	}

	return $solr;
}

function mssbc_query($aFilters, $facet_fields, $exclude_post, $exclude_page) {
	$solr = mssbc_get_solr();
	$response = NULL;

	if ($exclude_post=='1') $fq = 'type:"page"';
	if ($exclude_page=='1') $fq = 'type:"post"';
	for ($i=0; $i<count($aFilters); $i++) {
		$fq .= '+' . $aFilters[$i]['facetfield'] . ':"' . $aFilters[$i]['facetval'] . '"';
	}

	if ( $solr ) {
		$params = array();
		//$params['defType'] = 'dismax';
		$params['fl'] = '*,score';
		$params['fq'] = $fq;
		$params['facet'] = 'true';
		$params['facet.field'] = $facet_fields;
		$params['facet.mincount'] = '1';
		$response = $solr->search( "*:*", 0, 10, $params);
		if ( ! $response->getHttpStatus() == 200 ) {
			$response = NULL;
		}
	}
	return $response;
}
?>