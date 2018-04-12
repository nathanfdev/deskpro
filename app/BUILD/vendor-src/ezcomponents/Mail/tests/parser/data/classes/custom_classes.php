<?php
/**
 * DeskproEdit
 */
class SingleFileSet implements ezcMailParserSet
{
    private $fp = null;

    public function __construct( $file )
    {
        $fp = fopen( dirname( __FILE__ ). '../../' . $file, 'r' );
        if ( $fp == false )
        {
            throw new Exception( "Could not open file '{$file}' for testing." );
        }
        $this->fp = $fp;

//        while (!feof($fp)) {
//        $buffer = fgets($fp, 4096);
//        echo $buffer;
//    }
    }

    public function hasData()
    {
        return !feof( $this->fp );
    }

    public function getNextLine()
    {
        if ( feof( $this->fp ) )
        {
            if ( $this->fp != null )
            {
                fclose( $this->fp );
                $this->fp = null;
            }
            return null;
        }
        $next =  fgets( $this->fp );
        if ( $next == "" && feof( $this->fp ) ) // eat last linebreak
        {
            return null;
        }
        return $next;
    }

    public function nextMail()
    {
        return false;
    }
}
