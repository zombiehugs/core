OC.notify = {
	dom: {
		icon: $('<a id="notify-icon" class="header-right header-action" href="#" title=""><img class="svg" alt="" src="" /></a>'),
		counter: $('<span id="notify-counter" data-count="0">0</span>'),
		listContainer: $('<div id="notify-list" class="hidden"></div>'),
		list: $('<ul></ul>')
	},
	notificationTemplate: $('<li class="notification"><a href="#"></a></li>'),
	notifications: [],
	Notification: function(arg) {
		if(typeof(arg) == "string") {
			this.text = arg;
		} else {
			this.text = arg.text;
		}
		this.read = false;
		this.id = 0;
		this.element = OC.notify.notificationTemplate.clone().get(0);
		this.setRead = function(flag) {
			this.read = flag;
			if(this.read) {
				$(this.element).removeClass('unread').addClass('read');
			} else {
				$(this.element).removeClass('read').addClass('unread');
			}
		};
		$(this.element).addClass('unread').children('a').text(this.text);
		return this;
	},
	addNotification: function(notification) {
		OC.notify.notifications[notification.id] = notification;
		$(notification.element).attr('data-notify-id', notification.id).prependTo(OC.notify.dom.list);
		if(!notification.read) OC.notify.changeCount(1);
	},
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
    OC.notify.dom.listContainer.html('<strong>' + t('notify', 'Notifications') + '</strong>').append(OC.notify.dom.list).click(function(event) {
		event.stopPropagation();
	});
    $(window).click(function() {
        OC.notify.dom.listContainer.slideUp();
    });
    $('<span id="readAllNotifications">mark all as read</span>').click(function(e) {
		for(var n in OC.notify.notifications) {
			OC.notify.notifications[n].setRead(true);
		}
		OC.notify.setCount(0);
	}).appendTo(OC.notify.dom.listContainer);
    OC.notify.dom.icon.appendTo('#header').after(OC.notify.dom.listContainer);
    OC.notify.setDocTitle();
    var n = new OC.notify.Notification("Test 1");
    n.id = 1;
    OC.notify.addNotification(n);
    n = new OC.notify.Notification("Test 2");
    n.id = 2;
    n.setRead(true);
    OC.notify.addNotification(n);
    n = new OC.notify.Notification("Test 3");
    n.id = 3;
    OC.notify.addNotification(n);
});
