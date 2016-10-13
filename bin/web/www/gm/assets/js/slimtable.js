/*!
 * slimtable ( http://slimtable.mcfish.org/ )
 * 
 * Licensed under MIT license.
 *
 * @version 1.2.4
 * @author Pekka Harjamäki
 */

(function($) {
	$.fn.slimtable = function( options ) {
		//
		var settings = $.extend({
			tableData: null,
			dataUrl: null,

			itemsPerPage: 15,
			ipp_list: [15,30,50,100,200,500],

			keepAttrs: [],
			sortList: [],
			colSettings: [],

			text1: "<div class=''></div>",
			text2: "Loading...",

			sortStartCB: null,
			sortEndCB: null
		}, options);

		// Private variables
		var col_settings = [],
		    sort_list = [],
		    tbl_data = [],
		    table_thead,
		    table_tbody,
		    table_btn_container,
		    paging_start,
		    items_per_page,
		    show_loading,
		    html_cleaner_div;

		/* ******************************************************************* *
		 * Main part of the plugin
		 * ******************************************************************* */
		return this.each(function() {

			//
			paging_start = 0;
			show_loading = false;
			items_per_page = settings.itemsPerPage;
			html_cleaner_div = document.createElement('div');

			//
			table_thead = $(this).find("thead");
			table_tbody = $(this).find("tbody");

			// First we need to find both thead and tbody
			if(table_thead.length == 0 || table_tbody.length == 0)
			{
				console.log("Slimtable: thead/tbody missing from table!");
				return;
			}

			// Read table headers + data
			readTable();

			if( show_loading==false && !sanity_check_1() )
				return;

			// Add sort bindings & paging buttons
			$(this).addClass("slimtable");
			addSortIcons();
			addPaging( $(this) );

			//
			doSorting();
		} );

		function sanity_check_1() 
		{
			if(tbl_data.length>0 && col_settings.length != tbl_data[0].length)
			{
				console.log("Slimtable: Different number of columns in header and data!");
				return(false);
			}
			return(true);
		}

		/* ******************************************************************* *
		 * Add paging div and sort icons
		 * ******************************************************************* */
		function addPaging( tbl_obj ) {
			var t_obj1, t_obj2,
			    option, l1 ,
			    selector = document.createElement('select');

			//
			t_obj1 = document.createElement('div');
			$(t_obj1).addClass('slimtable-paging-div');

			//
			for(l1 = 0 ; l1<settings.ipp_list.length; l1++)
			{
				option = document.createElement('option');
				option.value=settings.ipp_list[l1];
				option.text=settings.ipp_list[l1];

				if(option.value == settings.itemsPerPage)
					option.selected = true;

				$(selector).append(option);
			}
	
			$(selector).on('change',handle_ipp_change).
				    addClass('slimtable-paging-select');

			// Create container for paging buttons
			t_obj2 = document.createElement('div');
			$(t_obj2).addClass('slimtable-paging-btnsdiv');
			$(t_obj1).append(t_obj2);
			table_btn_container = t_obj2;

			// Create container for select
			t_obj2 = document.createElement('div');
			$(t_obj2).addClass('slimtable-paging-seldiv');

			$(t_obj2).append(selector);
			$(t_obj2).append(settings.text1);
			$(t_obj1).append(t_obj2);

			// Move table to container div
			t_obj2 = document.createElement('div');
			$(t_obj2).addClass('slimtable-container-div');

			$(t_obj2).append(t_obj1);
			tbl_obj.before(t_obj2);
			tbl_obj.insertBefore(t_obj1);
		}

		function addSortIcons() {
			table_thead.find("th").each(function(index) {
				$(this).attr('unselectable','on');

				if(col_settings[index] && col_settings[index].sortable)
				{
					var obj = document.createElement("span");
					$(obj).attr('unselectable','on').
						addClass("slimtable-sprites");

					if( col_settings[index].sordir=="asc" )
					{
						$(obj).addClass("slimtable-sortasc");
					} else if( col_settings[index].sordir=="desc" ) {
						$(obj).addClass("slimtable-sortdesc");
					} else {
						$(obj).addClass("slimtable-sortboth");
					}

					$(this). prepend(obj).
						 css({ cursor: "pointer" }).
						 on("click",handleHeaderClick);
				} else {
					$(this).addClass("slimtable-unsortable")
				}
			});
		}

		/* ******************************************************************* *
		 * Utils
		 * ******************************************************************* */
		function readTable() 
		{
			var th_list=table_thead.find("th"), l1, l2, l3, t_row, t_obj, t_attr;

			//
			col_settings = [];
			for(l1=0; l1<th_list.length; l1++)
			{
				var val_sortable = true,
				    val_strip_html = false,
				    val_rowtype = -1,
				    val_sortdir = "asc",
				    val_xtraclasses = [];

				// has user set any custom settings to columns?
				for(l2=0; l2<settings.colSettings.length; l2++)
				if( settings.colSettings[l2].colNumber == l1 )
				{
					t_obj = settings.colSettings[l2];

					if( t_obj.enableSort==false )
						val_sortable = false;

					if( t_obj.stripHtml==true )
						val_strip_html = true;

					if( t_obj.sortDir == "asc" || t_obj.sortDir == "desc" )
						val_sortdir = t_obj.sortDir;

					if( t_obj.rowType>=0 )
						val_rowtype = t_obj.rowType;

					if( t_obj.addClasses && t_obj.addClasses.length>0)
						val_xtraclasses = t_obj.addClasses;

					break;
				}

				//
				col_settings[l1]={
					sortable: val_sortable,
					classes: val_xtraclasses,
					strip_html: val_strip_html,
					sortdir: val_sortdir,
					rowtype: val_rowtype
				};
			}

			//
			if( settings.sortList.length > 0 )
			{
				sort_list = settings.sortList;
			}

			// Get data either from table, pre defined array or ajax url
			if(settings.dataUrl && settings.dataUrl.length>2) 
			{
				show_loading = true;
				$.ajax({
					url: settings.dataUrl,
					dataType: "json"
				}).done(function(data){
					tbl_data = data;
					show_loading = false;
					createTableBody();
				}).fail(function(par1,par2){
					console.log("Slimtable: Ajax error: "+par2);
					return;
				});

			} else if(settings.tableData && settings.tableData.length>=0) {
		    		tbl_data = settings.tableData;
			} else {
				table_tbody.find("tr").each(function() {
					t_row = [];
					$(this).find("td").each(function() {
						t_obj = { orig: $(this).html() , attrs: [] , clean: null };

						// Does td contain sort-data  attr?
						t_attr = $(this).attr("sort-data");
						if ( typeof t_attr != "undefined" && t_attr != null )
						{
							t_obj.clean = t_attr;
						}

						// Find attributes to keep
						for(l3=0; l3<settings.keepAttrs.length; l3++)
						{
							t_attr = $(this).attr(settings.keepAttrs[l3]);
							if ( typeof t_attr != "undefined" )
							{
								t_obj.attrs.push({ attr: settings.keepAttrs[l3], value: t_attr});
							}
						}
						t_row.push( t_obj );
					});
					tbl_data.push(t_row);
				});
			}

			//
			determine_col_types();
		}

		function determine_col_types()
		{
			var l1, l2, t1, 
			    th_list=table_thead.find("th"), 
			    match_arr;

			// Determine col types
			for(l1=0; l1<th_list.length; l1++)
			if(col_settings[l1].rowtype == -1)
			{
				match_arr=[ 0, 0, 0, 0, 0 ];

				for(l2=0; l2<tbl_data.length; l2++)
				{
					// Remove HTML, TRIM data and create array with cleaned & original data
					if ( tbl_data[l2][l1].clean  == null )
					{
						tbl_data[l2][l1].clean = col_settings[l1].strip_html ? $(html_cleaner_div).html(tbl_data[l2][l1].orig).text() : tbl_data[l2][l1].orig;
						tbl_data[l2][l1].clean = $.trim( tbl_data[l2][l1].clean );
						tbl_data[l2][l1].clean = tbl_data[l2][l1].clean.toLowerCase()
					}

					// 
					match_arr[ return_row_type( tbl_data[l2][l1].clean ) ]++;
				}

				col_settings[l1].rowtype = $.inArray( Math.max.apply(this, match_arr) , match_arr );

				// Cleanup data bases on type
				for(l2=0; l2<tbl_data.length; l2++)
				{
					if ( col_settings[l1].rowtype == 0 )
					{
						tbl_data[l2][l1].clean = new String(tbl_data[l2][l1].clean);
					}

					// Remove end sign, change , to . and run parsefloat
					if ( col_settings[l1].rowtype == 2 || col_settings[l1].rowtype == 3 )
					{
						tbl_data[l2][l1].clean = parseFloat(tbl_data[l2][l1].clean.replace(",","."));
					}

					// Convert values to dates
					if ( col_settings[l1].rowtype == 4 )
					{
						t1 = tbl_data[l2][l1].clean.split(/[.\/-]/);
						tbl_data[l2][l1].clean = new Date ( t1[2], t1[1], t1[0] );
					}
				}
			}
		}

		/* ******************************************************************* *
		 * 
		 * ******************************************************************* */
		function createTableBody() {
			var end_pos = parseInt(paging_start)+parseInt(items_per_page),
			    t_obj1,t_obj2,pages, l1, l2, l3;

			//
			table_tbody.empty();
			end_pos = end_pos > tbl_data.length ? tbl_data.length : end_pos;
			pages = Math.ceil( tbl_data.length / items_per_page );

			//
			for(l1=paging_start; l1<end_pos; l1++)
			{
				t_obj1 = document.createElement("tr");
				for(l2=0; l2<tbl_data[l1].length; l2++)
				{
					// Create TD element
					t_obj2 = document.createElement("td");
					$(t_obj2).html( tbl_data[l1][l2].orig );

					// Restore attributes
					for(l3=0; l3<tbl_data[l1][l2].attrs.length; l3++)
					{
						$(t_obj2).attr(tbl_data[l1][l2].attrs[l3].attr, tbl_data[l1][l2].attrs[l3].value);
					}

					// Add extra css classes to td
					for(l3=0; l3<col_settings[l2].classes.length; l3++)
						$(t_obj2).addClass(col_settings[l2].classes[l3]);

					// Add td to tr
					$(t_obj1).append(t_obj2);
				}

				table_tbody.append(t_obj1);
			}

			// Create paging buttons
			$(table_btn_container).empty();
			for(l1=0; l1<pages; l1++)
			{
				t_obj1 = document.createElement("div");
				$(t_obj1).addClass("slimtable-page-btn").
					  on('click',handle_page_change).
					  text(l1+1);

				if( l1*items_per_page == paging_start )
					$(t_obj1).addClass("active");
					
				$(table_btn_container).append( t_obj1 );
			}
		}

		function createTableHead() {
			var l1, t_item1, t_item2;

			for(l1=0; l1<col_settings.length; l1++)
			{
				if( !col_settings[l1] || !col_settings[l1].sortable )
					continue;

				t_item1 = table_thead.find("th:nth-child("+(l1+1)+")");
				t_item2 = t_item1.find("span");

				if( $.inArray(l1,sort_list) < 0 )
				{
					t_item1.removeClass("slimtable-activeth");
					t_item2.removeClass("slimtable-sortasc");
					t_item2.removeClass("slimtable-sortdesc");
					t_item2.addClass("slimtable-sortboth");
				} else {
					t_item2.removeClass("slimtable-sortboth");
					t_item2.removeClass("slimtable-sort" + (col_settings[l1].sortdir=="asc"?"desc":"asc") );
					t_item2.addClass("slimtable-sort" + col_settings[l1].sortdir );
					t_item1.addClass("slimtable-activeth");
				}
			}
		}

		/* ******************************************************************* *
		 * 
		 * ******************************************************************* */
		function return_row_type(data)
		{
			var patt_01 = /[^0-9]/g,
			    patt_02 = /^[0-9]+([\.,][0-9]+)?$/,
			    patt_03 = /^([0-9]+([\.,][0-9]+)?)\s*[%$€£e]?$/,
			    patt_04 = /^[0-9]{1,2}[.-\/][0-9]{1,2}[.-\/][0-9]{4}$/,
			    patt_05 = /^-([0-9]+([\.,][0-9]+)?)\s*$/;

			// Given element doesn't containt any other characters than numbers
			if( !patt_01.test(data) )
				return(1);

			if( patt_05.test(data) )
				return(1);

			// Givent element is most likely float number
			if( patt_02.test(data) )
				return(2);

			// Float with cleanup
			if( patt_03.test(data) )
				return(3);
			
			// Date .. maybe?
			if( patt_04.test(data) )
				return(4);

			// String comparison
			return(0);
		
		}

		function doSorting()
		{
			//
			if(sort_list.length>0)
			tbl_data.sort(function(a,b) {
				var t1, ta, tb, l1, t2,
				    slist_length=sort_list.length,
				    same_item;

				for(l1=0; l1<slist_length; l1++)
				{
					t1 = sort_list[l1];

					// Swap variables, if sortdir = ascending
					if( col_settings[t1].sortdir == 'desc' )
					{
						ta = b[t1].clean; 
						tb = a[t1].clean;
					} else {
						ta = a[t1].clean; 
						tb = b[t1].clean;
					}

					// Given variables match, move to next sort parameter
					same_item = false;
					if ( col_settings[t1].rowtype == 0 )
					{
						if ( ta.localeCompare(tb) == 0 )
							same_item = true;
					} else if (col_settings[t1].rowtype == 4 ) {
						if ( ta - tb == 0 )
							same_item = true;
					} else { 
						if (ta == tb)
							same_item = true;
					}

					//
					if( same_item && l1 < (slist_length-1) )
						continue;

					// Compare values
					if ( col_settings[t1].rowtype == 0 )
						return( ta.localeCompare(tb) );
					else
						return( ta - tb );
				}

			});

			//
			createTableHead();
			createTableBody();
		}

		/* ******************************************************************* *
		 * Events
		 * ******************************************************************* */
		function handle_page_change(e) {
			var num = parseInt($(this).text())-1,
			    pages = Math.ceil( tbl_data.length / items_per_page );

			if(num<0 || num>=pages)
				return;

			paging_start = num*items_per_page;

			createTableBody();
		}

		function handle_ipp_change(e) {
			items_per_page = this.value;
			paging_start = 0;
			createTableBody();
		}

		function handleHeaderClick(e) {
			var idx = $(this).index(),
			    l1,
			    pos = $.inArray(idx,sort_list);

			//
			e.preventDefault();

			// Execute sort start callback, if one is defined
			if(settings.sortStartCB && typeof settings.sortStartCB == 'function')
				settings.sortStartCB.call(this);

			// Shift click
			if( e.shiftKey )
			{
				if( pos < 0 )
				{
					sort_list.push( idx );
					col_settings[idx].sortdir = "asc";
				} else {
					if(col_settings[idx].sortdir=="asc")	col_settings[idx].sortdir = "desc";
					else					col_settings[idx].sortdir = "asc";
				}
			} else {
				sort_list = [ idx ];
				if( pos < 0 )
				{
					col_settings[idx].sortdir = "asc";
				} else {
					if(col_settings[idx].sortdir=="asc")	col_settings[idx].sortdir = "desc";
					else					col_settings[idx].sortdir = "asc";
				}
			}


			//
			doSorting();

			// Execute sort end callback, if one is defined
			if(settings.sortEndCB && typeof settings.sortEndCB == 'function')
				settings.sortEndCB.call(this);
		}

	}
}(jQuery));
