<?php
	// Edit this ->
	define( 'SM_SERVER_ADDR', 'localhost' );
	define( 'SM_SERVER_PORT', 4242 );
	define( 'SM_TIMEOUT', 1 );
	// Edit this <-
	
	// Display everything in browser, because some people can't look in logs for errors
	Error_Reporting( E_ALL | E_STRICT );
	Ini_Set( 'display_errors', true );
	
	require __DIR__ . '/StarMadeQuery.class.php';
	
	$Timer = MicroTime( true );
	$Query = new StarMadeQuery( );
	
	try
	{
		$Query->Connect( SM_SERVER_ADDR, SM_SERVER_PORT, SM_TIMEOUT );
	}
	catch( StarMadeQueryException $e )
	{
		$Error = $e->getMessage( );
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>StarMade Query PHP Class</title>
	
	<link rel="stylesheet" href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css">
	<style type="text/css">
		footer {
			margin-top: 45px;
			padding: 35px 0 36px;
			border-top: 1px solid #e5e5e5;
		}
		footer p {
			margin-bottom: 0;
			color: #555;
		}
	</style>
</head>

<body>
    <div class="container">
    	<div class="page-header">
			<h1>StarMade Query PHP Class</h1>
		</div>

<?php if( isset( $Error ) ): ?>
		<div class="alert alert-info">
			<h4 class="alert-heading">Exception:</h4>
			<?php echo htmlspecialchars( $Error ); ?>
		</div>
<?php else: ?>
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th colspan="2">Server info</th>
						</tr>
					</thead>
					<tbody>
<?php if( ( $Info = $Query->GetInfo( ) ) !== false ): ?>
<?php foreach( $Info as $InfoKey => $InfoValue ): ?>
						<tr>
							<td><?php echo htmlspecialchars( $InfoKey ); ?></td>
							<td><?php
	if( Is_Array( $InfoValue ) )
	{
		echo "<pre>";
		print_r( $InfoValue );
		echo "</pre>";
	}
	else
	{
		echo htmlspecialchars( $InfoValue );
	}
?></td>
						</tr>
<?php endforeach; ?>
<?php endif; ?>
					</tbody>
				</table>
<?php endif; ?>
		<footer>
			<p class="pull-right">Generated in <span class="badge badge-success"><?php echo Number_Format( ( MicroTime( true ) - $Timer ), 4, '.', '' ); ?>s</span></p>
			
			<p>Written by <a href="http://xpaw.me" target="_blank">xPaw</a></p>
		</footer>
	</div>
</body>
</html>
