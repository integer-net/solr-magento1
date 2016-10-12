![IntegerNet_Solr Free](doc/solr_free_banner.png)

# IntegerNet_Solr Free
This is a free extension to integrate Solr as a product search in an online store running on Magento Community 
Edition 1.7 - 1.9.

## Features

### Fast Search Results
The main feature of this is extension is the integration of Solr as a product search in your Magento store. 
Solr is a lot faster as a search engine than Magento's native MySQL search. Also, the search algorith is better, 
so search results are more relevant.

### Fuzzy Search
The search algorithm performs a fuzzy search to help with usability. When you have typos or misspellings in 
your search request, the fuzzy search will look for similar words, increasing the chance of finding a match. 

### Boost Products or Attributes
Use your catalog search as a marketing feature to increase the sales of certain products. You can either select 
individual or a bunch of products to boost, or you can boost an attribute. Whenever one of these boosted 
products or attributes are a match with the search term, they are ranked higher than usual.

### Use second Solr Core to avoid downtime during Indexing
If you have a second Solr core available (you should!), you can use that to run the indexing process on while 
 the first core still serves the search during that time. Once indexing is finished, the cores will be switched
 automatically in an atomic operation.

## Requirements
- Magento Community Edition 1.7 to 1.9
- Solr 4.x to 6.x
- PHP 5.3 to 7.0 

## Installation 
1. Install Solr and create at least one working core
2. Copy the files from the solr_conf dir of the module repository to the conf dir of your Solr core
3. Reload the Solr core (or all of Solr)
4. (If activated: deactivate the Magento compiler)
5. If using **Composer**: Just add "integer-net/solr-magento1" to the list of requirements in your composer.json.
**Otherwise**: Download the archive from the [Releases Page](https://github.com/integer-net/solr-magento1/releases). 
Copy the files and directories from the src directory of the module repository into your Magento installation.
**Attention**: You will need the files of two more repositories: The 
[IntegerNet_Solr base library](https://github.com/integer-net/solr-base) and the 
[Aoe_LayoutConditions module](https://github.com/AOEpeople/Aoe_LayoutConditions). They are already contained in 
the release archive.
6. Clear the Magento cache
7. (Recompile and reactivate the Magento compiler – it is not recommended to use the compiler mode of Magento, 
independent of the IntegerNet_Solr module)
8. Go to the Magento backend, go to System -> Configuration -> Solr
9. Enter the Solr access data and configure the module (see 
[Documentation](https://www.integer-net.com/solr-magento/documentation/) for more information - you will also 
find an explanation there about how to find out the correct access data)
10. Click "Save Configuration". The connection to the Solr server will automatically be tested. You’ll get a 
success or error message about that.
11. Reindex the integernet\_solr index. We recommend doing this via shell. Go to the shell dir and call 
`php -f indexer.php -- --reindex integernet_solr`
12. Submit a search request with a slight typo on your page. The search result page should show matching products.

## Configuration
The extension comes with a complete installation and configuration guide. Every configuration option is 
explained. You can find it on our website: 
**[Documentation](https://www.integer-net.com/solr-magento/documentation/)**

## Modification
You are welcome to modify the extension as you like. To make it easier, we have implemented a number of events. 
For a quick start and an example, please have a look at our blog post: 
[How to Use Events to Tweak Solr Search](https://www.integer-net.com/utilizing-events-of-integernet_solr-an-example/)

## License
The extension IntegerNet_Solr Free is published under the license GNU Lesser Public General License (LGPL v3). 
You can find the license in the file [License](https://github.com/integer-net/solr-magento1/blob/master/LICENSE). 
For more information and an explanation of this license, please see the following article: 
[Information on GNU Lesser General Public License](https://www.gnu.org/licenses/lgpl-3.0.en.html)

## Contributing
If you would like to contribute to this extension, please fork the repository. 
Any pull requests are warmly welcome.

## Support
Please note that this is a free extension. We do not provide individual customer support for this extension. 
If you find a bug, please open a GitHub issue.

## Upgrade
IntegerNet\_Solr Free is an offspring of **IntegerNet_Solr Pro**, our powerful Solr extension for Magento. 
It offers more features, such as an extensive autosuggest window, multiselect filters, product lists in 
categories loaded via Solr and support of Magento Enterprise Edition. For more information, please visit our 
[website](https://www.integer-net.com/solr-magento/).

[![Upgrade to IntegerNet_Solr Pro](src/skin/adminhtml/default/default/integernet/solr/solr_free_banner_upgrade_to_pro.png)](https://www.integer-net.com/solr-magento/features/?utm_source=readme&utm_medium=banner&utm_term=features&utm_content=features&utm_campaign=upgrade)