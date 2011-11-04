/* gSpot Version 1.0.1 - by Mark Smalley - http://www.smalley.my */
/* Mark Smalley on GitHub - https://github.com/msmalley */
;(function ( $, window, document, undefined ) {

    var pluginName = 'gSpot',
        defaults = {
            zoom: 13,
			type: 'ROADMAP',
			lat: false,
			lng: false,
			imgs: 'img',
			markers: false
        };

	/* GSPOT GLOBALS */
	var map;
	var marker_count = 0;
	var marker = [];
	var info_box = [];
	var new_info_box = [];
	var clustered_markers = [];
	var marker_cluster = [];
	var map_position;
	var current_marker = [];
	var default_lat = 3.152864;
	var default_lng = 101.712624;
	var image_base = 'img';
	var map_container;
	var current_lat = false;
	var current_lng = false;

    /* ----------------------- */
	/* jQuery Construct Method */
	/* ----------------------- */

    function Plugin( element, options ) {
        this.element = element;
        this.options = $.extend( {}, defaults, options) ;
        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype.init = function () {
		marker_count = 0;
		if(this.options.debug===true){
			debug.log('This Element = ',this.element);
			debug.log('Options = ',this.options);
		} map_container = this.element;

		adhoc_zoom = parseInt($(map_container).attr('data-zoom'));
		adhoc_type = $(map_container).attr('data-type');
		adhoc_lat = parseFloat($(map_container).attr('data-lat'));
		adhoc_lng = parseFloat($(map_container).attr('data-lng'));
		adhoc_title = $(map_container).attr('data-title');
		adhoc_slug = $(map_container).attr('data-slug');
		adhoc_open = $(map_container).attr('data-open');
		adhoc_icon = $(map_container).attr('data-icon');
		adhoc_content = $(map_container).html();

		ajax = $(map_container).attr('data-ajax');

		if(((adhoc_zoom!==null)&&(adhoc_zoom!==false)&&((!this.options.zoom))||(($(map_container).attr('data-override')=='true')&&(adhoc_zoom)))){
			this.options.zoom = adhoc_zoom;
		} if(((adhoc_type!==null)&&(adhoc_type!==false)&&((!this.options.type))||(($(map_container).attr('data-override')=='true')&&(adhoc_type)))){
			this.options.type = adhoc_type;
		}

		if((!is_numeric(this.options.lat)) && (this.options.lat!==null)){
			if(adhoc_lat) this.options.lat = adhoc_lat;
			else this.options.lat = default_lat;
		} if((!is_numeric(this.options.lng)) && (this.options.lng!==null)){
			if(adhoc_lng) this.options.lng = adhoc_lng;
			else this.options.lng = default_lng;
		}

		var map_type = false;
		if(this.options.type=='SATELLITE'){
			map_type = google.maps.MapTypeId.SATELLITE;
		}else if(this.options.type=='HYBRID'){
			map_type = google.maps.MapTypeId.HYBRID;
		}else if(this.options.type=='TERRAIN'){
			map_type = google.maps.MapTypeId.TERRAIN;
		}else{
			map_type = google.maps.MapTypeId.ROADMAP;
		}

		if((this.options.imgs!==null)||(this.options.imgs!==false)){
			image_base = this.options.imgs;
		}

		clustered_markers[$(this.element).attr('id')] = [];

		if((!is_numeric(this.options.zoom)) && (this.options.zoom!==false)){
			this.options.zoom = 13;
		} if(this.options.zoom<1) this.options.zoom = 1;
		else if (this.options.zoom>16) this.options.zoom = 17;

		var map_options = {
			zoom: this.options.zoom,
			mapTypeId: map_type
		};
		map = new google.maps.Map(document.getElementById($(this.element).attr('id')), map_options);

		html5_status = false;

		if((this.options.lat===false)&&(this.options.lng===false)){
			// Try HTML5 geolocation
			if(navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function(position) {
					html5_status = 'Browser supports and uses HTML5 Geo-Location';
					current_lat = position.coords.latitude; current_lng = position.coords.longitude;
					map_position = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
					map.setCenter(map_position);
				}, function() {
					html5_status = 'Browser supports HTML5 Geo-Location';
					handle_no_geolocation(true);
				});
			}else{
			  html5_status = 'Browser does not support HTML5 Geo-Location';
			  handle_no_geolocation(false);
			}
		}else{
			html5_status = 'Specific Lat / Lng Used';
			current_marker['lat']=this.options.lat;
			current_marker['lng']=this.options.lng;
			current_lat = this.options.lat; current_lng = this.options.lng;
			map_position = new google.maps.LatLng(this.options.lat,this.options.lng);
			map.setCenter(map_position);
		}

		if(this.options.debug===true){
			debug.log('HTML5 Status = ', html5_status);
			debug.log('THIS LAT = ', this.options.lat);
			debug.log('THIS LNG = ', this.options.lng);
		}

		adhoc_marker = false;
		if((adhoc_lat!==null)&&(adhoc_lat!==false)&&(adhoc_lng!==null)&&(adhoc_lng!==false)){
			if((is_numeric(adhoc_lat)) && (is_numeric(adhoc_lng))){
				adhoc_marker = new Object();
				adhoc_marker['this_id'] = $(this.element).attr('id');
				if(!this.options.markers) this.options.markers = [];
				if((adhoc_lat!==null)&&(adhoc_lat!==false)){
					adhoc_marker['lat'] = adhoc_lat;
				} if((adhoc_lng!==null)&&(adhoc_lng!==false)){
					adhoc_marker['lng'] = adhoc_lng;
				} if((adhoc_title!==null)&&(adhoc_title!==false)){
					adhoc_marker['title'] = adhoc_title;
				} if((adhoc_slug!==null)&&(adhoc_slug!==false)){
					adhoc_marker['slug'] = adhoc_slug;
				} if((adhoc_content!==null)&&(adhoc_content!==false)){
					adhoc_marker['content'] = adhoc_content;
				} if((adhoc_open===true)||(adhoc_open==='true')){
					adhoc_marker['open'] = true;
				} if((adhoc_icon!==null)||(adhoc_icon!==false)){
					adhoc_marker['icon'] = adhoc_icon;
				} this.options.markers[this.options.markers.length] = adhoc_marker;

				if(this.options.debug===true){
					debug.log('adhoc_marker = ',adhoc_marker);
				}
			}
		}

		if(this.options.debug===true){
			debug.log('Markers = ',this.options.markers);
		}

		marker[$(map_container).attr('id')] = [];
		if(ajax){
			$.ajax({
			  url: ajax,
			  data: {lat:current_lat,lng:current_lng},
			  type: 'POST',
			  dataType: 'json',
			  success: function(result){
				these_markers = result;
				for(i=0; i< these_markers.length; i++) {
					open_window = false;
					if(these_markers[i].open===true) open_window = true;
					add_marker(
						$(map_container).attr('id'),
						these_markers[i].lat,
						these_markers[i].lng,
						these_markers[i].title,
						these_markers[i].content,
						these_markers[i].this_id,
						these_markers[i].slug,
						open_window,
						these_markers[i].icon
					);
				} if(these_markers.length>0){
					map_cluster($(map_container).attr('id'));
				}
			  }
			});
		}else{
			for(i=0; i< this.options.markers.length; i++) {
				if(this.options.debug===true){
					debug.log('This Marker = ',this.options.markers[i]);
				} open_window = false;
				if(this.options.markers[i].open===true) open_window = true;
				add_marker(
					$(map_container).attr('id'),
					this.options.markers[i].lat,
					this.options.markers[i].lng,
					this.options.markers[i].title,
					this.options.markers[i].content,
					this.options.markers[i].this_id,
					this.options.markers[i].slug,
					open_window,
					this.options.markers[i].icon
				);
			}
		} if(this.options.markers.length>0){
			map_cluster($(map_container).attr('id'));
		}

    };

	/* ---------------------- */
	/* gSpot Functions Follow */
	/* ---------------------- */

	/* COMMON FUNCTIONS */

	function is_numeric(value){
	  if(typeof value == 'number' && isFinite(value)){
		  return true;
	  } else {
		  return false;
	  }
	}

	function handle_no_geolocation(errorFlag) {
		current_lat = default_lat; current_lng = default_lng;
		map_position = new google.maps.LatLng(default_lat, default_lng);
		map.setCenter(map_position);
	}

	/* INFOWINDOW FUNCTIONS */

	function create_infobox(this_box,map_id,opts,this_id){
		google.maps.OverlayView.call(this_box);
		this_box.latlng_ = opts.latlng;
		this_box.map_ = opts.map;
		this_box.offsetVertical_ = -83;
		this_box.offsetHorizontal_ = 36;
		this_box.maxHeight_ = ($(map_container).height() - 90);
		this_box.maxWidth_ = ($(map_container).width() - 200);
		this_box.minWidth_ = ($(map_container).width() / 3);
		this_box.mongo_id = this_id;
		var me = this_box;
		this_box.boundsChangedListener_ = google.maps.event.addListener(map, 'bounds_changed', function() {
			return me.panMap.apply(me);
		});
		this_box.setMap(this_box.map_);
	}

	function remove_infobox(this_box){
		if(this_box.div_){
			this_box.div_.parentNode.removeChild(this_box.div_);
			this_box.div_ = null;
			info_box[this_box.mongo_id] = null;
		}
	}

	function draw_infobox(this_box){
		this_box.createElement();
		if(!this_box.div_) return;
		var pixPosition = this_box.getProjection().fromLatLngToDivPixel(this_box.latlng_);
		if (!pixPosition) return;
		this_box.div_.style.maxWidth = this_box.maxWidth_ + 'px';
		this_box.div_.style.left = (pixPosition.x + this_box.offsetHorizontal_) + 'px';
		this_box.div_.style.maxHeight = this_box.maxHeight_ + 'px';
		this_box.div_.style.height = 'auto';
		this_box.div_.style.top = (pixPosition.y + this_box.offsetVertical_) + 'px';
		this_box.div_.style.display = 'block';
	}

	function fill_infobox(this_box, this_url, title, content, this_id){
		var panes = this_box.getPanes();
		var div = this_box.div_;
		if(!div){
			/* WINDOW WRAPPER */
			div = this_box.div_ = document.createElement('div');
			jQuery(div).addClass('info-window-wrapper').css({
				'max-width':this_box.maxWidth_ +'px',
				'min-width':this_box.minWidth_ +'px',
				'max-height':this_box.maxHeight_ +'px'
			});
			/* WINDOW CONTENT */
			var content_div = document.createElement('div');
			jQuery(content_div).addClass('info-window-content').css({
				'max-height':(this_box.maxHeight_ - 100) +'px'
			}).html(content);
			/* CLOSE ICON */
			var title_bar = document.createElement('div');
			var close_img = document.createElement('img');
			jQuery(close_img).addClass('close-icon').attr('src',image_base+'/close.png');
			/* TITLE BAR */
			var title_content = '<div class="infobox-title"><a href="'+this_url+'">'+title+'</a></div>';
			jQuery(title_bar).addClass('info-window-title').html(title_content);
			title_bar.appendChild(close_img);
			/* ESTABLISH ACTIONS */
			function removeInfoBox(ib) {
				return function() {
					//this_box = remove_infobox(this_box);
					ib.setMap(null);
				};
			}
			function stealAction_(e) {
				if(navigator.userAgent.toLowerCase().indexOf('msie') != -1 && document.all) {
					window.event.cancelBubble = true;
					window.event.returnValue = false;
				}else{
					e.stopPropagation();
				}
			}
			/* CONTROL ACTIONS WITHIN INFO WINDOW */
			google.maps.event.addDomListener(close_img, 'click', removeInfoBox(this_box));
			google.maps.event.addDomListener(content_div, 'dblclick', stealAction_);
			google.maps.event.addDomListener(content_div, 'mousedown', stealAction_);
			google.maps.event.addDomListener(content_div, 'mousewheel', stealAction_);
			google.maps.event.addDomListener(content_div, 'DOMMouseScroll', stealAction_);
			google.maps.event.addDomListener(content_div, 'mousemove', stealAction_);
			/* CONSTRUCT THE WINDOW */
			div.appendChild(title_bar);
			div.appendChild(content_div);
			div.style.display = 'none';
			panes.floatPane.appendChild(div);
			this_box.panMap();
		} else if (div.parentNode != panes.floatPane) {
			div.parentNode.removeChild(div);
			panes.floatPane.appendChild(div);
		} else {
			// The panes have not changed, so no need to create or move the div.
		}
	}

	function pan_infobox(this_box){
		var map = this_box.map_;
		var bounds = map.getBounds();
		if (!bounds) return;
		var position = this_box.latlng_;
		var iwWidth = jQuery(this_box.div_).width();
		var iwHeight = jQuery(this_box.div_).height() - 100;
		var mapDiv = map.getDiv();
		var mapWidth = mapDiv.offsetWidth;
		var mapHeight = mapDiv.offsetHeight;
		var boundsSpan = bounds.toSpan();
		var longSpan = boundsSpan.lng();
		var latSpan = boundsSpan.lat();
		var degPixelX = longSpan / mapWidth;
		var degPixelY = latSpan / mapHeight;
		var centerX = position.lng() + ( iwWidth / 2) * degPixelX;
		var centerY = position.lat() - ( iwHeight / 2) * degPixelY;
		map.panTo(new google.maps.LatLng(centerY, centerX));
		google.maps.event.removeListener(this_box.boundsChangedListener_);
		this.boundsChangedListener_ = null;
	}

	function construct_infobox(this_id,map_id,opts,this_url,title,content,open_only){
		info_box[map_id][this_id] = function(map_id,opts){
			create_infobox(this,map_id,opts,this_id);
		}
		info_box[map_id][this_id].prototype = new google.maps.OverlayView();
		info_box[map_id][this_id].prototype.remove = function() {
			remove_infobox(this);
		};
		info_box[map_id][this_id].prototype.draw = function() {
			draw_infobox(this);
		};
		info_box[map_id][this_id].prototype.createElement = function() {
			fill_infobox(this, this_url, title, content, this_id);
		}
		info_box[map_id][this_id].prototype.panMap = function() {
			pan_infobox(this);
		};
		if(open_only){
			new_info_box[map_id] = [];
			new_info_box[map_id][this_id] = null;
			new_info_box[map_id][this_id] = new info_box[map_id][this_id](
				map_id,
				{latlng: marker[map_id][this_id].getPosition(), map: marker[map_id][this_id].map}
			);
		}else{
			new_info_box[map_id] = [];
			new_info_box[map_id][this_id] = new info_box[map_id][this_id](
				map_id,
				{latlng: marker[map_id][this_id].getPosition(), map: marker[map_id][this_id].map}
			);
		}
	}

	/* MARKER FUNCTIONS */

	function add_marker(map_id,lat,lng,title,content,this_id,slug,open,icon){
		var lat_lng = new google.maps.LatLng(lat,lng);
		var this_url = slug;
		var default_marker = 'default_marker.png';
		var default_shadow = 'shadow.png';
		if(icon) default_marker = icon;
		var image = new google.maps.MarkerImage(
			image_base+'/'+default_marker,
			new google.maps.Size(26,26),
			new google.maps.Point(0,0),
			new google.maps.Point(13,13)
		);
		var shadow = new google.maps.MarkerImage(
			image_base+'/'+default_shadow,
			new google.maps.Size(62,62),
			new google.maps.Point(0,0),
			new google.maps.Point(31,30)
		);
		marker[map_id][this_id] = new google.maps.Marker({
			position: lat_lng,
			icon: image,
			shadow: shadow,
			map: map,
			title: title
		});
		info_box[map_id] = [];
		clustered_markers[map_id][marker_count] = marker[map_id][this_id];
		if(open===true){
			if(info_box[map_id][this_id]==null){
				construct_infobox(
					this_id,
					map_id,
					{latlng: marker[map_id][this_id].getPosition(), map: marker[map_id][this_id].map},
					this_url,
					title,
					content
				);
			}
		}
		google.maps.event.addListener(marker[map_id][this_id], "click", function(e) {
			if(!info_box[map_id][this_id]){
				if(new_info_box[map_id][this_id]){
					if(new_info_box[map_id][this_id]['div_']===null){
						construct_infobox(
							this_id,
							map_id,
							{latlng: marker[map_id][this_id].getPosition(), map: marker[map_id][this_id].map},
							this_url,
							title,
							content,
							false
						);
					}
				}else{
					construct_infobox(
						this_id,
						map_id,
						{latlng: marker[map_id][this_id].getPosition(), map: marker[map_id][this_id].map},
						this_url,
						title,
						content,
						false
					);
				}
			}else{
				if(new_info_box[map_id][this_id]){
					if(new_info_box[map_id][this_id]['div_']===null){
						construct_infobox(
							this_id,
							map_id,
							{latlng: marker[map_id][this_id].getPosition(), map: marker[map_id][this_id].map},
							this_url,
							title,
							content,
							true
						);
					}
				}
			}
		});
		marker_count++;
	}

	/* CLUSTER FUNCTIONS */

	function map_cluster(map_id){
		var styles = [[{
		  url: image_base+'/shadow.png',
		  height: 62,
		  width: 62,
		  opt_anchor: [16, 0],
		  opt_textColor: '#333333',
		  opt_textSize: 18
		}, {
		  url: image_base+'/shadow.png',
		  height: 62,
		  width: 62,
		  opt_anchor: [16, 0],
		  opt_textColor: '#333333',
		  opt_textSize: 18
		}, {
		  url: image_base+'/shadow.png',
		  height: 62,
		  width: 62,
		  opt_anchor: [16, 0],
		  opt_textColor: '#333333',
		  opt_textSize: 18
		}]];
		if(marker_cluster[map_id]){
			marker_cluster[map_id].clearMarkers();
			//-> USING THIS MAKES MARKERS SKIP MAPS WHEN 2 OR MORE MAPS ON ONE PAGE
		}
		var zoom = 18;
		var size = 75;
		var style = 0;
		marker_cluster[map_id] = new MarkerClusterer(map, clustered_markers[map_id], {
			maxZoom: zoom,
			gridSize: size,
			styles: styles[style]
		});
	}

	/**
	 * @name MarkerClusterer for Google Maps v3
	 * @version version 1.0
	 * @author Luke Mahe
	 * @fileoverview
	 * The library creates and manages per-zoom-level clusters for large amounts of
	 * markers.
	 * <br/>
	 * This is a v3 implementation of the
	 * <a href="http://gmaps-utility-library-dev.googlecode.com/svn/tags/markerclusterer/"
	 * >v2 MarkerClusterer</a>.
	 */

	/**
	 * Licensed under the Apache License, Version 2.0 (the "License");
	 * you may not use this file except in compliance with the License.
	 * You may obtain a copy of the License at
	 *
	 *     http://www.apache.org/licenses/LICENSE-2.0
	 *
	 * Unless required by applicable law or agreed to in writing, software
	 * distributed under the License is distributed on an "AS IS" BASIS,
	 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 * See the License for the specific language governing permissions and
	 * limitations under the License.
	 */

	function MarkerClusterer(map, opt_markers, opt_options) {
		/* OPTIONS */
		this.extend(MarkerClusterer, google.maps.OverlayView);
		this.map_ = map;
		this.markers_ = [];
		this.clusters_ = [];
		this.sizes = [53, 56, 66, 78, 90];
		this.styles_ = [];
		this.ready_ = false;
		var options = opt_options || {};
		this.gridSize_ = options['gridSize'] || 60;
		this.maxZoom_ = options['maxZoom'] || null;
		this.styles_ = options['styles'] || [];
		this.imagePath_ = options['imagePath'];
		this.imageExtension_ = options['imageExtension'];
		this.zoomOnClick_ = options['zoomOnClick'] || true;
		this.setupStyles_();
		this.setMap(map);
		this.prevZoom_ = this.map_.getZoom();
		/* LISTENERS */
		var that = this;
		google.maps.event.addListener(this.map_, 'zoom_changed', function() {
			var maxZoom = that.map_.mapTypes[that.map_.getMapTypeId()].maxZoom;
			var zoom = that.map_.getZoom();
			if (zoom < 0 || zoom > maxZoom) {
			  return;
			}
			if (that.prevZoom_ != zoom) {
			  that.prevZoom_ = that.map_.getZoom();
			  that.resetViewport();
			}
		});
		google.maps.event.addListener(this.map_, 'bounds_changed', function() {
			that.redraw();
		});
		/* MARKERS */
		if (opt_markers && opt_markers.length) {
			this.addMarkers(opt_markers, false);
		}
	}

	MarkerClusterer.prototype.extend = function(obj1, obj2) {
		return (function(object) {
		for (property in object.prototype) {
		  this.prototype[property] = object.prototype[property];
		}
		return this;
		}).apply(obj1, [obj2]);
	};

	MarkerClusterer.prototype.onAdd = function() {
		this.setReady_(true);
	};

	MarkerClusterer.prototype.idle = function() {};
	MarkerClusterer.prototype.draw = function() {};

	MarkerClusterer.prototype.setupStyles_ = function() {
		for (var i = 0, size; size = this.sizes[i]; i++) {
		this.styles_.push({
		  url: this.imagePath_ + (i + 1) + '.' + this.imageExtension_,
		  height: size,
		  width: size
		});
		}
	};

	MarkerClusterer.prototype.setStyles = function(styles) {
		this.styles_ = styles;
	};

	MarkerClusterer.prototype.getStyles = function() {
		return this.styles_;
	};

	MarkerClusterer.prototype.isZoomOnClick = function() {
		return this.zoomOnClick_;
	};

	MarkerClusterer.prototype.getMarkers = function() {
		return this.markers_;
	};

	MarkerClusterer.prototype.getTotalMarkers = function() {
		return this.markers_;
	};

	MarkerClusterer.prototype.setMaxZoom = function(maxZoom) {
		this.maxZoom_ = maxZoom;
	};

	MarkerClusterer.prototype.getMaxZoom = function() {
		return this.maxZoom_ || this.map_.mapTypes[this.map_.getMapTypeId()].maxZoom;
	};

	MarkerClusterer.prototype.calculator_ = function(markers, numStyles) {
		var index = 0;
		var count = markers.length;
		var dv = count;
		while (dv !== 0) {
		dv = parseInt(dv / 10, 10);
		index++;
	}
	index = Math.min(index, numStyles);
		return {
		text: count,
		index: index
		};
	};

	MarkerClusterer.prototype.setCalculator = function(calculator) {
		this.calculator_ = calculator;
	};

	MarkerClusterer.prototype.getCalculator = function() {
		return this.calculator_;
	};

	MarkerClusterer.prototype.addMarkers = function(markers, opt_nodraw) {
		for (var i = 0, marker; marker = markers[i]; i++) {
		this.pushMarkerTo_(marker);
		}
		if (!opt_nodraw) {
		this.redraw();
		}
	};

	MarkerClusterer.prototype.pushMarkerTo_ = function(marker) {
		marker.setVisible(false);
		marker.setMap(null);
		marker.isAdded = false;
		if (marker['draggable']) {
			// If the marker is draggable add a listener so we update the clusters on
			// the drag end.
			var that = this;
			google.maps.event.addListener(marker, 'dragend', function() {
			  marker.isAdded = false;
			  that.resetViewport();
			  that.redraw();
			});
		}
		this.markers_.push(marker);
	};

	MarkerClusterer.prototype.addMarker = function(marker, opt_nodraw) {
		this.pushMarkerTo_(marker);
		if (!opt_nodraw) {
		this.redraw();
		}
	};

	MarkerClusterer.prototype.removeMarker = function(marker) {
		var index = -1;
		if (this.markers_.indexOf) {
		index = this.markers_.indexOf(marker);
			} else {
			for (var i = 0, m; m = this.markers_[i]; i++) {
			  if (m == marker) {
				index = i;
				continue;
			  }
			}
		}
		if (index == -1) {
			// Marker is not in our list of markers.
			return false;
		}
		this.markers_.splice(index, 1);
		marker.setVisible(false);
		marker.setMap(null);
		this.resetViewport();
		this.redraw();
		return true;
	};

	MarkerClusterer.prototype.setReady_ = function(ready) {
		if (!this.ready_) {
		this.ready_ = ready;
		this.createClusters_();
		}
	};

	MarkerClusterer.prototype.getTotalClusters = function() {
		return this.clusters_.length;
	};

	MarkerClusterer.prototype.getMap = function() {
		return this.map_;
	};

	MarkerClusterer.prototype.setMap = function(map) {
		this.map_ = map;
	};

	MarkerClusterer.prototype.getGridSize = function() {
		return this.gridSize_;
	};

	MarkerClusterer.prototype.setGridSize = function(size) {
		this.gridSize_ = size;
	};

	MarkerClusterer.prototype.getExtendedBounds = function(bounds) {
		var projection = this.getProjection();

		// Turn the bounds into latlng.
		var tr = new google.maps.LatLng(bounds.getNorthEast().lat(),
		  bounds.getNorthEast().lng());
		var bl = new google.maps.LatLng(bounds.getSouthWest().lat(),
		  bounds.getSouthWest().lng());

		// Convert the points to pixels and the extend out by the grid size.
		var trPix = projection.fromLatLngToDivPixel(tr);
		trPix.x += this.gridSize_;
		trPix.y -= this.gridSize_;

		var blPix = projection.fromLatLngToDivPixel(bl);
		blPix.x -= this.gridSize_;
		blPix.y += this.gridSize_;

		// Convert the pixel points back to LatLng
		var ne = projection.fromDivPixelToLatLng(trPix);
		var sw = projection.fromDivPixelToLatLng(blPix);

		// Extend the bounds to contain the new bounds.
		bounds.extend(ne);
		bounds.extend(sw);

		return bounds;
	};

	MarkerClusterer.prototype.isMarkerInBounds_ = function(marker, bounds) {
		return bounds.contains(marker.getPosition());
	};

	MarkerClusterer.prototype.clearMarkers = function() {
		this.resetViewport();

		// Set the markers a empty array.
		this.markers_ = [];
	};

	MarkerClusterer.prototype.resetViewport = function() {
		// Remove all the clusters
		for (var i = 0, cluster; cluster = this.clusters_[i]; i++) {
			cluster.remove();
		}

		// Reset the markers to not be added and to be invisible.
		for (var i = 0, marker; marker = this.markers_[i]; i++) {
			marker.isAdded = false;
			marker.setMap(null);
			marker.setVisible(false);
		}

		this.clusters_ = [];
	};

	MarkerClusterer.prototype.redraw = function() {
		this.createClusters_();
	};

	MarkerClusterer.prototype.createClusters_ = function() {
		if (!this.ready_) {
			return;
		}

		// Get our current map view bounds.
		// Create a new bounds object so we don't affect the map.
		var mapBounds = new google.maps.LatLngBounds(this.map_.getBounds().getSouthWest(),
		  this.map_.getBounds().getNorthEast());
		var bounds = this.getExtendedBounds(mapBounds);

		for (var i = 0, marker; marker = this.markers_[i]; i++) {
			var added = false;
			if (!marker.isAdded && this.isMarkerInBounds_(marker, bounds)) {
			  for (var j = 0, cluster; cluster = this.clusters_[j]; j++) {
				if (!added && cluster.getCenter() &&
					cluster.isMarkerInClusterBounds(marker)) {
				  added = true;
				  cluster.addMarker(marker);
				  break;
				}
			  }

			  if (!added) {
				// Create a new cluster.
				var cluster = new Cluster(this);
				cluster.addMarker(marker);
				this.clusters_.push(cluster);
			  }
			}
		}
	};

	function Cluster(markerClusterer) {
		this.markerClusterer_ = markerClusterer;
		this.map_ = markerClusterer.getMap();
		this.gridSize_ = markerClusterer.getGridSize();
		this.center_ = null;
		this.markers_ = [];
		this.bounds_ = null;
		this.clusterIcon_ = new ClusterIcon(this, markerClusterer.getStyles(),
		  markerClusterer.getGridSize());
	}

	Cluster.prototype.isMarkerAlreadyAdded = function(marker) {
		if (this.markers_.indexOf) {
			return this.markers_.indexOf(marker) != -1;
		} else {
			for (var i = 0, m; m = this.markers_[i]; i++) {
			  if (m == marker) {
				return true;
			  }
			}
		}
		return false;
	};

	Cluster.prototype.addMarker = function(marker) {
		if (this.isMarkerAlreadyAdded(marker)) {
			return false;
		}

		if (!this.center_) {
			this.center_ = marker.getPosition();
			this.calculateBounds_();
		}

		if (this.markers_.length == 0) {
			// Only 1 marker in this cluster so show the marker.
			marker.setMap(this.map_);
			marker.setVisible(true);
			} else if (this.markers_.length == 1) {
			// Hide the 1 marker that was showing.
			this.markers_[0].setMap(null);
			this.markers_[0].setVisible(false);
		}

		marker.isAdded = true;
		this.markers_.push(marker);

		this.updateIcon();
		return true;
	};

	Cluster.prototype.getMarkerClusterer = function() {
		return this.markerClusterer_;
	};

	Cluster.prototype.getBounds = function() {
		this.calculateBounds_();
		return this.bounds_;
	};

	Cluster.prototype.remove = function() {
		this.clusterIcon_.remove();
		delete this.markers_;
	};

	Cluster.prototype.getCenter = function() {
		return this.center_;
	};

	Cluster.prototype.calculateBounds_ = function() {
		var bounds = new google.maps.LatLngBounds(this.center_, this.center_);
		this.bounds_ = this.markerClusterer_.getExtendedBounds(bounds);
	};

	Cluster.prototype.isMarkerInClusterBounds = function(marker) {
		return this.bounds_.contains(marker.getPosition());
	};

	Cluster.prototype.getMap = function() {
		return this.map_;
	};

	Cluster.prototype.updateIcon = function() {
		var zoom = this.map_.getZoom();
		var mz = this.markerClusterer_.getMaxZoom();

		if (zoom > mz) {
			// The zoom is greater than our max zoom so show all the markers in cluster.
			for (var i = 0, marker; marker = this.markers_[i]; i++) {
			  marker.setMap(this.map_);
			  marker.setVisible(true);
			}
			return;
		}

		if (this.markers_.length < 2) {
			// We have 0 or 1 markers so hide the icon.
			this.clusterIcon_.hide();
			return;
		}

		var numStyles = this.markerClusterer_.getStyles().length;
		var sums = this.markerClusterer_.getCalculator()(this.markers_, numStyles);
		this.clusterIcon_.setCenter(this.center_);
		this.clusterIcon_.setSums(sums);
		this.clusterIcon_.show();
	};

	function ClusterIcon(cluster, styles, opt_padding) {
		cluster.getMarkerClusterer().extend(ClusterIcon, google.maps.OverlayView);
		this.styles_ = styles;
		this.padding_ = opt_padding || 0;
		this.cluster_ = cluster;
		this.center_ = null;
		this.map_ = cluster.getMap();
		this.div_ = null;
		this.sums_ = null;
		this.visible_ = false;
		this.setMap(this.map_);
	}

	ClusterIcon.prototype.triggerClusterClick = function() {
		var markerClusterer = this.cluster_.getMarkerClusterer();
		// Trigger the clusterclick event.
		google.maps.event.trigger(markerClusterer, 'clusterclick', [this.cluster_]);
		if (markerClusterer.isZoomOnClick()) {
		// Center the map on this cluster.
		this.map_.panTo(this.cluster_.getCenter());
		// Zoom into the cluster.
		this.map_.fitBounds(this.cluster_.getBounds());
		}
	};

	ClusterIcon.prototype.onAdd = function() {
		this.div_ = document.createElement('DIV');
			if (this.visible_) {
			var pos = this.getPosFromLatLng_(this.center_);
			this.div_.style.cssText = this.createCss(pos);
			this.div_.innerHTML = this.sums_.text;
		}
		var panes = this.getPanes();
		panes.overlayImage.appendChild(this.div_);

		var that = this;
		google.maps.event.addDomListener(this.div_, 'click', function() {
		that.triggerClusterClick();
		});
	};

	ClusterIcon.prototype.getPosFromLatLng_ = function(latlng) {
		var pos = this.getProjection().fromLatLngToDivPixel(latlng);
		pos.x -= parseInt(this.width_ / 2, 10);
		pos.y -= parseInt(this.height_ / 2, 10);
		return pos;
	};

	ClusterIcon.prototype.draw = function() {
		if (this.visible_) {
			var pos = this.getPosFromLatLng_(this.center_);
			this.div_.style.top = pos.y + 'px';
			this.div_.style.left = pos.x + 'px';
		}
	};

	ClusterIcon.prototype.hide = function() {
		if (this.div_) {
			this.div_.style.display = 'none';
		}
		this.visible_ = false;
	};

	ClusterIcon.prototype.show = function() {
		if (this.div_) {
			var pos = this.getPosFromLatLng_(this.center_);
			this.div_.style.cssText = this.createCss(pos);
			this.div_.style.display = '';
		}
		this.visible_ = true;
	};

	ClusterIcon.prototype.remove = function() {
		this.setMap(null);
	};

	ClusterIcon.prototype.onRemove = function() {
		if (this.div_ && this.div_.parentNode) {
			this.hide();
			this.div_.parentNode.removeChild(this.div_);
			this.div_ = null;
		}
	};

	ClusterIcon.prototype.setSums = function(sums) {
		this.sums_ = sums;
		this.text_ = sums.text;
		this.index_ = sums.index;
		if (this.div_) {
			this.div_.innerHTML = sums.text;
		}
		this.useStyle();
	};

	ClusterIcon.prototype.useStyle = function() {
		var index = Math.max(0, this.sums_.index - 1);
		index = Math.min(this.styles_.length - 1, index);
		var style = this.styles_[index];
		this.url_ = style.url;
		this.height_ = style.height;
		this.width_ = style.width;
		this.textColor_ = style.opt_textColor;
		this.anchor = style.opt_anchor;
		this.textSize_ = style.opt_textSize;
	};

	ClusterIcon.prototype.setCenter = function(center) {
		this.center_ = center;
	};

	ClusterIcon.prototype.createCss = function(pos) {
		var style = [];
		if (document.all) {
		style.push('filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(' +
			'sizingMethod=scale,src="' + this.url_ + '");');
		} else {
		style.push('background:url(' + this.url_ + ');');
		}

		if (typeof this.anchor_ === 'object') {
		if (typeof this.anchor_[0] === 'number' && this.anchor_[0] > 0 &&
			this.anchor_[0] < this.height_) {
		  style.push('height:' + (this.height_ - this.anchor_[0]) +
			  'px; padding-top:' + this.anchor_[0] + 'px;');
		} else {
		  style.push('height:' + this.height_ + 'px; line-height:' + this.height_ +
			  'px;');
		}
		if (typeof this.anchor_[1] === 'number' && this.anchor_[1] > 0 &&
			this.anchor_[1] < this.width_) {
		  style.push('width:' + (this.width_ - this.anchor_[1]) +
			  'px; padding-left:' + this.anchor_[1] + 'px;');
		} else {
		  style.push('width:' + this.width_ + 'px; text-align:center;');
		}
		} else {
		style.push('height:' + this.height_ + 'px; line-height:' +
			this.height_ + 'px; width:' + this.width_ + 'px; text-align:center;');
		}

		var txtColor = this.textColor_ ? this.textColor_ : 'black';
		var txtSize = this.textSize_ ? this.textSize_ : 11;

		style.push('cursor:pointer; top:' + pos.y + 'px; left:' +
		  pos.x + 'px; color:' + txtColor + '; position:absolute; font-size:' +
		  txtSize + 'px; font-family:Arial,sans-serif; font-weight:bold');
		return style.join('');
	};

	// Export Symbols for Closure
	// If you are not going to compile with closure then you can remove the
	// code below.
	window['MarkerClusterer'] = MarkerClusterer;
	MarkerClusterer.prototype['addMarker'] = MarkerClusterer.prototype.addMarker;
	MarkerClusterer.prototype['addMarkers'] = MarkerClusterer.prototype.addMarkers;
	MarkerClusterer.prototype['clearMarkers'] = MarkerClusterer.prototype.clearMarkers;
	MarkerClusterer.prototype['getCalculator'] = MarkerClusterer.prototype.getCalculator;
	MarkerClusterer.prototype['getGridSize'] = MarkerClusterer.prototype.getGridSize;
	MarkerClusterer.prototype['getMap'] = MarkerClusterer.prototype.getMap;
	MarkerClusterer.prototype['getMarkers'] = MarkerClusterer.prototype.getMarkers;
	MarkerClusterer.prototype['getMaxZoom'] = MarkerClusterer.prototype.getMaxZoom;
	MarkerClusterer.prototype['getStyles'] = MarkerClusterer.prototype.getStyles;
	MarkerClusterer.prototype['getTotalClusters'] = MarkerClusterer.prototype.getTotalClusters;
	MarkerClusterer.prototype['getTotalMarkers'] = MarkerClusterer.prototype.getTotalMarkers;
	MarkerClusterer.prototype['redraw'] = MarkerClusterer.prototype.redraw;
	MarkerClusterer.prototype['removeMarker'] = MarkerClusterer.prototype.removeMarker;
	MarkerClusterer.prototype['resetViewport'] = MarkerClusterer.prototype.resetViewport;
	MarkerClusterer.prototype['setCalculator'] = MarkerClusterer.prototype.setCalculator;
	MarkerClusterer.prototype['setGridSize'] = MarkerClusterer.prototype.setGridSize;
	MarkerClusterer.prototype['onAdd'] = MarkerClusterer.prototype.onAdd;
	MarkerClusterer.prototype['draw'] = MarkerClusterer.prototype.draw;
	MarkerClusterer.prototype['idle'] = MarkerClusterer.prototype.idle;
	ClusterIcon.prototype['onAdd'] = ClusterIcon.prototype.onAdd;
	ClusterIcon.prototype['draw'] = ClusterIcon.prototype.draw;
	ClusterIcon.prototype['onRemove'] = ClusterIcon.prototype.onRemove;

	/* -------------------------- */
	/* INLINE DEBUG FUNCTIONALITY */
	/* -------------------------- */

	window.debug = (function(){
	  var window = this,
		aps = Array.prototype.slice,
		con = window.console,
		that = {},
		callback_func,
		callback_force,
		log_level = 9,
		log_methods = [ 'error', 'warn', 'info', 'debug', 'log' ],
		pass_methods = 'assert clear count dir dirxml exception group groupCollapsed groupEnd profile profileEnd table time timeEnd trace'.split(' '),
		idx = pass_methods.length,
		logs = [];
	  while ( --idx >= 0 ) {
		(function( method ){
		  that[ method ] = function() {
			log_level !== 0 && con && con[ method ]
			  && con[ method ].apply( con, arguments );
		  }
		})( pass_methods[idx] );
	  }

	  idx = log_methods.length;
	  while ( --idx >= 0 ) {
		(function( idx, level ){
		  that[ level ] = function() {
			var args = aps.call( arguments ),
			  log_arr = [ level ].concat( args );
			logs.push( log_arr );
			exec_callback( log_arr );
			if ( !con || !is_level( idx ) ) { return; }
			con.firebug ? con[ level ].apply( window, args )
			  : con[ level ] ? con[ level ]( args )
			  : con.log( args );
		  };
		})( idx, log_methods[idx] );
	  }
	  function exec_callback( args ) {
		if ( callback_func && (callback_force || !con || !con.log) ) {
		  callback_func.apply( window, args );
		}
	  };
	  that.setLevel = function( level ) {
		log_level = typeof level === 'number' ? level : 9;
	  };
	  function is_level( level ) {
		return log_level > 0
		  ? log_level > level
		  : log_methods.length + log_level <= level;
	  };
	  that.setCallback = function() {
		var args = aps.call( arguments ),
		  max = logs.length,
		  i = max;
		callback_func = args.shift() || null;
		callback_force = typeof args[0] === 'boolean' ? args.shift() : false;
		i -= typeof args[0] === 'number' ? args.shift() : max;
		while ( i < max ) {
		  exec_callback( logs[i++] );
		}
	  };
	  return that;
	})();

    /* PREVENT MULTIPLE INSTANTIATIONS */
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin( this, options ));
            }
        });
    }

})( jQuery, window, document );