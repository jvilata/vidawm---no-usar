<?php
/**
* Copyright (c) Microsoft Corporation.  All Rights Reserved.  Licensed under the MIT License.  See License in the project root for license information.
* 
* WorkbookWorksheetProtection File
* PHP version 7
*
* @category  Library
* @package   Microsoft.Graph
* @copyright © Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @version   GIT: 1.4.0
* @link      https://graph.microsoft.io/
*/
namespace Microsoft\Graph\Model;

/**
* WorkbookWorksheetProtection class
*
* @category  Model
* @package   Microsoft.Graph
* @copyright © Microsoft Corporation. All rights reserved.
* @license   https://opensource.org/licenses/MIT MIT License
* @version   Release: 1.4.0
* @link      https://graph.microsoft.io/
*/
class WorkbookWorksheetProtection extends Entity
{
    /**
    * Gets the options
    *
    * @return WorkbookWorksheetProtectionOptions The options
    */
    public function getOptions()
    {
        if (array_key_exists("options", $this->_propDict)) {
            if (is_a($this->_propDict["options"], "Microsoft\Graph\Model\WorkbookWorksheetProtectionOptions")) {
                return $this->_propDict["options"];
            } else {
                $this->_propDict["options"] = new WorkbookWorksheetProtectionOptions($this->_propDict["options"]);
                return $this->_propDict["options"];
            }
        }
        return null;
    }
    
    /**
    * Sets the options
    *
    * @param WorkbookWorksheetProtectionOptions $val The options
    *
    * @return WorkbookWorksheetProtection
    */
    public function setOptions($val)
    {
        $this->_propDict["options"] = $val;
        return $this;
    }
    
    /**
    * Gets the protected
    *
    * @return bool The protected
    */
    public function getProtected()
    {
        if (array_key_exists("protected", $this->_propDict)) {
            return $this->_propDict["protected"];
        } else {
            return null;
        }
    }
    
    /**
    * Sets the protected
    *
    * @param bool $val The protected
    *
    * @return WorkbookWorksheetProtection
    */
    public function setProtected($val)
    {
        $this->_propDict["protected"] = boolval($val);
        return $this;
    }
    
}