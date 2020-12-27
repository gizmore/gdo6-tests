<?php
namespace GDO\Tests;

use GDO\Core\GDT_Response;
use GDO\UI\GDT_Page;
use GDO\User\GDO_User;
use function PHPUnit\Framework\assertEquals;
use GDO\Session\GDO_Session;
use GDO\Core\Method;
use GDO\Net\GDT_IP;
use GDO\Core\Website;
use GDO\User\Module_User;

/**
 * A GDO test case knows a few helper functions and sets up a clean response environment.
 * 
 * @author gizmore
 * @version 6.10
 * @since 6.10
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    #################
    ### Init test ###
    #################
    private $ipc = 0;
    private $ipd = 1;
    private function nextIP()
    {
        $this->ipd++;
        if ($this->ipd>255)
        {
            $this->ipd = 1;
            $this->ipc++;
        }
        $ip = sprintf('127.0.%d.%d', $this->ipc, $this->ipd);
        return $ip;
    }
    
    protected function setUp(): void
    {
        # Increase IP
        GDT_IP::$CURRENT = $this->nextIP();
        
        # Clear input
        $_REQUEST = $_POST = $_GET = [];
        
        # Clear code
        GDT_Response::$CODE = 200;
        
        # Clear navs
        $p = GDT_Page::$INSTANCE;
        $p->topNav ? $p->topNav->clearFields() : 0;
        $p->leftNav ? $p->leftNav->clearFields() : 0;
        $p->rightNav ? $p->rightNav->clearFields() : 0;
        $p->bottomNav ? $p->bottomNav->clearFields() : 0;
        $p->topTabs ? $p->topTabs->clearFields() : 0;
        Website::$TOP_RESPONSE = null;
        
        # Set gizmore user
        if (Module_User::instance()->isPersisted())
        {
            $user = count(MethodTest::$USERS) ? MethodTest::$USERS[0] : GDO_User::system();
            $this->user($user);
        }
    }
    
    protected function session(GDO_User $user)
    {
        GDO_Session::$INSTANCE = $session = new GDO_Session();
        $session->setVar('sess_user', $user->getID());
    }

    ###################
    ### User switch ###
    ###################
    protected function ghost() { return GDO_User::ghost(); }
    protected function system() { return GDO_User::system(); }
    protected function gizmore() { return MethodTest::$USERS[0]; }
    protected function peter() { return MethodTest::$USERS[1]; }
    protected function monica() { return MethodTest::$USERS[2]; }
    protected function gaston() { return MethodTest::$USERS[3]; }
    
    protected function userGhost() { return $this->user(GDO_User::ghost()); } # ID 0
    protected function userSystem() { return $this->user(GDO_User::system()); } # ID 1 
    protected function userGizmore() { return $this->user($this->gizmore()); } # Admin 
    protected function userPeter() { return $this->user($this->peter()); } # Staff
    protected function userMonica() { return $this->user($this->monica()); } # Member
    protected function userGaston() { return $this->user($this->gaston()); } # Guest
    
    protected function user(GDO_User $user)
    {
        $this->session($user);
        return GDO_User::setCurrent($user);
    }
    
    ###################
    ### Assert code ###
    ###################
    protected function assert200($message) { $this->assertCode(200, $message); }
    protected function assert405($message) { $this->assertCode(405, $message); }
    protected function assertCode($code, $message)
    {
        assertEquals($code, GDT_Response::$CODE, $message);
    }
    
    ###################
    ### Call method ###
    ###################
    protected function callMethod(Method $method, array $parameters=null, array $getParameters=null)
    {
        $r = MethodTest::make()->method($method)->user(GDO_User::current())->parameters($parameters)->getParameters($getParameters)->execute();
        $this->assert200(sprintf('Test if %s::%s response code is 200.', 
            $method->getModuleName(), $method->getMethodName()));
        return $r;
    }
    
}