## Architecture:

* We use migrate to continually migrate/import content into Drupal.
* By default migrate just imports content once and is done.
* After that we use "track_changes" (see below) to avoid that.

    ```
    source:
      plugin: afar_dc_port
      track_changes: true
    ```
* This basically resaves all entities if they have been there already.
* A custom destination plugin (\Drupal\afar_import\Plugin\migrate\destination\EntityChangedRevision)
  ensures that we always create new revisions, especially on updates.

### Port migration

* The port migration is separated into two files.
* afar_term_port: This migrates all ports into a term on the 'ports' vocabulary.
* afar_dc_port: This migrates all ports into a dc_content:port. On top of that
  this contains an entity reference pointing to the previously imported term, see:

    ```
    field_port_code:
      plugin: migration
      migration: afar_term_port
      source: id
    ```
  The source property here points to a value in the current migration which will
  be used as source ID for the other migration to fetch the right destination ID

## How to debug

```
drush en config_devel migrate_plus migrate_tools afar_import -y
```

### How to manually test the revision update functionality.

* Install the guzzle_helper module, it provides some hack to force one new
  entry all the time.

* Cleanup once
`bash test.bash`

* Import all content the first time.
`drush mi afar_dc_place -y`

* Import it again, one update should appear
`drush mi afar_dc_place -y`

* Look into the databas table for dc_content, there should be two more entries
  as 71350 is sent twice.

```
SELECT id, revision_id FROM dc_content_revision WHERE ID = 71350
```

### Configuration

Available configuration (see afar_import.settings.yml)

* base_url: Allows you to switch between the staging and afar live environment
