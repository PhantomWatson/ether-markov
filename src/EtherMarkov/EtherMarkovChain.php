<?php
namespace EtherMarkov;

class EtherMarkovChain
{
    protected $blockSize;
    protected $caseInsensitive;
    protected $lcWords;
    protected $sample;
    protected $words;

    /**
     * @param string $sample
     * @param int $blockSize
     * @param boolean $caseInsensitive
     */
    public function __construct($sample, $blockSize = 2, $caseInsensitive = true)
    {
        $this->sample = $sample;
        $this->caseInsensitive = $caseInsensitive;
        $this->blockSize = $blockSize;
        $this->words = $this->splitText($this->sample, $this->blockSize);

        if ($this->caseInsensitive) {
            $this->lcWords = $this->splitText(strtolower($this->sample), $this->blockSize);
        }
    }

    /**
     * $beginning can be a string that the chain must begin with,
     * TRUE to start the chain with a sentence-beginner from the
     * sample text, or FALSE for random.
     *
     * @param int $chainLength
     * @param string|boolean $beginning
     * @return string
     */
    public function generate($chainLength = 10, $beginning = true)
    {
        $startingPoint = null;

        if (is_string($beginning)) {
            $startingPoint = $this->getMatchingBlock($beginning);
        } else {
            $startingPoint = $beginning ? $this->getRandomSentenceBeginning($this->sample, $this->blockSize) : $this->getRandomBlock();
        }

        return $this->makeChain($startingPoint, $chainLength);
    }

    /**
     * Retrieves a random chunk of $this->blockSize words
     * @return string
     */
    public function getRandomBlock()
    {
        $index = array_rand($this->words);
        return $this->words[$index];
    }

    /**
     * Gets chunk of $this->blockSize words matching $string
     * @param $string
     */
    public function getMatchingBlock($string)
    {
        $pattern = '/\b'.preg_quote($string, '/').'\b/';
        if ($this->caseInsensitive) {
            $pattern .= 'i';
        }
        $search = preg_grep($pattern, $this->words);
        return $search[array_rand($search)];
    }

    /**
     * @param string $beginning
     * @param int $chainLength
     * @return string
     */
    public function makeChain($beginning, $chainLength = 10)
    {
        $prevBlock = $beginning;
        $retval = $beginning;

        for ($i = 1; $i <= $chainLength; $i++) {
            $complement = $this->findMatch($prevBlock) ?: $this->getRandomBlock();
            $retval .= ' '.$complement;
            $prevBlock = $complement;
        }

        return $retval;
    }

    /**
     * @param $string
     * @return string|null
     */
    public function findMatch($string)
    {
        $string = strtolower($string);
        $search = $this->caseInsensitive ? array_keys($this->lcWords, $string) : array_keys($this->words, $string);
        if (count($search)) {
            $index = $search[array_rand($search)] + 1;
            return $this->words[$index];
        }

        return null;
    }

    /**
     * @param string $text
     * @param int $blockSize
     * @return array
     */
    public function splitText($text, $blockSize)
    {
        $words = preg_split("/\s+/", $text);

        if ($blockSize == 1) {
            return $words;
        }

        $chunks = array_chunk($words, $blockSize);
        $split = [];

        foreach ($chunks as $chunk) {
            $split[] = implode(' ', $chunk);
        }

        return $split;
    }

    /**
     * Find the position of the Xth occurrence of a substring in a string
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @param int $number
     * @return int
     */
    public static function strposX($haystack, $needle, $offset, $number)
    {
        if ($number == 1){
            return strpos($haystack, $needle, $offset);
        }
        return strpos($haystack, $needle, EtherMarkovChain::strposX($haystack, $needle, $offset, $number - 1) + strlen($needle));
    }

    /**
     * Get the beginnings of each sentence, each $blockSize words long.
     * Will stop looking for new sentence beginnings after finding $limit,
     * which can be set to FALSE to find all sentence beginnings.
     * @param string $text
     * @param int $blockSize
     * @param int|boolean $limit
     * @return array
     */
    public static function getSentenceBeginnings($text, $blockSize, $limit = 100)
    {
        $matches = [];
        preg_match('/(?:^|(?:[.!?]\s))(\w+)/', $text, $matches);

        // Get the first sentence beginning
        $pos = EtherMarkovChain::strposX($text, ' ', 0, $blockSize);
        $beginning = substr($text, 0, $pos);
        $sentenceBeginnings = [
            strip_tags($beginning)
        ];

        // Get subsequent sentence beginnings
        $sentenceEndings = ['.', '!', '?'];
        $count = 0;
        foreach ($sentenceEndings as $ending) {
            $ending .= ' ';
            $offset = 0;
            while ($endingPos = strpos($text, $ending, $offset)) {
                $endingPos += strlen($ending);
                $endOfBeginningPos = EtherMarkovChain::strposX($text, ' ', $endingPos, $blockSize);
                $length = $endOfBeginningPos - $endingPos;
                $beginning = substr($text, $endingPos, $length);

                // Reject anything that doesn't contain words
                if (! preg_match('/[A-Za-z]/', $beginning)) {
                    $offset = $endOfBeginningPos;
                    continue;
                }

                $sentenceBeginnings[] = strip_tags($beginning);
                $count++;
                if ($limit && $count == $limit) {
                    return $sentenceBeginnings;
                }
                $offset = $endOfBeginningPos;
            }
        }

        return $sentenceBeginnings;
    }

    public static function getRandomSentenceBeginning($text, $blockSize)
    {
        $beginnings = EtherMarkovChain::getSentenceBeginnings($text, $blockSize);
        $key = array_rand($beginnings);
        return $beginnings[$key];
    }

    public static function trimToNaturalEnding($text)
    {
        $endings = ['.', '?', '!'];
        $endPos = 0;
        foreach ($endings as $ending) {
            $pos = strrpos($text, $ending);
            if ($pos > $endPos) {
                $endPos = $pos;
            }
        }
        return substr($text, 0, $endPos + 1);
    }
}
