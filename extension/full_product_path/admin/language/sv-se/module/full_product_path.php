<?php
// English   Full Product Path  		Author: Sirius Dev
// Heading
$_['heading_title']      = '<img src=" data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAAUUlEQVQ4jWNgoBAwQun/OMQJAiZKXTDwBqAD9LAgCFhIMAxrwGIzgCRX4IsuolxA9UDEBvB6CV8gEmMYI7EGIGvElfxJNug/AwPpXiDbZpwAAMv7DBjuBkPvAAAAAElFTkSuQmCC" style="vertical-align:top;padding-right:4px"/> Path Manager';
$_['module_title']		  = '<span>Path </span> Manager';

// Text
$_['tab_fpp_product']   = 'Product';
$_['tab_fpp_category']   = 'Category';
$_['tab_fpp_manufacturer']   = 'Manufacturer';
$_['tab_fpp_search']   = 'Search/Tag';
$_['tab_fpp_common']   = 'Common';
$_['text_fpp_cat_canonical']   = 'Category canonical:';
$_['text_fpp_cat_mode_0']   = 'Direct link';
$_['text_fpp_cat_mode_1']   = 'Full path';
$_['text_fpp_cat_canonical_help']   = 'What kind of link you want to give to search engines ?<br/><b>Direct link</b>: /category (default)<br/><b>Full path</b>: /cat1/cat2/category<br/><br/>With direct link path mode the canonical is automatically set on directl link too';
$_['text_fpp_mode']   = 'Product path mode:';
$_['text_fpp_mode_0']   = 'Direct link';
$_['text_fpp_mode_1']   = 'Shortest path';
$_['text_fpp_mode_2']   = 'Largest path';
$_['text_fpp_mode_3']   = 'Manufacturer path';
$_['text_fpp_mode_4']   = 'Last category';
$_['text_fpp_slash']   = 'Final slash';
$_['text_fpp_slash_mode_0']   = 'No final slash';
$_['text_fpp_slash_mode_1']   = 'Final slash on categories';
$_['text_fpp_slash_mode_2']   = 'Final slash on all urls';
$_['text_fpp_slash_help']   = 'Insert a final slash on urls, this is matter of preference, there is no SEO impact.';
$_['placeholder_category']   = 'Categories';
$_['text_fpp_noprodbreadcrumb'] = 'Remove last breadcrumb:';
$_['text_fpp_noprodbreadcrumb_help'] = '<span class="help">Do not display the last breadcrumb link</span>';
$_['text_fpp_bc_mode'] = 'Breadcrumbs mode:';
$_['text_fpp_breadcrumbs_fix'] = 'Breadcrumbs generator:';
$_['text_fpp_breadcrumbs_0']   = 'Default';
$_['text_fpp_breadcrumbs_1']   = 'Generate if empty';
$_['text_fpp_breadcrumbs_2']   = 'Always generate';

$_['text_fpp_mode_help']   = '<span class="help"><b>Direct link:</b> direct link to product, no category included (ex: /product_name), this is default opencart behaviour<br/>
																		  <b>Shortest path:</b> shortest path by default, can be altered by banned categories (ex: /category/product_name)<br/>
																		  <b>Largest path:</b> largest path by default, can be altered by banned categories (ex: /category/sub-category/product_name)<br/>
																		  <b>Last category:</b> only the last category of the product will be displayed, if you have a product in /category/sub-category/product_name the link will be /sub-category/product_name<br/>
																		  <b>Manufacturer path:</b> manufacturer path instead of categories (ex: /manufacturer/product_name)</span>';
$_['text_fpp_breadcrumbs_help']   = '<span class="help"><b>Default:</b> default opencart behaviour: will display breadcrumbs coming from categories<br/>
																		  <b>Generate if empty:</b> generate breadcrumbs only when it is not already available, so category breadcrumb is preserved (recommended)<br/>
																		  <b>Always generate:</b> overwrite also the category breadcrumbs, so the only breadcrumbs you will get is the one generated by the module<br/></span>';
$_['text_fpp_bypasscat'] = 'Rewrite product path in categories:';
$_['text_fpp_bypasscat_help'] = '<span class="help">If disabled, the product link from categories remains the same in order to preserve normal behaviour and breadcrumbs.<br/>If enabled, the product link from categories is overwritten with path generated by the module.<br>In any case canonical link is updated with good value so google will only see the url generated by the module for a given product.</span>';
$_['text_fpp_directcat'] = 'Category path mode:';
$_['text_fpp_directcat_help'] = 'What kind of link you want to display on your website ?<br/><b>Direct link</b>: /category<br/><b>Full path</b>: /cat1/cat2/category (default)';
$_['text_fpp_homelink'] = 'Rewrite home link:';
$_['text_fpp_homelink_help'] = '<span class="help">Set homepage link to mystore.com instead of mystore.com/index.php?route=common/home</span>';
$_['text_fpp_nolang'] = 'Remove lang tag:';
$_['text_fpp_nolang_help'] = '<span class="help">Remove /en-gb/ from the url, this parameter should be enabled only when using only one language</span>';
$_['text_fpp_noroute'] = 'Remove main route:';
$_['text_fpp_noroute_help'] = '<span class="help">Remove /product/ /category/ etc from the url</span>';
$_['text_fpp_depth']   		= 'Max levels:';
$_['text_fpp_depth_help']   = '<span class="help">Maximum category depth you want to display, for example if you have a product in /cat/subcat/subcat/product and set this option to 2 the link will become /cat/subcat/product<br/>This option works in largest and shortest path modes</span>';
$_['text_fpp_unlimited']   = 'Unlimited';
$_['text_fpp_brand_parent']   = 'Manufacturer parent:';
$_['text_fpp_brand_parent_help']   = '<span class="help">Include the manufacturers inside the manufacturer list url.<br/>For example if your manufacturer list is /brand, the manufacturer apple will appear this way /brand/apple instead of direct /apple</span>';
$_['text_fpp_remove_search']   = 'Remove search/tag parameters:';
$_['text_fpp_remove_search_help']   = '<span class="help">Remove the search or tag parameter (?search=something, ?tag=something) from product urls in search results (products urls only, not search page url itself)</span>';
$_['text_fpp_seo_tag']		= 'Seo tag:<span class="help">For tag urls (route=product/search&tag=something) define the keyword to use for nice url, for example if you set "tag" the result url will be /tag/something</span>, let empty to disable';
$_['entry_category']		= 'Banned categories:<span class="help">Choose the categories that will never be displayed in case of multiple paths</span>';

$_['text_module']        = 'Modules';
$_['text_success']       = 'Success: You have modified the module!';

$_['tab_about']			 = 'About';

// Error
$_['error_permission'] 	 = 'Warning: You do not have permission to modify this module';
?>