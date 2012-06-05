OC.notify = {
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
		$("#notify-counter").attr("data-count", count).text(count);
		OC.notify.setDocTitle();
	},
	changeCount: function(diff) {
		var count = parseInt($("#notify-counter").attr("data-count"));
		OC.notify.setCount(count + diff);
	},
	originalDocTitle: document.title,
	setDocTitle: function() {
		if(!document.title.match(/^\([0-9]+\) /)) {
			OC.notify.originalDocTitle = document.title;
		}
		var count = parseInt($("#notify-counter").attr("data-count"));
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
		var current = parseInt($("#notify-counter").attr("data-count"));
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
    $("#notify-icon").click(function(event) {
        $("#notify-list").slideToggle();
        event.preventDefault();
        event.stopPropagation();
    });
    $("#notify-list").click(function(event) {
		event.stopPropagation();
	});
    $(window).click(function() {
        $("#notify-list").slideUp();
    });
    OC.notify.setDocTitle();
});
