<?php

return [

	'url' => 'https://public.opendatasoft.com/api/records/1.0/search/?',

	'query' => 'dataset=us-zip-code-latitude-and-longitude&q=%zip_code%&facet=state&facet=timezone&facet=dst',

	'query_parameters' => [
		'country' => 'US',
	],

	'iterate_on' => 'records',

	'fields' => [
    'city'  => 'fields.city'
		'zip' => 'fields.zip',

		'country_id' => null,

		'country_name' => null,

		'state_id' => null,

		'state_name' => 'places.0.state',

		'place' => 'fields.city',

		'longitude' => 'fields.longitude',

		'latitude' => 'fields.latitude',
	],

];
