<?php
/**
 * Packt das WordPress-Theme als installierbare ZIP-Datei:
 *   php tools/build-zip.php  →  dist/schweisstechnik-blitz-theme.zip
 * (Ordner „schweisstechnik-blitz/“ an der Wurzel, Pfade mit „/“ – so erwartet es WordPress.)
 */
$root  = dirname( __DIR__ );
$theme = $root . '/theme/schweisstechnik-blitz';
$dist  = $root . '/dist';
@mkdir( $dist, 0777, true );
$file = $dist . '/schweisstechnik-blitz-theme.zip';
@unlink( $file );

$zip = new ZipArchive();
if ( true !== $zip->open( $file, ZipArchive::CREATE ) ) {
	fwrite( STDERR, "ZIP konnte nicht angelegt werden\n" );
	exit( 1 );
}
$count = 0;
$it    = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $f ) {
	$rel = str_replace( '\\', '/', substr( $f->getPathname(), strlen( $theme ) + 1 ) );
	if ( preg_match( '#(^|/)\.|\.map$#', $rel ) ) {
		continue;
	}
	$zip->addFile( $f->getPathname(), 'schweisstechnik-blitz/' . $rel );
	$count++;
}
$zip->close();
printf( "%s (%d Dateien, %.1f MB)\n", $file, $count, filesize( $file ) / 1048576 );
