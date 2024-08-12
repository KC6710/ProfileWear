/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

$(document).ready(function(){
	if (getURLVar('adding_attribute_value') !== '' && ocme.utils().versionCompare(ocme.config('oc_version'), '4', '>=')) {
		Swal.fire({
			html: ocme.trans('text_to_add_value_please_select_attribute'),
			icon: 'info'
		});
	}
	
	$('#ocme-app input[type=checkbox][name^=selected]').each(function(){
		var attribute_id = $(this).val(),
			adding_attribute_value = getURLVar('adding_attribute_value') !== '',
			url = ocme.config('url.attribute_values') + '&filter_attribute_id=' + attribute_id,
			route = 'attribute/edit',
			$link;

		if (ocme.utils().versionCompare(ocme.config('oc_version'), '4', '>=')) {
			route = 'attribute.form';
		}
			
		if( adding_attribute_value ) {
			url += '&adding_attribute_value=1';
		}
		
		$link = $('<a class="btn btn-primary" data-toggle="tooltip">')
			.attr('href', url)
			.attr('title', ocme.trans('text_values'))
			.append($('<i class="fa">')
				.addClass( 'fa-' + ( adding_attribute_value ? 'plus' : 'list' ) )
			);
		
		$(this).closest('tr').find('a[href*="' + route + '"]:first').before( $link );
		
		$link.tooltip().after(' ');
	});
});