<?php
/**
 * Copyright (c) 2012-2020 Troy Wu
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



use Exception;



class CSV
{
    const CSV_DELIM_DEFAULT = ",";
    const CSV_DELIM_COMMA   = self::CSV_DELIM_DEFAULT;
    const CSV_DELIM_OUTPUT  = self::CSV_DELIM_COMMA;
    const CSV_DELIM_SEMI    = ";";
    const CSV_COMMENT_CHAR  = "#";

    const DEBUG_CSV_VERBOSE = false;


    private $filepath;
    private $delim;
    private $columns;


    public function __construct ( $filepath, $delim = self::CSV_DELIM_COMMA )
    {
        $this->filepath = $filepath;
        $this->delim    = $delim;
    }


    /**
     * Checks the first character of a string, to compare with test character.
     *
     * @param $string String - Input to check for match.
     * @param $c      String - Test character.
     *
     * @return bool
     */
    private function isFirstCharacter ( $string, $c )
    {
        $firstChar = substr($string[0], 0, 1);

        if ( self::DEBUG_CSV_VERBOSE ) clog("first char", $firstChar);

        return $c == $firstChar;
    }


    /**
     * @param $tokens array - CSV tokens.
     *
     * @return bool - (true) if line is empty (may return 1 token of zero-length; (false) otherwise.
     */
    private function isEmptyLine ( $tokens )
    {
        return (
            (0 == count($tokens))
            ||
            (1 == count($tokens) && 0 == strlen(trim($tokens[0])))
        );
    }


    /**
     * @param $tokens array - CSV tokens.
     *
     * @return bool - (true) if trimmed line is either empty or starts with '#'; (false) otherwise.
     */
    private function isCommentLine ( $tokens )
    {
        if ( null == $tokens || $this->isEmptyLine($tokens) ) return true;

        $token = $tokens[0];

        if ( $this->isFirstCharacter($token, self::CSV_COMMENT_CHAR) ) return true;

        return false;
    }


    private function stripBOMHeader ( $tokens )
    {
        if ( self::DEBUG_CSV_VERBOSE ) clog("stripBOMHeader tokens", $tokens);

        $first = $tokens[0];

        $f0 = substr($first, 0, 1);
        $f1 = substr($first, 1, 1);
        $f2 = substr($first, 2, 1);

        // The UTF-8 representation of the BOM is the (hexadecimal) byte sequence 0xEF,0xBB,0xBF.

        $is0 = 0xEF === ord($f0);
        $is1 = 0xBB === ord($f1);
        $is2 = 0xBF === ord($f2);

        $hasBOM = $is0 && $is1 && $is2;

        if ( self::DEBUG_CSV_VERBOSE ) clog("has BOM", $hasBOM);
        if ( self::DEBUG_CSV_VERBOSE ) clog("    is0", $is0);
        if ( self::DEBUG_CSV_VERBOSE ) clog("    is1", $is1);
        if ( self::DEBUG_CSV_VERBOSE ) clog("    is2", $is2);

        if ( $hasBOM )
        {
            $output    = [];
            $output[0] = substr($first, 3);
            $len       = count($tokens);
            for ( $i = 1; $i < $len; ++$i )
            {
                $output[$i] = $tokens[$i];
            }

            if ( self::DEBUG_CSV_VERBOSE ) clog("output", $output);

            return $output;
        }

        if ( self::DEBUG_CSV_VERBOSE ) clog("after", $tokens);

        return $tokens;
    }


    /**
     * @param $visitor CSVisitor - Visits each line in the CSV, tokenized by fgetcsv().
     *
     * @throws Exception
     */
    public function read ( $visitor )
    {
        $csv = fopen($this->filepath, "r");

        if ( false === $csv )
        {
            clog("File {$csv} could not be opened for reading; aborting.");
            throw new Exception("CSV could not open {$csv}.");
        }

        clog("about to read file...");

        $lineIndex = -1;

        while ( false !== ($tokens = fgetcsv($csv, 0, $this->delim)) )
        {
            ++$lineIndex;

            if ( self::DEBUG_CSV_VERBOSE ) clog("start", $tokens);

            $visitor->ante($lineIndex);

            $tokens = $this->stripBOMHeader($tokens);

            if ( self::DEBUG_CSV_VERBOSE ) clog("after BOM-strip", $tokens);

            if ( $this->isCommentLine($tokens) )
            {
                if ( self::DEBUG_CSV_VERBOSE ) cclog(TEXT_COLOR_UL_YELLOW, "Skipping line: " . implode(",", $tokens));
                $visitor->parseComment($lineIndex, $tokens);
            }
            else
            {
                $visitor->parse($lineIndex, $tokens);
            }

            $visitor->post($lineIndex);
        }

        fclose($csv);

        $visitor->finish();
    }



    /**
     * @param $visitor CSVisitor - Visits each line in the CSV, tokenized by fgetcsv().
     *
     * @throws Exception
     */
    public function readWithHeaderRow ( $visitor )
    {
        $csv = fopen($this->filepath, "r");

        if ( false === $csv )
        {
            clog("File {$csv} could not be opened for reading; aborting.");
            throw new Exception("CSV could not open {$csv}.");
        }

        clog("about to read file...");


        $firstLineTokens = fgetcsv($csv); // Assume first line has column names
        $this->columns   = $this->stripBOMHeader($firstLineTokens); // Excel bullshit.

        $lineIndex = -1;

        while ( false !== ($tokens = fgetcsv($csv, 0, $this->delim)) )
        {
            ++$lineIndex;

            $visitor->ante($lineIndex);

            if ( $this->isCommentLine($tokens) )
            {
                $visitor->parseComment($lineIndex, $tokens);
            }
            else
            {
                $visitor->parse($lineIndex, $tokens, $this->columns);
            }

            $visitor->post($lineIndex);
        }

        fclose($csv);

        $visitor->finish();
    }
}
