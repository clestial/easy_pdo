<?php
/**
 * *** BEGIN LICENSE BLOCK *****
 *
 * This file is part of EasyPDO (http://easypdo.robpoyntz.com/).
 *
 * Software License Agreement (New BSD License)
 *
 * Copyright (c) 2010, Robert Poyntz
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 *
 *     * Neither the name of Robert Poyntz nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * ***** END LICENSE BLOCK *****
 *
 * @originalworkcopyright   Copyright (C) 2010 Robert Poyntz 
 * @originalworkauthor      Robert Poyntz <rob@digitalfinery.com.au>
 * @modifiedworkcopyright   Copyright (C) 2013 clestial
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @package     EasyPDO
 * @version     0.1.7
 * 
 * CHANGELOG by clestial since 0.1.6:
 * 0.1.7
 * - Added functions FetchValues, and FetchValuesAssoc which works as FetchValue, but instead fetches all values to a numeric or assoc array. Kinda weird results. But hey, I needed it for something... not sure what.
 * - Added functions FetchNumericArray and FetchAssocArray, as shortcuts to FetchArray.
 * - Added functions FetchAllAsNumeric and FetchAllAsAssoc, as shortcuts to FetchAll. Changing and keeping track of fetchmode is annoying... 
 * - Changed BindParams to accept named parameters. examples:
 * ExecuteSQL("SELECT id FROM table WHERE id=?", "i", 2); // works as before.
 * ExecuteSQL(“SELECT id FROM table WHERE id=:id”, array(“:id”=>array(2, “i”))); // set type to PDO::PARAM_INT
 * ExecuteSQL(“SELECT id FROM table WHERE email=:email”, array(“:email”=>”this@email.com”)); // no type, defaults to PDO::PARAM_STR (shortest))
 * ExecuteSQL(“SELECT id FROM table WHERE email=:email”, array(“:email”=>array(”this@email.com”)); // also defaults to PDO::PARAM_STR (array unnecessary though)
 * ExecuteSQL(“SELECT id FROM table WHERE email=:email”, array(“:email”=>array(”this@email.com”, "s")); // set type to PDO::PARAM_STR (array very unnecessary though)
 * 
 * - Added function GetLastInterpolatedSQL, which returns the last query as it would be executed on the database, interpolated with the parameters set. (this can be improved to give actual working SQL queries...)
 * - Overloaded ExecuteSQL to return either last insert ID, or the rowcount (affected rows) from select/update, depending on type of query, INSERT or SELECT/UPDATE
 * - Added support for optional port to connect to, just add it to $server, "localhost:33060"
 */
 
  require_once dirname(__FILE__) . '/easypdo.php';

  class EasyPDO_MySQL extends EasyPDO {
    
    private $LastParams = array();

    function __construct($server, $database, $username = null, $password = null, $charset = 'utf8', $collate = 'utf8_unicode_ci') {
      if (stripos($server, ':')) {
        $split = explode(':', $server);
        $server = $split[0] . ';port=' . $split[1];
      }
      $connectionString = 'mysql:host=' . $server . ';dbname=' . $database . ';charset=' . $charset;
      parent::__construct($connectionString, $username, $password);
      $this->PDO->exec("SET NAMES '$charset' COLLATE '$collate'");
    }

    /*
     * Returns a numeric array with all results from SQL SELECT statement
     * @param string $sql
     * @param string $types optional parameter type definition
     * @param mixed $value,... optional parameter value
     * @return mixed
     */
    public function FetchValues($sql) {
      $fetchMode = $this->FetchMode;
      $this->SetFetchMode(self::FETCH_MODE_NUMERIC_ARRAY);
      $passArgs = func_get_args();
      $return = call_user_func_array(array($this, 'Fetch'), $passArgs);
      $this->SetFetchMode($fetchMode);
      $rd = array();
      foreach ($return as $data) {
        $rd[] = $data[0];
      }
      return $rd;
    }

    public function FetchValuesAssoc($sql) {
      $fetchMode = $this->FetchMode;
      $this->SetFetchMode(self::FETCH_MODE_NUMERIC_ARRAY);
      $passArgs = func_get_args();
      $return = call_user_func_array(array($this, 'Fetch'), $passArgs);
      $this->SetFetchMode($fetchMode);
      $rd = array();
      foreach ($return as $data) {
        $rd[$data[0]] = 1;
      }
      return $rd;      
    }

    public function FetchNumericArray($sql) {
      $fetchMode = $this->FetchMode;
      $this->SetFetchMode(self::FETCH_MODE_NUMERIC_ARRAY);
      $passArgs = func_get_args();
      $return = call_user_func_array(array($this, 'FetchArray'), $passArgs);
      $this->SetFetchMode($fetchMode);
      return $return;
    }
    
    public function FetchAssocArray($sql) {
      $fetchMode = $this->FetchMode;
      $this->SetFetchMode(self::FETCH_MODE_ASSOCIATIVE_ARRAY);
      $passArgs = func_get_args();
      $return = call_user_func_array(array($this, 'FetchArray'), $passArgs);
      $this->SetFetchMode($fetchMode);
      return $return;
    }
    
    public function FetchAsAssocArray($sql) {
      $fetchMode = $this->FetchMode;
      $this->SetFetchMode(self::FETCH_MODE_ASSOCIATIVE_ARRAY);
      $passArgs = func_get_args();
      $return = call_user_func_array(array($this, 'Fetch'), $passArgs);
      $this->SetFetchMode($fetchMode);
      return $return;
    }
    
    public function FetchAsNumericArray($sql) {
      $fetchMode = $this->FetchMode;
      $this->SetFetchMode(self::FETCH_MODE_NUMERIC_ARRAY);
      $passArgs = func_get_args();
      $return = call_user_func_array(array($this, 'Fetch'), $passArgs);
      $this->SetFetchMode($fetchMode);
      return $return;
    }
    
    public function FetchAllAsNumeric($sql) {
      $fetchMode = $this->FetchMode;
      $this->SetFetchMode(self::FETCH_MODE_NUMERIC_ARRAY);
      $passArgs = func_get_args();
      $return = call_user_func_array(array($this, "FetchAll"), $passArgs);
      $this->SetFetchMode($fetchMode);
      return $return;
    }
    
    public function FetchAllAsAssoc($sql) {
      $fetchMode = $this->FetchMode;
      $this->SetFetchMode(self::FETCH_MODE_ASSOCIATIVE_ARRAY);
      $passArgs = func_get_args();
      $return = call_user_func_array(array($this, "FetchAll"), $passArgs);
      $this->SetFetchMode($fetchMode);
      return $return;
    }

    public function getError() {
      return $this->PDO->errorInfo();
    }

    /**
     * Binds values to the parameters in a PDOStatement object.
     * $args is assumed to be an array, the first element of which specifies parameter types,
     * the remaining elements being the parameter values. If the second argument is an array,
     * it's elements are used as the parameter values
     * 
     * addendum:
     * if args is an array with one elements, it is assumed that named statements is being used.
     * @param array $args
     */
    protected function BindParams($args) {
      $this->LastParams = array();
      if (count($args) > 1) {
        if ((count($args) === 2) && is_array($args[1]))
	    		array_splice($args, 1, 1, $args[1]);
				
        $types = str_split(array_shift($args));
        if (count($types) !== count($args))
          throw new EDatabaseException('Number of parameters does not equal number of parameter types');

        for ($paramIndex = 0; $paramIndex < count($args); $paramIndex++) {
          $this->LastParams[] = $args[$paramIndex];
          $this->Query->bindParam(1 + $paramIndex, $args[$paramIndex], $this->ParamTypes[$types[$paramIndex]]);
        }
      } elseif (count($args)==1 && is_array($args[0])) {
        foreach ($args[0] as $paramKey => $paramData) {
          if (is_array($paramData)) {
            $paramType = (isset($paramData[1])) ? $this->ParamTypes[$paramData[1]] : PDO::PARAM_STR;
            $paramValue = $paramData[0];
            $this->Query->bindParam($paramKey, $args[0][$paramKey][0], $paramType);
          } else {
            $paramType = PDO::PARAM_STR;
            $paramValue = $paramKey;
            $this->Query->bindParam($paramKey, $args[0][$paramKey], $paramType);
          }
          $this->LastParams[$paramKey] = ($paramValue==null) ? 'null' : $paramValue; // For logging purposes, null have to be printed out.
        }
      }
    }
    public function ExecuteSQL($sql) {
      $passArgs = func_get_args();
      $return = call_user_func_array(array('parent', 'ExecuteSQL'), $passArgs);
      // Take note of the insert_id
      if ( preg_match( '/^\s*(insert|replace)\s/i', $sql ) ) 
        $this->insert_id = $this->GetLastInsertID();

      return $this->GetLastInsertID() ? $this->GetLastInsertID() : $this->Query->rowCount();
    }

    /*
     * Returns the number of rows affected.
     */
    public function GetLastRowCount()
    {
      return $this->Query->rowCount();
    }

    public function GetLastInterpolatedSQL() {
      $keys = array();
      foreach ($this->LastParams as $key => $value) {
        if (is_string($key)) {
          $keys[] = '/'.$key.'/';
        } else {
          $keys[] = '/[?]/';
        }
      }
      return preg_replace($keys, $this->LastParams, $this->GetLastSQL(), 1, $count);
      // $count contains number of keys.
    }
    
  }
