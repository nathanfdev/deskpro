<?php
/**
 * DeskPRO Edit
 *
 * Copy of https://github.com/zetacomponents/Mail/blob/master/tests/parser/parser_test.php
 */

require dirname( __FILE__ ) . '/data/classes/custom_classes.php';

class ezcMailParserTest extends \PHPUnit\Framework\TestCase
{
    public function testUuencodedAttachment()
    {
        $parser = new \ezcMailParser();
        $set = new SingleFileSet( 'various/mail_with_dummy_pdf_uuencoded.mail' );
        $mail = $parser->parseMail( $set );
        $this->assertEquals( 1, count( $mail ) );
        $mail = $mail[0];
        $this->assertEquals( new \ezcMailAddress( 'fh@ez.no', 'Frederik Holljen', 'utf-8' ), $mail->from );
        $this->assertEquals( array( new \ezcMailAddress( 'fh@ez.no', '', 'utf-8' ) ), $mail->to );
        $this->assertEquals( array(), $mail->cc );
        $this->assertEquals( array(), $mail->bcc );
        $this->assertEquals( 'Mail with attachment', $mail->subject );
        $this->assertEquals( true, $mail->body instanceof \ezcMailMultipartMixed );
        $parts = $mail->body->getParts();
        $this->assertEquals( true, $parts[0] instanceof \ezcMailText );
        $this->assertEquals( true, $parts[1] instanceof \ezcMailFile );
        $this->assertEquals( 'Boundary-00=_M715D0nt6IAUljt', $mail->body->boundary );

        // check the body
        $this->assertEquals( "This is the body\n", $parts[0]->text );

        // check the file
        $this->assertEquals( 'dummy.pdf', strstr( $parts[1]->fileName, 'dummy.pdf' ) );
        $this->assertEquals( \ezcMailFile::CONTENT_TYPE_APPLICATION, $parts[1]->contentType );
        $this->assertEquals( \ezcMailFile::DISPLAY_ATTACHMENT, $parts[1]->dispositionType );
        $this->assertEquals( 'octet-stream', $parts[1]->mimeType );
        $this->assertEquals( '<200602061535.56671.fh@ez.no>', $mail->messageID );
        $this->assertFileEquals(
            dirname( __FILE__ ) . '/data/various/dummy.pdf',
            $parts[1]->fileName,
            "Wrong decoded file"
        );
    }

    public function testBase64EncodedAttachment()
    {
        $parser = new \ezcMailParser();
        $set = new SingleFileSet( 'various/mail_with_dummy_jpg_base64encoded.mail' );
        $mail = $parser->parseMail( $set );
        $this->assertEquals( 1, count( $mail ) );
        $mail = $mail[0];
        $this->assertEquals( new \ezcMailAddress( 'fh@ez.no', 'Frederik Holljen', 'utf-8' ), $mail->from );
        $this->assertEquals( array( new \ezcMailAddress( 'fh@ez.no', '', 'utf-8' ) ), $mail->to );
        $this->assertEquals( array(), $mail->cc );
        $this->assertEquals( array(), $mail->bcc );
        $this->assertEquals( 'Mail with attachment', $mail->subject );
        $this->assertEquals( true, $mail->body instanceof \ezcMailMultipartMixed );
        $parts = $mail->body->getParts();
        $this->assertEquals( true, $parts[0] instanceof \ezcMailText );
        $this->assertEquals( true, $parts[1] instanceof \ezcMailFile );
        $this->assertEquals( 'Boundary-00=_M715D0nt6IAUljt', $mail->body->boundary );

        // check the body
        $this->assertEquals( "This is the body\n", $parts[0]->text );

        // check the file
        $this->assertEquals( 'dummy.jpg', strstr( $parts[1]->fileName, 'dummy.jpg' ) );
        $this->assertEquals( \ezcMailFile::CONTENT_TYPE_IMAGE, $parts[1]->contentType );
        $this->assertEquals( \ezcMailFile::DISPLAY_ATTACHMENT, $parts[1]->dispositionType );
        $this->assertEquals( 'jpeg', $parts[1]->mimeType );
        $this->assertEquals( '<200602061535.56671.fh@ez.no>', $mail->messageID );
        $this->assertFileEquals(
            dirname( __FILE__ ) . '/data/various/dummy.jpg',
            $parts[1]->fileName,
            "Wrong decoded file"
        );
    }
}