<?php

$types = get_terms([
    'taxonomy' => 'property_type'
]);
$currentType = get_query_var('property_type');
?>


<h2>Effectuez une recherche</h2>
<div>
    <?= get_search_form() ?>
</div>