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
 * @copyright   Copyright (C) 2010 Robert Poyntz
 * @author      Robert Poyntz <rob@digitalfinery.com.au>
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @package     EasyPDO
 * @version     0.1.6
 */
  require_once dirname(__FILE__) . '/easypdo.php';

  class EasyPDO_SQLite extends EasyPDO
  {
    protected function __construct($connectionString, $username = null, $password = null)
    {
      define('ERROR_DUPLICATE_KEY', 23000);
      parent::__construct($connectionString, $username, $password);
    }

    protected function GetLastInsertID()
    {
      /*
       * Not available in SQLite. To obtain the last insert id, insert queries must be performed within a transaction, eg:
       * $db->StartTransaction();
       * $db->ExecuteSQL('INSERT INTO table (fieldlist) VALUES (values)');
       * $lastInsertID = $db->FetchValue('SELECT last_insert_rowid() as last_insert_rowid');
       * $db->CommitTransaction();
       */

      return null;
    }

    public static function Instance($filename = null)
    {
      if (!isset(EasyPDO::$Instance))
      {
        if (!$filename || !file_exists($filename) || is_dir($filename))
          $connectionString = 'sqlite::memory';
        else
          $connectionString = 'sqlite:' . $filename;
        EasyPDO::$Instance = new EasyPDO_SQLite($connectionString);
      }
      return EasyPDO::$Instance;
    }
  }


