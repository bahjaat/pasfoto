<?php
/**
 * Config
 **/

// Pasfoto is origineel 35 cm breed en 45 cm hoog
$pasfotoX = 35;	// Voor verhoudingen van pasfoto formaat
$pasfotoY = 45; // Voor verhoudingen van pasfoto formaat

$multiplier = 10;	// Voor de grootte van de pasfoto in pixels
					// Afmeldingen pasfoto zijn $pasfotoX * $multiplier x $pasfotoY * $multiplier
$jpeg_quality = 100; // 0 - 100
$export_location = "../data/pasfoto"; // Zonder '/' aan de achterkant

/**
 * Don't edit anything below this line
 **/

if ($_GET['action'] == "capture" && !empty($_GET['filename']))
{
	$filename = $export_location.'/'.$_GET['filename'].'_'.(time() + microtime(TRUE)).'.jpg';
	$result = file_put_contents( $filename, file_get_contents('php://input') );
	if (!$result) {
		print "ERROR: Failed to write data to $filename, check permissions\n";
		exit();
	}

	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $filename;
	print "$url\n";
	exit;
}
 
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$targ_w = $pasfotoX * 10;
	$targ_h = $pasfotoY * 10;

	$filename = $_GET['filename'];
	$src = $export_location.'/'.$filename.'.jpg';
	$output_filename = $export_location.'/'.$filename . '_crop.jpg';
	$img_r = imagecreatefromjpeg($src);
	$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

	imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);

	//header('Content-type: image/jpeg');
	//imagejpeg($dst_r,null,$jpeg_quality);
	
	imagejpeg($dst_r, $output_filename, $jpeg_quality);

	exit;
}

$csnumber = date('YmdHis'); // moet sessie var worden oid.
$csnumber = $_GET['filename'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Webcam Capture</title>
	<meta name="author" content="Intr@works (Edwin Heij)">
	
    <script type="text/javascript" src="../include/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="../include/jquery-ui-1.7.3.custom.min.js"></script>
    <script type="text/javascript" src="jquery.Jcrop.js"></script>
    <script type="text/javascript" src="webcam.js"></script>
	
    <link rel="stylesheet" href="jquery.Jcrop.css" type="text/css" />
    <link rel="stylesheet" href="../images/black-tie/jquery-ui-1.8.2.custom.css" media="screen,projection" type="text/css" />
	<link rel="stylesheet" href="../include/style.css" media="screen,projection" type="text/css" />
	
	<style>
	body {
		background: white;
	}
	#fase1 {
		
	}
	#webcam_movie , #target, .canvas {
		border: 40px solid gray;
		-webkit-border-radius: 40px;
		-moz-border-radius: 40px;
		border-radius: 40px;
	}
	
	#fase1, #fase2, #imageWrapper {
		float:left;
	}
	
	.buttons {
		position: relative;
		top: -21px;
		padding: 3px 0;
		width: 100%;
		height: 20px;
		display:block;
		text-align: center;
	}
	
	.buttons span {
		margin: 0 3px;
	}
	
		.camera {
			margin-top:1px;
			background: url('images/rec.png') no-repeat;
			width: 41px;
			height: 14px;
			display:inline-block;
			cursor: pointer;
		}
	
		.back {
			
			background: url('images/cancel.png') no-repeat;
			width: 16px;
			height: 16px;
			display:inline-block;
			cursor: pointer;
		}
	
		.crop {
			
			background: url('images/ok.png') no-repeat;
			width: 16px;
			height: 16px;
			cursor: pointer;
			display:inline-block;
		}
	
	form, .frmButtons {
		clear: both;
	}
	
	.ui-dialog .ui-dialog-content { overflow: hidden; }

	</style>

	<!--[if IE]>
	<style>
		.buttons {
			/*position: relative;*/
			top: -40px;
			/*left: 340px;*/
		}
		/*webcam_movie , #target,*/
		#fase1, #fase2, #webcam_movie {
			width: 700px;
			height: 540px;
		}
	</style>
	<![endif]-->
	
	<script>
      //jQuery(function($){

        // The variable jcrop_api will hold a reference to the
        // Jcrop API once Jcrop is instantiated.
        var jcrop_api;

        // In this example, since Jcrop may be attached or detached
        // at the whim of the user, I've wrapped the call into a function
        //initJcrop();
        
        // The function is pretty simple
        function initJcrop()//{{{
        {

          // Invoke Jcrop in typical fashion
          $('#target').Jcrop({
            onRelease: releaseCheck,
			onSelect: updateCoords,
			onChange: updateCoords
          },function(){

            jcrop_api = this;
			jcrop_api.setOptions({
				animationDelay: 30,
				bgColor: 'white'
			});
			this.animateTo(getCropSize());
			//this.release();
			
          });

        };
        //}}}
		
	    function checkCoords()
		{
			if (
				parseInt($('#x').val()) >= 0 &&
				parseInt($('#y').val()) >= 0 &&
				parseInt($('#w').val()) >= 0 &&
				parseInt($('#h').val()) >= 0
			) return true;
			alert('Selecteer een gebied om uit te knippen a.u.b. ...');
			return false;
		};

		function updateCoords(c)
		{
			$('#x').val(Math.round(c.x));
			$('#y').val(Math.round(c.y));
			$('#w').val(Math.round(c.w));
			$('#h').val(Math.round(c.h));
		};
		
		function getCropSize(a) {
			
			var pasfotoX = <?php echo $pasfotoX; ?>;
			var pasfotoY = <?php echo $pasfotoY; ?>;
			
			jcrop_api.setOptions({ aspectRatio: pasfotoX/pasfotoY });
			
			var widgetSize = jcrop_api.getWidgetSize();
			var marginTopBottom = 5;
			
			var CoordTopLeftY = marginTopBottom;
			var CoordBottomRightY = widgetSize[1] + 2 - marginTopBottom; // + 2 voor borders
			
			var height = CoordBottomRightY - CoordTopLeftY;
			var width = height * (pasfotoX / pasfotoY);
			var CoordTopLeftX = (widgetSize[0] - width) / 2;
			var CoordBottomRightX =  widgetSize[0] - CoordTopLeftX;
			
			if (a == "center") {
				var CoordTopLeftX = widgetSize[0] / 2;
				var CoordTopLeftY = widgetSize[1] / 2;
				var CoordBottomRightX = CoordTopLeftX;
				var CoordBottomRightY = CoordTopLeftY;
				
			}
			if (a == "out") {
				var CoordTopLeftX = 0;
				var CoordTopLeftY = 0;
				var CoordBottomRightX = widgetSize[0];
				var CoordBottomRightY = widgetSize[1];
				jcrop_api.setOptions({ aspectRatio: 0 });
			}
			
			return [
				Math.round(CoordTopLeftX),
				Math.round(CoordTopLeftY),
				Math.round(CoordBottomRightX),
				Math.round(CoordBottomRightY)
			];
        };
		
        // This function is bound to the onRelease handler...
        // In certain circumstances (such as if you set minSize
        // and aspectRatio together), you can inadvertently lose
        // the selection. This callback re-enables creating selections
        // in such a case. Although the need to do this is based on a
        // buggy behavior, it's recommended that you in some way trap
        // the onRelease callback if you use allowSelect: false
        function releaseCheck()
        {
          jcrop_api.setOptions({ allowSelect: true });
        };

        // Attach interface buttons
        // This may appear to be a lot of code but it's simple stuff
        $('#animateTo').click(function(e) {
          // Animates the selection
          jcrop_api.animateTo(getCropSize());
		});
        $('#bye').click(function(){
			jcrop_api.animateTo(
			  getCropSize('out'),
			  function(){
				$('#frmCut input[type=text]').val(''); // clear coordinates
				this.release();
			  }
			);
			return false;
		  });
		  
		  
		webcam.set_hook( 'onComplete', 'my_completion_handler' );
		
		function do_upload() {
			webcam.snap();
		}
		
		function my_completion_handler(msg) {
			if (msg.match(/(http\:\/\/\S+)/)) {
				var image_url = RegExp.$1;
				$("#target").attr('src', image_url).parent().addClass('canvas');
				$("#fase2").fadeIn('slow');
				webcam.reset();
				
				$("#fase1").slideToggle();
				//$("#fase1").hide();
				initJcrop();
			}
			else alert("PHP Error: " + msg);
		}
		
		function back() {
			jcrop_api.destroy();
			$("#fase2").fadeOut('slow', function(){
				$("#fase1").slideToggle();
			});
		}

		$().ready(function(){
		
		$( "#dialog" ).dialog({
			height: 543,
			width: 374,
			modal: true,
			autoOpen: false,
			hide: 'explode',
			show: 'blind',
			title: 'Uitgeknipte pasfoto',
			buttons: {
				"Opnieuw uitknippen": function() { $(this).dialog("close"); },
				"Opslaan": function() {
					window.parent.jQuery("input[name*='photo']").val( $("#dialogImage").attr('src') ).change();
					$(this).dialog("close");
					
					jcrop_api.destroy();
					$("#fase2").fadeOut('slow', function(){
						$("#fase1").slideToggle();
					});
					window.parent.jQuery("#pasfotoDialog").dialog('close');
				}
			}
		});
			$("#frmCut").submit(function(e){
				e.preventDefault();
				if (checkCoords()) {
					var capture_url = $("#target").attr('src');
					var myRegex = /^.*\/(.*)\.(.*)$/g;
					var match = myRegex.exec(capture_url);
					var filename = match[1];
					
					$.post("<?php echo $_SERVER['PHP_SELF'] ?>?filename=" + filename,
						$(this).serialize(),
						function(data){
							/*
							$("#fase2").fadeOut('slow', function(){
							jcrop_api.destroy();
								$("#fase1").slideToggle();
							});
							*/
							$("#dialogImage").attr('src', '').attr('src', '<?=$export_location?>/' + filename + '_crop.jpg?' + new Date().getTime());
							$("#dialog").dialog('open');
						}
					);
				}
			});
			
		});
		  
	</script>
</head>
<body>
	
	<div id="dialog">
		<img src="" id="dialogImage" />
	</div>
	
	<div id="camera">
		<script language="JavaScript">
			webcam.set_api_url( '<?php echo $_SERVER['PHP_SELF']."?action=capture&filename=".$csnumber; ?>' );
			webcam.set_quality( 100 ); // JPEG quality (1 - 100)
			webcam.set_shutter_sound( true ); // play shutter click sound
			//webcam.set_shutter_sound( false ); // play shutter click sound
		</script>
		
		<div id="fase1">
			<script language="JavaScript">
				document.write( webcam.get_html(640, 480) );
			</script>
			
			<form class="buttons">
				<span class="camera" onClick="do_upload()" /></span>
			</form>
		</div>
		
		<div id="fase2" style="display:none;">
			<div id="imageWrapper">
				<img id="target" src="" />
			</div>
			
			<form class="buttons">
				<span class="back" onClick="back()" /></span>
				<span class="crop" onClick="$('#frmCut').submit()" /></span>
			</form>
			
			<form id="frmCut" action="" method="post">
				<input type="hidden" id="x" name="x" />
				<input type="hidden" id="y" name="y" />
				<input type="hidden" id="w" name="w" />
				<input type="hidden" id="h" name="h" />
			</form>
		</div>

	</div>
</body>
</html>
