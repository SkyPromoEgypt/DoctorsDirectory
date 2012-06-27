/**
 * Vote Action
 */

$(document).ready(function() {
	
	var sitename = window.location.hostname;
	var ajaxSqlUrl = "http://" + sitename + "/ajax/getsql/index.php";

	// Horizontal Vote View
	$(".vote").each(function() {
		var width = $(this).css("width");
		$(this).css("width", 0);
		$(this).animate({
			"width" : width
		}, "slow", "swing");
	});

	// Vertical Vote View
	var startLeftPosition = 10;
	var incremetBy = parseInt($(".verticalVote").css("width"), 10) + 15;
	$(".verticalVote").each(function() {
		var itemHeight = $(this).css("height");
		$(this).css("height", 0);
		$(this).css("left", startLeftPosition);
		startLeftPosition += incremetBy;
		$(this).animate({
			"height" : itemHeight
		}, "slow", "linear");
	});
	$("#pollDetails").show("slow", "linear");

	// SqlStatemnet DIV
	var marginToReach = parseInt($(".sqlStatement").css("height"), 10) * 1.8;
	var bodyMargin = parseInt($("body").css("margin-top"), 10);
	$("body").css("margin-top", marginToReach);
	$(".sqlStatement").click(function() {
		$(".sqlStatement").slideUp("slow");
		$("body").animate({
			"margin-top" : bodyMargin
		}, "normal", "linear");
	});
	
	// SQL Window
	$("body").append("<div id=\"overlay\"></div>");
	var sqlWIndowBottomPosition = parseInt($("#sqlWindow").css("bottom"), 10);
	var sqlWindowStatus = false;
	var overlayWindowStatus = false;
	$("#sqlWindow a:first").click(function(e){
		sqlWindowStatus ? toggleSqlWIndow(sqlWIndowBottomPosition) : toggleSqlWIndow(0);
		overlayWindowStatus ? toggleOverlayWIndow(0) : toggleOverlayWIndow(0.7);
		e.preventDefault();
	});
	
	function toggleSqlWIndow(position)
	{
		$("#sqlWindow").animate({
			"bottom" : position
		}, "normal", "linear", function() {
			sqlWindowStatus = (position == 0) ? true : false;
		});
	}
	
	function toggleOverlayWIndow(opacity)
	{ 
		overlayWindowStatus ? null : $("#overlay").show();
		$("#overlay").animate({
			"opacity" : opacity
		}, "normal", "linear", function() {
			overlayWindowStatus = (opacity == 0) ? false : true;
			overlayWindowStatus ? null : $("#overlay").hide();
		});
	}
	
	$("#excutesql").click(function(e) {
		var sql = $("#sqlquery").val();
		if (sql == "") {
			alert("Please write a valid sql statement to proceed.");
		} else {
			$.ajax({
				url: ajaxSqlUrl , 
				data : { sql : sql }, 
				dataType : "html",
				beforeSend: function() {
					$('#sqlResults').html('<p><img src="http://poll/images/loading.gif" align="top" width="16" height="16" />Fetching Data from Database...</p>');
				},
				success: function(results) {
					$('#sqlResults').html(results);
				}
			});
		}
		e.preventDefault();
	});
});