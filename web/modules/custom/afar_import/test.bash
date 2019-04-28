#!/bin/bash

# Remove all dc_content first
drush ev '\Drupal::entityManager()->getStorage("dc_content")->delete(\Drupal::entityManager()->getStorage("dc_content")->loadMultiple());'
#drush ev '\Drupal::entityManager()->getStorage("dc_content")->delete(\Drupal::entityManager()->getStorage("dc_content")->loadByProperties(["type" => "port"]));'

drush ev 'db_query("truncate migrate_map_afar_dc_place");'
drush ev 'db_query("truncate migrate_map_afar_dc_port");'
drush ev 'db_query("truncate migrate_map_afar_dc_region");'
drush ev 'db_query("truncate migrate_map_afar_dc_destination");'

drush mrs afar_dc_place
drush mrs afar_dc_port
drush mrs afar_dc_region
drush mrs afar_dc_destination

drush mi afar_dc_place -y
drush mi afar_dc_port -y
drush mi afar_dc_region -y
drush mi afar_dc_destination -y
