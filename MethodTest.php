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
use function PHPUnit\Framework\assertEquals;

/**
 * Helper Class to test a gdo method.
 * @author gizmore
 */
final class MethodTest
{
    # 0 - gizmore (admin)
    # 1 - Peter (staff)
    # 2 - Monica (member)
    # 3 - Gaston (guest)
    public static $USERS = []; # store some users here for testing.
    
    public static function make()
    {
        return new self();
    }
    
    ###############
    ### Options ###
    ###############
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
        return $this;
    }
    
    ############
    ### Exec ###
    ############
    /**
     * Execute the settings. Copy the parameters into request array. 
     * @param string $btn
     * @return \GDO\Core\GDT_Response
     */
    public function execute($btn='submit')
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        
        # Set user if desired. Default is admin gizmore.
        if ($this->user) GDO_User::setCurrent($this->user);
        
        # Set options
        $_REQUEST['fmt'] = $_GET['fmt'] = $this->json ? 'json' : 'html';
        $_REQUEST['ajax'] = $_GET['ajax'] = $this->json ? '1' : '0';

        # Copy params
        $_POST['form'] = [];
        $_REQUEST['form'] = [];
        $_POST['form'][$btn] = $btn;
        $_REQUEST['form'][$btn] = $btn;
        foreach ($this->parameters as $key => $value)
        {
            $_POST['form'][$key] = $value;
            $_REQUEST['form'][$key] = $value;
        }
        
        # Exec
        $this->method->init();
        echo "Executing Method {$this->method->getModuleName()}::{$this->method->getMethodName()}\n"; ob_flush();
        $response = $this->method->exec();
        ob_flush();
        
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
    public function plugParam(GDT $gdt)
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
