<?php
namespace exface\UI5Facade\Exceptions;

use exface\Core\Exceptions\Actions\ActionExceptionTrait;
use exface\Core\Interfaces\Exceptions\ActionExceptionInterface;

/**
 * Exception thrown if the Fiori exporter cannot deal with an action.
 * 
 * @author Andrej Kabachnik
 *
 */
class UI5ExportUnsupportedActionException extends UI5ExportUnsupportedException implements ActionExceptionInterface
{
    use ActionExceptionTrait;
}