<!DOCTYPE html>
<html>

	<head>
	
		<title><?php echo $CONFIG->site_title; ?></title>
		
		<?php echo $HEAD_ASSETS; ?>
	
	</head>
	
	<body>
	
		<div>
		
		<?php echo $CONTENT; ?>
		
		</div>
		
		<?php echo $ERRORS; ?>
		
		<?php echo $BODY_ASSETS; ?>
	
	</body>

</html>