"function"!=typeof Object.create&&(Object.create=function(o){function e(){}return e.prototype=o,new e}),function($,o,e,i){var t={init:function(o,e){var i=this;i.elem=e,i.$elem=$(e),i.imageSrc=i.$elem.data("zoom-image")?i.$elem.data("zoom-image"):i.$elem.attr("src"),i.options=$.extend({},$.fn.elevateZoom.options,o),i.options.tint&&(i.options.lensColour="none",i.options.lensOpacity="1"),"inner"==i.options.zoomType&&(i.options.showLens=!1),i.$elem.parent().removeAttr("title").removeAttr("alt"),i.zoomImage=i.imageSrc,i.refresh(1),$("#"+i.options.gallery+" a").click(function(o){return i.options.galleryActiveClass&&($("#"+i.options.gallery+" a").removeClass(i.options.galleryActiveClass),$(this).addClass(i.options.galleryActiveClass)),o.preventDefault(),$(this).data("zoom-image")?i.zoomImagePre=$(this).data("zoom-image"):i.zoomImagePre=$(this).data("image"),i.swaptheimage($(this).data("image"),i.zoomImagePre),!1})},refresh:function(o){var e=this;setTimeout(function(){e.fetch(e.imageSrc)},o||e.options.refresh)},fetch:function(o){var e=this,i=new Image;i.onload=function(){e.largeWidth=i.width,e.largeHeight=i.height,e.startZoom(),e.currentImage=e.imageSrc,e.options.onZoomedImageLoaded(e.$elem)},i.src=o},startZoom:function(){var e=this;e.nzWidth=e.$elem.width()-1,e.nzHeight=e.$elem.height()-1,e.isWindowActive=!1,e.isLensActive=!1,e.isTintActive=!1,e.overWindow=!1,e.options.imageCrossfade&&(e.zoomWrap=e.$elem.wrap('<div style="height:'+e.nzHeight+"px;width:"+e.nzWidth+'px;" class="zoomWrapper" />'),e.$elem.css("position","absolute")),e.zoomLock=1,e.scrollingLock=!1,e.changeBgSize=!1,e.currentZoomLevel=e.options.zoomLevel,e.nzOffset=e.$elem.offset();var t=$(o).scrollTop(),n=e.$elem.offset().top,s=n-t;if(e.nzOffset={top:s,left:0},e.widthRatio=e.largeWidth/e.currentZoomLevel/e.nzWidth,e.heightRatio=e.largeHeight/e.currentZoomLevel/e.nzHeight,"window"==e.options.zoomType&&(e.zoomWindowStyle="overflow: hidden;background-position: 0px 0px;text-align:center;background-color: "+String(e.options.zoomWindowBgColour)+";width: "+String(e.options.zoomWindowWidth)+"px;height: "+String(e.options.zoomWindowHeight)+"px;float: left;background-size: "+e.largeWidth/e.currentZoomLevel+"px "+e.largeHeight/e.currentZoomLevel+"px;display: none;z-index:100;border: "+String(e.options.borderSize)+"px solid "+e.options.borderColour+";background-repeat: no-repeat;position: absolute;"),"inner"==e.options.zoomType){var a=e.$elem.css("border-left-width");e.zoomWindowStyle="overflow: hidden;margin-left: "+String(a)+";margin-top: "+String(a)+";background-position: 0px 0px;width: "+String(e.nzWidth)+"px;height: "+String(e.nzHeight)+"px;px;float: left;display: none;cursor:"+e.options.cursor+";px solid "+e.options.borderColour+";background-repeat: no-repeat;position: absolute;"}"window"==e.options.zoomType&&(e.nzHeight<e.options.zoomWindowWidth/e.widthRatio?lensHeight=e.nzHeight:lensHeight=String(e.options.zoomWindowHeight/e.heightRatio),e.largeWidth<e.options.zoomWindowWidth?lensWidth=e.nzWidth:lensWidth=e.options.zoomWindowWidth/e.widthRatio,e.lensStyle="background-position: 0px 0px;width: "+String(e.options.zoomWindowWidth/e.widthRatio)+"px;height: "+String(e.options.zoomWindowHeight/e.heightRatio)+"px;float: right;display: none;overflow: hidden;z-index: 999;-webkit-transform: translateZ(0);opacity:"+e.options.lensOpacity+";filter: alpha(opacity = "+100*e.options.lensOpacity+"); zoom:1;width:"+lensWidth+"px;height:"+lensHeight+"px;background-color:"+e.options.lensColour+";cursor:"+e.options.cursor+";border: "+e.options.lensBorderSize+"px solid "+e.options.lensBorderColour+";background-repeat: no-repeat;position: absolute;"),e.tintStyle="display: block;position: absolute;background-color: "+e.options.tintColour+";filter:alpha(opacity=0);opacity: 0;width: "+e.nzWidth+"px;height: "+e.nzHeight+"px;",e.lensRound="","lens"==e.options.zoomType&&(e.lensStyle="background-position: 0px 0px;float: left;display: none;border: "+String(e.options.borderSize)+"px solid "+e.options.borderColour+";width:"+String(e.options.lensSize)+"px;height:"+String(e.options.lensSize)+"px;background-repeat: no-repeat;position: absolute;"),"round"==e.options.lensShape&&(e.lensRound="border-top-left-radius: "+String(e.options.lensSize/2+e.options.borderSize)+"px;border-top-right-radius: "+String(e.options.lensSize/2+e.options.borderSize)+"px;border-bottom-left-radius: "+String(e.options.lensSize/2+e.options.borderSize)+"px;border-bottom-right-radius: "+String(e.options.lensSize/2+e.options.borderSize)+"px;"),$(".envirabox-overlay").hasClass("overlay-captioned")||$(".envirabox-overlay").hasClass("overlay-polaroid")||$(".envirabox-overlay").hasClass("overlay-sleek")||$(".envirabox-overlay").hasClass("overlay-base")||$(".envirabox-overlay").hasClass("overlay-subtle")||$(".envirabox-overlay").hasClass("overlay-base")||$(".envirabox-overlay").hasClass("overlay-showcase")?0==$(".zoomContainer").length&&(e.zoomContainer=$('<div class="zoomContainer" style="position:static;left:'+e.nzOffset.left+"px;top:"+e.nzOffset.top+"px;height:"+e.nzHeight+"px;width:"+e.nzWidth+'px;"></div>'),$(".envirabox-inner").append(e.zoomContainer)):0==$(".zoomContainer").length&&(e.zoomContainer=$('<div class="zoomContainer" style="-webkit-transform: translateZ(0);position:absolute;left:'+e.nzOffset.left+"px;top:"+e.nzOffset.top+"px;height:"+e.nzHeight+"px;width:"+e.nzWidth+'px;"></div>'),$("body .envirabox-container").prepend(e.zoomContainer)),e.options.containLensZoom&&"lens"==e.options.zoomType&&e.zoomContainer.css("overflow","hidden"),"inner"!=e.options.zoomType&&(e.zoomLens=$("<div class='zoomLens' style='"+e.lensStyle+e.lensRound+"'>&nbsp;</div>").appendTo(e.zoomContainer).click(function(){e.$elem.trigger("click")}),e.options.tint&&(e.tintContainer=$("<div/>").addClass("tintContainer"),e.zoomTint=$("<div class='zoomTint' style='"+e.tintStyle+"'></div>"),e.zoomLens.wrap(e.tintContainer),e.zoomTintcss=e.zoomLens.after(e.zoomTint),e.zoomTintImage=$('<img style="position: absolute; left: 0px; top: 0px; max-width: none; width: '+e.nzWidth+"px; height: "+e.nzHeight+'px;" src="'+e.imageSrc+'">').appendTo(e.zoomLens).click(function(){e.$elem.trigger("click")}))),isNaN(e.options.zoomWindowPosition)?e.zoomWindow=$("<div style='z-index:999;left:"+e.windowOffsetLeft+"px;top:"+e.windowOffsetTop+"px;"+e.zoomWindowStyle+"' class='zoomWindow'>&nbsp;</div>").appendTo("body").click(function(){e.$elem.trigger("click")}):e.zoomWindow=$("<div style='z-index:999;left:"+e.windowOffsetLeft+"px;top:"+e.windowOffsetTop+"px;"+e.zoomWindowStyle+"' class='zoomWindow'>&nbsp;</div>").appendTo(e.zoomContainer).click(function(){e.$elem.trigger("click")}),e.zoomWindowContainer=$("<div/>").addClass("zoomWindowContainer").css("width",e.options.zoomWindowWidth),e.zoomWindow.wrap(e.zoomWindowContainer),"lens"==e.options.zoomType&&e.zoomLens.css({backgroundImage:"url('"+e.imageSrc+"')"}),"window"==e.options.zoomType&&e.zoomWindow.css({backgroundImage:"url('"+e.imageSrc+"')"}),"inner"==e.options.zoomType&&e.zoomWindow.css({backgroundImage:"url('"+e.imageSrc+"')"}),e.$elem.bind("touchmove",function(o){o.preventDefault();var i=o.originalEvent.touches[0]||o.originalEvent.changedTouches[0];e.setPosition(i)}),e.zoomContainer!==i&&(e.zoomContainer.bind("touchmove",function(o){"inner"==e.options.zoomType&&e.showHideWindow("show"),o.preventDefault();var i=o.originalEvent.touches[0]||o.originalEvent.changedTouches[0];e.setPosition(i)}),e.zoomContainer.bind("touchend",function(o){e.showHideWindow("hide"),e.options.showLens&&e.showHideLens("hide"),e.options.tint&&"inner"!=e.options.zoomType&&e.showHideTint("hide")})),e.$elem.bind("touchend",function(o){e.showHideWindow("hide"),e.options.showLens&&e.showHideLens("hide"),e.options.tint&&"inner"!=e.options.zoomType&&e.showHideTint("hide")}),e.options.showLens&&(e.zoomLens.bind("touchmove",function(o){o.preventDefault();var i=o.originalEvent.touches[0]||o.originalEvent.changedTouches[0];e.setPosition(i)}),e.zoomLens.bind("touchend",function(o){e.showHideWindow("hide"),e.options.showLens&&e.showHideLens("hide"),e.options.tint&&"inner"!=e.options.zoomType&&e.showHideTint("hide")})),e.$elem.bind("mousemove",function(o){0==e.overWindow&&e.setElements("show"),(e.lastX!==o.clientX||e.lastY!==o.clientY)&&(e.setPosition(o),e.currentLoc=o),e.lastX=o.clientX,e.lastY=o.clientY}),e.zoomContainer!==i&&e.zoomContainer.bind("mousemove",function(o){0==e.overWindow&&e.setElements("show"),(e.lastX!==o.clientX||e.lastY!==o.clientY)&&(e.setPosition(o),e.currentLoc=o),e.lastX=o.clientX,e.lastY=o.clientY}),"inner"!=e.options.zoomType&&e.zoomLens.bind("mousemove",function(o){(e.lastX!==o.clientX||e.lastY!==o.clientY)&&(e.setPosition(o),e.currentLoc=o),e.lastX=o.clientX,e.lastY=o.clientY}),e.options.tint&&"inner"!=e.options.zoomType&&e.zoomTint.bind("mousemove",function(o){(e.lastX!==o.clientX||e.lastY!==o.clientY)&&(e.setPosition(o),e.currentLoc=o),e.lastX=o.clientX,e.lastY=o.clientY}),"inner"==e.options.zoomType&&e.zoomWindow.bind("mousemove",function(o){(e.lastX!==o.clientX||e.lastY!==o.clientY)&&(e.setPosition(o),e.currentLoc=o),e.lastX=o.clientX,e.lastY=o.clientY}),e.zoomContainer!==i&&e.zoomContainer.add(e.$elem).mouseenter(function(){0==e.overWindow&&e.setElements("show")}).mouseleave(function(){e.scrollLock||(e.setElements("hide"),e.options.onDestroy(e.$elem))}),"inner"!=e.options.zoomType&&e.zoomWindow.mouseenter(function(){e.overWindow=!0,e.setElements("hide")}).mouseleave(function(){e.overWindow=!1}),1!=e.options.zoomLevel,e.options.minZoomLevel?e.minZoomLevel=e.options.minZoomLevel:e.minZoomLevel=2*e.options.scrollZoomIncrement,e.options.scrollZoom&&e.zoomContainer.add(e.$elem).bind("wheel DOMMouseScroll MozMousePixelScroll",function(o){e.scrollLock=!0,clearTimeout($.data(this,"timer")),$.data(this,"timer",setTimeout(function(){e.scrollLock=!1},250));var i=o.originalEvent.deltaY||-1*o.originalEvent.detail;return o.stopImmediatePropagation(),o.stopPropagation(),o.preventDefault(),i/120>0?e.currentZoomLevel>=e.minZoomLevel&&e.changeZoomLevel(e.currentZoomLevel-e.options.scrollZoomIncrement):e.options.maxZoomLevel?e.currentZoomLevel<=e.options.maxZoomLevel&&e.changeZoomLevel(parseFloat(e.currentZoomLevel)+e.options.scrollZoomIncrement):e.changeZoomLevel(parseFloat(e.currentZoomLevel)+e.options.scrollZoomIncrement),!1})},setElements:function(o){var e=this;return e.options.zoomEnabled?("show"==o&&e.isWindowSet&&("inner"==e.options.zoomType&&e.showHideWindow("show"),"window"==e.options.zoomType&&e.showHideWindow("show"),e.options.showLens&&e.showHideLens("show"),e.options.tint&&"inner"!=e.options.zoomType&&e.showHideTint("show")),void("hide"==o&&("window"==e.options.zoomType&&e.showHideWindow("hide"),e.options.tint||e.showHideWindow("hide"),e.options.showLens&&e.showHideLens("hide"),e.options.tint&&e.showHideTint("hide")))):!1},setPosition:function(e){var t=this;if(!t.options.zoomEnabled)return!1;t.nzHeight=t.$elem.height(),t.nzWidth=t.$elem.width(),t.nzOffset=t.$elem.offset();var n=$(o).scrollTop(),s=t.$elem.offset().top,a=s-n;return t.nzOffsetZoomContainer={top:a,left:0},t.options.tint&&"inner"!=t.options.zoomType&&(t.zoomTint.css({top:0}),t.zoomTint.css({left:0})),t.options.responsive&&!t.options.scrollZoom&&t.options.showLens&&(t.nzHeight<t.options.zoomWindowWidth/t.widthRatio?lensHeight=t.nzHeight:lensHeight=String(t.options.zoomWindowHeight/t.heightRatio),t.largeWidth<t.options.zoomWindowWidth?lensWidth=t.nzWidth:lensWidth=t.options.zoomWindowWidth/t.widthRatio,t.widthRatio=t.largeWidth/t.nzWidth,t.heightRatio=t.largeHeight/t.nzHeight,"lens"!=t.options.zoomType&&(t.nzHeight<t.options.zoomWindowWidth/t.widthRatio?lensHeight=t.nzHeight:lensHeight=String(t.options.zoomWindowHeight/t.heightRatio),t.nzWidth<t.options.zoomWindowHeight/t.heightRatio?lensWidth=t.nzWidth:lensWidth=String(t.options.zoomWindowWidth/t.widthRatio),t.zoomLens.css("width",lensWidth),t.zoomLens.css("height",lensHeight),t.options.tint&&(t.zoomTintImage.css("width",t.nzWidth),t.zoomTintImage.css("height",t.nzHeight))),"lens"==t.options.zoomType&&t.zoomLens.css({width:String(t.options.lensSize)+"px",height:String(t.options.lensSize)+"px"})),t.zoomContainer!==i&&(t.zoomContainer.css({top:t.nzOffsetZoomContainer.top}),t.zoomContainer.css({left:t.nzOffset.left})),t.mouseLeft=parseInt(e.pageX-t.nzOffset.left),t.mouseTop=parseInt(e.pageY-t.nzOffset.top),"window"==t.options.zoomType&&(t.Etoppos=t.mouseTop<t.zoomLens.height()/2,t.Eboppos=t.mouseTop>t.nzHeight-t.zoomLens.height()/2-2*t.options.lensBorderSize,t.Eloppos=t.mouseLeft<0+t.zoomLens.width()/2,t.Eroppos=t.mouseLeft>t.nzWidth-t.zoomLens.width()/2-2*t.options.lensBorderSize),"inner"==t.options.zoomType&&(t.Etoppos=t.mouseTop<t.nzHeight/2/t.heightRatio,t.Eboppos=t.mouseTop>t.nzHeight-t.nzHeight/2/t.heightRatio,t.Eloppos=t.mouseLeft<0+t.nzWidth/2/t.widthRatio,t.Eroppos=t.mouseLeft>t.nzWidth-t.nzWidth/2/t.widthRatio-2*t.options.lensBorderSize),t.mouseLeft<0||t.mouseTop<0||t.mouseLeft>t.nzWidth||t.mouseTop>t.nzHeight?void t.setElements("hide"):(t.options.showLens&&(t.lensLeftPos=String(Math.floor(t.mouseLeft-t.zoomLens.width()/2)),t.lensTopPos=String(Math.floor(t.mouseTop-t.zoomLens.height()/2))),t.Etoppos&&(t.lensTopPos=0),t.Eloppos&&(t.windowLeftPos=0,t.lensLeftPos=0,t.tintpos=0),"window"==t.options.zoomType&&(t.Eboppos&&(t.lensTopPos=Math.max(t.nzHeight-t.zoomLens.height()-2*t.options.lensBorderSize,0)),t.Eroppos&&(t.lensLeftPos=t.nzWidth-t.zoomLens.width()-2*t.options.lensBorderSize)),"inner"==t.options.zoomType&&(t.Eboppos&&(t.lensTopPos=Math.max(t.nzHeight-2*t.options.lensBorderSize,0)),t.Eroppos&&(t.lensLeftPos=t.nzWidth-t.nzWidth-2*t.options.lensBorderSize)),"lens"==t.options.zoomType&&(t.windowLeftPos=String(-1*((e.pageX-t.nzOffset.left)*t.widthRatio-t.zoomLens.width()/2)),t.windowTopPos=String(-1*((e.pageY-t.nzOffset.top)*t.heightRatio-t.zoomLens.height()/2)),t.zoomLens.css({backgroundPosition:t.windowLeftPos+"px "+t.windowTopPos+"px"}),t.changeBgSize&&(t.nzHeight>t.nzWidth?("lens"==t.options.zoomType&&t.zoomLens.css({"background-size":t.largeWidth/t.newvalueheight+"px "+t.largeHeight/t.newvalueheight+"px"}),t.zoomWindow.css({"background-size":t.largeWidth/t.newvalueheight+"px "+t.largeHeight/t.newvalueheight+"px"})):("lens"==t.options.zoomType&&t.zoomLens.css({"background-size":t.largeWidth/t.newvaluewidth+"px "+t.largeHeight/t.newvaluewidth+"px"}),t.zoomWindow.css({"background-size":t.largeWidth/t.newvaluewidth+"px "+t.largeHeight/t.newvaluewidth+"px"})),t.changeBgSize=!1),t.setWindowPostition(e)),t.options.tint&&"inner"!=t.options.zoomType&&t.setTintPosition(e),"window"==t.options.zoomType&&t.setWindowPostition(e),"inner"==t.options.zoomType&&t.setWindowPostition(e),t.options.showLens&&(t.fullwidth&&"lens"!=t.options.zoomType&&(t.lensLeftPos=0),t.zoomLens.css({left:t.lensLeftPos+"px",top:t.lensTopPos+"px"})),void 0)},showHideWindow:function(o){var e=this;"show"==o&&(e.isWindowActive||(e.options.zoomWindowFadeIn?e.zoomWindow.stop(!0,!0,!1).fadeIn(e.options.zoomWindowFadeIn):e.zoomWindow.show(),e.isWindowActive=!0)),"hide"==o&&e.isWindowActive&&(e.options.zoomWindowFadeOut?e.zoomWindow.stop(!0,!0).fadeOut(e.options.zoomWindowFadeOut,function(){e.loop&&(clearInterval(e.loop),e.loop=!1)}):e.zoomWindow.hide(),e.isWindowActive=!1)},showHideLens:function(o){var e=this;"show"==o&&(e.isLensActive||(e.options.lensFadeIn?e.zoomLens.stop(!0,!0,!1).fadeIn(e.options.lensFadeIn):e.zoomLens.show(),e.isLensActive=!0)),"hide"==o&&e.isLensActive&&(e.options.lensFadeOut?e.zoomLens.stop(!0,!0).fadeOut(e.options.lensFadeOut):e.zoomLens.hide(),e.isLensActive=!1)},showHideTint:function(o){var e=this;"show"==o&&(e.isTintActive||(e.options.zoomTintFadeIn?e.zoomTint.css({opacity:e.options.tintOpacity}).animate().stop(!0,!0).fadeIn("slow"):(e.zoomTint.css({opacity:e.options.tintOpacity}).animate(),e.zoomTint.show()),e.isTintActive=!0)),"hide"==o&&e.isTintActive&&(e.options.zoomTintFadeOut?e.zoomTint.stop(!0,!0).fadeOut(e.options.zoomTintFadeOut):e.zoomTint.hide(),e.isTintActive=!1)},setLensPostition:function(o){},setWindowPostition:function(o){var e=this;if(isNaN(e.options.zoomWindowPosition))e.externalContainer=$("#"+e.options.zoomWindowPosition),e.externalContainerWidth=e.externalContainer.width(),e.externalContainerHeight=e.externalContainer.height(),e.externalContainerOffset=e.externalContainer.offset(),e.windowOffsetTop=e.externalContainerOffset.top,e.windowOffsetLeft=e.externalContainerOffset.left;else switch(e.options.zoomWindowPosition){case 1:e.windowOffsetTop=e.options.zoomWindowOffety,e.windowOffsetLeft=+e.nzWidth;break;case 2:e.options.zoomWindowHeight>e.nzHeight&&(e.windowOffsetTop=-1*(e.options.zoomWindowHeight/2-e.nzHeight/2),e.windowOffsetLeft=e.nzWidth);break;case 3:e.windowOffsetTop=e.nzHeight-e.zoomWindow.height()-2*e.options.borderSize,e.windowOffsetLeft=e.nzWidth;break;case 4:e.windowOffsetTop=e.nzHeight,e.windowOffsetLeft=e.nzWidth;break;case 5:e.windowOffsetTop=e.nzHeight,e.windowOffsetLeft=e.nzWidth-e.zoomWindow.width()-2*e.options.borderSize;break;case 6:e.options.zoomWindowHeight>e.nzHeight&&(e.windowOffsetTop=e.nzHeight,e.windowOffsetLeft=-1*(e.options.zoomWindowWidth/2-e.nzWidth/2+2*e.options.borderSize));break;case 7:e.windowOffsetTop=e.nzHeight,e.windowOffsetLeft=0;break;case 8:e.windowOffsetTop=e.nzHeight,e.windowOffsetLeft=-1*(e.zoomWindow.width()+2*e.options.borderSize);break;case 9:e.windowOffsetTop=e.nzHeight-e.zoomWindow.height()-2*e.options.borderSize,e.windowOffsetLeft=-1*(e.zoomWindow.width()+2*e.options.borderSize);break;case 10:e.options.zoomWindowHeight>e.nzHeight&&(e.windowOffsetTop=-1*(e.options.zoomWindowHeight/2-e.nzHeight/2),e.windowOffsetLeft=-1*(e.zoomWindow.width()+2*e.options.borderSize));break;case 11:e.windowOffsetTop=e.options.zoomWindowOffety,e.windowOffsetLeft=-1*(e.zoomWindow.width()+2*e.options.borderSize);break;case 12:e.windowOffsetTop=-1*(e.zoomWindow.height()+2*e.options.borderSize),e.windowOffsetLeft=-1*(e.zoomWindow.width()+2*e.options.borderSize);break;case 13:e.windowOffsetTop=-1*(e.zoomWindow.height()+2*e.options.borderSize),e.windowOffsetLeft=0;break;case 14:e.options.zoomWindowHeight>e.nzHeight&&(e.windowOffsetTop=-1*(e.zoomWindow.height()+2*e.options.borderSize),e.windowOffsetLeft=-1*(e.options.zoomWindowWidth/2-e.nzWidth/2+2*e.options.borderSize));break;case 15:e.windowOffsetTop=-1*(e.zoomWindow.height()+2*e.options.borderSize),e.windowOffsetLeft=e.nzWidth-e.zoomWindow.width()-2*e.options.borderSize;break;case 16:e.windowOffsetTop=-1*(e.zoomWindow.height()+2*e.options.borderSize),e.windowOffsetLeft=e.nzWidth;break;default:e.windowOffsetTop=e.options.zoomWindowOffety,e.windowOffsetLeft=e.nzWidth}e.isWindowSet=!0,e.windowOffsetTop=e.windowOffsetTop+e.options.zoomWindowOffety,e.windowOffsetLeft=e.windowOffsetLeft+e.options.zoomWindowOffetx,e.zoomWindow.css({top:e.windowOffsetTop}),e.zoomWindow.css({left:e.windowOffsetLeft}),"inner"==e.options.zoomType&&(e.zoomWindow.css({top:0}),e.zoomWindow.css({left:0})),e.windowLeftPos=String(-1*((o.pageX-e.nzOffset.left)*e.widthRatio-e.zoomWindow.width()/2)),e.windowTopPos=String(-1*((o.pageY-e.nzOffset.top)*e.heightRatio-e.zoomWindow.height()/2)),e.Etoppos&&(e.windowTopPos=0),e.Eloppos&&(e.windowLeftPos=0),e.Eboppos&&(e.windowTopPos=-1*(e.largeHeight/e.currentZoomLevel-e.zoomWindow.height())),e.Eroppos&&(e.windowLeftPos=-1*(e.largeWidth/e.currentZoomLevel-e.zoomWindow.width())),e.fullheight&&(e.windowTopPos=0),e.fullwidth&&(e.windowLeftPos=0),("window"==e.options.zoomType||"inner"==e.options.zoomType)&&(1==e.zoomLock&&(e.widthRatio<=1&&(e.windowLeftPos=0),e.heightRatio<=1&&(e.windowTopPos=0)),"window"==e.options.zoomType&&(e.largeHeight<e.options.zoomWindowHeight&&(e.windowTopPos=0),e.largeWidth<e.options.zoomWindowWidth&&(e.windowLeftPos=0)),e.options.easing?(e.xp||(e.xp=0),e.yp||(e.yp=0),e.loop||(e.loop=setInterval(function(){e.xp+=(e.windowLeftPos-e.xp)/e.options.easingAmount,e.yp+=(e.windowTopPos-e.yp)/e.options.easingAmount,e.scrollingLock?(clearInterval(e.loop),e.xp=e.windowLeftPos,e.yp=e.windowTopPos,e.xp=-1*((o.pageX-e.nzOffset.left)*e.widthRatio-e.zoomWindow.width()/2),e.yp=-1*((o.pageY-e.nzOffset.top)*e.heightRatio-e.zoomWindow.height()/2),e.changeBgSize&&(e.nzHeight>e.nzWidth?("lens"==e.options.zoomType&&e.zoomLens.css({"background-size":e.largeWidth/e.newvalueheight+"px "+e.largeHeight/e.newvalueheight+"px"}),e.zoomWindow.css({"background-size":e.largeWidth/e.newvalueheight+"px "+e.largeHeight/e.newvalueheight+"px"})):("lens"!=e.options.zoomType&&e.zoomLens.css({"background-size":e.largeWidth/e.newvaluewidth+"px "+e.largeHeight/e.newvalueheight+"px"}),e.zoomWindow.css({"background-size":e.largeWidth/e.newvaluewidth+"px "+e.largeHeight/e.newvaluewidth+"px"})),e.changeBgSize=!1),e.zoomWindow.css({backgroundPosition:e.windowLeftPos+"px "+e.windowTopPos+"px"}),e.scrollingLock=!1,e.loop=!1):Math.round(Math.abs(e.xp-e.windowLeftPos)+Math.abs(e.yp-e.windowTopPos))<1?(clearInterval(e.loop),e.zoomWindow.css({backgroundPosition:e.windowLeftPos+"px "+e.windowTopPos+"px"}),e.loop=!1):(e.changeBgSize&&(e.nzHeight>e.nzWidth?("lens"==e.options.zoomType&&e.zoomLens.css({"background-size":e.largeWidth/e.newvalueheight+"px "+e.largeHeight/e.newvalueheight+"px"}),e.zoomWindow.css({"background-size":e.largeWidth/e.newvalueheight+"px "+e.largeHeight/e.newvalueheight+"px"})):("lens"!=e.options.zoomType&&e.zoomLens.css({"background-size":e.largeWidth/e.newvaluewidth+"px "+e.largeHeight/e.newvaluewidth+"px"}),e.zoomWindow.css({"background-size":e.largeWidth/e.newvaluewidth+"px "+e.largeHeight/e.newvaluewidth+"px"})),e.changeBgSize=!1),e.zoomWindow.css({backgroundPosition:e.xp+"px "+e.yp+"px"}))},16))):(e.changeBgSize&&(e.nzHeight>e.nzWidth?("lens"==e.options.zoomType&&e.zoomLens.css({"background-size":e.largeWidth/e.newvalueheight+"px "+e.largeHeight/e.newvalueheight+"px"}),e.zoomWindow.css({"background-size":e.largeWidth/e.newvalueheight+"px "+e.largeHeight/e.newvalueheight+"px"})):("lens"==e.options.zoomType&&e.zoomLens.css({"background-size":e.largeWidth/e.newvaluewidth+"px "+e.largeHeight/e.newvaluewidth+"px"}),e.largeHeight/e.newvaluewidth<e.options.zoomWindowHeight?e.zoomWindow.css({"background-size":e.largeWidth/e.newvaluewidth+"px "+e.largeHeight/e.newvaluewidth+"px"}):e.zoomWindow.css({"background-size":e.largeWidth/e.newvalueheight+"px "+e.largeHeight/e.newvalueheight+"px"})),e.changeBgSize=!1),e.zoomWindow.css({backgroundPosition:e.windowLeftPos+"px "+e.windowTopPos+"px"})))},setTintPosition:function(o){var e=this;e.nzOffset=e.$elem.offset(),e.tintpos=String(-1*(o.pageX-e.nzOffset.left-e.zoomLens.width()/2)),e.tintposy=String(-1*(o.pageY-e.nzOffset.top-e.zoomLens.height()/2)),e.Etoppos&&(e.tintposy=0),e.Eloppos&&(e.tintpos=0),e.Eboppos&&(e.tintposy=-1*(e.nzHeight-e.zoomLens.height()-2*e.options.lensBorderSize)),e.Eroppos&&(e.tintpos=-1*(e.nzWidth-e.zoomLens.width()-2*e.options.lensBorderSize)),e.options.tint&&(e.fullheight&&(e.tintposy=0),e.fullwidth&&(e.tintpos=0),e.zoomTintImage.css({left:e.tintpos+"px"}),e.zoomTintImage.css({top:e.tintposy+"px"}))},swaptheimage:function(o,e){var i=this,t=new Image;i.options.loadingIcon&&(i.spinner=$("<div style=\"background: url('"+i.options.loadingIcon+"') no-repeat center;height:"+i.nzHeight+"px;width:"+i.nzWidth+'px;z-index: 2000;position: absolute; background-position: center center;"></div>'),i.$elem.after(i.spinner)),i.options.onImageSwap(i.$elem),t.onload=function(){i.largeWidth=t.width,i.largeHeight=t.height,i.zoomImage=e,i.zoomWindow.css({"background-size":i.largeWidth+"px "+i.largeHeight+"px"}),i.swapAction(o,e)},t.src=e},swapAction:function(o,e){var i=this,t=new Image;if(t.onload=function(){i.nzHeight=t.height,i.nzWidth=t.width,i.options.onImageSwapComplete(i.$elem),i.doneCallback()},t.src=o,i.currentZoomLevel=i.options.zoomLevel,i.options.maxZoomLevel=!1,"lens"==i.options.zoomType&&i.zoomLens.css({backgroundImage:"url('"+e+"')"}),"window"==i.options.zoomType&&i.zoomWindow.css({backgroundImage:"url('"+e+"')"}),"inner"==i.options.zoomType&&i.zoomWindow.css({backgroundImage:"url('"+e+"')"}),i.currentImage=e,i.options.imageCrossfade){var n=i.$elem,s=n.clone();if(i.$elem.attr("src",o),i.$elem.after(s),s.stop(!0).fadeOut(i.options.imageCrossfade,function(){$(this).remove()}),i.$elem.width("auto").removeAttr("width"),i.$elem.height("auto").removeAttr("height"),n.fadeIn(i.options.imageCrossfade),i.options.tint&&"inner"!=i.options.zoomType){var a=i.zoomTintImage,h=a.clone();i.zoomTintImage.attr("src",e),i.zoomTintImage.after(h),h.stop(!0).fadeOut(i.options.imageCrossfade,function(){$(this).remove()}),a.fadeIn(i.options.imageCrossfade),i.zoomTint.css({height:i.$elem.height()}),i.zoomTint.css({width:i.$elem.width()})}i.zoomContainer.css("height",i.$elem.height()),i.zoomContainer.css("width",i.$elem.width()),"inner"==i.options.zoomType&&(i.options.constrainType||(i.zoomWrap.parent().css("height",i.$elem.height()),i.zoomWrap.parent().css("width",i.$elem.width()),i.zoomWindow.css("height",i.$elem.height()),i.zoomWindow.css("width",i.$elem.width()))),i.options.imageCrossfade&&(i.zoomWrap.css("height",i.$elem.height()),i.zoomWrap.css("width",i.$elem.width()))}else i.$elem.attr("src",o),i.options.tint&&(i.zoomTintImage.attr("src",e),i.zoomTintImage.attr("height",i.$elem.height()),i.zoomTintImage.css({height:i.$elem.height()}),i.zoomTint.css({height:i.$elem.height()})),i.zoomContainer.css("height",i.$elem.height()),i.zoomContainer.css("width",i.$elem.width()),i.options.imageCrossfade&&(i.zoomWrap.css("height",i.$elem.height()),i.zoomWrap.css("width",i.$elem.width()));i.options.constrainType&&("height"==i.options.constrainType&&(i.zoomContainer.css("height",i.options.constrainSize),i.zoomContainer.css("width","auto"),i.options.imageCrossfade?(i.zoomWrap.css("height",i.options.constrainSize),i.zoomWrap.css("width","auto"),i.constwidth=i.zoomWrap.width()):(i.$elem.css("height",i.options.constrainSize),i.$elem.css("width","auto"),i.constwidth=i.$elem.width()),"inner"==i.options.zoomType&&(i.zoomWrap.parent().css("height",i.options.constrainSize),i.zoomWrap.parent().css("width",i.constwidth),i.zoomWindow.css("height",i.options.constrainSize),i.zoomWindow.css("width",i.constwidth)),i.options.tint&&(i.tintContainer.css("height",i.options.constrainSize),i.tintContainer.css("width",i.constwidth),i.zoomTint.css("height",i.options.constrainSize),i.zoomTint.css("width",i.constwidth),i.zoomTintImage.css("height",i.options.constrainSize),i.zoomTintImage.css("width",i.constwidth))),"width"==i.options.constrainType&&(i.zoomContainer.css("height","auto"),i.zoomContainer.css("width",i.options.constrainSize),i.options.imageCrossfade?(i.zoomWrap.css("height","auto"),i.zoomWrap.css("width",i.options.constrainSize),i.constheight=i.zoomWrap.height()):(i.$elem.css("height","auto"),i.$elem.css("width",i.options.constrainSize),i.constheight=i.$elem.height()),"inner"==i.options.zoomType&&(i.zoomWrap.parent().css("height",i.constheight),i.zoomWrap.parent().css("width",i.options.constrainSize),i.zoomWindow.css("height",i.constheight),i.zoomWindow.css("width",i.options.constrainSize)),i.options.tint&&(i.tintContainer.css("height",i.constheight),i.tintContainer.css("width",i.options.constrainSize),i.zoomTint.css("height",i.constheight),i.zoomTint.css("width",i.options.constrainSize),i.zoomTintImage.css("height",i.constheight),i.zoomTintImage.css("width",i.options.constrainSize))))},doneCallback:function(){var e=this;e.options.loadingIcon&&e.spinner.hide(),e.nzOffset=e.$elem.offset(),e.nzWidth=e.$elem.width(),e.nzHeight=e.$elem.height();var i=$(o).scrollTop(),t=e.$elem.offset().top,n=t-i;return e.nzOffset={top:n,left:0},e.currentZoomLevel=e.options.zoomLevel,e.widthRatio=e.largeWidth/e.nzWidth,e.heightRatio=e.largeHeight/e.nzHeight,e.gallerylist=[],e.options.gallery?$("#"+e.options.gallery+" a").each(function(){var o="";$(this).data("zoom-image")?o=$(this).data("zoom-image"):$(this).data("image")&&(o=$(this).data("image")),o==e.zoomImage?e.gallerylist.unshift({href:""+o,title:$(this).find("img").attr("title")}):e.gallerylist.push({href:""+o,title:$(this).find("img").attr("title")})}):e.gallerylist.push({href:""+e.zoomImage,title:$(this).find("img").attr("title")}),e.gallerylist},changeZoomLevel:function(o){var e=this;e.scrollingLock=!0,e.newvalue=parseFloat(o).toFixed(2),newvalue=parseFloat(o).toFixed(2),maxheightnewvalue=e.largeHeight/(e.options.zoomWindowHeight/e.nzHeight*e.nzHeight),maxwidthtnewvalue=e.largeWidth/(e.options.zoomWindowWidth/e.nzWidth*e.nzWidth),"inner"!=e.options.zoomType&&(maxheightnewvalue<=newvalue?(e.heightRatio=e.largeHeight/maxheightnewvalue/e.nzHeight,e.newvalueheight=maxheightnewvalue,e.fullheight=!0):(e.heightRatio=e.largeHeight/newvalue/e.nzHeight,e.newvalueheight=newvalue,e.fullheight=!1),maxwidthtnewvalue<=newvalue?(e.widthRatio=e.largeWidth/maxwidthtnewvalue/e.nzWidth,e.newvaluewidth=maxwidthtnewvalue,e.fullwidth=!0):(e.widthRatio=e.largeWidth/newvalue/e.nzWidth,e.newvaluewidth=newvalue,e.fullwidth=!1),"lens"==e.options.zoomType&&(maxheightnewvalue<=newvalue?(e.fullwidth=!0,e.newvaluewidth=maxheightnewvalue):(e.widthRatio=e.largeWidth/newvalue/e.nzWidth,e.newvaluewidth=newvalue,e.fullwidth=!1))),"inner"==e.options.zoomType&&(maxheightnewvalue=parseFloat(e.largeHeight/e.nzHeight).toFixed(2),maxwidthtnewvalue=parseFloat(e.largeWidth/e.nzWidth).toFixed(2),newvalue>maxheightnewvalue&&(newvalue=maxheightnewvalue),newvalue>maxwidthtnewvalue&&(newvalue=maxwidthtnewvalue),maxheightnewvalue<=newvalue?(e.heightRatio=e.largeHeight/newvalue/e.nzHeight,newvalue>maxheightnewvalue?e.newvalueheight=maxheightnewvalue:e.newvalueheight=newvalue,e.fullheight=!0):(e.heightRatio=e.largeHeight/newvalue/e.nzHeight,newvalue>maxheightnewvalue?e.newvalueheight=maxheightnewvalue:e.newvalueheight=newvalue,e.fullheight=!1),maxwidthtnewvalue<=newvalue?(e.widthRatio=e.largeWidth/newvalue/e.nzWidth,newvalue>maxwidthtnewvalue?e.newvaluewidth=maxwidthtnewvalue:e.newvaluewidth=newvalue,e.fullwidth=!0):(e.widthRatio=e.largeWidth/newvalue/e.nzWidth,e.newvaluewidth=newvalue,e.fullwidth=!1)),scrcontinue=!1,"inner"==e.options.zoomType&&(e.nzWidth>=e.nzHeight&&(e.newvaluewidth<=maxwidthtnewvalue?scrcontinue=!0:(scrcontinue=!1,e.fullheight=!0,e.fullwidth=!0)),e.nzHeight>e.nzWidth&&(e.newvaluewidth<=maxwidthtnewvalue?scrcontinue=!0:(scrcontinue=!1,e.fullheight=!0,e.fullwidth=!0))),"inner"!=e.options.zoomType&&(scrcontinue=!0),scrcontinue&&(e.zoomLock=0,e.changeZoom=!0,e.options.zoomWindowHeight/e.heightRatio<=e.nzHeight&&(e.currentZoomLevel=e.newvalueheight,"lens"!=e.options.zoomType&&"inner"!=e.options.zoomType&&(e.changeBgSize=!0,e.zoomLens.css({height:String(e.options.zoomWindowHeight/e.heightRatio)+"px"})),("lens"==e.options.zoomType||"inner"==e.options.zoomType)&&(e.changeBgSize=!0)),e.options.zoomWindowWidth/e.widthRatio<=e.nzWidth&&("inner"!=e.options.zoomType&&e.newvaluewidth>e.newvalueheight&&(e.currentZoomLevel=e.newvaluewidth),"lens"!=e.options.zoomType&&"inner"!=e.options.zoomType&&(e.changeBgSize=!0,e.zoomLens.css({width:String(e.options.zoomWindowWidth/e.widthRatio)+"px"})),("lens"==e.options.zoomType||"inner"==e.options.zoomType)&&(e.changeBgSize=!0)),"inner"==e.options.zoomType&&(e.changeBgSize=!0,e.nzWidth>e.nzHeight&&(e.currentZoomLevel=e.newvaluewidth),e.nzHeight>e.nzWidth&&(e.currentZoomLevel=e.newvaluewidth))),e.setPosition(e.currentLoc)},closeAll:function(){self.zoomWindow&&self.zoomWindow.hide(),self.zoomLens&&self.zoomLens.hide(),self.zoomTint&&self.zoomTint.hide()},changeState:function(o){var e=this;"enable"==o&&(e.options.zoomEnabled=!0),"disable"==o&&(e.options.zoomEnabled=!1)}};$.fn.elevateZoom=function(o){return this.each(function(){var e=Object.create(t);e.init(o,this),$.data(this,"elevateZoom",e)})},$.fn.elevateZoom.options={zoomActivation:"hover",zoomEnabled:!0,preloading:1,zoomLevel:1,scrollZoom:!1,scrollZoomIncrement:.1,minZoomLevel:!1,maxZoomLevel:!1,easing:!1,easingAmount:12,lensSize:200,zoomWindowWidth:400,zoomWindowHeight:400,zoomWindowOffetx:0,zoomWindowOffety:0,zoomWindowPosition:1,zoomWindowBgColour:"#fff",lensFadeIn:!1,lensFadeOut:!1,debug:!1,zoomWindowFadeIn:!1,zoomWindowFadeOut:!1,
zoomWindowAlwaysShow:!1,zoomTintFadeIn:!1,zoomTintFadeOut:!1,borderSize:4,showLens:!0,borderColour:"#888",lensBorderSize:1,lensBorderColour:"#000",lensShape:"square",zoomType:"window",containLensZoom:!1,lensColour:"white",lensOpacity:.4,lenszoom:!1,tint:!1,tintColour:"#333",tintOpacity:.4,gallery:!1,galleryActiveClass:"zoomGalleryActive",imageCrossfade:!1,constrainType:!1,constrainSize:!1,loadingIcon:!1,cursor:"default",responsive:!0,onComplete:$.noop,onDestroy:function(){},onZoomedImageLoaded:function(){},onImageSwap:$.noop,onImageSwapComplete:$.noop}}(jQuery,window,document);