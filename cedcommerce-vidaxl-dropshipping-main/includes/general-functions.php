<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
function get_countries(){
    $countries = array(
        ''   => "-Select Region-",
        // 'US' => "United States",
        // 'FR' => "France",
        // 'SE' => "Sweden",
        // 'DK' => "Denmark",
        // 'IT' => "Italy",
        // 'AU' => "Australia",
        // 'CH(DE)' => "Switzerland(DE)",
        // 'CH(FR)' => "Switzerland(FR)",
        // 'NL' => "Netherlands",
        // 'DE' => "Germany",
        // 'BE(NL)' => "Belgium(NL)",
        // 'BE(FR)' => "Belgium(FR)",
        // 'ES' => "Spain",
        // 'HR' => "Croatia",
        // 'RO' => "Romania",
        // 'LT' => "Lithuania",
        // 'GR' => "Greece",
        // 'PT' => "Portugal",
        // 'AT' => "Austria",
        // 'SI' => "Slovenia",
        // 'NO' => "Norway",
        // 'Uk' => "United Kingdom",
        // 'BG' => "Bulgaria",
        // 'LV' => "Latvia",
        // 'CR' => "Costa Rica",
        // 'PL' => "Poland",
        // 'HU' => "Hungary",
        // 'IE' => "Ireland",
        'EE' => "Estonia",
        // 'FI' => "Finland",
        // 'SK' => "Slovakia"
    );
    return $countries;
}

?>        