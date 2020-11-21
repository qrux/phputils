<?php
/**
 * Copyright (c) 2012 Troy Wu
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 */



namespace FJ;



use \Exception;



class PG
{
    const DEBUG_SQL    = false;
    const DEBUG_SCALAR = false;
    const DEBUG_LIST   = false;
    const DEBUG_MAP    = false;



    private $db = false;



    /**
     * PG constructor.
     *
     * @param $connString
     *
     * @throws Exception
     */
    function __construct ( $connString )
    {
        $this->db = pg_connect($connString);
        if ( false === $this->db )
        {
            throw new \Exception("Cannot open Postgres database; check server and connection string.");
        }
    }



    function __destruct ()
    {
        if ( false !== $this->db )
        {
            clog("Closing database connection...");
            pg_close($this->db);
        }
    }



    public function queryScalar ( $sql )
    {
        if ( self::DEBUG_SQL ) clog("sql", $sql);

        $result = pg_query($this->db, $sql);

        if ( false === $result ) return [ false, pg_last_error($this->db) ];

        if ( $row = pg_fetch_row($result) )
        {
            if ( self::DEBUG_SCALAR ) clog("DB row", $row);

            $val = $row[0];

            if ( self::DEBUG_SCALAR ) clog("val", $val);

            return [ $val, false ];
        }

        return [ false, pg_last_error($this->db) ];
    }



    public function querySimpleMap ( $sql )
    {
        if ( self::DEBUG_SQL ) clog("sql", $sql);

        $result = pg_query($this->db, $sql);

        if ( false === $result ) return [ false, pg_last_error($this->db) ];

        $map = [];
        while ( $row = pg_fetch_row($result) )
        {
            if ( self::DEBUG_MAP ) clog("DB row", $row);

            $key = $row[0];
            $val = $row[1];

            $map[$key] = $val;
        }

        if ( self::DEBUG_MAP ) clog("map", $map);
        if ( self::DEBUG_MAP ) clog("count", count($map));

        return [ $map, false ];
    }



    public function queryList ( $sql )
    {
        if ( self::DEBUG_SQL ) clog("sql", $sql);

        $result = pg_query($this->db, $sql);

        if ( false === $result ) return [ false, pg_last_error($this->db) ];

        $list = [];
        while ( $row = pg_fetch_row($result) )
        {
            if ( self::DEBUG_LIST ) clog("DB row", $row);

            $val = $row[0];

            $list[] = $val;
        }

        if ( self::DEBUG_LIST ) clog("list", $list);
        if ( self::DEBUG_LIST ) clog("count", count($list));

        return [ $list, false ];
    }



    public function query ( $sql )
    {
        if ( self::DEBUG_SQL ) clog("sql", $sql);

        $result = pg_query($this->db, $sql);

        if ( false === $result ) return [ false, pg_last_error($this->db) ];

        return [ $result, false ];
    }



    /**
     * Returns the query as a map.
     *
     * @param string $sql - Query.
     *
     * @return array
     */
    public function queryMap ( $sql )
    {
        if ( self::DEBUG_SQL ) clog("sql", $sql);

        $result = pg_query($this->db, $sql);

        if ( false === $result ) return [ false, pg_last_error($this->db) ];

        $rowid   = 0;
        $columns = $this->getColumnNames($result);
        $map     = [];

        while ( $row = pg_fetch_row($result) )
        {
            $rowmap = array_combine($columns, $row);
            $map[]  = $rowmap;
        }
        return [ $map, false ];
    }



    private function getColumnNames ( $result )
    {
        $columns = [];
        $count   = pg_num_fields($result);
        for ( $i = 0; $i < $count; ++$i )
        {
            $columns[$i] = pg_field_name($result, $i);
        }

        return $columns;
    }



    public function insert ( $table, $array )
    {
        if ( self::DEBUG_SQL ) clog("Inserting into $table", $array);

        $result = pg_insert($this->db, $table, $array);

        if ( false === $result ) return [ false, pg_last_error($this->db) ];

        return [ true, false ];
    }



    public function exists ( $table, $array )
    {
        $where = "";
        $and   = "";
        foreach ( $array as $key => $inputValue )
        {
            $value = $this->esc($inputValue);

            $where .= $and . "$key = $value";
            $and   = " and ";
        }

        $sql = <<<EOF
select count(1) from $table where $where;
EOF;

        list($result, $err) =
            $this->queryScalar($sql);

        if ( false === $result ) return [ false, $err ];

        $exists = 0 < $result;

        return [ $exists, false ];
    }



    public function update ( $table, $array, $conditions )
    {
        $result = pg_update($this->db, $table, $array, $conditions);

        if ( false === $result ) return [ false, pg_last_error($this->db) ];

        return [ true, false ];
    }



    public function delete ( $table, $array )
    {
        $result = pg_delete($this->db, $table, $array);

        if ( false === $result ) return [ false, pg_last_error($this->db) ];

        return [ true, false ];
    }



    public function esc ( $string )
    {
        //clog("string", $string);
        return pg_escape_literal($this->db, $string);
    }
}
