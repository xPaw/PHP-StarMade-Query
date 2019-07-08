<?php
class StarMadeQueryException extends Exception
{
	// Exception thrown by StarMadeQuery class
}

class StarMadeQuery
{
	/*
	 * Class written by xPaw
	 *
	 * Website: http://xpaw.me
	 * GitHub: https://github.com/xPaw
	 */
	
	const TYPE_INT = 1;
	const TYPE_LONG = 2;
	const TYPE_FLOAT = 3;
	const TYPE_STRING = 4;
	const TYPE_BOOLEAN = 5;
	const TYPE_BYTE = 6;
	const TYPE_SHORT = 7;
	
	private $Socket;
	private $Info;
	
	public function Connect( $Ip, $Port = 4242, $Timeout = 3 )
	{
		if( !is_int( $Timeout ) || $Timeout < 0 )
		{
			throw new InvalidArgumentException( 'Timeout must be an integer.' );
		}
		
		$this->Socket = FSockOpen( 'tcp://' . $Ip, (int)$Port, $ErrNo, $ErrStr, $Timeout );
		
		if( $ErrNo || $this->Socket === false )
		{
			throw new StarMadeQueryException( 'Could not create socket: ' . $ErrStr );
		}
		
		Stream_Set_Timeout( $this->Socket, $Timeout );
		Stream_Set_Blocking( $this->Socket, true );
		
		if( !$this->WriteData( ) )
		{
			FClose( $this->Socket );
			
			throw new StarMadeQueryException( 'Failed to write to socket.' );
		}
		
		if( !$this->ReadData( ) )
		{
			FClose( $this->Socket );
			
			throw new StarMadeQueryException( 'Failed to read from socket.' );
		}
		
		FClose( $this->Socket );
	}
	
	public function GetInfo( )
	{
		return isset( $this->Info ) ? $this->Info : false;
	}
	
	private function WriteData( )
	{
		/*
		 * First we write packet size integer, which is 9,
		 * then we write header, which is 5 bytes
		 * and lastly we write integer 0, because there are no parameters
		 */
		$Command = "\x00\x00\x00\x09\x2A\xFF\xFF\x1\x6F\x00\x00\x00\x00";
		$Length  = StrLen( $Command );
		
		return $Length === FWrite( $this->Socket, $Command, $Length );
	}
	
	private function ReadData( )
	{
		$Data = FRead( $this->Socket, 4 );
		
		if( $Data === false || StrLen( $Data ) !== 4 )
		{
			return false;
		}
		
		// Packet size
		$Length = UnPack( 'N', $Data );
		$Length = $Length[ 1 ];
		
		if( $Length <= 0 )
		{
			return false;
		}
		
		$Data = FRead( $this->Socket, $Length + 8 );
		$Data = SubStr( $Data, 13 ); // Skip header crap: long + byte + short + byte + byte
		
		// Amount of parameters
		$Length = UnPack( 'N', SubStr( $Data, 0, 4 ) );
		$Length = $Length[ 1 ];
		
		if( $Length < 7 )
		{
			return false;
		}
		
		$CurrentChar = 4;
		$Parameters  = Array( );
		
		for( $i = 0; $i < $Length; $i++ )
		{
			$Type = Ord( $Data[ $CurrentChar++ ] );
			
			$Parameter = 'broken';
			
			switch( $Type )
			{
				case self :: TYPE_LONG:
				{
					//$Parameter = UnPack( 'L', SubStr( $Data, $CurrentChar, $CurrentChar + 8 ) );
					//$Parameter = $Parameter[ 1 ];
					
					$CurrentChar += 8;
					
					break;
				}
				case self :: TYPE_STRING:
				{
					$LengthString = ( Ord( $Data[ $CurrentChar ] ) << 8 ) | Ord( $Data[ $CurrentChar + 1 ] );
					
					$Parameter = SubStr( $Data, $CurrentChar + 2, $LengthString );
					
					$CurrentChar += 2 + $LengthString;
					
					break;
				}
				case self :: TYPE_FLOAT:
				{
					$Parameter = UnPack( 'N', SubStr( $Data, $CurrentChar, $CurrentChar + 4 ) );
					$Parameter = UnPack( 'f', Pack( 'I', $Parameter[ 1 ] ) );
					$Parameter = $Parameter[ 1 ];
					
					$CurrentChar += 4;
					
					break;
				}
				case self :: TYPE_INT:
				{
					$Parameter = UnPack( 'N', SubStr( $Data, $CurrentChar, $CurrentChar + 4 ) );
					$Parameter = $Parameter[ 1 ];
					
					$CurrentChar += 4;
					
					break;
				}
				case self :: TYPE_BOOLEAN:
				{
					$Parameter = Ord( $Data[ $CurrentChar ] );
					
					$CurrentChar += 1;
					
					break;
				}
				case self :: TYPE_BYTE:
				{
					$Parameter = Ord( $Data[ $CurrentChar ] );
					
					$CurrentChar += 1;
					
					break;
				}
				case self :: TYPE_SHORT:
				{
					$Parameter = UnPack( 'S', SubStr( $Data, $CurrentChar, $CurrentChar + 4 ) );
					$Parameter = $Parameter[ 1 ];
					
					$CurrentChar += 2;
					
					break;
				}
				default:
				{
					return false;
				}
			}
			
			$Parameters[ ] = $Parameter;
		}
		
		$this->Info = Array(
			'InfoVersion' => $Parameters[ 0 ],
			'Version'     => $Parameters[ 1 ],
			'Name'        => $Parameters[ 2 ],
			'Description' => $Parameters[ 3 ],
		//	'StartTime'   => $Parameters[ 4 ],
			'Players'     => $Parameters[ 5 ],
			'MaxPlayers'  => $Parameters[ 6 ]
		);
		
		return true;
	}
}
