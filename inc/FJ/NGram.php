<?php
/**
 * Copyright (c) 2014-2020 Troy Wu
 * Copyright (c) 2021      Version2 OÜ
 * All rights reserved.
 *
 * SHOULD THE COPYRIGHT HOLDERS GRANT PERMISSION TO USE THIS SOFTWARE,
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */



namespace FJ;



class NGram
{
    const DEBUG         = false;
    const DEBUG_CORPUS  = false;
    const DEBUG_VERBOSE = false;

    const SHORT_CORPUS_THRESHOLD = 1024; // 1 kiB; below this, don't split into sentences...



    private $n               = 0; // (n)-gram.
    private $minCountAllowed = 1; // count
    private $shouldLimitSize = true; // limit (n)?
    private $hasAllowedWords = false;
    private $allowedWords    = [];



    function __construct ( $params )
    {
        $this->n               = FJ::getParam("ngramSize", $params);
        $this->minCountAllowed = FJ::getParam("ngramCount", $params);
        $this->allowedWords    = FJ::getParam("allowedWords", $params);

        if ( false === $this->allowedWords )
        {
            $this->hasAllowedWords = false;
            $this->allowedWords    = [];
        }
        else
        {
            $this->hasAllowedWords = true;
            sort($this->allowedWords); // Allows us to use BINARY_SEARCH, instead of in_array().
        }

        if ( !is_numeric($this->n) || 0 > $this->n )
        {
            $this->n = 0;
        }
        $this->n = (int)$this->n; // Make it a number.

        if ( self::DEBUG )
        {
            clog("       Generating", (0 === $this->n ? "*" : $this->n) . "-grams");
            clog("        min-count", $this->minCountAllowed);
            clog("    allowed-words", count($this->allowedWords));
            clog("has-allowed-words", $this->hasAllowedWords);
        }

        if ( 0 === $this->n ) $this->shouldLimitSize = false;

        if ( false == $this->minCountAllowed || 1 > $this->minCountAllowed ) $this->minCountAllowed = 1;
    }


    public function count ( $corpus )
    {
        $corpus = trim($corpus);

        if ( self::DEBUG_CORPUS ) clog("NGram.count() - corpus", $corpus);

        //mb_internal_encoding('UTF-8');

        $sentences = (strlen($corpus) < self::SHORT_CORPUS_THRESHOLD)
            ? [ $corpus ]
            : preg_split('/[^\s|\pL]/umi', $corpus, -1, PREG_SPLIT_NO_EMPTY);

        if ( self::DEBUG_CORPUS ) clog("sentences", $sentences);

        if ( false == $sentences ) return [];

        $wordsSequencesCount = [];
        foreach ( $sentences as $sentence )
        {
            //$words = array_map('mb_strtolower',
            $words = array_map('strtolower',
                               preg_split('/[^\pL+]/umi', $sentence, -1, PREG_SPLIT_NO_EMPTY));

            if ( self::DEBUG_VERBOSE ) clog("words", $words);

            foreach ( $words as $index => $word )
            {
                if ( self::DEBUG_VERBOSE ) clog("index", $index);

                $wordsSequence = '';
                $ngSize        = 1;

                $slice = array_slice($words, $index);

                if ( self::DEBUG_VERBOSE ) clog("slice", $slice);

                foreach ( $slice as $nextWord )
                {
                    if ( self::DEBUG_VERBOSE ) clog("   next word", $nextWord);
                    if ( self::DEBUG_VERBOSE ) clog("  current(n)", $ngSize);
                    if ( self::DEBUG_VERBOSE ) clog("should-limit", $this->shouldLimitSize);

                    if ( $this->shouldLimitSize && $ngSize > $this->n ) break;

                    $wordsSequence .= $wordsSequence ? (' ' . $nextWord) : $nextWord;
                    if ( !isset($wordsSequencesCount[$wordsSequence]) )
                    {
                        $wordsSequencesCount[$wordsSequence] = 0;
                    }
                    ++$wordsSequencesCount[$wordsSequence];

                    ++$ngSize;
                }
            }
        }

        if ( self::DEBUG_VERBOSE ) clog("word-seq-count", $wordsSequencesCount);

        $ngramsCount = array_filter($wordsSequencesCount,
            function ( $count, $ngram )
            {
                if ( $this->hasAllowedWords )
                {
                    $words = explode(" ", $ngram);
                    foreach ( $words as $word )
                        if ( $this->isNotAllowed($word) ) return false;
                }

                if ( self::DEBUG_VERBOSE ) clog("count($ngram)", $count);

                return $count >= $this->minCountAllowed;
            }, ARRAY_FILTER_USE_BOTH);

        return $ngramsCount;
    }


    private function isNotAllowed ( $word )
    {
        $w = strtoupper($word);

        return !in_array($w, $this->allowedWords);
    }
}
