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
}
