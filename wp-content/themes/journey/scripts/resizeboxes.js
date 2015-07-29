// created using getElementsByClass by Dustin Diaz
// and Width-based layout by Simon Collison

/*
window.onload = function() {
	if (!document.getElementById) return false;
	var boxes = getElementsByClass('box01');
	var cols03 = getElementsByClass('cols03');
	var theWidth = 0;
	if (window.innerWidth) {
		theWidth = window.innerWidth
		} 
	else if (document.documentElement && document.documentElement.clientWidth) {
		theWidth = document.documentElement.clientWidth
	    } 
	else if (document.body) {
		theWidth = document.body.clientWidth
	    }
	for (var i=0; i< boxes.length; i++) {
		if (theWidth != 0) {
 	        if (theWidth < 920) {
				boxes[i].style.width = '600px';
			} else {
				boxes[i].style.width = '900px';
   	   		}
		}
	}
	for (var i=0; i< cols03.length; i++) {
		if (theWidth != 0) {
 	        if (theWidth < 920) {
				cols03[i].style.width = '580px';
			} else {
				cols03[i].style.width = '880px';
   	   		}
		}
	}
}

window.onresize = function() {
	if (!document.getElementById) return false;
	var boxes = getElementsByClass('box01');
	var cols03 = getElementsByClass('cols03');
	var theWidth = 0;
	if (window.innerWidth) {
		theWidth = window.innerWidth
		} 
	else if (document.documentElement && document.documentElement.clientWidth) {
		theWidth = document.documentElement.clientWidth
	    } 
	else if (document.body) {
		theWidth = document.body.clientWidth
	    }
	for (var i=0; i< boxes.length; i++) {
		if (theWidth != 0) {
 	        if (theWidth < 920) {
				boxes[i].style.width = '600px';
			} else {
				boxes[i].style.width = '900px';
   	   		}
		}
	}
	for (var i=0; i< cols03.length; i++) {
		if (theWidth != 0) {
 	        if (theWidth < 920) {
				cols03[i].style.width = '580px';
			} else {
				cols03[i].style.width = '880px';
   	   		}
		}
	}
}
*/
function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

