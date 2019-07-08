<?php
	require __DIR__ . '/StarMadeQuery.class.php';
	
	// For the sake of this example
	Header( 'Content-Type: text/plain' );
	
	// Edit this ->
	define( 'SM_SERVER_ADDR', 'localhost' );
	define( 'SM_SERVER_PORT', 4242 );
	define( 'SM_TIMEOUT', 2 );
	// Edit this <-
	
	$Query = new StarMadeQuery( );
	
	try
	{
		$Query->Connect( SM_SERVER_ADDR, SM_SERVER_PORT, SM_TIMEOUT );
		
		print_r( $Query->GetInfo( ) );
	}
	catch( StarMadeQueryException $e )
	{
		echo $e->getMessage( );
	}
