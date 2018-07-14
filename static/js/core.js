var Core = function() {
	var __Core = this;
	
	this.init = function() {
		this.setElementEvents();
		this.initTooltip();
		this.lazyloadInit();
	};
	
	this.setElementEvents = function() {
		$('.btn_SwitchLanguage').off().on('click', function(e) {
			e.preventDefault();
			changeLanguage($(this).attr('data-lg'));
		});
	};
	
	var showAjaxModal = function(path, title, data) {
		if (typeof path == "undefined") {
			return;
		}
		var data = data || {};
		$.ajax({
				type: "GET",
				url: path,
				data: data,
				success: function(Data) {
					if (Data) {
						showDialog(title, Data);	
					} else {
						showDialog(title, 'no content');
					}
				}
			});
	};
	
	var showDialog = function(title, message) {
		if (typeof title == 'undefined') {
			var title = 'SocialMedia Manager';
		}
		if (typeof message == 'undefined') {
			var message = 'no content';
		}
		bootbox.alert({
			title: title,
			message: message,
			onEscape: true,
			backdrop: true,
			closeButton: true,
			buttons: {
				ok: {
	            	label: 'Schließen',
					className: 'btn-cancel'
		        }
    		}
		});
	};

	var changeLanguage = function(lg) {
		var originalURL = window.location.href;
		var exists = originalURL.indexOf('&lang=');
		if(exists === -1) {
			var newLocation = originalURL + '&lang=' + lg;
		} else {
			var newLocation = originalURL.substr(0, exists + 6) + lg;
		}
		window.location.href = newLocation;
	};
	
	this.initTooltip = function() {
		$('[data-toggle="tooltip"]').tooltip({ container: "body" });
	};
	
	this.lazyloadInit = function( container ){ 
		var container = container || 'body';
		if (typeof $.fn.lazyload == "undefined") {
			console.log( 'lazyload not included' ); return ;
		}
		$("div.cover:not(.lazyloaded)", container).lazyload({ effect : "fadeIn" , failure_limit : 15 });
		$("div.cover:not(.lazyloaded)", container).addClass('lazyloaded');
	};
	
	$(function(){
		__Core.init();
	});
};

if(typeof CoreObject == "undefined" ) {
	var CoreObject = new Core();
}