
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
    </style>
  </head>
  <body>
  
  	<?php
	  	$drillCode = $_POST["drillCode"];
  	?>
  
    <form method="post" action="<?php echo $PHP_SELF;?>">
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
			$drillCode = preg_replace('/(X[0-9.]*?)(Y[\-0-9.]*)/i', "G00 Z1\nG00 $1 $2\nG01 Z-0.5", $drillCode);
			
            
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
			$drillCode = $header . $drillCode;
			
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