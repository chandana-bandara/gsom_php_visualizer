$(function(){
	
	var mouseX;
	var mouseY;
	$(document).mousemove( function(e) {
	   mouseX = e.pageX; 
	   mouseY = e.pageY;
	});  
	var curHoverNode;
	
	$(".mapNode").mouseover(function(){
		//curHoverNode = $(this);
		//$('#nodeItemShowHover').html($(curHoverNode).next('.nodeItems').html());
	});

	$(".mapNode").mouseout(function(){
		curHoverNode = $(this);
	//	$('#nodeItemShowHover').html($(curHoverNode).next('.nodeItems').html()).show();
	});
	
	$('.mapNode').bind('click',function(){
		curHoverNode = $(this);
		$('#nodeItemShowHover').html($(curHoverNode).next('.nodeItems').html());
	});
	
	$('#mapInfo').html($('#dummyMapInfo').html());
	$('#clusterInfo').html($('#dummyClusterInfo').html());
	
	var informerX;
	var informerY;
	
	//$(document).mousemove( function(e) {
	//   informerX = e.pageX; 
	//   informerY = e.pageY;
	//});  
	
	
	
	/* $(".mapBlock").hover(function(){
		
		informerX = $(this).left;
		informerY = $(this).top;
		
		
		$('#nodeInformer').css({'position':'absolute','top':mouseY,'left':mouseX}).show();
		
	}, function(){
		$('#nodeInformer').hide();	
	}); */


	$('.mapBlock').bt({
	  positions: ['top','bottom'],
	  contentSelector: "$(this).parent().next().children('.forToolTip')",
	  trigger: 'click',
	  width: 220,
	  centerPointX: .9,
	  spikeLength: 65,
	  spikeGirth: 40,
	  padding: 15,
	  cornerRadius: 25,
	  fill: '#FFF',
	  strokeStyle: '#ABABAB',
	  strokeWidth: 1
	});

	
	//$('body').layout({ applyDemoStyles: true });
});
