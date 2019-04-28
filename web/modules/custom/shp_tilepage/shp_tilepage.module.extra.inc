<?php

function fff() {

//  $props = array('id' => 'AD');

  $module = 'shp_tilepage';
  $alls = _get_blank_info();

  $regions = array();
  $keyfield = 'availableCurrency';

   foreach ($alls as $all) {
      $key = $all[$keyfield];
      $all[$keyfield];
      foreach (array_keys($all) as $field) {
          $entry = $all[$field];
          if (! isset($regions[$key][$field]) )   $regions[$key][$field] = [];
          if (! in_array($entry, $regions[$key][$field]) )     $regions[$key][$field][] = $entry ;
      }
   }


   foreach($regions as $short => $data) {
        $data['langType'] = 'currency';
        $label = $data['availableCurrency'][0];
        $lang = entity_create('configurable_language', ['id' => $short, 'label' => $label ] );
        foreach ( array_keys($data) as $key) {
            $val = $data[$key];
            $lang->setThirdPartySetting('shp_tilepage', $key,$val);
        }

//       $lang->save();

   }

   return $regions;

}



function eee() {

//  $props = array('id' => 'AD');

  $module = 'shp_tilepage';
  $alls = _get_blank_info();

  $regions = array();
  $keyfield = 'regionShort';

   foreach ($alls as $all) {
      $key = $all[$keyfield];
      $all[$keyfield];
      foreach (array_keys($all) as $field) {
          $entry = $all[$field];
          if (! isset($regions[$key][$field]) )   $regions[$key][$field] = [];
          if (! in_array($entry, $regions[$key][$field]) )     $regions[$key][$field][] = $entry ;
      }
   }

//            error_log(' regions ' . print_r($regions, true) );


   foreach($regions as $short => $data) {

      if (! isset($short) ) continue;

        drupal_set_message('region ' . $short );

        if (! ( isset($short) && strlen($short)) ) {
            continue;
            error_log(' regions ' . print_r($regions, true) );

            die('bad format');
        }

        $data['langType'] = 'region';
        $label = $data['region'][0];
        $lang = entity_create('configu3able_language', ['id' => $short, 'label' => $label ] );
        foreach ( array_keys($data) as $key) {
            $val = $data[$key];
            $lang->setThirdPartySetting('shp_tilepage', $key,$val);
        }

        try{
           $lang->save();
        } catch(Exception $e) {
            error_log(' regions ' . print_r($regions, true) );
            die('bad format ' . $short);
        }

        drupal_set_message('Saved ' . $label );

   }

   return $regions;

}



function ddd() {

//  $props = array('id' => 'AD');
  $props = [];

  $items = \Drupal::entityTypeManager()->getStorage('configurable_language')->loadByProperties( $props );

  foreach ($items as $cc => $item) {
      $props = _get_info($cc);
      if ( $props) {
          $props['langType'] = 'country';
          foreach($props as $key => $val ) {
            if (is_array($val) ) $val = reset($val);
            $item->setThirdPartySetting('shp_tilepage', $key,$val);
          }
          $item->save();
//          drupal_set_message( 'item ' . print_r($item, true) ) ;
      }
  }

  drupal_set_message('done');

//$keys = array_keys($items) ;   drupal_set_message(' keys ' . implode(', ' , $keys)  . '           ') ;
 // drupal_set_message

}


function _get_blank_info() {

//# polarCountryCode, countryName, availableCurrency, region
//#'CA', 'CANADA', 'CAD', 'North and Central America'


 $query = db_query('SELECT * FROM {_countries} ' );
 $data = $query->fetchAll();

 $return = [];
 if ($data) { 
    foreach($data as $item) {
      $items = (array) $item;
      $return[] = $items;
    }

    return $return;

    $result = (array) $data[$cc];
 //   drupal_set_message(' results ' . print_r($result, true));
    return $result;
//   echo print_r($result, true) . "\n";
 }

 return FALSE;

}




function _get_info($cc = '') {

//# polarCountryCode, countryName, availableCurrency, region
//#'CA', 'CANADA', 'CAD', 'North and Central America'


 $query = db_query('SELECT * FROM {_countries} where polarCountryCode = :cc', array(':cc' => $cc ) );
 $data = $query->fetchAllAssoc('polarCountryCode');

 if (($data) && (array_key_exists($cc, $data) ) ) { 
    $result = (array) $data[$cc];
    drupal_set_message(' results ' . print_r($result, true));
    return $result;
//   echo print_r($result, true) . "\n";
 }

 return FALSE;

}

