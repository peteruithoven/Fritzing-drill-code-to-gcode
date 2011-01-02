
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Fritzing drill file to gcode convertor</title>
    
    <style>
    body
    {
    	font-family: Arial;
    	border: 0 none; 
    	height: 100%
    }
    textarea, input
    {
    	margin: 0 0 1em 0;
    }
    label
    {
    	width: 130px;
    	display: block;
    	float: left;
    }
    .input-text
    {
    	width: 50px;	
    }
    </style>
  </head>
  <body>
  
  	<?php
  		
  		$drillCode = $_POST["drillCode"];
	  	$width = $_POST["width"];
	  	$height = $_POST["height"];
	  	$drillDepth = $_POST["drillDepth"];
	  	$thickness = $_POST["thickness"];
	  	$drillCodeText = "";
	  	$widthText = "";
	  	$heightText = "";
	  	$drillDepthText = "0.2";
	  	$thicknessText = "2";
	  	
  		if(isset($_POST['submit']))
  		{
  			$drillCodeText = $drillCode;
  			$widthText = $width;
  			$heightText = $height;
  			$drillDepthText = $drillDepth;
  			$thicknessText = $thickness;
  		}
  		else
  		{
  			$drillCodeText = "Enter code from your _drill.txt file. You can generate a drill file when you do a Gerber export.";
  		}
  	?>
  
    <form method="post" action="<?php echo $PHP_SELF;?>">
    	<label>Board width: </label><input class="input-text" type="text" name="width" value="<? echo $widthText ?>"/> mm<br/>
    	<label>Board height: </label><input class="input-text" type="text" name="height" value="<? echo $heightText ?>"/> mm<br/>
    	<label>Drill depth: </label><input class="input-text" type="text" name="drillDepth" value="<? echo $drillDepthText ?>"/> mm<br/>
    	<label>Board thickness: </label><input class="input-text" type="text" name="thickness" value="<? echo $thicknessText ?>"/> mm<br/>
    	
	    <textarea rows="20" cols="50" name="drillCode"><?php echo (isset($_POST['submit']))? $drillCode : "Enter code from your _drill.txt file. You can generate a drill file when you do a Gerber export."; ?></textarea><br /> 
	    <input type="submit" value="submit" name="submit"><br />
	</form>
    
    <?php
		
		
		function inches2mm($matches)
		{
			$axis = $matches[1];
			$inches = $matches[2];
			$mm = $inches*25.4;
			$space = $matches[3];
		  	return $axis.$mm.$space;
		}
		
		
		if (isset($_POST['submit']))
		{
			// remove unkown commands
			$drillCode = preg_replace('/[M|T|F|C|TZ|%][0-9.,\s]*/i', "", $drillCode);
		
			// make sure the drill first moves up, then goes to the area and then drills downs.
			$drillCode = preg_replace('/(X[0-9.]*?)(Y[\-0-9.]*)/i', "G00 Z1\nG00 $1 $2\nG01 Z-$drillDepth", $drillCode);
			
			// inches 2 mm
            $drillCode = preg_replace_callback('/([X|Y])([0-9.]*?)(\s)/',
			            "inches2mm",
			            $drillCode);
            
			// add header;
			$header = 	"G90 (use absolute coordinates)\n";
			$header .= 	"G21 (mm)\n";
			$header .= 	"M3 (spindle on, CW)\n";
			$header .= 	"\n";
			$header .= 	"F400\n";
			$header .= 	"S1000\n";
			$header .= 	"\n";
			$header .= 	"(drilling holes)\n";
			$drillCode = $header . $drillCode;
			
			// add cutting out board
			$removingBoard = 	"\n(milling out board)\n";
			$removingBoard .= 	"G00 Z1.000\n";
			$removingBoard .= 	"G00 X0 Y0\n";
			$removingBoard .= 	"G01 Z-$thickness\n";
			$removingBoard .= 	"G01 X$width Y0\n";
			$removingBoard .= 	"G01 X$width Y$height\n";
			$removingBoard .= 	"G01 X0 Y$height\n";
			$removingBoard .= 	"G01 X0 Y0\n";
			$drillCode = $drillCode . $removingBoard;
			
			// add footer;
			$footer = 	"\n";
			$footer .= 	"G00 Z10.000\n";
			$footer .= 	"G00 X0 Y0\n";
			$footer .= 	"M5 (stop spindle)\n";
			$footer .= 	"M2 (end program)\n";
			
			$drillCode = $drillCode . $footer;
			
	?> 
    <textarea rows="20" cols="50" name="drillCode"><?php echo $drillCode; ?></textarea><br /> 
    <?php
    	};
    ?>
    
  </body>
</html>