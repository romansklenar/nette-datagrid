<?php

/**
 * Gettext translator.
 * This solution is mostly based on Zend_Translate_Adapter_Gettext (c) Zend Technologies USA Inc. (http://www.zend.com), new BSD license
 *
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
class Translator extends Object implements /*Nette\*/ITranslator
{
	/** @var string */
	public $locale;
	
	/** @var bool */
	private $endian = FALSE;
	
	/** @var string|stream  MO gettext file */
	protected $file = FALSE;
	
	/** @var array  translation table */
	protected $dictionary = array();
	
	/** @var array */
	protected $meta;
	
	
	
	/**
	 * Translator contructor.
	 * @param string
	 * @param string
	 * @return void
	 */
	public function __construct($filename, $locale = NULL)
	{
		$this->locale = $locale;
		$this->buildDictionary($filename);
	}
	
	
	/**
	 * Translates the given string.
	 * @param  string	translation string
	 * @param  int		count
	 * @return string
	 */
	public function translate($message, $count = 1)
	{
		$message = (string)$message;
		if (!empty($message) && isset($this->dictionary[$message])) {
			$word = $this->dictionary[$message];
		
			$s = preg_replace('/([a-z]+)/', '$$1', "n=$count;" . $this->meta['Plural-Forms']);
			eval($s);
			$message = $word->translate($plural);
		}
		
		$args = func_get_args();
		if (count($args) > 1) {
			array_shift($args);
			$message = vsprintf($message, $args);
		}
		return $message;
	}
	
	
	/**
	 * Load translation data (MO file reader) and builds the dictionary.
	 * @param  string  $filename  MO file to add, full path must be given for access
	 * @throws InvalidArgumentException
	 * @return void
	 */
	protected function buildDictionary($filename)
	{
		$this->endian = FALSE;
		$this->file = @fopen($filename, 'rb');
		if (!$this->file) {
			throw new InvalidArgumentException("Error opening translation file '$filename'.");
		}
		if (@filesize($filename) < 10) {
			InvalidArgumentException("'$filename' is not a gettext file.");
		}

		// get endian
		$input = $this->readMoData(1);
		if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
			$this->endian = FALSE;
		} else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
			$this->endian = TRUE;
		} else {
			InvalidArgumentException("'$filename' is not a gettext file.");
		}
		// read revision - not supported for now
		$input = $this->readMoData(1);

		// number of bytes
		$input = $this->readMoData(1);
		$total = $input[1];

		// number of original strings
		$input = $this->readMoData(1);
		$originalOffset = $input[1];

		// number of translation strings
		$input = $this->readMoData(1);
		$translationOffset = $input[1];

		// fill the original table
		fseek($this->file, $originalOffset);
		$origtemp = $this->readMoData(2 * $total);
		fseek($this->file, $translationOffset);
		$transtemp = $this->readMoData(2 * $total);

		for ($count = 0; $count < $total; ++$count) {
			if ($origtemp[$count * 2 + 1] != 0) {
				fseek($this->file, $origtemp[$count * 2 + 2]);
				$original = @fread($this->file, $origtemp[$count * 2 + 1]);
			} else {
				$original = '';
			}

			if ($transtemp[$count * 2 + 1] != 0) {
				fseek($this->file, $transtemp[$count * 2 + 2]);
				$tr = fread($this->file, $transtemp[$count * 2 + 1]);
				if ($original === '') {
					$this->generateMeta($tr);
					continue;
				}
				
				$word = new Word(explode(String::chr(0x00), $original), explode(String::chr(0x00), $tr));
				$this->dictionary[$word->message] = $word;
			}
		}
		return $this->dictionary;
	}
	
	
	/**
	 * Read values from the MO file.
	 * @param  string
	 */
	private function readMoData($bytes)
	{
		$data = fread($this->file, 4 * $bytes);
		return $this->endian === FALSE ? unpack('V' . $bytes, $data) : unpack('N' . $bytes, $data);
	}
	
	
	/**
	 * Generates meta information about distionary.
	 * @return void
	 */
	private function generateMeta($s)
	{
		$s = trim($s);
		
		$s = preg_split('/[\n,]+/', $s);
		foreach ($s as $meta) {
			$pattern = ': ';
			$tmp = preg_split("($pattern)", $meta);
			$this->meta[trim($tmp[0])] = count($tmp) > 2 ? ltrim(strstr($meta, $pattern), $pattern) : $tmp[1];
		}
	}	
}


/**
 * Class that represents translatable word.
 * 
 * @author     Roman Sklenář
 * @package    DataGrid\Example
 */
class Word extends Object
{
	/** @var string|array */
	protected $message;
	
	/** @var string|array */
	protected $translation;
	
	/**
	 * Word constructor.
	 * @param string|array
	 * @param string|array
	 * @return void
	 */
	public function __construct($message, $translation)
	{
		$this->message = $message;
		$this->translation = $translation;
	}
	
	
	/**
	 * @return string
	 */
	public function getTranslation($index = 0)
	{
		return is_array($this->translation) ? $this->translation[$index] : $this->translation;
	}
	
	
	/**
	 * @return string
	 */
	public function getMessage($index = 0)
	{
		return is_array($this->message) ? $this->message[$index] : $this->message;
	}	
	
	
	/**
	 * Translates a word.
	 * @param  string  translation string
	 * @param  int	 count
	 * @return string
	 */
	public function translate($plural = 0)
	{
		$msg = $this->getTranslation($plural);
		return !empty($msg) ? $msg : $this->getMessage($plural);
	}
}