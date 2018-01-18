(function() {
	function zxsq_ajaxpost(formid, recall) {
		var request;
		if (window.XMLHttpRequest) {
			request = new XMLHttpRequest();
		} else if ( window.ActiveXObject) {
			request = new ActiveXObject("Microsoft.XMLHTTP");
		} else {
			return;
		}

		request.onreadystatechange = function() {recall(request, formid);};

		var sendData = new FormData($(formid));

		request.open('POST', $(formid).action, true);
		request.send(sendData);
	}
		
	var zxsq_mindmap_forms = document.querySelectorAll('.zxsq_mindmap_form form');
	for(var i=0;i<zxsq_mindmap_forms.length;i++) {
		var cur_form = zxsq_mindmap_forms[i];
		var formid = cur_form.id;
		var textarea = cur_form.querySelector('textarea');
		var oldcode = textarea.value;
		textarea.defaultValue = oldcode.replace(/<br \/>\n/g, '\n');

		zxsq_ajaxpost(formid, showMindMap);
	}

	function showMindMap(request, formid) {
		var imgid = 'img_' + formid;
		if (request.readyState == 4) {
			if(request.status == 200) {
				try {
					var res = JSON.parse(request.responseText);
					$(imgid).src = res['imgpath'];
					return true;
				} catch(e) {
					onError(imgid);
				}
			} else {
				onError(imgid);
			}
		}
	}

	function onError(id) {
		$(id).src = $(id).src.replace(/loading.gif/, 'error.jpg');
	}
})()
