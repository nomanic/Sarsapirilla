var Sarsapirilla={
	q:[],
	ajax_calls:0,
	ajax:(function() {
		var that = {};
		that.send = function(url, options, args) {
			var on_success = options.onSuccess || function() {},
				on_error = options.onError || function() {},
				on_timeout = options.onTimeout || function() {},
				timeout = options.timeout || 10000; // ms
			Sarsapirilla.ajax_calls++;
			var xmlhttp = new XMLHttpRequest();
			xmlhttp.args = args;
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
					Sarsapirilla.ajax_calls--;
					on_success(xmlhttp.responseText,xmlhttp.args);
				} else {
					if (xmlhttp.readyState == 4) {
						Sarsapirilla.ajax_calls--;
						on_error(2, xmlhttp.args);
					}
				}
			};
			xmlhttp.timeout = timeout;
			xmlhttp.ontimeout = function() {
				Sarsapirilla.ajax_calls--;
				on_timeout(xmlhttp.args);
			}
			xmlhttp.open("GET", url, true);
			xmlhttp.send();
		}
		return that;
	})(),
	another:function() {
		if (Sarsapirilla.q.length<1) {return;}
		Sarsapirilla.ajax.send(Sarsapirilla.urlpath+'Sarsapirilla.php?sarsaparilla='+encodeURI(Sarsapirilla.q[0].getAttribute('SASP'))+'&path='+encodeURI(Sarsapirilla.urlpath), {
			onSuccess: function(data,args) {
		        if (args.tagName=='IMG') {
		          args.src=data;
		        }
		        else {
		          args.style.backgroundImage='url('+data+')';
		          args.style.backgroundSize='cover';
		        }
		        args.setAttribute('SASP_loaded',data);
				setTimeout(Sarsapirilla.another,50);
			},
			onError: function() {
				console.log("Error");
			},
			onTimeout: function() {
				console.log("Timeout");
			},
			timeout: 10000
		},Sarsapirilla.q.shift());
	},
	parse:function(sasp,cb) {
		Sarsapirilla.ajax.send(Sarsapirilla.urlpath+'Sarsapirilla.php?sarsaparilla='+encodeURI(sasp)+'&path='+encodeURI(Sarsapirilla.urlpath), {
			onSuccess: function(data) {
				cb(data);
			},
			onError: function() {
				console.log("Error");
			},
			onTimeout: function() {
				console.log("Timeout");
			},
			timeout: 10000
		},1);
	},
	fill:function(b) {
		if (b) {
			Sarsapirilla.q=[...Sarsapirilla.q,...b];
		}
		else {
			Sarsapirilla.q=[...Sarsapirilla.q,...document.querySelectorAll('[SASP]')];
		}
		Sarsapirilla.another();
	},
	setup:function() {
		var scripts=document.getElementsByTagName('script'),
			path=scripts[scripts.length-1].src.split('?')[0];
		Sarsapirilla.urlpath=path.split('/').slice(0, -1).join('/')+'/';
	}
};

Sarsapirilla.setup();
