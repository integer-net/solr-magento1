IntegerNet_Solr
===============
Users / Developers Manual

About
-----
IntegerNet_Solr is a Magento 1.x module which creates a better search experience using Apache Solr as its engine. 
Its main features are an autosuggest window with product and keyword suggestions based on what is being entered in the search bar, plus better search results regarding quality and speed.

Features
--------
#### General
- Correction of spelling, fuzzy search
- Displays exact search results first, followed by results to similar search terms
- Select multiple filter values
- Filters can be displayed left or above product lists
- Supports multi store functionality of Magento completely
- Compatible with default, modern and rwd themes of Magento
- Can use one Solr core for several Magento store views or separate cores 
- Can use separate Solr cores for indexing only and swap cores afterwards
- Allows logging of all Solr requests
- Checks connection and configuration of Solr server

#### Autosuggest window
- Appears after the first two letters have been typed into the search form
- Displays product suggestions, category suggestions, attribute suggestions, CMS page suggestions and keyword suggestions
- Number of suggestions for each type is configurable in the Magento backend
- Attributes to display can be defined in configuration
- Can skip Magento instantiation and use PHP only for faster results

#### Search results
- Provides all features from Magento default, like sorting, pagination and filters
- Takes search results from Solr for better speed and quality
- Prerenders product HTML blocks for faster rendering
- Allows to configure the price filter steps
- Automatic update of Solr index on create/edit/delete of products

#### Modification of search results
- Modify "fuzziness"
- Allows boosting of products and attributes
- Enables the exclusion of certain categories, products and CMS pages from search results
- Redirects to product and category pages for exact matches with search term
- Provides events for modifying indexing process and search requests

#### Category Pages
- Optional use of Solr index for product display on category pages and layered navigation

Requirements
------------
- **Magento Community Edition** 1.7 to 1.9 or **Magento Enterprise Edition** 1.12 to 1.14
- **Solr** 4.x to 6.x
- **PHP** 5.3 to 5.5 (5.5 recommended), probably compatible with PHP 5.6 and 7.0 as well (not tested yet)

Installation
------------
1. Install **Solr** and create at least one working **core**
2. Copy the files from the `solr_conf` dir of the module repository to the `conf` dir of your Solr core
3. Reload the Solr core (or all of Solr)
4. (If activated: deactivate the Magento compiler)
5. Copy the files and directories from the `src` directory of the module repository into your Magento installation. If you are using **modman** and/or **composer** you can find a `modman` file and a `composer.json` in the root directory.
6. Clear the Magento cache
7. (Recompile and reactivate the Magento compiler - it is not recommended to use the compiler mode of Magento, independent of the IntegerNet_Solr module)
8. Go to the Magento backend, go to `System -> Configuration -> Solr`.
9. Enter the Solr access data and configure the module ([see below](#solr-server-data))
10. Click **Save Config**. The connection to the Solr server will automatically be tested. You'll get a success or error message about that.
11. If you are using the Magento Enterprise Edition, you have to switch off the integrated Solr engine by switching `System -> Configuration -> Catalog -> Catalog Search -> Search Engine` to `MySql Fulltext`.
12. Reindex the integernet_solr index. We recommend doing this via shell. Go to the `shell` dir and call `php -f indexer.php -- --reindex integernet_solr`
13. Try typing a few letters in the search box on the frontend. A box with product and keyword suggestions should appear.
 
<a name="technical-workflow">Technical workflow</a>
------------------

### Indexing
For each product and store view combination, a Solr document is created on the Solr server. This happens through the Magento indexing mechanism which allows to react on every product change. You can either have a full reindex process which processes all products efficiently (in batch of 1000 products each, configurable) or a partial reindex.
A partial reindex will happen if any product is created, modified or deleted and will recreate the corresponding documents in the Solr server for the affected products only, so the Solr index is always up to date.

The data which is stored on Solr contains the following information:

- Product ID
- Store ID
- Category IDs
- Contents of all product attributes which are marked as "searchable" in Magento
- Generated HTML for autosuggest window, containing the defined data and layout (i.e. name, price, image, ...)
- If configured: Generated HTML for results page, once for grid mode and once for list mode
- IDs of all options of filterable attributes for the layered navigation

If you are using the full reindex regularly, we recommend using the **swap** functionality. You can configure the module to use a different Solr core for indexing and swap cores afterwards (`System -> Configuration -> Solr -> Indexing -> Swap Cores after Full Reindex`).  

### Autosuggest
When using the autosuggest functionality, there will be an AJAX call whenever a customer has typed the first few letters into the search field on the frontend. The AJAX response will be the HTML of the Autosuggest window, including product data, keyword suggestions, matching categories and/or attributes. The target of the AJAX call will differ depending on the configuration setting `System -> Configuration -> Solr -> Autosuggest Box -> Method to retrieve autosuggest information`

#### Magento Controller
This is the basic method which uses Magento methods only, as does the MySQL default or the Solr functionality of the Magento Enterprise Edition. It's the slowest but the most flexible. It's intended as a fallback if other methods are not working due to whatever reason.

#### Magento with separate PHP file
This will call a separate PHP file `autosuggest-mage.php` in the Magento root dir directly. It skips the routing process of Magento and thus will deliver the contents faster. Still, all Magento functionality should be working.
We haven't found any disadvantages of this method yet, except its speed (see below).

#### PHP without Magento instantiation
This will call a different PHP file `autosuggest.php` in the Magento root dir directly. It doesn't use most of the Magento functionality and is a lot faster in most environments. As it doesn't do any database calls, all the data which is needed for the autosuggest window will have to come either from Solr directly or from a text file. The module automatically generates text files which contain the information used for the default autosuggest window:

- The Solr configuration
- Some additional configuration values
- All category data (names, IDs and URLs)
- All attribute data which is configured to be used in autosuggest (option names, IDs and URLs)
- Some additional information like the Store Base URL or the filename of the template file (see below)
- A copy of the `template/integernet/solr/result/autosuggest.phtml` file which is used in your theme. It has all the translation text already translated.

The information is stored in `var/integernet_solr/store_x/config.txt` (as a serialized array) and `var/integernet_solr/store_x/autosuggest.phtml`. These files will be automatically recreated in any of the following events:

- AJAX call on the frontend occurs while the file `var/integernet_solr/store_x/config.txt` doesn't exist.
- The configuration of the Solr module is changed.
- All Cache is cleared.
- Button "Rebuild Solr Autosuggest Cache" on the Magento admin cache management page is used.

So if you want to force recreating that information, trigger any of the above events.

Note that you won't have all Magento functionality available if you are using this method. Please try to stick to those methods used in `app/design/frontend/base/default/template/integernet/solr/autosuggest.phtml`. For example, you cannot include static blocks or other external information without further modification.

Configuration
-------------

You will find the configuration in the admin area of Magento at *System -> Configuration -> Solr*:

![Configuration Menu](http://www.integer-net.com/download/solr/integernet-solr-config-menu-en.png)

The configuration option are listed and described here:

### General

![General](http://www.integer-net.com/download/solr/integernet-solr-config-general-en.png)

In the upper area, success messages, error messages, warnings and information messages are display. For example, there is an automated check if the module is activated, if access data to the Solr server is filled in and if the connection is working correctly.

#### Is active

If this switch is set to "No", the search module cannot be used on the frontend. Instead, the default search of Magento will be used. You can set the options for single websites and store views separately.

#### License Key

The module needs a correct license key in order to work correctly. You will get it from us after purchase and payment of the module. Please contact solr@integer-net.com if you are having problems with your license key.

You can test the module for two weeks without any license key. Only after this period, the license key will be necessary for the module to work.
A license key is valid for one live instance and an arbitrary number of corresponding development, test and staging instances.

Attention: there will be no internet connection to a license server. As soon as a valid license key is entered, the module will work on its own without any external dependencies (except the Solr server of course).

#### Activate Logging

If this switch is activated, all requests to the Solr server will be saved in a log file. This affects the autosuggest function and the search results. You can find the logs in the directory `/var/log` with the file names `solr.log` respectively `solr_suggestions.log`.

The log files are used for bug tracing and for optimization of search results only. As the files can get pretty large with a frequently used search function, we usually recommend switching off logging on live environments.

<a name="solr-server-data"></a>

### Server

![Server](http://www.integer-net.com/download/solr/integernet-solr-config-server-en.png)

You can enter the connection and access data to your Solr server here. If the data is correct, you will see according success messages on the upper part of the configuration page - error messages otherwise.
In case you don't know the access data, you can get them from your administrator or hosting company which has installed the Solr server.

If you have access to the admin area of the Solr server, you can find the access data as follows:

1. Select your core in the Core Selector on the lower left part of the page:
 ![Solr Admin 1](http://www.integer-net.com/download/solr/solr-admin-1.png)  
2. Select "Query" below the Core Selector  
 ![Solr Admin 2](http://www.integer-net.com/download/solr/solr-admin-2.png)  
3. Click "Execute Query"  
 ![Solr Admin 3](http://www.integer-net.com/download/solr/solr-admin-3.png)  
4. In the upper part on the right you will now find the URL which was used for your sample request:  
 ![Solr Admin 4](http://www.integer-net.com/download/solr/solr-admin-4.png)  

The URL can be divided into the single parts as follows:

![Solr Admin URL](http://www.integer-net.com/download/solr/solr-config-server.png)

The single parts can then be entered into the configuration:

![Solr Server Configuration](http://www.integer-net.com/download/solr/solr-server-config-en.png)

Please take care that the field *Core* doesn't contain any slashes, while the field *Path* must contain at least one slash at the front and one at the end.

#### HTTP Transport Method

If you don't get an error message after entering your credentials for the Solr server, you should stick to the default method *cURL*. Otherwise you can try switching to *file_get_contents*. The availability of both methods depends on server settings of the Magento server.

#### HTTP Basic Authentication

Please enter username and password here if those are needed for the access from Magento to the Solr server.

### Connection Check

![Connection Check](http://www.integer-net.de/download/solr/integernet-solr-config-connection-check-en.png)

To make sure that the connection to your Solr server is not lost unnoticed, the module is able to automatically perform a connection check.

#### Check Solr Server Connectivity automatically

When the value "Yes" is selected, an automatic connection check will be performed every 5 minutes.

#### Send Notification Email after X Failures in a Row

If you would like to be notified about each failed connection check, enter the value 1.

#### Email Recipient(s)
 
Notifications are sent to the email addresses provided in this field. For multiple recipients, divide addresses by comma.

#### Email Template

You are able to set up your own email template for connection check notifications. The template is stored in `System -> Transactional Emails`  in the  Magento backend.
If you have modified the email template, please make sure that the one selected in the configuration matches the one you wish to use.

#### Email Sender

Here you can change the sender of your connection check notifications.

### Indexing 

![Indexing](http://www.integer-net.com/download/solr/integernet-solr-config-indexing-en.png)

#### Number of Products per Bunch

The number entered here is the number of products which is processed by the indexer (see above) at the same time. That's also how many product data sets are transferred to the Solr server in a single request. The performance of the indexing process depends heavily on this setting. You can reduce this value if you are getting errors during indexing.

#### Delete all Solr Index Entries before Reindexing

You should only deactivate this setting if you recreate the index completely (i.e. during each night) but can't use a *Swap* core. If this setting is active the Solr index will be emptied completely at the start of every reindexing process before rebuilding it.

#### Swap Cores after Full Reindex

If you rebuild the Solr index regularly (i.e. nightly) we recommend to use the functionality to swap cores. You need a second core for that. In this case, you should activate this setting and enter the name of the second core into the field *Name of Core to swap active Core with* below.

#### Disconnect and reconnect MySQL Connections during indexing

If you experience issues due to SQL timeouts during indexing, this configuration will help you. Its default value is "No". If you switch to "Yes", MySQL connections will be automatically disconnected and reconnected during the indexing process. This feature only works with Magento CE 1.9.1 / EE 1.14.1 or newer versions.

### Fuzzy Search

![Fuzzy Search](http://www.integer-net.com/download/solr/integernet-solr-config-fuzzy-en.png)

#### Is active for Search

If this setting is deactivated, only exact search matches will be registered. No spelling error correction will be performed. On the other hand, searches are conducted faster if this setting is deactivated.

#### Sensitivity for Search

Here you can enter how sensitive the fuzzy search should be. The value must be between 0 and 1, i.e. *0.75*. The lower the value the more matches you will get, as spelling mistakes will be corrected more generously. You should test different values to get the optimal value for your shop. We recommend settings between 0.6 and 0.9.

#### Number of Sufficient Direct Search Results

Direct search results are automatically complemented by fuzzy search results if fuzzy search is activated.
You can limit this functionality by entering a number of sufficient direct search results. If at least this many direct search results are found, no fuzzy search is performed.
If you enter no value or 0, fuzzy search will always be performed.

#### Is active for Autosuggest

Like above, but individually adjustable for the autosuggest box. It can be interesting to deactivate this function for the autosuggest only due to performance reasons.

#### Sensitivity for Autosuggest

Like above, but individually adjustable for the autosuggest functionality.

#### Number of Sufficient Direct Search Results for Autosuggest

Just like for search requests you can limit the activity of fuzzy search for autosuggest, too. 
If the sufficient number of direct search results for autosuggest is reached, a fuzzy search for autosuggest terms will not be performed.
In case the entered value is 0 or empty, fuzzy search will always be performed.

### Search Results

![Search Results](http://www.integer-net.com/download/solr/integernet-solr-config-search-results-en.png)

#### Use HTML from Solr Index

If this setting is activated, the HTML code which displays a single product in the search results will already be generated during indexing. Of course, this will take a bit longer, but on the other hand, the output will be faster in the search results. This is because this part won't have to be generated on the fly for every product.

Thus, we recommend to activate this setting. There is an exception if the product data in the search results should be displayed customer dependent or customer group dependent, i.e. if the prices differ for the different customer groups.
Please deactivate this setting in that case.

#### Search Operator

You can choose between *AND* and *OR*. The search operator is used if there is more than one search word in the request, i.e. "red shirt". When using *AND*, only search results will be displayed which match both (or all) search words. 
When using *OR*, results which match only one of the search words will be displayed.
In most cases, *AND* is the better setting.

#### Position of Filters

Filters can be displayed either in the left column next to the list of products or above the products. The latter is recommended if you have a rather narrow template.

#### Maximum number of Filter Options per Filter

If there are many filter options for a filter, for the sake of clarity you can limit the number of displayed filter values. Entering the value "0" means that all filter values will be displayed.

#### Sort Filter Options alphabetically

Usually filter options are sorted by number of results. In some cases it makes sense to sort them alphabetically instead. To activate alphabetical sorting, set the value to "Yes".

#### Solr Priority of Category Names

Configure with which priority category names are handled in the Solr index. For example, if the search term "black shirts" should primarily return those products as search results which are contained in a category named "shirts", you might want to enter a higher value than 1.
The default value is 1. If you enter a higher value, category names have a higher priority in the Solr index.

#### Show products which are out of stock

In the default setting, even products which are not in stock are shown on the search results page. To keep your search results clear from items which are out of stock, select "No".

#### Solr Priority Multiplier for Products being out of Stock

This is a factor which manipulates the ranking of search results depending on the product's stock status. If you prefer to have items which are out of stock in the list of search results, select a value that is greater than 0. To put sold out products at the very bottom of the search results, enter "0.1".  The value "1" means that the stock status has no impact on the search results ranking. 

#### Size of Price Steps

This setting is used by the price filter. You can set the steps which are used for the single intervals. I.e. *10* leads to the intervals *0.00-10.00*, *10.00-20.00*, *20.00-30.00* and so on.
 
#### Upper Limit of Price Steps

This setting is used for the price filter as well. This value defines the topmost interval. If set to *200*, this would be *from 200.00*. All products which cost more than 200.00 will be combined in this interval.

#### Redirect to product page on direct match in one of these attributes

If the entered search term is an exact match with an important attribute of a product, you can here activate a direct redirect to the matching product page. As a result, the way to the product is shortened, because you skip the step of showing the search results page.
It is recommended to only use this redirect for attributes which have unique values for each product.   

#### Redirect to category page on direct match in one of these attributes

Just like a redirect to a product page, you can also activate redirects for search terms which exactly match a category's attribute. Please make sure to only use this feature for attributes which allow for unambiguous matching with a category page. 

#### Use Custom Price Intervals
If you don't want to have a linear arrangement of intervals and you are using Solr 4.10 or above, you can set the desired interval borders for the price filter individually here. In the example *10,20,50,100,200,300,400,500* this would be the intervals *0.00-10.00*, *10.00-20.00*, *20.00-50.00* and so on until *400.00-500.00* and *from 500.00*. 

#### Redirect to product page on direct match in one of these attributes

If the entered search term is an exact match with an important attribute of a product, you can here activate a direct redirect to the matching product page. As a result, the way to the product is shortened, because you skip the step of showing the search results page.
It is recommended to only use this redirect for attributes which have unique values for each product.   

#### Redirect to category page on direct match in one of these attributes

Just like a redirect to a product page, you can also activate redirects for search terms which exactly match a category's attribute. Please make sure to only use this feature for attributes which allow for unambiguous matching with a category page. 

### Category Pages

![Category Pages](http://www.integer-net.com/download/solr/integernet-solr-config-category-display-en.png)

#### Use Solr to display products on category pages

If you activate this setting, Solr will be used to displayed products on category pages. Especially for stores with a huge amount of products or filterable attributes for layered navigation, this will speed up the load time of category pages.   

#### Show products which are out of stock

In the default setting, products which are not in stock are shown in the product list on category pages. To remove sold out products from category pages, select "No".

#### Position of Filter

Independent of the filters' position on search result pages, you can choose where to display filters on category pages: either in the left column next to the products or above the products. This is a default value which can be overwritten by the category's configuration.

#### Use Solr to index category pages

When you activate this setting, categories that match the search term will be displayed in the autosuggest box. To finetune suggested categories, you can exclude single categories from being indexed.  

#### Display categories as search results

If you set this configuration to "Yes", your search results page will show an additional tab where matching category pages for the search term are displayed.

#### Maximum number of results

This value regulates how many matching category pages are listed as search results.

#### Fuzzy Search is active

Do you want to apply fuzzy search to category pages as results for a search term? Then select "Yes". 

#### Sensitivity for Search

When fuzzy search is activated, you can configure how sensitive it's supposed to be. Values between 0 and 1 are allowed. Lower values lead to more fuzzy results.

### CMS

![CMS Pages](http://www.integer-net.com/download/solr/integernet-solr-config-CMS-en.png)

#### Use Solr to index cms pages

When activated, matching CMS pages are displayed in the autosuggest box. It works similar to indexing categories. To finetune suggested CMS pages, you can exclude single CMS pages from being indexed.  

#### Display CMS pages as search results

If you set this configuration to "Yes", your search results page will show an additional tab where matching CMS pages for the search term are displayed.

#### Maximum number of results

This value regulates how many matching CMS pages are listed as search results.

#### Fuzzy Search is active

Do you want to apply fuzzy search to CMS pages as results for a search term? Then select "Yes". 

#### Sensitivity for Search

When fuzzy search is activated, you can configure how sensitive it's supposed to be. Values between 0 and 1 are allowed. Lower values lead to more fuzzy results.

### Autosuggest Box

![Autosuggest Box](http://www.integer-net.com/download/solr/integernet-solr-config-autosuggest-en.png)

#### Is active

If you deactivate this setting, no autosuggest window will be displayed.

#### Method to retrieve autosuggest information

This settings is described in the chapter [*Technical Workflow*](#technical-workflow) in detail.

#### Maximum number of searchword suggestions

Depending on your products, the given search word(s) will be expanded to meaningful variants. For example: if the text "re" is entered, the following suggestions will appear: *regular…*, *resistant…*, *refined…*, *red…*.
 
#### Maximum number of product suggestions

The number of products which will be displayed in the autosuggest window.

#### Maximum number of category suggestions

The number of categories which will be displayed in the autosuggest window. If "Use Solr to index category pages" is activated, too, the displayed categories are those whose name and description match the search term. If not, only those categories are displayed which contain products matching the search term.

#### Maximum number of cms page suggestions

The number of CMS pages which will be displayed in the autosuggest window. This feature only works, if "Use Solr to index cms pages" is set to "Yes".

#### Show complete category path

If this setting is active, not only the category names will be displayed, but their parent categories as a path as well.
For example, this will be "Electronics > Cameras > Accessories" instead of "Accessories".

#### Type of Category Links

The link which is behind the displayed categories. It can be:
- Search results page while the category filter is set to show only products of the selected category
- Category page

#### Attribute Filter Suggestions

You can enter an arbitrary number of attributes here which will be displayed in the autosuggest window, including the options which are contained in most of the corresponding products. For every row you can select the attribute and the number of displayed options. Additionally you can define the sorting of the attributes - the attribute with the lowest value in the "Sorting" field will be shown first.

Only attributes with the property "Use In Search Results Layered Navigation" will be selectable.

#### Show products which are out of stock

In the default setting, products which are not in stock are hidden from search suggestions. To show sold out products in the autosuggest box, select "Yes".

### SEO 

![SEO](http://www.integer-net.de/download/solr/integernet-solr-config-seo-en.png)

Here you are able to select which of the pages processed by IntegerNet_Solr shall be hidden from bots and search engines. As a results, these pages' meta element robots has the value "NOINDEX,NOFOLLOW". Please note that this configuration may have a great impact on your store's ranking in search engine results.   

Modifying the sequence of search results
----------------------------------------------

Even with the default settings of this module, the search results will be put in a sequence which depends on the frequency of the occurrences of the search words in the product attributes. This already leads to good results - much better than with the default search of Magento.

Still, there are some possibilities to adjust the sequence of search results:

### Boosting of Attributes

If search words occur in the name or the SKU of a product, it should be valued higher than an occurrence in the product description. By default some attributes are already valued higher than others.

The prioritization follows the value "Solr Priority" which you can set for each product attribute. This new property can be seen in the attribute grid (*Catalog -> Attributes -> Manage Attributes*):

![Attribute Grid](http://www.integer-net.com/download/solr/integernet-solr-attribute-grid-en.png)

In this case, the grid is sorted by the value "Solr Priority". You can set this value in the attribute properties:
 
![Attribute View](http://www.integer-net.com/download/solr/integernet-solr-attribute-view-en.png)

The calculated priority of the search word(s) for the product will be multiplied with this value if the search word occurs in the attribute. Thus, *1.0* is the default - no multiplication happens here. You can increase or decrease the value of individual attributes. We recommend values between 0.5 and 10 (maximum).

Please note that you have to rebuild the Solr index after adjusting the Solr priority of one or more attributes.

### Boosting of Products

From time to time, products should be emphasized, either because they are top sellers or because they should be sold out.
This module makes it possible to increase or decrease the priority of individual products.

Therefore, the new product attribute "Solr priority" is used. You can see it in the tab "Solr" on the product view page in the Magento backend.

![Product View](http://www.integer-net.com/download/solr/integernet-solr-product-boost-en.png)

With that, you have the possibility to position a product, as far as it matches the search word(s), further up or down, relative to its default position. We recommend using values between 0.5 and 10 (maximum). The mechanism is the same as with the boosting of attributes. Though, a reindexing isn't necessary after you have adjusted this value for one or more product(s), as far as automatic index updates are activated.

### Excluding certain CMS Pages

If you would prefer to exclude certain CMS Pages from search results, the settings to do so can be found in the corresponding CMS page in the tab named "Solr".

![CMS Page View](http://www.integer-net.de/download/solr/integernet-solr-CMS-exclude-en.png)

Set "Exclude this Page from Solr Index" to "Yes" to exclude the page from search results.
Use the field "Solr Priority" to weight this page more heavily in the search results. The higher the entered number, the higher the boost factor for this CMS page.

Category adjustments
---------------------

![Category View](http://www.integer-net.de/download/solr/integernet-solr-category-exclude-en.png)

### Exclude this Category from Solr Index
If need be you can exclude categories from Solr search results. The necessary settings can be found in your Magento backend in the corresponding category in the tab named "Solr". When the value is set to "Yes", this category will no longer be displayed in the autosuggest window.

### Exclude Child Categories from Solr Index
Next to excluding a category, you can also opt to exlude all child categories. The excluded categories will no longer be shown in the search suggestions. However, all products connected to the excluded categories will still be shown as product suggestions and as search results.

### Remove Filters
Even if you don't use IntegerNet_Solr to load product lists on category pages, you can use the extension's feature to remove unnecessary filters from a category page. For example, you can thus prevent the filter "Gender" from being shown on a category page for male clothing.  

### Position of Filters
For each category, you can change the position of filters, overwriting the default value from the IntegerNet_Solr configuration. Filters can be displayed either in the left column next to the product list or above the product list.

Template adjustments
--------------------

If you are using a non-standard template, probably some adjustments need to be made. The template of the autosuggest box and the results page is defined in `app/design/frontend/base/default/template/integernet/solr/` (PHTML files) and `skin/frontend/base/default/integernet/solr/` for the CSS file which is included into every page. Copy the files to your own theme directory (same directory and file name) and adjust them there.

### Results page
Most probably you already have a template for the search results page. Usually it can be found at `template/catalog/product/list.phtml` in your theme directory. To generate the according content for the PHTML files of the IntegerNet_Solr module, you have to split the content of your file into three parts.

- The parts inside `<li class="item...">` go to `template/integernet/solr/result/list/item.phtml` and `template/integernet/solr/result/grid/item.phtml` respectively.
- The remainder goes to `template/integernet/solr/result.phtml`. The previously cut out part must be replaced with the following code:

    <?php echo $this
        ->getChild('item')
        ->setProduct($_product)
        ->setListType('list')
        ->toHtml() ?>

Replace `list` with `grid` depending on the part you are replacing.
You should switch off the configuration option `Search Results -> Use HTML from Solr Index` while modifying the template files. If you have this option activated, you have to do a full reindex after activating / changing a list or grid template file.

### Autosuggest box
You can copy and modify the `template/integernet/solr/autosuggest.phtml` and `template/integernet/solr/autosuggest/item.phtml` files to modify the appearance of the autosuggest window. Attention: as the generated HTML for each product is stored in the Solr index, you'll have to reindex after you made changes to the `template/integernet/solr/autosuggest/item.phtml` file.

Pay attention: as the autosuggest functionality isn't delivered by Magento but by a raw PHP version in order to improve performance, you cannot use all Magento functions in your `template/integernet/solr/result/autosuggest.phtml`. 
Try to stick to the functions which are used in `app/design/frontend/base/default/template/integernet/solr/result/autosuggest.phtml`. As the HTML is generated by Magento instead, you can use all Magento functions in your `template/integernet/solr/result/autosuggest.phtml`.

If you aren't using product, category, attribute or keyword suggestions on your autosuggest page, please switch them off in configuration as well because this will improve the performance.

Events
---------------------
In order to further customize the module, we integrated several events which can be observed by another module. The following events are included in IntegerNet_Solr:

- integernet_solr_get_product_data
- integernet_solr_update_query_text
- integernet_solr_before_search_request
- integernet_solr_after_search_request
- integernet_solr_product_collection_load_before
- integernet_solr_product_collection_load_after
- integernet_solr_can_index_product

For further information about these events, their parameters and usage, as well as a sample module, please see our [blog post](https://www.integer-net.com/utilizing-events-of-integernet_solr-an-example/).

Possible Problems and their solutions
-------------------------------------
1. **Rewrite conflicts with modules which affect the layered navigation**    
    You can't avoid this. But you can resolve the conflicts. You can see how we resolved such a conflict with one of those modules in the file `app/code/community/IntegerNet/Solr/Model/Resource/Catalog/Layer/Filter/Price.php`.
    
2. **Saving products in the backend takes a long time**    
    This may happen if you have many store views. We recommend switching the indexing mode of the `integernet_solr` index to "Manually" and do a full reindex at night via cronjob if possible.

3. **Product information on the results page should be different for different customer groups, but it's the same for all**    
    Turn off `Search Results -> Use HTML from Solr Index` in this case so the product HTML will be regenerated at every call. 
    Please not that this will affect the performance of the search result page. 
    
4. **Product information on the autosuggest window should be different for different customer groups, but it's the same for all**
    As the product HTML will always be stored in the Solr index, this is impossible. Try to modify the HTML in `template/integernet/solr/autosuggest/item.phtml` so it doesn't contain customer specific information anymore (e.g. prices).