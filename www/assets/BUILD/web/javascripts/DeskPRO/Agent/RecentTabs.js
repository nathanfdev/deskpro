Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.RecentTabs = new Orb.Class({
	initialize: function() {
		var self = this;
		this.recentTabIds = {};
		this.recent  = [];
		this.recentPendingSync = [];
		this.list = $('#recent_tabs_list');
		this.idW = 0;

		var eatNext = false;
		$('#recent_tabs_list').on('click', function(ev) {
			Orb.shimClickCallbackPop();
		});
		$('#recent_tabs_list_filter').on('keydown', function(ev) {
			if (ev.keyCode == 13 /* enter key */) {
				var current = self.list.find('.dp-cursor');
				eatNext = true;
				if (current[0]) {
					DeskPRO_Window.runPageRouteFromElement(current.find('a'));
					Orb.shimClickCallbackPop();
				}

			} else if (ev.keyCode == 40 /* down key */ || ev.keyCode == 38 /* up key */) {
				eatNext = true;
				var current = self.list.find('.dp-cursor');
				current.removeClass('dp-cursor');
				var dir = ev.keyCode == 40 ? 'down' : 'up';
				var next;

				if (!current.length) {
					if (dir == 'down') {
						self.list.find('.dp-vis').first().addClass('dp-cursor');
					} else {
						self.list.find('.dp-vis').last().addClass('dp-cursor');
					}
				} else {
					if (dir == 'down') {
						next = current.next('li.dp-vis');
						if (!next.length) {
							next = self.list.find('.dp-vis').first().addClass('dp-cursor');
						}
					} else {
						next = current.prev('li.dp-vis');
						if (!next.length) {
							next = self.list.find('.dp-vis').last().addClass('dp-cursor');
						}
					}

					next.addClass('dp-cursor');
				}
			}
		}).on('keyup', function(ev) {
			if (eatNext) {
				return;
			}
			var val = $.trim($(this).val());

			if (!val) {
				self.list.find('li').show().addClass('dp-vis');
				return;
			}

			val = val.toLowerCase();

			self.list.find('li').each(function() {
				if ($(this).data('string-match').indexOf(val) !== -1) {
					$(this).show().addClass('dp-vis');
				} else {
					$(this).hide().removeClass('dp-vis');
				}
			});
		});

		this.reloadRecentTabs();
	},

	reloadRecentTabs: function() {
		$.ajax({
			url: BASE_URL + 'agent/ui/load-recent-tabs.json',
			type: 'GET',
			dataType: 'JSON',
			context: this,
			success: function(data) {
				// Any tabs opened before the last list was re-loaded
				var readd = false, self = this;
				if (this.recent.length) {
					readd = this.recent;
				}

				var pending = this.recentPendingSync;

				this.recent = [];

				// Regen tab IDs lookup map
				if (data) {
					this.addBatch(data);
				}

				// Reset the proper pending list (dont re-sync the ones we just loaded)
				this.recentPendingSync = pending;

				if (readd) {
          this.addBatch(readd);
				}
			}
		});
	},

	open: function() {
		// Backwards compat
	},

	close: function() {
		// Backwards compat
	},

	/**
	 * Add a new item to the list
	 *
	 * @param {String} type
	 * @param {Integer} id
	 * @param {String} title
	 * @param {String} url
	 * @param {Integer} ts
	 */
	add: function(type, id, title, url, ts) {

		$('#recent_tabs_list_li_none').remove();

		if (!ts) {
			ts = parseInt((new Date()).getTime() / 1000);
		}

		var idString = type + '-' + id, idx = null;

		// If we already have the tab, remove it so it will be
		// re-added to the front of the array
		if (this.recentTabIds[idString]) {
			delete this.recentTabIds[idString];
			this.recent.forEach(function(item, i) {
				if ((item[0] + '-' + item[1]) == idString) {
					idx = i;
					return false;
				}
			});

			if (idx !== null) {
				this.recent.splice(idx, 1);
				this.list.find('li.' + idString).remove();
			}
		}

		this.recent.unshift([type, id, title, url, ts]);
		this.recentTabIds[idString] = true;

		while (this.recent.length > 150) {
			var last = this.recent.pop();
			this.list.find('li.' + last[0] + '-' + last[1]).remove();
		}

		var itm = [type, id, title, url, ts];
		this.recentPendingSync.unshift(itm);
		this.renderRow(itm);

		this.length = this.recent.length;
	},

	addBatch: function(items) {
    $('#recent_tabs_list_li_none').remove();
    var nowTs = parseInt((new Date()).getTime() / 1000);

    var self = this;
    var rows = [];
    var removeIds = [];
    var filterVal = $.trim($('#recent_tabs_list_filter').val());

    items.forEach(function(item) {
    	var type = item[0], id = item[1], title = item[2], url = item[3], ts = item[4] || nowTs;
      var idString = type + '-' + id, idx = null;

      // If we already have the tab, remove it so it will be
      // re-added to the front of the array
      if (self.recentTabIds[idString]) {
        delete self.recentTabIds[idString];
        self.recent.forEach(function(item, i) {
          if ((item[0] + '-' + item[1]) == idString) {
            idx = i;
            return false;
          }
        });

        if (idx !== null) {
          self.recent.splice(idx, 1);
          removeIds.push(idString);
        }
      }

      self.recent.unshift([type, id, title, url, ts]);
      self.recentTabIds[idString] = true;

      var itm = [type, id, title, url, ts];
      self.recentPendingSync.unshift(itm);
      rows.unshift(self.renderRowHtml(itm, filterVal));
		});

    var updateList = function() {
      if (rows.length) {
        self.list.prepend($(rows));
			}
			if (removeIds) {
        removeIds.forEach(function(id) {
					self.list.find('li.' + id).remove();
				});
			}
		};

    while (this.recent.length > 150) {
      var last = this.recent.pop();
      removeIds.push(last[0] + '-' + last[1]);
    }

    if (rows.length || removeIds.length) {
			if (window.requestAnimationFrame) {
				window.requestAnimationFrame(updateList);
			} else {
        updateList();
			}
		}
	},

	/**
	 * Render an item onto the beginning of the list
	 *
	 * @param {Array} item
	 * @returns {jQuery}
	 */
	renderRow: function(item, dontAdd, filterMatch) {
		var row = $(this.renderRowHtml(item, filterMatch));
		if (!dontAdd) {
      this.list.prepend(row);
    }

		return row;
	},

	renderRowHtml: function(item, filterVal) {
		var stringMatch = item[2].toLowerCase();

		var d = new Date(item[4]*1000);

		var timeAgo = Orb.Util.TimeAgo.get(d);
		var classNames = [item[0] + '-' + item[1] + ' ' + item[0]];
		var doHide = false;
		if (typeof filterVal === 'undefined') {
			filterVal = $.trim($('#recent_tabs_list_filter').val());
		}
		if (!filterVal || stringMatch.indexOf(filterVal.toLowerCase()) !== -1) {
			classNames.push('dp-vis');
		} else {
			doHide = true;
		}

		var stringMatchV = Orb.escapeHtml(stringMatch);

		return '<li class="' + classNames.join(' ') + '" '+ (doHide ? 'style="display:none"' : '') +' data-string-match="'+stringMatchV+'">\n' +
			'<a data-route="page:'+item[3]+'" route-notabreload="1">\n' +
			'  <time datetime="' + d.toISOString() + '">' + timeAgo + '</time>' +
			'  <div class="title">\n' +
			'    <i class="icon-envelope fa dp-icon-placeholder"></i>\n' +
			'    <strong>'+item[1]+'</strong>\n' +
			'    <span>'+Orb.escapeHtml(item[2]+'')+'</span>\n' +
			'  </div>\n' +
			'</a>\n' +
			'</li>';
	},


	/**
	 * @return {Array}
	 */
	getAll: function() {
		return this.recent;
	},


	/**
	 * Clears recent list
	 */
	clear: function() {
		this.recent = [];
		this.recentPendingSync = [];
		this.list.clear();
		this.length = 0;
	},


	/**
	 * Gets info for the last (oldest) item in the list
	 *
	 * @returns {Array}
	 */
	getLast: function() {
		if (this.recent.length) {
			return this.recent[this.recent.length-1];
		}

		return null;
	},


	/**
	 * Gets info for the first (latest) item in the list
	 *
	 * @returns {Array}
	 */
	getFirst: function() {
		if (this.recent.length) {
			return this.recent[0];
		}

		return null;
	}
});
