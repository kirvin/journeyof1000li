(function($){

// gallery/image switch by button #pe2-switch
var pe2_gallery=false;
// falg to album handler for insert shortcode instead of open album's images
var pe2_shortcode=false;
/* state of the code: reflect the header part displayed
 * 	nouser - input for user
 * 	albums - show albums
 *  images - show images from album
 */
var pe2_state='albums';
// picasa user name
var pe2_user_name = 'undefined';
// numbering 
var pe2_current=1;
// save the last request to the server for reload button
var pe2_last_request=false;
var pe2_no_request=false;
// cache for server request both albums and images 
var pe2_cache=[];
var pe2_options = {
    waiting     : 'loading image and text for waiting message',
    env_error   : 'error if editor function can not be found',
    image       : 'label for button Image',
    gallery     : 'label for button Gallery',
    reload      : 'label for button Reload',
    options     : 'label for button Reload',

    uniqid      : 'uniq id for gallery',

    thumb_w     : 150,      //'thumbnail width',
    thumb_h     : 0,        //'thumbnail height',
    thumb_crop  : false,    //'exact dimantions for thumbnail',

    state       : 'state by default or saved'
};

// variable to store options that are updated temporarily during this insert
var pe2_updated_options=[];

$(function() {
	// convert encoded options
	for ( var i in window['pe2_options'] ) {
		if (window['pe2_options'][i]=='0'||window['pe2_options'][i]=='') pe2_options[i]=false;
		else pe2_options[i] = decodeURIComponent(window['pe2_options'][i]);
	}
    
    // get username
    pe2_user_name = pe2_options.pe2_user_name;
    $('#pe2-user').text(pe2_user_name);
    // restore state
    if ('images' == pe2_options.state) {
        $('#pe2-albums').show().siblings('.pe2-header').hide();
        pe2_request({
            action: 'pe2_get_images',
            guid: pe2_options.pe2_last_album
        });
    } else {
        pe2_switch_state(pe2_options.state);
    }

    // set options unchanged handlers
    $('#pe2-options input').change(function() {
		// options page input value changed
        var name = $(this).attr('name');
        if (($(this).attr('type') == 'text') || ($(this).attr('type') == 'hidden')) {
			// this is a text or hidden input, we can just get the value
            pe2_updated_options[name] = $(this).val();
            pe2_options[name] = $(this).val();
        } else {
			// we only set the value if this element is checked
            if ($(this).attr('checked')){
                pe2_updated_options[name] = $(this).val();
                pe2_options[name] = $(this).val();
            }else{
                pe2_updated_options[name] = false;
                pe2_options[name] = false;
			}
        }
    });
    $('#pe2-options select').change(function() {
		// options page selection box value changed
        var name = $(this).attr('name');
        pe2_updated_options[name] = $(this).val();
        pe2_options[name] = $(this).val();
    });

	// the form unchanged handler
	$('#pe2-nouser form').submit(pe2_change_user);
});

$(document).ajaxError(function(event, request, settings, error) {
    console.log("Error requesting page " + settings.url+'\nwith data: '+settings.data+'\n'+error);
});

function pe2_switch_state(state){
	pe2_state = state;
	$('#pe2-'+state).show().siblings('.pe2-header').hide();
	pe2_set_handlers();
}

function pe2_save_state(last_request) {
    if (pe2_options.pe2_save_state) {
        $.post(ajaxurl, {
            action: 'pe2_save_state',
            state:pe2_state,
            last_request:last_request
        });
    }
}

function pe2_set_handlers() {

	$('.button').unbind();
	
	$('.pe2-reload').click(function(){
		if (pe2_last_request) {
			if (pe2_state != 'albums') $('#pe2-albums').show().siblings('.pe2-header').hide();
			pe2_cache[pe2_serialize(pe2_last_request)] = false;
			pe2_request(pe2_last_request);
		}
		return(false);
	});

    $('.pe2-options').toggle(
        function(){
            $('#pe2-options').slideDown('fast');
            pe2_show_options();
            return(false);
        },function(){
            $('#pe2-options').slideUp('fast');
            pe2_show_options();
            // handle exceptions
            if (pe2_gallery && !(pe2_options['pe2_link'].indexOf('thickbox') != -1 || pe2_options.pe2_link == 'lightbox' || pe2_options.pe2_link == 'highslide')) {
                $('#pe2-switch').click();
            }
            return(false);
    });
    pe2_show_options();

	switch (pe2_state) {
		case 'nouser':
			$('#pe2-change-user').click(pe2_change_user);
			$('#pe2-cu-cancel').click(function() {
				pe2_switch_state('albums');
				return(false);
			});
			$('#pe2-main').empty();
		break;
		
		case 'albums':
			$('#pe2-user').click(function(){
				$('#pe2-nouser input').val(pe2_user_name);
				pe2_switch_state('nouser');
				return(false);
			});
			$('#pe2-switch2').click(function() {
                pe2_shortcode = pe2_shortcode?false:true;
                $(this).text((pe2_shortcode)?pe2_options.shortcode:pe2_options.album);
				return(false);
			});
            pe2_get_albums();
		break;
		
		case 'images':
			pe2_current=1;
			$('#pe2-switch').click(function() {
				if (pe2_gallery || (pe2_options['pe2_link'].indexOf('thickbox') != -1) || pe2_options.pe2_link == 'lightbox' || pe2_options.pe2_link == 'highslide') {
					pe2_gallery = pe2_gallery?false:true;
					pe2_current=1;
					$('#pe2-main td.selected').removeClass('selected').click();
				}
                $(this).text((pe2_gallery)?pe2_options.gallery:pe2_options.image);
				return(false);
			});
			$('#pe2-album-name').click(function(){
				pe2_switch_state('albums');
				return(false);
			});
			$('#pe2-insert').click(function(){
				// hide the insert button so it cannot be clicked again (since the
				// ajax for processing the shortcodes might take a couple of secs)
				$('#pe2-insert').hide();

				// save our current state
                pe2_save_state(pe2_last_request.guid);

				// grab the shortcode
				var shortcode = pe2_make_image_shortcode('#pe2-main td.selected');

				// check to see if we need to add the shortcode to the editor
				// OR perform the ajax request to process the shortcode into
				// HTML to return HTML instead
				if(pe2_options['pe2_return_single_image_html'] == true){
					// use ajax to send the generated shortcode back to the
					// plugin for processing into HTML, then return the
					// returned HTML to the editor instead of the shortcode
					$.ajax({
						url: ajaxurl, 
						data: { action: 'pe2_process_shortcode', data: shortcode },
						type: 'POST',
						success: function(data){
							// throw the returned data into the editor
							if(data['error']){
								// we had an error
								alert(data['error']);
							}else if(data['html']){
								// we're ok
								pe2_add_to_editor(data['html']);
								return(false);
							}else{
								// hmm, not sure what happened
								alert('Error, no data returned');
							}
						}, // end success function
						dataType: 'json'
					});
				}else{
					// this is a standard insert of the shortcode into the editor
					pe2_add_to_editor(shortcode);
					return(false);
				}
			}).hide();
		break;
	}
}

function pe2_change_user() {
	pe2_user_name = $('#pe2-nouser input').val();
	$('#pe2-user').text(pe2_user_name);
	pe2_switch_state('albums');
    pe2_save_state(pe2_user_name);
	return(false);
}

function pe2_request(data) {
	
	if (pe2_no_request) return;
	pe2_no_request = true;
	$('.pe2-reload').hide();
	
	pe2_last_request = data;
	var callback = (data.action=='pe2_get_gallery')?pe2_albums_apply:pe2_images_apply;
	
	if (pe2_cache[pe2_serialize(data)]) {
		callback(pe2_cache[pe2_serialize(data)]);
	} else {
		// set progress image
		$('#pe2-message2').html(pe2_options.waiting);	

		data['cache'] = pe2_serialize(data);
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(ajaxurl, data, callback,'json');
	}	
}

function pe2_get_albums(){

	pe2_request({
		action: 'pe2_get_gallery',
		user: pe2_user_name
	});
}

function pe2_album_handler(){

    var guid = $('a',this).attr('href').replace(/^[^#]*#/,'');

    if (pe2_shortcode) {
		// inserting the shortcode for the album now.  first, nab the
		// tags options
		var include_featured = $('#pe2_featured_tag:checked').val();
		var additional_tags = $('#pe2_additional_tags').val();
		if(include_featured != null){
			// include only featured tagged photos
			var tags = 'Featured';
		}
		if((additional_tags != undefined) && (additional_tags.length > 0)){
			// include additional tags
			if(tags == undefined){
				var tags = '';
			}else{
				tags += ',';
			}
			tags += additional_tags.replace(/\s/g, '+');
		}

		// generate the short code
		var shortcode = '[pe2-gallery album="'+guid+'"';
		if(tags != undefined){
			// add our tags query string to the string returned
			shortcode += ' tags="' + tags + '"';
		}

		// initialize the attributes to add
		var attributes = ' ';

		// go through any updated options and add the attributes
		for(updated in pe2_updated_options){
			// go through any elements of the pe2_updated_options array and add
			// those to our attributes
			if((updated.indexOf('_size_mode') > 0) || (updated.indexOf('_size_dimension') > 0) || (updated.indexOf('_size_crop') > 0)){
				// this is one of the fields used to calculate the single image/video
				// size, we want to ignore them
			}else if((updated == 'pe2_featured_tag') || (updated == 'pe2_additional_tags')){
				// this is one of the tag options that we've already handled above,
				// skip it
			}else{
				// this is an attribute we need to include in the shortcode
				attributes += updated + '="' + pe2_updated_options[updated] + '" ';
			}
		}// end looping through any updated options

		// add any updated attributes to the shortcode
		shortcode += attributes + ']';

		// now save state and return the data to the editor
        pe2_save_state(pe2_user_name);
		
		// add the short code to the editor
        pe2_add_to_editor(shortcode);
    } else {
        pe2_request({
            action: 'pe2_get_images',
            guid: guid
        });
    }
    
	return(false);
}

function pe2_show_reload() {
	pe2_no_request = false;
	$('.pe2-reload').show().text(pe2_options.reload);
}

function pe2_show_options() {
	$('.pe2-options').show().text(pe2_options.options);
}

function pe2_albums_apply(response) {
	
	pe2_show_reload();
	
	if (response.error) {
		$('#pe2-nouser input').val(pe2_user_name);
		pe2_switch_state('nouser');
		$('#pe2-message1').text(response.error);
		return;
	}

	pe2_cache[response.cache] = response;

	$('#pe2-main').html(response.data);
	$('#pe2-message2').text(response.title);
	document.body.scrollTop=0;

	$('#pe2-main td').unbind().click(pe2_album_handler);
	// state switched before request
}

function pe2_images_apply(response) {
	
	pe2_show_reload();
	
	if (response.error) {
		$('#pe2-message2').text(response.error);
		return;
	}

	pe2_cache[response.cache] = response;

	$('#pe2-main').html(response.data);
	$('#pe2-album-name').text(response.title);
	document.body.scrollTop=0;

	$('#pe2-main td').unbind().click(pe2_image_handler);
	pe2_switch_state('images');
}
	
function pe2_image_handler(){
	if (pe2_options.pe2_gal_order) {
		if ($(this).hasClass('selected')) {
			var current = Number($('div.numbers',this).html());
			$('div.numbers',this).remove();
			// decrement number for rest if >current
			$('#pe2-main td.selected').each(function(){
				var i = Number($('div.numbers',this).html());
				if (i>current) $('div.numbers',this).html(i-1);
			});
			pe2_current--;
		} else {
			$(this).prepend("<div class='numbers'>"+pe2_current+"</div>");
			pe2_current++;
		}
	}
	
	$(this).toggleClass('selected');

	// check selected to show/hide Insert button
	if ($('#pe2-main td.selected').length==0) $('#pe2-insert').hide();
	else $('#pe2-insert').show();
	
	return(false);
}

function pe2_serialize(data) {
	function Dump(d,l) {
	    if (l == null) l = 1;
	    var s = '';
	    if (typeof(d) == "object") {
	        s += typeof(d) + " {\n";
	        for (var k in d) {
	            for (var i=0; i<l; i++) s += "  ";
	            s += k+": " + Dump(d[k],l+1);
	        }
	        for (var i=0; i<l-1; i++) s += "  ";
	        s += "}\n";
	    } else {
	        s += "" + d + "\n";
	    }
	    return s;
	}
	return Dump(data);
}

function pe2_add_to_editor(data) {
	var win = window.dialogArguments || opener || parent || top;
	if (win['send_to_editor']) win.send_to_editor(data);
	else {
		alert(pe2_options.env_error);
		tb_remove();
	}
}

String.prototype.trim = function() {
    var s=this.toString().split('');
    for (var i=0;i<s.length;i++) if (s[i]!=' ') break;
    for (var j=s.length-1;j>=i;j--) if (s[j]!=' ') break;
    return this.substring(i,j+1);
}

String.prototype.escape = function() {
    var s = this.toString();
    s = s.replace(/&/g, "&amp;");
    s = s.replace(/>/g, "&gt;");
    s = s.replace(/</g, "&lt;");
    s = s.replace(/"/g, "&quot;");
    s = s.replace(/'/g, "&#039;");
    return s;
}

function pe2_make_image_shortcode(case_selector) {

	var codes=[], code, icaption, ihref, isrc, ialt, ititle, ilink, iorig, item_type;

	// begin the attributes to add to the shortcode
	var attributes = '';

	// go through any updated options and add the attributes
	for(updated in pe2_updated_options){
		// go through any elements of the pe2_updated_options array and add
		// those to our attributes
		if((updated.indexOf('_size_mode') > 0) || (updated.indexOf('_size_dimension') > 0) || (updated.indexOf('_size_crop') > 0)){
			// this is one of the fields used to calculate the single image/video
			// size, we want to ignore them
		}else{
			// this is an attribute we need to include in the shortcode
			attributes += updated + '="' + pe2_updated_options[updated] + '" ';
		}
	}// end looping through any updated options

	// selection order
	var order = (pe2_options.pe2_gal_order);

	// define our codes array that we'll generate next
	var codes = [];

	// go through each selected image and add the shorttags
	$(case_selector).each(function(i){
		// for each image in our selecctions, grab the necessary info storing
		// the URLs, captions, etc
		icaption = $('span',this).text().escape(); // ENT_QUOTES
		ihref    = $('a',this).attr('href');
		isrc     = $('img',this).attr('src');
		ialt     = $('img',this).attr('alt');
		ilink    = $('a',this).attr('href');
		item_type = $('img',this).attr('type');

		// create the shortcode adding in any common attributes overridden by
		// the "Options" page in the image selection window
		code = '[pe2-image src="' + isrc + '" href="' + ihref + '" caption="' + 
			icaption + '" type="' + item_type + '" alt="' + ialt + '" ' + attributes + ']';

		// add this image's shortcode to our array of image shortcodes to join together
		if (order) {
			codes[Number($('div.numbers',this).html())] = code;
		} else {
			codes.push(code);
		}
	});

	// join all of the selected images together
	if (pe2_gallery) {
		//FIXME - this logic is not complete nor tested.  The shortcode processing
		// in picasa-express2.php needs to change the thumbnail size to the album 
		// thumb size when the pe2-image shortcodes are wrapped with the pe2-gallery
		// shortcode, and full testing needs completed
		
		// join all of the codes together inside the gallery tag
		codes = codes.join('');

        var gal_css = [pe2_options.pe2_gal_css || '',((pe2_options.pe2_gal_align!='none') && 'align'+pe2_options.pe2_gal_align) || ''].join(' ').trim();

        codes = '[pe2-gallery%css_style%]\n%images%[/pe2-gallery]'.replace(/%(\w+)%/g,function($0,$1){
            switch ($1) {
                case 'css_style':
                    var a = [(gal_css && 'class="'+gal_css+'"') || '',(pe2_options.pe2_gal_style && 'style="'+pe2_options.pe2_gal_style+'"') || ''].join(' ').trim();
                    return (a && ' '+a+' ');
                case 'images':
                    return codes;
            }
        });
	} else {
		// we're not creating a "gallery" of individual images, so simply join
		// all of the selected image codes together, separating by two character
		// returns to return to the editor
        codes = codes.join("\n\n")+' ';
    }
	
	// determine if we're adding our automatic clear after the group of images that
	// we just created
	if(pe2_options.pe2_auto_clear){
		// add the clear
		codes = codes + "\n\n<p class=\"clear\"></p>\n\n";
	}

	// return our formatted codes
    return codes;
}// end function pe2_make_image_shortcode(..)


})(jQuery);
