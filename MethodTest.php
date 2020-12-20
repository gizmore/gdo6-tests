<?php
namespace GDO\Tests;

use GDO\Core\GDT;
use GDO\Core\Method;
use GDO\User\GDO_User;
use GDO\DB\GDT_String;
use GDO\Util\Classes;
use GDO\DB\GDT_Int;
use GDO\DB\GDT_Decimal;
use function PHPUnit\Framework\assertTrue;

/**
 * Helper Class to test a gdo method.
 * @author gizmore
 */
final class MethodTest
{
    public static function make()
    {
        return new self();
    }
    
    public $json = false;
    public function json($json=true)
    {
        $this->json = $json;
        return $this;
    }
    
    public $method;
    public function method(Method $method)
    {
        $this->method = $method;
        return $this;
    }
    
    public $parameters = [];
    public function parameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }
    
    public $user;
    public function user(GDO_User $user)
    {
        $this->user = $user;
    }
    
    public function execute($btn='submit')
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        GDO_User::$CURRENT = $this->user ? $this->user : GDO_User::$CURRENT;
        
        $_REQUEST['fmt'] = $_GET['fmt'] = $this->json ? 'json' : 'html';
        
        $_POST['form'] = [];
        $_REQUEST['form'] = [];
        foreach ($this->parameters as $key => $value)
        {
            $_POST['form'][$key] = $value;
            $_REQUEST['form'][$key] = $value;
        }
        
        $_POST[$btn] = $btn;
        $_REQUEST[$btn] = $btn;
        
        $this->method->init();
        
        $response = $this->method->exec();
        
        assertTrue($response->code === 200);
        
        return $response;
    }
    
    ################################
    ### Automatic Method Testing ###
    ################################
    /**
     * Try to plug default values for a method and test it.
     * @param string $moduleName
     * @param string $methodName
     * @param array $parameters
     * @return self
     */
    public function defaultMethod($moduleName, $methodName, $parameters=[], $button='submit')
    {
        $method = method($moduleName, $methodName);
        
        # Plug default params
        foreach ($method->gdoParameterCache() as $name => $gdt)
        {
            if ($gdt->notNull && $gdt->getVar() === null)
            {
                if (!isset($parameters[$name]))
                {
                    $parameters[$name] = $this->plugParam($gdt);
                }
            }
        }
        
        # Exec
        return self::make()->method($method)->parameters($parameters)->execute($button);
    }
    
    /**
     * Try to guess default params for a GDT.
     * @param GDT $gdt
     * @return string
     */
    private function plugParam(GDT $gdt)
    {
        # Select first object
        if (Classes::class_uses_trait($gdt, 'GDO\\DB\\WithObject'))
        {
            return $gdt->gdo->table()->select()->first()->exec()->fetchObject()->getID();
        }
        
        # Title and description
        if ($gdt instanceof GDT_String)
        {
            return "Test String <script>alert(1);</script>";
        }
        
        if ($gdt instanceof GDT_Int)
        {
            return "4"; # chosen by fair avg dice roll.
        }
        
        if ($gdt instanceof GDT_Decimal)
        {
            return "3.14";
        }
    }

}
