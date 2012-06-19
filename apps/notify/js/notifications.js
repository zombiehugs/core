OC.notify = {
	dom: {
		icon: $('<a id="notify-icon" class="header-right header-action" href="#" title=""><img class="svg" alt="" src="" /></a>'),
		counter: $('<span id="notify-counter" data-count="0">0</span>'),
		listContainer: $('<div id="notify-list" class="hidden"></div>'),
		list: $('<ul></ul>')
	},
	notificationTemplate: $('<li class="notification"><a href="#"></a></li>'),
	notifications: [],
    unreadNumber: 0,
    lastUpdateTime: 0,
    autoRefresh: true,
    refreshInterval: 100,
	loaded: false,
	updated: false,
    setCount: function(count) {
		if(count < 0) {
			count = 0;
		}
		OC.notify.dom.counter.attr("data-count", count).text(count);
		OC.notify.setDocTitle();
	},
	changeCount: function(diff) {
		var count = parseInt(OC.notify.dom.counter.attr("data-count"));
		OC.notify.setCount(count + diff);
	},
	originalDocTitle: document.title,
	setDocTitle: function() {
		if(!document.title.match(/^\([0-9]+\) /)) {
			OC.notify.originalDocTitle = document.title;
		}
		var count = parseInt(OC.notify.dom.counter.attr("data-count"));
		if(count > 0) {
			document.title = "(" + count + ") " + OC.notify.originalDocTitle;
		} else {
			document.title = OC.notify.originalDocTitle;
		}
	},
	markRead: function(id, read) {
		if(typeof(read) == "undefined") {
			read = true;
		}
		var notify = $('div.notification[data-notify-id="' + id + '"]');
		$.post(
			OC.filePath('notify','ajax','markRead.php'),
			{id: id, read: read},
			function(data) {
				if(data.status == "success") {
					if(notify.hasClass("unread") && read) {
						notify.removeClass("unread").addClass("read");
					} else if(notify.hasClass("read") && !read) {
						notify.removeClass("read").addClass("unread");
					}
					OC.notify.setCount(data.unread);
				}
			}
		);
		if(notify.hasClass("unread") && read) {
			notify.removeClass("unread").addClass("read");
			OC.notify.changeCount(-1);
		} else if(notify.hasClass("read") && !read) {
			notify.removeClass("read").addClass("unread");
			OC.notify.changeCount(1);
		}
	},
	getCount: function() {
		var current = parseInt(OC.notify.dom.counter.attr("data-count"));
		$.post(
			OC.filePath('notify','ajax','getCount.php'),
			null,
			function(data) {
				var count = parseInt(data);
				if(count != current) {
					OC.notify.setCount(parseInt(data));
					OC.notify.updated = true;
				}
				if(OC.notify.autoRefresh) {
					window.setTimeout("OC.notify.getCount()", OC.notify.refreshInterval * 1000);
				}
			}
		);
	}
};

$(document).ready(function() {
	OC.notify.dom.icon.append(OC.notify.dom.counter).click(function(event) {
		OC.notify.dom.listContainer.slideToggle();
		event.preventDefault();
		event.stopPropagation();
	}).attr('title', t('notify', 'Notifications'))
		.children('img').attr('alt', t('notify', 'Notifications')).attr('src', OC.imagePath('notify', 'headerIcon.svg'));
    OC.notify.dom.listContainer.click(function(event) {
		event.stopPropagation();
	});
    $(window).click(function() {
        OC.notify.dom.listContainer.slideUp();
    });
    $(OC.notify.dom.icon, OC.notify.dom.listContainer).appendTo('#header');
    OC.notify.setDocTitle();
});
