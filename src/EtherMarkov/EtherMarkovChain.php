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
}
