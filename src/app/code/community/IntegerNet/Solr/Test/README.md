# Integration Test Suite

The Magento 1.x integration test suite requires a running Solr instance at `localhost:8983/solr/` with cores `core0`, `core1` and `core2`

If your Solr configuration differs, change it in `fixtures/config.yaml`

## Run Tests:

    phpunit --group IntegerNet_Solr