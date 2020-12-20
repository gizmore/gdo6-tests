<?php
namespace GDO\Tests;

use GDO\Core\Method;
use GDO\User\GDO_User;

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
        
        $response = $this->method->execute();
        
        assert($response->code === 200);
        
        return $response;
    }
    
}
