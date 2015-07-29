// Tab for option page
function cs_question_meta(){
	$('#set_featured').click(function(e){
		e.preventDefault();
		qa_show_waiting_after(this, false);
		$.ajax({
			data: {
				cs_ajax: true,
				cs_ajax_html: true,
				action: 'set_question_featured',
			},
			dataType: 'html',
			context:this,
			success: function (response) {				
				qa_hide_waiting(this);
				location.reload();
			},
		});		
	});
	
	$('#q_meta_remove_featured_image').click(function(){
		$.ajax({
			data: {
				cs_ajax: true,
				cs_ajax_html: true,
				args: $(this).data('args'),
				action: 'delete_featured_image',
			},
			context:this,
			success: function (response) {
				//$(this).closest('.question-image-container').find('.featured-image').remove();
				$('.image-preview').hide();
				$('#q_meta_remove_featured_image').hide();
				location.reload();
			},
		});	
	});

}

function cs_tab(){
	jQuery('.ra-option-tabs li:first-child').addClass('active');
	jQuery('.ra-option-tabs li a').click(function(e){
		e.preventDefault();
		jQuery('.ra-option-tabs li').removeClass('active');
		jQuery(this).parent().addClass('active');
		var t = jQuery(this).data('toggle');
		jQuery('[class^="qa-part-form-tc-"]').hide();
		jQuery(t).show();
		
	});
}
function dropdown_override() {
	$('.main-menu .dropdown').hover(function() {
			$(this).stop(true, true).addClass('open');

	}, function() {

			$(this).stop(true, true).removeClass('open');

	});

	$('.main-menu .dropdown > a').click(function(){
		
			location.href = this.href;
	});

}
function cs_set_active_sub_nav(elem){
	$(elem).closest('.qa-nav-sub-list').find('li a').removeClass('qa-nav-sub-selected');
	$(elem).addClass('qa-nav-sub-selected');
}

function cs_ajax_sub_menu(elem){
	$(elem).click(function(e){
		e.preventDefault();
		cs_set_active_sub_nav(this);
		
		var url = $(this).attr('href');
		$.get( url, function( data ) {
			var html = $(data).find('.qa-part-q-list form');
			$('.qa-part-q-list').html(html);
			
		});
	});
}

function cs_vote_click(){
	$('body').delegate('.vote-up, .vote-down', 'click', function(){
		cs_ajax_loading(this);
		if (typeof ($(this).data('id')) != 'undefined'){
			var ens=$(this).data('id').split('_');
			var parent = $(this).parent();
			var postid=ens[1];
			var vote=parseInt(ens[2]);
			var code=$(this).data('code');
			var anchor=ens[3];
			
			qa_ajax_post('vote', {postid:postid, vote:vote, code:code},
				function(lines) {
					if (lines[0]=='1') {
						qa_set_inner_html(document.getElementById('voting_'+postid), 'voting', lines.slice(1).join("\n"));
						$('.voting a').tooltip({placement:'bottom'});
						

					} else if (lines[0]=='0') {						
						cs_alert(lines[1]);					
					} else
						qa_ajax_error();
				}
			);	
		}
		return false;
	});	
}
function cs_ajax_loading($elm){
	var position = $($elm).offset();
	var html = '<div id="ajax-loading"></div>';	
	$(html).appendTo('body').ajaxStart(function () {
		$('#ajax-loading').css(position);
		$(this).show();
	});

	$("#ajax-loading").ajaxStop(function () {
		$(this).remove();
	});
}
function cs_favorite_click()
{
	$('body').delegate( '.fav-btn', 'click', function() {
		cs_ajax_loading(this);
		var ens 	=	$(this).data('id').split('_');
		var code	=	$(this).data('code');
		var elem	=	$(this);
		qa_ajax_post('favorite', {entitytype:ens[1], entityid:ens[2], favorite:parseInt(ens[3]), code:code},
			function (lines) {
				if (lines[0]=='1'){
					
					elem.parent().empty().html(lines.slice(1).join("\n"));
					$('.fav-btn').tooltip({placement:'bottom'});
				}else if (lines[0]=='0') {
					alert(lines[1]);
					//cs_remove_process(elem);
				} else
					qa_ajax_error();
			}
		);
		
		//cs_process(elem, false);
		
		return false;
	});
}
function cs_alert($mesasge){
	if($('#ra-alert').length > 0)
		$('#ra-alert').remove();
	var html = '<div id="ra-alert" class="alert fade in"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">&times;</button>'+$mesasge+'</div>';
	$(html).appendTo('body');
	$('#ra-alert').css({left:($(window).width()/2 - $('#ra-alert').width()/2)}).animate({top:'50px'},300);
}
function cs_sparkline(elm){
 	
  	var isRgbaSupport = function(){
		var value = 'rgba(1,1,1,0.5)',
		el = document.createElement('p'),
		result = false;
		try {
			el.style.color = value;
			result = /^rgba/.test(el.style.color);
		} catch(e) {}
		el = null;
		return result;
	};

	var toRgba = function(str, alpha){
		var patt = /^#([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/;
		var matches = patt.exec(str);
		return "rgba("+parseInt(matches[1], 16)+","+parseInt(matches[2], 16)+","+parseInt(matches[3], 16)+","+alpha+")";
	};

	// chart js
	var generateSparkline = function($re){
		$(elm).each(function(){
			var $data = $(this).data();
			if($re && !$data.resize) return;
			if($data.type == 'bar'){
				!$data.barColor && ($data.barColor = "#3fcf7f");
				!$data.barSpacing && ($data.barSpacing = 2);
				$(this).next('.axis').find('li').css('width',$data.barWidth+'px').css('margin-right',$data.barSpacing+'px');
			};
			
			($data.type == 'pie') && $data.sliceColors && ($data.sliceColors = eval($data.sliceColors));
			
			// $data.fillColor && ($data.fillColor.indexOf("#") !== -1) && isRgbaSupport() && ($data.fillColor = toRgba($data.fillColor, 0.5));
			$data.spotColor = $data.minSpotColor = $data.maxSpotColor = $data.highlightSpotColor = $data.lineColor;
			$(this).sparkline( $data.data || "html", $data);

			if($(this).data("compositeData")){
				var $cdata = {};
				$cdata.composite = true;
				$cdata.spotRadius = $data.spotRadius;
				$cdata.lineColor = $data.compositeLineColor || '#a3e2fe';
				$cdata.fillColor = $data.compositeFillColor || '#e3f6ff';
				$cdata.highlightLineColor =  $data.highlightLineColor;
				$cdata.spotColor = $cdata.minSpotColor = $cdata.maxSpotColor = $cdata.highlightSpotColor = $cdata.lineColor;
				isRgbaSupport() && ($cdata.fillColor = toRgba($cdata.fillColor, 0.5));
				$(this).sparkline($(this).data("compositeData"),$cdata);
			};
			if($data.type == 'line'){
				$(this).next('.axis').addClass('axis-full');
			};
		});
	};

	var sparkResize;
	$(window).resize(function(e) {
		clearTimeout(sparkResize);
		sparkResize = setTimeout(function(){generateSparkline(true)}, 500);
	});
	generateSparkline(false);

  }
 /*  
function cs_load_items(){
	var winwidth 	= $(window).width(),
		contwidth 	= $('#site-body').width(),
		ajaxblockwidth 	= winwidth - contwidth;

	if(winwidth > 1170 && ajaxblockwidth > 250){
		$.ajax({
            data: {
				cs_ajax: true,
				cs_ajax_html: true,
				height: $('#site-body').height(),
                action: 'get_ajax_block',
            },
            dataType: 'html',
            context: this,
            success: function (response) {
				$('#ajax-item #ajax-blocks').css('width', (winwidth - contwidth)- 30 );
				$(response).appendTo('#ajax-item #ajax-blocks');
				cs_sparkline('.pieact');
            },
        });
		
	}

} */
/* function cs_ajax_item_resize(){
	var winwidth 	= $(window).width(),
		contwidth 	= $('#site-body').width(),
		ajaxblockwidth 	= winwidth - contwidth;

	if(winwidth > 1170 && ajaxblockwidth > 250){	
		$('#ajax-item #ajax-blocks').css('width', (winwidth - contwidth)- 30 );		
	}else{
		$('#ajax-item #ajax-blocks').hide();
	}

} */

function cs_slide_menu(){

	$(".slide-mobile-menu").on("click", function(e){  
		e.preventDefault();  
		if($(this).hasClass( "open" )) {  
			closeSidepage(); 
		} else {  
			openSidepage();  
		}  
		$(this).toggleClass("open");  
	});

	function openSidepage() {
		$('#nav-top .qa-nav-main').animate({'left':0,'width': 190}, 200);
		$('.left-sidebar').animate({'max-width':180}, 200);
		$('.qa-main').animate({'width': $('.qa-main').width(), 'left':190,'position':'fixed'},200);
		$('.qa-nav-sub').animate({'width': $('.qa-main').width(), 'left':190},200);
		$('body').addClass('menu-open');
	}
	function closeSidepage() {
		$('#nav-top .qa-nav-main').animate({'left':'-180','width': 'auto'}, 200, function(){$(this).removeAttr('style')});
		$('.left-sidebar, #nav-top .qa-nav-main').animate({'max-width':0}, 200, function(){$(this).removeAttr('style')});
		$('.qa-main').animate({'width': 'auto', 'left':10}, 200, function(){$(this).removeAttr('style'); $('body').removeClass('menu-open');});
		$('.qa-nav-sub').animate({'width': 'auto', 'left':10}, 200, function(){$(this).removeAttr('style'); $('body').removeClass('menu-open');});
	}
}
function cs_float_left(){
	var winwidth 	= $(window).width();
	if(winwidth < 980)
		$('.left-sidebar .float-nav').removeAttr('style');
	else
	$(window).scroll(function(){
		var st = $(this).scrollTop();
		
		if(winwidth > 980){
			$('.left-sidebar').each(function(){
				var $this = $(this), 
					offset = $this.offset(),
					h = $this.height(),
					$float = $this.find('.float-nav'),
					floatH = $float.height(),
					topFloat = 0;
				if(st >= offset.top-topFloat){
					$float.css({'position':'fixed', 'top':topFloat+'px'});
				}else if(st < offset.top + h-topFloat - floatH){
					$float.css({'position':'absolute', 'top':0});
				}else{
					$float.css({'position':'absolute', 'top':0});
				}
			})
		}else{
			$('.left-sidebar .float-nav').removeAttr('style');
		}
	});
}

function cs_widgets(){
	$('.position-toggler').click(function(){
		$('.position-canvas').not($(this).parent().next()).hide();
		$(this).parent().next().toggle(0);
		$(this).toggleClass('icon-angle-up icon-angle-down');
	});	
	$('#ra-widgets').delegate('.widget-delete', 'click', function(){
		
		var id = $(this).closest('.draggable-widget').data('id');
		$.ajax({
			url : theme_url+'/inc/ajax.php',
			data: {
				id: id,
				action: 'delete_widget',
			},
			dataType: 'html',
			success: function (response) {
				
			},
		});	
		$(this).closest('.draggable-widget').remove();
	});		
	$('#ra-widgets').delegate('.draggable-widget select, .draggable-widget input, .draggable-widget textarea', 'click', function(){
		var $parent = $(this).closest('.widget-canvas');
		$parent.find('.widget-save').addClass('active');	
	});	
	$('#ra-widgets').delegate('.widget-template-to', 'click', function(){
		var $parent = $(this).closest('.position-canvas');
		$(this).closest('.draggable-widget').find('.select-template').slideToggle(200);
	});	
	$('#ra-widgets').delegate('.widget-options', 'click', function(){
		var $parent = $(this).closest('.position-canvas');
		$(this).closest('.draggable-widget').find('.widget-option').slideToggle(200);
	});	

	
	$('#ra-widgets').delegate('.widget-save.active', 'click', function(){
		var $parent = $(this).closest('.widget-canvas').find('.position-canvas');
		cs_save_widget($parent);
	});
	
	if ($('#ra-widgets').length>0) {
		$('#ra-widgets .widget-list .draggable-widget').draggable({
			connectToSortable: '.position-canvas',
			helper: 'clone',
			handle: '.drag-handle',
			drag: function (e, t) {
				t.helper.width(299);
				t.helper.height(42);
			}
		});

		$('.position-canvas').sortable({
			connectWith: '.column',
			opacity: .35,
			placeholder: 'placeholder',
			handle: '.drag-handle',
			start: function (e, ui) {
				ui.placeholder.height(42);
			},
			stop: function () { 
				$(this).closest('.widget-canvas').find('.widget-save').addClass('active');				
			}
		});
	}
}
function cs_save_widget($elm){
	var widget ={};
	var	locations = {};
	var	options = {};
		
	$elm.find('.draggable-widget').each(function(){		
		var name = $(this).data('name');
		var id = typeof $(this).data('id') == 'undefined' ? 0 : $(this).data('id') ;
		var order = $(this).index();
		var locations = {};
		var options = {};
		widget[order] = {'name' : name, 'id' : id, 'locations':'', 'options':''};
		
		$(this).find('.select-template input').each(function(){
			locations[$(this).attr('name')] = $(this).is(':checked') ? true : false;
		});
		$(this).find('.widget-option input, .widget-option select, .widget-option textarea').each(function(){
			options[$(this).attr('name')] = $(this).val();
		});
		
		widget[order]['locations'] = locations;
		widget[order]['options'] = options;
		
	});

	 $.ajax({
		url : theme_url+'/inc/ajax.php',
		data: {
			position: $elm.data('name'),
			widget_names: JSON.stringify(widget),
			action: 'save_widget_position',
		},
		dataType: 'json',
		context: $elm,
		success: function (response) {
			$.each(response, function(index, item) {
				$elm.find('.draggable-widget').eq(index).data('id', item);
			});
			$elm.closest('.widget-canvas').find('.widget-save').removeClass('active');
		},
	});
}

function cs_ask_box_autocomplete(){
	$( "#ra-ask-search" ).autocomplete({
		source: function( request, response ) {
			$.ajax({
				data: {
					cs_ajax: true,
					cs_ajax_html: true,
					start_with: request.term,
					action: 'get_question_suggestion',
				},
				dataType: 'json',
				context: this,
				success: function (data) {
					response($.map(data, function(obj) {
						return {
							label: obj.title,
							url: obj.url,
							tags: obj.tags,			
							answers: obj.answers,			
							blob: obj.blob			
						};
					}));
				},
			});
		},
		minLength: 3,
		appendTo:".ra-ask-widget",
		messages: {
			noResults: '',
			results: function() {}
		}
	}).data( "uiAutocomplete" )._renderItem = function( ul, item ) {
		var avatar = '';
		if(item.blob != null && item.blob != undefined && item.blob != 'undefined')
			avatar = '<img src="'+item.blob+'" />';
		return $("<li></li>")
		.data("item.uiAutocomplete", item)
		.append('<a href="'+item.url+'" class="">'+avatar+'<span class="title">' + item.label + '</span><span class="tags icon-tags">'+item.tags+'</span><span class="category icon-chat">'+item.answers+'</span></a>')
		.appendTo(ul);
	};

    $('#ra-ask-search').off('keyup keydown keypress');
}

function back_to_top(){
	$("#back-to-top").hide();
	$(function () {
		$(window).scroll(function () {
			if ($(this).scrollTop() > 50) {
				$('#back-to-top').fadeIn();
			} else {
				$('#back-to-top').fadeOut();
			}
		});
		$('#back-to-top').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 500);
			return false;
		});
	});
}

function cs_load_login_register(){
	$('#login-register').not('active').click(function(){
		$.ajax({
			data: {
				cs_ajax: true,
				cs_ajax_html: true,
				action: 'get_login_register',
			},
			dataType: 'html',
			success: function (response) {
				$('.qa-main > .list-c').html(response);
			},
		});
	});
}
function cs_save_image(image){
	$.ajax({
		data: {
			cs_ajax: true,
			cs_ajax_html: true,
			featured_image: image,
			action: 'save_q_meta',
		},
		dataType: 'html',
		success: function (response) {
			//$('.question-image-container').append('<img src="'+response+'" />');
		},
	});	
}

function cs_user_popover(){	
	$('body').on('mouseenter', '.avatar[data-handle]', function( event ) {
		
		if($('.user-popover').is(':visible'))
			$('.user-popover').hide();

		var handle = $(this).data('handle');
		var userid = $(this).data('id');
		var offset = $(this).offset();
		var $this = $(this);
		
		popover_time = setTimeout(function(){
			if($('body').find('#'+userid+'_popover').length == 0 && (handle.length > 0)){
			$this.addClass('mouseover');
				$.ajax({
					type: 'POST',
					data: {
						cs_ajax: true,
						action: 'user_popover',
						handle: handle,
					},
					dataType: 'html',
					context: $this,
					success: function (response) {
						$('body').append(response);
						$('#'+userid+'_popover').position({my: 'center bottom',at: 'center top', of:$this, collision: 'fit flip'});
						$('#'+userid+'_popover').show();
						$this.delay(500).queue(function() {$this.removeClass('mouseover'); $this.dequeue();});
					},
				});
			}else{
				//if($('.user-popover').is(':visible'))
					//$('.user-popover').hide();
				//$(this).addClass('mouseover');	
				$('#'+userid+'_popover').removeAttr('style');
				$('#'+userid+'_popover').position({my: 'center bottom',at: 'center top', of:$this, collision: 'fit flip'});
				$('#'+userid+'_popover').show();
			}
		},500);
	}).on('mouseleave', '.avatar[data-handle]', function( event ) {
		clearTimeout(popover_time);
		var userid = $(this).data('id');
		$('#'+userid+'_popover').hide();
		$(this).removeClass('mouseover');
	});
}

function cs_check_site_status_size(){
	if($('.site-status-inner .bar-float').width() < 160)
		$('.site-status-inner > *').css({'float': 'none', 'width':'100%'});
}
$(document).ready(function(){

	var win_height = $(window).height();
	var main_height = $('#site-body').height() +60;
	
	if( main_height < win_height)
		$('#site-body').css('height', win_height -50);
	
	cs_float_left();	
	dropdown_override();
	cs_slide_menu();
	cs_vote_click();
	cs_favorite_click();
	cs_tab();
	cs_widgets();
	back_to_top();
	cs_question_meta();
	cs_load_login_register();
	cs_user_popover();
	cs_check_site_status_size();
	if ($('.ra-ask-widget').length>0)
		cs_ask_box_autocomplete();
	
	$("#q_meta_remove_featured_image").click(function(e){
		$("#featured_image").val("");
		//$("#image-preview").attr("src",theme_url + "/images/featured-preview.jpg");
		$("#image-preview").hide(500);
	});
	if($("#fileuploader").length){
		$("#fileuploader").uploadFile({
			url:theme_url + "/inc/upload.php",
			allowedTypes:"png,gif,jpg,jpeg",
			fileName:"featured",

			maxFileCount:1,
			multiple:false,
			showDelete: false,
			showAbort:false,
			showDone:false,
			showStatusAfterSuccess:false,
			showProgress :false,
			onSuccess:function(files,data,xhr){
				var u_files = $.parseJSON( data );
				if($('#question-meta').length)
					cs_save_image(u_files[0]);
					
				$("#featured_image").val(u_files[0]);
				$("#image-preview").attr("src",theme_url + "/uploads/"+u_files[0]);
				$("#image-preview").show(500);
				$("#q_meta_remove_featured_image").show(500);
			},
		});
	}
	
	$('.question-label').click(function(){
		$(this).next().slideToggle()
	});
	
	$('.form-search .icon-search').click(function(){
		$('.search-query').focus();
	});
	
	
	$('#left-position .widget-title').click(function(){
		$(this).next().slideToggle(200);
	});
	$('#featured-slider').carousel({
		interval: 10000
	})
	$('.voting a, .fav-btn ').tooltip({placement:'top'});
	
	$(window).resize(function(){
		$('.left-sidebar').removeAttr('style');
		$('.qa-main').removeAttr('style');
		$('#nav-top .qa-nav-main').removeAttr('style');
		cs_check_site_status_size();
	});
	
	cs_sparkline('.sparkline');
});