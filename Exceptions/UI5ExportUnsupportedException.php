<?php
namespace exface\UI5Facade\Exceptions;

use exface\Core\Exceptions\RuntimeException;

/**
 * Exception thrown if the Fiori exporter does not support some part of the model.
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5ExportUnsupportedException extends RuntimeException
{
    private $errors = [];
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Exceptions\RuntimeException::getDefaultAlias()
     */
    public function getDefaultAlias()
    {
        return '77FTNCB';
    }
    
    public function getErrors() : array
    {
        return array_unique($this->errors);
    }
    
    public function addError(string $message) : UI5ExportUnsupportedException
    {
        $this->errors[] = $message;
        return $this;
    }
    
    public function hasErrors() : bool
    {
        return ! empty($this->errors);
    }
}