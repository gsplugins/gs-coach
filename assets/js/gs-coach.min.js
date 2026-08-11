jQuery(function($) {

	"use strict";

	var is_rtl = $('html').attr('dir') == "rtl";
	var popupEventsAdded = false;

	var filterVals;

	// Multi Select Class
	function load_multi_select_class() {

		function GS_Multi_Select( $widget, options ) {
	
			this.$widget = $widget;

			this.config = $.extend( {}, {
				ellipsis: false
			}, options );
	
			this.$wrapper = this.$widget.closest( '.gs_coach_area' );

			this.init();
	
			return this;
	
		}
		
		GS_Multi_Select.prototype.init = function() {
	
			var $selects = this.$wrapper.find('.search-filter select');
			var _this = this;

			if ( ! $selects.length ) return;

			$selects.each(function() {

				_this.build_multi_select( $(this) );

			});

			$('body').on( 'change', '.gs-multi-select--wrapper input[type=checkbox]', function( event ) {
				_this.update_value( $(event.target).closest('.gs-multi-select--wrapper'), $(event.target) );
			});

			// Clear All
			$('body').on( 'click', '.gs-mu-select--has-value .gs-multi-select--arrow', function( event ) {
				event.stopPropagation();
				$(event.target).closest('.gs-multi-select--wrapper')
					.removeClass('gs-select--open')
					.find('input[type=checkbox]').prop('checked', false).first().prop('checked', true).trigger('change');
			});

		}
		
		GS_Multi_Select.prototype.build_multi_select = function( $select ) {

			$select.prop( 'multiple', true );

			var options = $select.find('option').map(function() {

				return `<div class="gs-multi-select--single">
					<label>
						<input style="display: none !important;" type="checkbox" data-text="${ $(this).text() }" value="${ $(this).val() }" ${ $(this).is(':checked') ? 'checked' : '' } />
						<span class="gs-select--indicator"></span>
						<span class="gs-select--text">${ $(this).text() }</span>
					</label>
				</div>`;

			}).toArray().join('');

			var optionsWrapper = '<div class="gs-multi-select--options">' + options + '</div>';

			var multiSelectBar = `<div class="gs-multi-select--bar"><span class="gs-multi-select--value ${this.config.ellipsis ? 'gs-text-ellipsis' : '' }">${$select.find('option:selected').text()}</span><span class="gs-multi-select--arrow"></span></div>`;

			var html = '<div class="gs-multi-select--wrapper">' + multiSelectBar + optionsWrapper + '</div>';

			$select.hide().after( $(html) );

		}
		
		GS_Multi_Select.prototype.update_value = function( $currentWrapper, $item ) {

			var $allItems 		= $currentWrapper.find('input[type=checkbox]');
			var $starItem 		= $allItems.filter('[value="*"]');
			var $checkedItems 	= $allItems.filter(':checked');
			var $selectElem 	= $currentWrapper.siblings('select');

			var values = [];
			var texts = [];
			var _text = '';
			
			// If user click on * item, then deselect all other items
			if ( $item.val() == '*' && $item.is(':checked') ) {
				$allItems.not( $item ).prop( 'checked', false );
				$currentWrapper.removeClass('gs-mu-select--has-value');
			}
			
			// If user click on non * item, then deselect the * item
			if ( $starItem.length && $item.val() != '*' && $item.is(':checked') ) {
				$starItem.prop( 'checked', false );
				$currentWrapper.addClass('gs-mu-select--has-value');
			}
			
			// If user deselect all items, then select the * item
			if ( $starItem.length && !$checkedItems.length ) {
				$starItem.prop( 'checked', true );
				$currentWrapper.removeClass('gs-mu-select--has-value');
			}

			// rerender the checked items
			$checkedItems = $checkedItems = $allItems.filter(':checked');

			// Populate Values & Texts
			$checkedItems.each(function() {
				values.push( $(this).val() );
				texts.push( $(this).data('text') );
			});

			// Update the native select element
			$selectElem.children().each(function() {
				$(this).prop( 'selected', values.indexOf( $(this).val() ) > -1 );
			}).end().trigger('change');

			// Generate the text to display
			if ( texts && texts.length ) {

				if ( this.config.ellipsis ) {
					_text = texts.join(', ');
				} else {
					_text = texts[0];
					if ( texts.length > 1 ) _text = _text + ' (+' + (texts.length-1) + ')';
				}

			}

			// Update the text in dom
			$currentWrapper.find('.gs-multi-select--bar .gs-multi-select--value').text( _text );

		}
	
		$.fn.gs_multi_select = function( options ) {
			return new GS_Multi_Select( $(this).first(), options );
		}

		$('body').on( 'click', function() {
			$('.gs-multi-select--wrapper').removeClass('gs-select--open');
		});

		$('body').on( 'click', '.gs-multi-select--wrapper', function(event) {
			event.stopPropagation();
		});

		$('body').on( 'click', '.gs-multi-select--wrapper .gs-multi-select--bar', function() {
			var $parent = $(this).parent();
			$('.gs-multi-select--wrapper').not($parent).removeClass('gs-select--open');
			$parent.toggleClass('gs-select--open');
		});
	
	}

	// Filter Class
	function load_filter_class() {

		function GS_Coach_filter( $widget ) {
	
			this.$widget = $widget;
	
			this.$wrapper = this.$widget.closest( '.gs_coach_area' );

			this.wrapperOptions = $.extend({}, {
				search_through_all_fields : 'off'
			}, this.$wrapper.data('options'));
	
			this.filters = {};

			const is_ajax_filter = $(".search-filter").hasClass('search-filter-ajax');

			if ( is_ajax_filter ) {
				this.setFilterEventsAjax();
			} else {
				this.initIsotope();
		
				this.setFilterEvents();
			}

			this.$widget.data( 'gs-coach-filter', this );
	
			return this;
	
		}
		
		GS_Coach_filter.prototype.initIsotope = function() {

			if ( ! this.filters.group ) {
				this.filters.group = this.$wrapper.find('.gs-coach-filter-cats > li.filter').first().addClass('active').children('a').data('filter');
			}
	
			this.$filter_widget = this.$widget.gs_isotope({
				itemSelector: '.gs-filter-single-item',
				layoutMode: 'fitRows',
				originLeft: !is_rtl,
				filter: this.isotopeFilter.bind(this)
			});
	
		}
		
		GS_Coach_filter.prototype.isotopeFilter = function( index, item ) {
	
			var $item = $(item);

			if ( $item.hasClass('gs-filter--group-title') || $item.hasClass('gs-filter--group-divider') ) return true; // skip filtering the group title and group divider
			
			var searchString = this.filters.search;
			var searchFilter = true;

			if ( searchString ) {

				searchString = searchString.toLocaleLowerCase().replaceAll('-', ' ').replaceAll('_', ' ');

				if ( $item.find('.gs-coach-name').length ) {
					searchFilter = $item.find('.gs-coach-name').text().toLocaleLowerCase().match( searchString );
				}

				if ( !searchFilter && this.wrapperOptions.search_through_all_fields == 'on' ) {

					let itemClasses = $item.attr('class');

					if ( itemClasses ) {
						itemClasses = $item.attr('class').toLocaleLowerCase().replaceAll('-', ' ').replaceAll('_', ' ');
						searchFilter = searchString ? itemClasses.match( searchString ) : true;
					}

				}

			}
			
			// Company Search
			var companySearchString = this.filters.companySearch;
			var companySearchFilter = true;
			if ( companySearchString ) {
				companySearchString = companySearchString.toLocaleLowerCase();
				if ( $item.find('.gs-coach-coach--company').length ) {
					companySearchFilter = $item.find('.gs-coach-coach--company').text().toLocaleLowerCase().match( companySearchString );
				}
			}
			
			// Zip Search
			var zipSearchString = this.filters.zipSearch;
			var zipSearchFilter = true;
			if ( zipSearchString ) {
				zipSearchString = zipSearchString.toLocaleLowerCase();
				if ( $item.find('.gs-coach-coach--zip-codes').length ) {
					zipSearchFilter = $item.find('.gs-coach-coach--zip-codes').text().toLocaleLowerCase().match( zipSearchString );
				}
			}
			
			// Tag Search
			var tagSearchString = this.filters.tagSearch;
			var tagSearchFilter = true;
			if ( tagSearchString ) {
				tagSearchString = tagSearchString.toLocaleLowerCase();
				if ( $item.find('.gs-coach-coach--tags').length ) {
					tagSearchFilter = $item.find('.gs-coach-coach--tags').text().toLocaleLowerCase().match( tagSearchString );
				}
			}
	
			var filters = [ searchFilter, companySearchFilter, zipSearchFilter, tagSearchFilter, this.filters.group ? $item.is( this.filters.group ) : true ];

			var filters2 = [this.filters.designation, this.filters.location, this.filters.language, this.filters.gender, this.filters.specialty, this.filters.extra_one, this.filters.extra_two, this.filters.extra_three, this.filters.extra_four, this.filters.extra_five].map(function( _filter ) {

				if ( !_filter ) return true;

				if ( typeof _filter !== 'object' ) return $item.is( _filter );

				if ( !_filter.length ) return true;

				return _filter.some(function( _fil ) { return $item.is( _fil ) });

			});
			
			return filters.concat(filters2).every(function( _filter ) {
				return !! _filter;
			});
	
		}
		
		GS_Coach_filter.prototype.setFilterEvents = function() {
	
			var _this = this;
	
			this.$wrapper.find('.gs-coach-filter-cats').on( 'click', 'a', function() {
				_this.filters.group = $(this).data('filter');
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-designation').on( 'change', function() {
				_this.filters.designation = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-location').on( 'change', function() {
				_this.filters.location = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-language').on( 'change', function() {
				_this.filters.language = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-gender').on( 'change', function() {
				_this.filters.gender = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-specialty').on( 'change', function() {
				_this.filters.specialty = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-extra_one').on( 'change', function() {
				_this.filters.extra_one = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-extra_two').on( 'change', function() {
				_this.filters.extra_two = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-extra_three').on( 'change', function() {
				_this.filters.extra_three = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-extra_four').on( 'change', function() {
				_this.filters.extra_four = $(this).val();
				_this.refreshIsotope();
			});
		
			this.$wrapper.find('.filters-select-extra_five').on( 'change', function() {
				_this.filters.extra_five = $(this).val();
				_this.refreshIsotope();
			});
		
			// use value of search field to filter
			var $search_by_name = this.$wrapper.find('.search-by-name');
			$search_by_name.on( 'keyup', debounce(function() {
				_this.filters.search = $search_by_name.val();
				_this.refreshIsotope();
			}) );
			$search_by_name.on( 'change', function() {
				_this.filters.search = $search_by_name.val();
				_this.refreshIsotope();
			});
		
			// use value of search field to filter
			var $search_by_company = this.$wrapper.find('.search-by-company');
			$search_by_company.on( 'keyup', debounce(function() {
				_this.filters.companySearch = $search_by_company.val();
				_this.refreshIsotope();
			}) );
			$search_by_company.on( 'change', function() {
				_this.filters.companySearch = $search_by_company.val();
				_this.refreshIsotope();
			});
		
			// use value of search field to filter
			var $search_by_tag = this.$wrapper.find('.search-by-tag');
			$search_by_tag.on( 'keyup', debounce(function() {
				_this.filters.tagSearch = $search_by_tag.val();
				_this.refreshIsotope();
			}) );
			$search_by_tag.on( 'change', function() {
				_this.filters.tagSearch = $search_by_tag.val();
				_this.refreshIsotope();
			});
		
			// change is-checked class on buttons
			this.$wrapper.find('.gs-coach-filter-cats').on( 'click', 'a', function(e) {
				// e.preventDefault();
				$(this).parents('.gs-coach-filter-cats').find('li').removeClass('active');
				$(this).parent('li').addClass('active');
			});

			$(window).on( 'load', function() {
				_this.refreshIsotope();
				setTimeout(function() {
					_this.refreshIsotope();
				}, 200 );
			});
		
		}

		GS_Coach_filter.prototype.renderAjaxTemplate = function() {

			// console.log(this.$widget);
			// console.log(this.$wrapper);

			const gsCoachArea = this.$wrapper;
			const shortcodeId = gsCoachArea.data('shortcode-id');
			const loader = gsCoachArea.find('.gs-coach-filter-loader-spinner');
			
			const gsCoachAreaID = $(`#gs_coach_area_${shortcodeId}`);

			const initialItems = gsCoachAreaID.data('options').initial_items;

			let postsPerPage;

			let paginationDiv = $(`#gs-coach-pagination-wrapper-${shortcodeId}`);

			const isAjaxPagination = paginationDiv.find(`#gs-coach-ajax-pagination-${shortcodeId}`).length > 0;
			const isLoadMoreButton = paginationDiv.find(`#gs-coach-load-more-button-${shortcodeId}`).length > 0;
			const isLoadMoreScroll = paginationDiv.find(`#gs-coach-load-more-scroll-${shortcodeId}`).length > 0;

			const isPaginationDisabled = !isAjaxPagination && !isLoadMoreButton && !isLoadMoreScroll;

			if( isPaginationDisabled ){
				postsPerPage = -1;
			} else if ( isAjaxPagination ) {
				postsPerPage = gsCoachAreaID.data('options').coach_per_page;
				paginationDiv = paginationDiv.find(`#gs-coach-ajax-pagination-${shortcodeId}`);
			} else if ( isLoadMoreButton ) {
				postsPerPage = gsCoachAreaID.data('options').load_per_click;
				paginationDiv = paginationDiv.find(`#gs-coach-load-more-button-${shortcodeId}`);
			} else if ( isLoadMoreScroll ) {
				postsPerPage = gsCoachAreaID.data('options').per_load;
				paginationDiv = paginationDiv.find(`#gs-coach-load-more-scroll-${shortcodeId}`);
			}

	
			$.ajax({
				url: GSCoachData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'gscoach_filter_coaches',
					_ajax_nonce: GSCoachData.nonce,
					shortcode_id: shortcodeId,
					filters: this.filters,
					posts_per_page: postsPerPage
				},
				beforeSend: function() {
					gsCoachAreaID.find('.gs_coach').hide();
					paginationDiv.hide();
					loader.show();
				}
			})
			.done(response => {
	
				let dataCoaches = $.parseHTML( response.data.coaches );
				let coachDivs = $(dataCoaches).find('.gs_coach');

				setTimeout(() => {
					loader.hide();
					gsCoachAreaID.find('.gs_coach').replaceWith(coachDivs);

					if( response.data.foundCoaches <= initialItems ){

						$(this.$wrapper).find('#gs-coach-load-more-coach-btn').hide();
						// paginationDiv.hide();
						if( isLoadMoreScroll ){
							paginationDiv.hide();
						}
						
					} else{

						$(this.$wrapper).find('#gs-coach-load-more-coach-btn').show();
						paginationDiv.show( response.data.pagination );

						if( isAjaxPagination ){
							paginationDiv.html( response.data.pagination );
						}

						if( isLoadMoreScroll ){
							initGSCoachScrollLoader();
						}

						paginationDiv.show();
					}
				}, 500);
	
			});
		}
		
		GS_Coach_filter.prototype.setFilterEventsAjax = function() {

			filterVals = this.filters;
	
			var _this = this;
	
			this.$wrapper.find('.gs-coach-filter-cats').on( 'click', 'a', function() {
				const filterGroup = ($(this).data('filter') === '*') ? '' : $(this).data('filter');
				_this.filters.group = filterGroup.replace('.', '');
				_this.renderAjaxTemplate();
			});
					
			this.$wrapper.find('.filters-select-designation').on( 'change', function() {
				const designationText = $('.filters-select-designation option:selected').text();
				_this.filters.designation = ( designationText === 'Show All Designation' ) ? '' : designationText ;
				_this.renderAjaxTemplate();
			});
					
			this.$wrapper.find('.filters-select-language').on( 'change', function() {
				const filterLanguage = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.language = filterLanguage.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			this.$wrapper.find('.filters-select-location').on( 'change', function() {
				const filterLocation = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.location = filterLocation.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			this.$wrapper.find('.filters-select-gender').on( 'change', function() {
				const filterGender = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.gender = filterGender.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			this.$wrapper.find('.filters-select-specialty').on( 'change', function() {
				const filterSpecialty = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.specialty = filterSpecialty.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			this.$wrapper.find('.filters-select-extra_one').on( 'change', function() {
				const filterExtraOne = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.extra_one = filterExtraOne.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			this.$wrapper.find('.filters-select-extra_two').on( 'change', function() {
				const filterExtraTwo = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.extra_two = filterExtraTwo.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			this.$wrapper.find('.filters-select-extra_three').on( 'change', function() {
				const filterExtraThree = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.extra_three = filterExtraThree.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			this.$wrapper.find('.filters-select-extra_four').on( 'change', function() {
				const filterExtraFour = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.extra_four = filterExtraFour.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			this.$wrapper.find('.filters-select-extra_five').on( 'change', function() {
				const filterExtraFive = $(this).val() === '*' ? '' : $(this).val();
				_this.filters.extra_five = filterExtraFive.replace('.', '');
				_this.renderAjaxTemplate();
			});
		
			// use value of search field to filter
			var $search_by_name = this.$wrapper.find('.search-by-name');
			$search_by_name.on( 'keyup', debounce(function() {
				_this.filters.search = $search_by_name.val();
				_this.renderAjaxTemplate();
			}) );
		
			// use value of search field to filter
			var $search_by_tag = this.$wrapper.find('.search-by-tag');
			$search_by_tag.on( 'keyup', debounce(function() {
				_this.filters.tagSearch = $search_by_tag.val();
				_this.renderAjaxTemplate();
			}) );

			this.$wrapper.find('.gs-coach-filter-cats > li.filter').first().addClass('active')
		
			// change is-checked class on buttons
			this.$wrapper.find('.gs-coach-filter-cats').on( 'click', 'a', function(e) {
				// e.preventDefault();
				$(this).parents('.gs-coach-filter-cats').find('li').removeClass('active');
				$(this).parent('li').addClass('active');
			});
		
		}
		
		GS_Coach_filter.prototype.refreshIsotope = function() {
			
			this.$filter_widget.gs_isotope();
	
		}
	
		$.fn.gs_coach_filter = function() {
			return new GS_Coach_filter( $(this).first() );
		}
	
	}

	// Reset Filters Button
	function gs_filter_clear_filter_button( $wrapper, options ) {

		var config = $.extend( {}, {
			multiSelect: false,
			resetFilterText: 'Reset Filters'
		}, options);

		var $parent = $wrapper.closest('.gs_coach_area');
		var $search_filter_area = $parent.find('.search-filter');
		var $clear_filter_button_area;

		if ( ! $search_filter_area.length ) return;

		if ( !$clear_filter_button_area || !$clear_filter_button_area.length ) {
			$clear_filter_button_area = $('<div class="gs-clear-filter-button--area"><span class="gs-clear-filter-button">'+config.resetFilterText+'</span></div>').insertAfter( $search_filter_area );
		}

		$clear_filter_button_area.find('.gs-clear-filter-button').on('click', function() {

			$parent.find('.gs-coach-filter-cats li.filter').removeClass('active');
			$search_filter_area.find('input[type=text]').val('').trigger('change');

			if ( config.multiSelect ) {
				$search_filter_area.find('input[type=checkbox]').prop('checked', false).trigger('change');
			} else {
				$search_filter_area.find('select').each(function() {
					$(this).find('option').eq(0).prop('selected', true);
					$(this).trigger('change');
				});
			}

			var filterClass = $wrapper.data('gs-coach-filter');

			if ( filterClass && filterClass.refreshIsotope ) {
				filterClass.filters.group = '*'
				filterClass.refreshIsotope();
			}

		});

		$search_filter_area.find('input, select').on('change', function() {

			var show_button = $search_filter_area.find('input, select').toArray().map(function(_input) {
				
				var state = false;

				if ( _input.type == 'text' ) state = _input.value.trim() ? true : false;

				if ( config.multiSelect ) {
					if ( _input.type == 'checkbox' && _input.checked ) state = _input.value !== '*' ? true : false;
				} else {
					if ( _input.type == 'select-one' ) state = _input.value !== '*' ? true : false;
				}

				return state;

			}).some(function(_state) { return _state; });

			show_button ? $clear_filter_button_area.show() : $clear_filter_button_area.hide();

		});

	}

	// debounce so filtering doesn't happen every millisecond
	function debounce( fn, threshold ) {

		var timeout;

		return function debounced() {

			if ( timeout ) clearTimeout( timeout );

			function delayed() {
				fn();
				timeout = null;
			}

			setTimeout( delayed, threshold || 100 );

		};

	}

	// Flip
	function do_flip_vertical( $flip_vertical ) {

		if ( ! $flip_vertical.length ) return;

		$flip_vertical.flip({
			trigger: 'hover'
		});

	}

	// Scroll Effect
	function do_cbp_so_scroller( $cbp_so_scroller ) {

		if ( ! ('cbpScroller' in window) ) return;

		if ( ! $cbp_so_scroller.length ) return;

		$cbp_so_scroller.each(function() {
			new cbpScroller( this );
		});

	}

	// Carousel
	function do_carousel( $sliders ) {

		if ( ! $sliders.length ) return;

		$sliders.each( function() {

			var $that = $(this);

            $that.find('img').each(function() {
                if ( $(this).data('src') ) {
                    $(this).attr('src', $(this).data('src'));

					// Hub WordPress Theme Support
					$(this).removeClass('ld-lazyload');
                }
            });
			
			var options = $that.closest('.gs_coach_area').data('options');

			var navText = [options.prev_txt, options.next_txt];

			if ( $that.hasClass('carousel-has-navs') && ! $that.hasClass('carousel-navs--default') ) {
				if ( $that.hasClass('carousel-navs--style-two') ) {
					navText = ['<svg viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1792 800v192q0 14-9 23t-23 9h-1248v224q0 21-19 29t-35-5l-384-350q-10-10-10-23 0-14 10-24l384-354q16-14 35-6 19 9 19 29v224h1248q14 0 23 9t9 23z"/></svg>', '<svg viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1728 893q0 14-10 24l-384 354q-16 14-35 6-19-9-19-29v-224h-1248q-14 0-23-9t-9-23v-192q0-14 9-23t23-9h1248v-224q0-21 19-29t35 5l384 350q10 10 10 23z"/></svg>'];
				} else {
					navText = ['<i class="fas fa-angle-left"></i>', '<i class="fas fa-angle-right"></i>'];
				}
			}
			
			var autoplay = options.carousel_autoplay !== 'off';
			var autoplayHoverPause = options.carousel_autoplay_hover_pause !== 'off';
			var loop = options.carousel_loop !== 'off';
			var autoplaySpeed = parseInt( options.carousel_autoplay_speed, 10 ) || 1000;
			var autoplayTimeout = parseInt( options.carousel_autoplay_timeout, 10 ) || 2500;

			$(this).owlCarousel({
				rtl: is_rtl,
				autoplay: autoplay,
				autoplayHoverPause: autoplayHoverPause,
				loop: loop,
				rewind: false,
				margin: 30,
				autoplaySpeed: autoplaySpeed,
				autoplayTimeout: autoplayTimeout,
				navSpeed: autoplaySpeed,
				dots: $that.hasClass('carousel-has-dots'),
				nav: $that.hasClass('carousel-has-navs'),
				navText: navText,
				responsiveClass:true,
				lazyLoad: true,
				responsive:{
					0: {
						items: $that.data('carousel-mobile'),
						nav: $that.hasClass('carousel-has-navs')
					},
					576: {
						items: $that.data('carousel-mobile-portrait'),
						nav: $that.hasClass('carousel-has-navs')
					},
					768: {
						items: $that.data('carousel-tablet'),
						nav: $that.hasClass('carousel-has-navs')
					},
					1025: {
						items: $that.data('carousel-desktop'),
						nav: $that.hasClass('carousel-has-navs')
					}
				}
			});
		});
	}

	// Popup
	function do_popup( $single_coach_pop, $gs_coach_area ) {

		if ( ! $single_coach_pop.length ) return;

		$gs_coach_area.each(function() {

			$(this).magnificPopup({
				type:'inline',
				midClick: true,
				gallery: {
					enabled:true
				},
				delegate: '.single-coach-pop a.gs_coach_pop:visible',

				closeMarkup: '<button title="%title%" type="button" class="mfp-close"><svg xmlns="http://www.w3.org/2000/svg" width="22.62" height="22.62" viewBox="0 0 22.62 22.62"><path fill="#c1c1c7" d="M1474.1,7297.69l21.21,21.21-1.41,1.41-21.21-21.21Zm-1.41,21.21,21.21-21.21,1.41,1.41-21.21,21.21Z" transform="translate(-1472.69 -7297.69)"/></svg></button>',

				removalDelay: 500, // delay removal by X to allow out-animation
				callbacks: {
					beforeOpen: function() {
						
						var extraClasses = ['mfp-gscoach'];

						extraClasses.push( this.st.el.attr('data-effect') ? this.st.el.attr('data-effect') : 'mfp-fade' );
						extraClasses.push( this.st.el.attr('data-theme') ? this.st.el.attr('data-theme') : 'gs-coach-popup--default' );

						this.st.mainClass = this.st.mainClass + ' ' + extraClasses.join(' ');

					}
				}
			});
		});

		if ( popupEventsAdded ) return;

		$('body').on('click', '.mfp-close', function(e) {
			e.preventDefault();
			$.magnificPopup.instance.close();
		});

		$('body').on('click', '.gs_coach_popup .popup-navigation .popup-nav', function(e) {
			e.preventDefault();
			$(this).hasClass('next') ? $.magnificPopup.instance.next() : $.magnificPopup.instance.prev();
		});

		function popup_get_unique_index( action, popup, currentSrc, currentIndex ) {

			// Set new index
			var newIndex = (action == 'next') ? currentIndex+1 : currentIndex-1;
			
			// Validate new index
			if ( newIndex < 0 ) newIndex = popup.items.length - 1;
			if ( newIndex >= popup.items.length ) newIndex = 0;

			// Get new item
			var newItem = popup.items[ newIndex ];

			// Get new item src
			var newSrc = newItem.src ? newItem.src : $(newItem).data('mfp-src');

			// Return the new index if it is unique
			if ( currentSrc !== newSrc ) return newIndex;

			// Find the new unique index and return
			return popup_get_unique_index( action, popup, newSrc, newIndex );

		}

		$.magnificPopup.instance.next = function() {
			this.index = popup_get_unique_index( 'next', this, this.currItem.src, this.index ) - 1;
			$.magnificPopup.proto.next();
		}

		$.magnificPopup.instance.prev = function() {
			this.index = popup_get_unique_index( 'prev', this, this.currItem.src, this.index ) + 1;
			$.magnificPopup.proto.prev();
		}

		popupEventsAdded = true;
	}

	// Gridder
	function do_gridder( $gridder ) {

		if ( ! $gridder.length ) return;

		$gridder.gridderExpander({
			// scroll: true,
			is_rtl: is_rtl,
			scrollOffset: 30,
			scrollTo: "listitem",                  // panel or listitem
			animationSpeed: 400,
			animationEasing: "easeInOutExpo",
			showNav: true,                      // Show Navigation
			nextText: '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="20px"><path fill-rule="evenodd"  fill-opacity="0.502" fill="rgb(255, 255, 255)" d="M11.899,10.000 L2.000,19.899 L0.586,18.485 L9.071,10.000 L0.586,1.514 L2.000,0.100 L10.485,8.585 L10.485,8.585 L11.899,10.000 Z"/></svg>',	// Next button text
			prevText: '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="12px" height="20px"><path fill-rule="evenodd"  fill="rgb(255, 255, 255)" d="M11.414,18.485 L10.000,19.899 L0.100,10.000 L1.515,8.585 L1.515,8.585 L10.000,0.100 L11.414,1.514 L2.929,10.000 L11.414,18.485 Z"/></svg>',     // Previous button text
			closeText: '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24"><path fill-rule="evenodd" fill="rgb(204, 204, 204)" d="M23.314,21.899 L21.899,23.313 L12.000,13.414 L2.100,23.313 L0.686,21.899 L10.586,12.000 L0.686,2.100 L2.100,0.686 L12.000,10.585 L21.899,0.686 L23.314,2.100 L13.414,12.000 L23.314,21.899 Z"/></svg>',           // Close button text
		});
	}

	// Auto Height Fixing
	function fix_coachs_height( $gs_themes ) {

		$('.gs_coach_area .gs_coach_image__wrapper img').removeAttr('style');

		var selectors = '.gs_tm_theme1, .gs_tm_theme2, .gs_tm_grid2, .gs_tm_drawer2, .gs_tm_theme7, .gs_tm_theme8, .gs_tm_theme9, .gs_tm_theme10, .gs_tm_theme11, .gs_tm_theme12, .gs_tm_theme13, .gs_tm_theme20, .gs_tm_theme23, .gs_tm_theme24, .gs-coach-circle-three, .gs-coach-horizontal-three';

		if ( ! $gs_themes ) {
			$gs_themes = $(selectors);
		} else if ( $gs_themes.length ) {
			$gs_themes = $gs_themes.filter(selectors);
		}

		if ( $gs_themes.length ) {

			$gs_themes.each(function() {

				if ( $gs_themes.hasClass('gs-coach-circle-three') && ! $gs_themes.find('.gscoach-gridder').length ) return;
	
				var $elements = $(this).removeClass('gs-coach--fixed-height');
	
				var $elements = $(this).find('.single-coach').css({
					'min-height': '0',
					'max-height': 'none',
					'height': 'auto'
				});
	
				var heights = $elements.map(function() {
					return $(this).height();
				});
	
				$elements.height( Math.max.apply( Math, heights ) );

				if ( ! $gs_themes.hasClass('gs-coach-circle-three') ) {
					$(this).addClass('gs-coach--fixed-height');
				}
	
			});

		}

		$('.gs_coach_area.gs_coach_loading').removeClass('gs_coach_loading');
	}

	// Filter
	function do_filter( $gs_filter_wrapper ) {

		if ( ! $gs_filter_wrapper.length ) return;

		var options = $gs_filter_wrapper.closest('.gs_coach_area').data('options');

		// Load multi select class if not loaded
		if ( options.enable_multi_select == 'on' && ! $.fn.gs_multi_select ) load_multi_select_class();
		
		// Load filter class if not loaded
		if ( ! $.fn.gs_coach_filter ) load_filter_class();

		$gs_filter_wrapper.each(function() {

			if ( options.enable_multi_select == 'on' ) $(this).gs_multi_select({
				ellipsis: options.multi_select_ellipsis == 'on'
			});

			if ( options.enable_clear_filters == 'on' ) gs_filter_clear_filter_button( $(this), {
				multiSelect: options.enable_multi_select == 'on',
				resetFilterText: options.reset_filters_text
			});

			$(this).gs_coach_filter();

		});

	}

	function do_pagination( $gs_coach_area ) {

		// AJAX Pagination
		$($gs_coach_area).on('click', '.gs-coach-ajax-pagination-link a', function(e){
			e.preventDefault();

			const link = $(this).attr('href');
			const urlParams = new URLSearchParams(link.split('?')[1]);
			const paged = urlParams.get(Object.keys(Object.fromEntries(urlParams)).find(key => key.includes('paged')));

			let coachParent = $gs_coach_area.find('.gs_coach');

			// For table theme
			const table = coachParent.find('table');
			let isTableTheme;
			let tbody;

			if (table.length > 0) {
				isTableTheme = true;
				tbody = table.find('tbody');
				coachParent = tbody;
			}

			const container = $(this).closest('[id^=gs-coach-ajax-pagination-]');
			const shortcodeId = container.attr('id').replace('gs-coach-ajax-pagination-', '');
			const postsPerPage = container.data('posts-per-page');

			$.ajax({
				url: GSCoachData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'gscoach_ajax_pagination',
					_ajax_nonce: GSCoachData.nonce,
					shortcode_id: shortcodeId,
					filters: filterVals,
					paged: paged,
					posts_per_page: postsPerPage
				},
				beforeSend: function() {
					container.css('opacity', '0.5');
					coachParent.css('opacity', '0.5');
				}
			})
			.done( response => {

				let dataCoaches = $.parseHTML( response.data.coaches );
				let coachDivs = $(dataCoaches).find('.single-coach-div');

				if( isTableTheme ){
					$gs_coach_area.find('.gs_coach').append( $(dataCoaches).find('.gs_coach_popup') );
					tbody.html(coachDivs);
				} else {
					coachParent.html(coachDivs);
				}

				container.html( response.data.pagination );

				container.css('opacity', '1');
				coachParent.css('opacity', '1');
			})
			.fail((xhr, status, error) => {
				console.error("AJAX request failed:", status, error);
				container.css('opacity', '1'); // reset even if failed
				coachParent.css('opacity', '1'); // reset even if failed
			});
			
		});

			
		$($gs_coach_area).on('click', '#gs-coach-load-more-coach-btn', function(e){

			e.preventDefault();

			const shortcodeId = $gs_coach_area.data('shortcode-id');

			let coachParent = $gs_coach_area.find('.gs_coach');

			// For table theme
			const table = coachParent.find('table');
			let isTableTheme;
			let tbody;

			if (table.length > 0) {
				isTableTheme = true;
				tbody = table.find('tbody');
				coachParent = tbody;
			}

			const currentCoachQuantity = parseInt(coachParent[0].children.length);

			const dataOptions = $gs_coach_area.attr('data-options');
			const fixedDataOptions = dataOptions.replace(/'/g, '"');
			const parsedData = JSON.parse(fixedDataOptions);
			const loadPerActionValue = parseInt(parsedData.load_per_click);

			$.ajax({
				url: GSCoachData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'gscoach_load_more_coach',
					_ajax_nonce: GSCoachData.nonce,
					shortcodeId: shortcodeId,
					filters: filterVals,
					loadPerAction: loadPerActionValue,
					offset: currentCoachQuantity
				}
			})
			.done( response => {

				let dataEls = $.parseHTML( response.data.coaches );
				let coachDivs = $(dataEls).find('.single-coach-div');

				if( isTableTheme ){
					$gs_coach_area.find('.gs_coach').append( $(dataEls).find('.gs_coach_popup') );
					tbody.append(coachDivs);
				} else {
					coachParent.append(coachDivs);
				}


				if (response.data.foundCoaches <= (currentCoachQuantity + loadPerActionValue)) {
					$gs_coach_area.find('#gs-coach-load-more-coach-btn').hide();
				}
		
			})
			.fail((xhr, status, error) => {
				console.error("AJAX request failed:", status, error);
			});
		});


		// Load more coaches on scroll
		function initGSCoachScrollLoader() {

			const shortcodeId = $gs_coach_area.data('shortcode-id');
			
			const scrollWrapper = $gs_coach_area.find(`#gs-coach-load-more-scroll-${shortcodeId}`);

			const dataOptions = $gs_coach_area.attr('data-options');

			if (!shortcodeId || !dataOptions) return;

			const parsedData = JSON.parse(dataOptions);
			const loadPerActionValue = parseInt(parsedData.per_load);

			let coachParent = $gs_coach_area.find('.gs_coach');

			// For table theme
			const table = coachParent.find('table');
			let isTableTheme;
			let tbody;

			if (table.length > 0) {
				isTableTheme = true;
				tbody = table.find('tbody');
				coachParent = tbody;
			}

			let isLoading = false;
			let noMoreData = false;

			function loadMoreCoaches() {
				if (isLoading || noMoreData) return;

				const currentCoachQuantity = parseInt(coachParent[0].children.length);

				isLoading = true;
				scrollWrapper.find('.gs-coach-loader-spinner').show();

				$.ajax({
					url: GSCoachData.ajaxUrl,
					type: 'POST',
					data: {
						action: 'gscoach_load_more_coach',
						_ajax_nonce: GSCoachData.nonce,
						shortcodeId: shortcodeId,
						filters: filterVals,
						loadPerAction: loadPerActionValue,
						offset: currentCoachQuantity
					}
				})
				.done(response => {

					scrollWrapper.find('.gs-coach-loader-spinner').fadeOut();
					setTimeout(function() {

						isLoading = false;
						
						if (response.success) {

							let dataEls = $.parseHTML(response.data.coaches);
							let coachDivs = $(dataEls).find('.single-coach-div');

							if( isTableTheme ){
								coachParent.append( $(dataEls).find('.gs_coach_popup') );
								tbody.append(coachDivs);
							} else {
								coachParent.append(coachDivs);
							}

						}
					}, 600);

					
					if (response.data.foundCoaches <= (currentCoachQuantity + loadPerActionValue)) {
						noMoreData = true;
					}
					
				})
				.fail((xhr, status, error) => {
					console.error("AJAX request failed:", status, error);
				});
			}

			// Scroll detection
			$(window).on('scroll', function() {
				if (isLoading || noMoreData) return;

				const scrollTop = $(window).scrollTop();
				const windowHeight = $(window).height();
				const coachAreaBottom = $gs_coach_area.offset().top + $gs_coach_area.outerHeight();

				if (scrollTop + windowHeight >= coachAreaBottom - 100) {
					loadMoreCoaches();
				}
			});
		}

		const shortcodeId = $gs_coach_area.data('shortcode-id');
		const scrollID = $gs_coach_area.find(`#gs-coach-load-more-scroll-${shortcodeId}`);

		if( scrollID.length !== 0 ){
			initGSCoachScrollLoader();
		}
	}

	// Fix Tooltip
	function fix_tooltip( $staff_meta ) {

		if ( ! $staff_meta.length ) return;
		
		$staff_meta.css( 'display', 'block' );

	}

	// Staff opacity animation - categories
	function do_staff_category_animation( $staff_category ) {

		if ( ! $staff_category.length ) return;

		$staff_category.delegate(".staff-coach", "mouseover mouseout", function(e) {
			
			if ( e.type == 'mouseover' ) {
				$(this).not(this).dequeue().animate({opacity: "0.4"}, 600);
			} else {
				$(this).not(this).dequeue().animate({opacity: "1"}, 600);
			}

		});

	}

	// Panel Slide
	function do_panel_slide( $gs_coach_panelslide ) {

		if ( ! $gs_coach_panelslide.length ) return;

		$gs_coach_panelslide.panelslider({
			side: 'right',
			clickClose: true,
			duration: 200
		});
		
	}


	if ( $.panelslider !== undefined ) {

		$('.close-gscoach-panel-bt').on( 'click', function () {
			$.panelslider.close();
		});
	
		$('.next-gscoach-panel-bt').on( 'click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$.panelslider.next();
		});
	
		$('.prev-gscoach-panel-bt').on( 'click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$.panelslider.prev();
		});

	}

	// Blob Effects Animation
	function update_blob_effects_anims( $gs_themes ) {

		var selectors = '.gs-coach-circle-two';

		if ( ! $gs_themes ) {
			$gs_themes = $(selectors);
		} else if ( $gs_themes.length ) {
			$gs_themes = $gs_themes.filter(selectors);
		}

		if ( $gs_themes.length ) {
			var durations = ['9s', '10s', '11s', '12s', '13s', '14s'];
			$gs_themes.each(function() {
				$(this).find('.gs_coach_image__wrapper').each(function() {
					var duration = durations[ Math.floor( ( Math.random() * durations.length ) ) ];
					$(this).css({
						'animation-duration': duration,
						'-webkit-animation-duration': duration
					});
				})
			});
		}

	}

	// Init GS Coach
	function gs_coach_widget_single_init( $widget_box ) {

		do_flip_vertical( $('.flip-vertical', $widget_box) );
	
		do_cbp_so_scroller( $('.cbp-so-scroller', $widget_box) );
	
		do_carousel( $('.slider', $widget_box) );
	
		do_gridder( $('.gridder', $widget_box) );
	
		do_filter( $('.gs-all-items-filter-wrapper', $widget_box) );

		do_pagination( $widget_box );
	
		do_popup( $('.single-coach-pop', $widget_box ), $widget_box );
	
		fix_tooltip( $('.staff-meta', $widget_box) );
	
		do_staff_category_animation( $(".staff-category", $widget_box) );
	
		do_panel_slide( $('.gs_coach_panelslide_link', $widget_box) );
	
		fix_coachs_height( $widget_box );

		update_blob_effects_anims( $widget_box );

		$widget_box.addClass( 'gs_coach--loaded' );

	}


	window.gs_coach_init = function() {

		var $gs_coach_area = $('.gs_coach_area');

		if ( ! $gs_coach_area.length ) return;

		$gs_coach_area.each(function() {

			if ( ! $(this).parent().is(':visible') ) return;

			if ( $(this).data('et-js-processed') ) return;

			$(this).data( 'et-js-processed', 1 );
	
			gs_coach_widget_single_init( $(this) );

		});

	}

	// Init
	gs_coach_init();

	// Init on Editor
	$(window).on( 'gscoach:scripts:reprocess', function() {
		gs_coach_init();
	});

	// Init on Load
	$(window).on('load', function() {
		fix_coachs_height();
		gs_coach_init();
	});

	// Fix sizes on resize
	$(window).on( 'resize', debounce(function() {
		fix_coachs_height();
	}) );

	// Fix with other plugins
	$('body').on( 'click', function() {
		fix_coachs_height();
		gs_coach_init();
		jQuery(window).trigger('resize');
	});

	$('.table-responsive--dense').on('search.bs.table', function( event, text ) {

		var table = $(this).find('table.table');

		if ( text ) {
			table.show();
		} else {
			table.hide();
		}
		
	});

	// Fix for thirdparty Smooth Scroll
    $('.gs-coach--scrollbar').on('wheel mousewheel DOMMouseScroll', function(e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
	
});