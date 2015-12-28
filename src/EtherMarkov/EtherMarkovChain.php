<?php
namespace EtherMarkov;

class EtherMarkovChain
{
    protected $sample;
    protected $words;
    protected $lcWords;
    protected $caseInsensitive;
    
    /**
     * @param string $sample
     * @param int $chainLength
     * @param boolean $caseInsensitive
     */
    public function __construct($sample, $chainLength = 2, $caseInsensitive = true)
    {
        $this->sample = $sample;
        $this->caseInsensitive = $caseInsensitive;
        $this->words = $this->splitText($this->sample, $chainLength);
        if ($this->caseInsensitive) {
            $this->lcWords = $this->splitText(strtolower($this->sample), $chainLength);
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
    public function generate($chainLength = 2, $beginning = true)
    {
        $startingPoint = null;
    
        if (is_string($beginning)) {
            $startingPoint = $this->getMatchingBlock($beginning);
        } else {
            $startingPoint = $beginning ? $this->getRandomSentenceBeginning($this->sample, $chainLength) : $this->getRandomBlock();
        }
    
        return $this->makeChain($startingPoint, $chainLength);
    }
    
    /**
     * Retrieves a random chunk of $chainLength words
     * @return string
     */
    public function getRandomBlock()
    {
        $index = array_rand($this->words);
        return $this->words[$index];
    }
    
    /**
     * Gets chunk of $chainLength words matching $string
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
    public function makeChain($beginning, $chainLength = 2)
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
     * @param int $chainLength
     * @return array
     */
    public function splitText($text, $chainLength)
    {
        $words = preg_split("/\s+/", $text);
    
        if ($chainLength == 1) {
            return $words;
        }
    
        $chunks = array_chunk($words, $chainLength);
        $split = [];
    
        foreach ($chunks as $chunk) {
            $split[] = implode(' ', $chunk);
        }
    
        return $split;
    }
}
