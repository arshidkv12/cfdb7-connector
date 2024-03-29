<?php
/**
 * Class QROptions
 *
 * @filesource   QROptions.php
 * @created      08.12.2015
 * @package      chillerlan\QRCode
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\QRCode;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Traits\Container;

/**
 * @property int    $version
 * @property int    $versionMin
 * @property int    $versionMax
 * @property int    $eccLevel
 * @property int    $maskPattern
 * @property bool   $addQuietzone
 * @property bool   $quietzoneSize
 *
 * @property string $outputType
 * @property string $cachefile
 *
 * @property string $eol
 * @property int    $scale
 *
 * @property string $cssClass
 *
 * @property string $textDark
 * @property string $textLight
 *
 * @property bool   $imageBase64
 * @property bool   $imageTransparent
 * @property array  $imageTransparencyBG
 * @property int    $pngCompression
 * @property int    $jpegQuality
 *
 * @property array  $moduleValues
 */
class QROptions{
	use Container;

	/**
	 * QR Code version number
	 *
	 *   [1 ... 40] or QRCode::VERSION_AUTO
	 *
	 * @var int
	 */
	protected $version = QRCode::VERSION_AUTO;

	/**
	 * Minimum QR version (if $version = QRCode::VERSION_AUTO)
	 *
	 * @var int
	 */
	protected $versionMin = 1;

	/**
	 * Maximum QR version
	 *
	 * @var int
	 */
	protected $versionMax = 40;

	/**
	 * Error correct level
	 *
	 *   QRCode::ECC_X where X is
	 *    L =>  7%
	 *    M => 15%
	 *    Q => 25%
	 *    H => 30%
	 *
	 * @var int
	 */
	protected $eccLevel = QRCode::ECC_L;

	/**
	 * Mask Pattern to use
	 *
	 *  [0...7] or QRCode::MASK_PATTERN_AUTO
	 *
	 * @var int
	 */
	protected $maskPattern = QRCode::MASK_PATTERN_AUTO;

	/**
	 * Add a "quiet zone" (margin) according to the QR code spec
	 *
	 * @var bool
	 */
	protected $addQuietzone = true;

	/**
	 *  Size of the quiet zone
	 *
	 *   internally clamped to [0 ... $moduleCount / 2], defaults to 4 modules
	 *
	 * @var int
	 */
	protected $quietzoneSize = 4;

	/**
	 * QRCode::OUTPUT_MARKUP_XXXX where XXXX = HTML, SVG
	 * QRCode::OUTPUT_IMAGE_XXX where XXX = PNG, GIF, JPG
	 * QRCode::OUTPUT_STRING_XXXX where XXXX = TEXT, JSON
	 *
	 * @var string
	 */
	protected $outputType = QRCode::OUTPUT_IMAGE_PNG;

	/**
	 * /path/to/cache.file
	 *
	 * @var string
	 */
	protected $cachefile;

	/**
	 * newline string [HTML, SVG, TEXT]
	 *
	 * @var string
	 */
	protected $eol = PHP_EOL;

	/**
	 * size of a QR code pixel [SVG, IMAGE_*]
	 * HTML -> via CSS
	 *
	 * @var int
	 */
	protected $scale = 5;

	/**
	 * a common css class
	 *
	 * @var string
	 */
	protected $cssClass;

	/**
	 * string substitute for dark
	 *
	 * @var string
	 */
	protected $textDark = '🔴';

	/**
	 * string substitute for light
	 *
	 * @var string
	 */
	protected $textLight = '⭕';

	/**
	 * toggle base64 or raw image data
	 *
	 * @var bool
	 */
	protected $imageBase64 = true;

	/**
	 * toggle transparency, not supported by jpg
	 *
	 * @var bool
	 */
	protected $imageTransparent = true;

	/**
	 * @see imagecolortransparent()
	 *
	 * @var array [R, G, B]
	 */
	protected $imageTransparencyBG = [255, 255, 255];

	/**
	 * @see imagepng()
	 *
	 * @var int
	 */
	protected $pngCompression = -1;

	/**
	 * @see imagejpeg()
	 *
	 * @var int
	 */
	protected $jpegQuality = 85;

	/**
	 * Module values map
	 *
	 *   HTML : #ABCDEF, cssname, rgb(), rgba()...
	 *   IMAGE: [63, 127, 255] // R, G, B
	 *
	 * @var array
	 */
	protected $moduleValues = [
		// light
		QRMatrix::M_DATA            => false, // 4
		QRMatrix::M_FINDER          => false, // 6
		QRMatrix::M_SEPARATOR       => false, // 8
		QRMatrix::M_ALIGNMENT       => false, // 10
		QRMatrix::M_TIMING          => false, // 12
		QRMatrix::M_FORMAT          => false, // 14
		QRMatrix::M_VERSION         => false, // 16
		QRMatrix::M_QUIETZONE       => false, // 18
		QRMatrix::M_TEST            => false, // 255
		// dark
		QRMatrix::M_DARKMODULE << 8 => true,  // 512
		QRMatrix::M_DATA << 8       => true,  // 1024
		QRMatrix::M_FINDER << 8     => true,  // 1536
		QRMatrix::M_ALIGNMENT << 8  => true,  // 2560
		QRMatrix::M_TIMING << 8     => true,  // 3072
		QRMatrix::M_FORMAT << 8     => true,  // 3584
		QRMatrix::M_VERSION << 8    => true,  // 4096
		QRMatrix::M_TEST << 8       => true,  // 65280
	];

}
