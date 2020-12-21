<?php
namespace GDO\Tests;

use GDO\Core\GDT_Response;
use GDO\UI\GDT_Page;
use GDO\User\GDO_User;
use function PHPUnit\Framework\assertEquals;

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
    protected function setUp(): void
    {
        # Clear code
        GDT_Response::$CODE = 200;
        
        # Clear navs
        $p = GDT_Page::$INSTANCE;
        $p->topNav ? $p->topNav->clearFields() : 0;
        $p->leftNav ? $p->leftNav->clearFields() : 0;
        $p->rightNav ? $p->rightNav->clearFields() : 0;
        $p->bottomNav ? $p->bottomNav->clearFields() : 0;
        $p->topTabs ? $p->topTabs->clearFields() : 0;
        
        # Set gizmore user
        $user = count(MethodTest::$USERS) ? MethodTest::$USERS[0] : GDO_User::system();
        $this->user($user);
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
    
    protected function userGhost() { $this->user(GDO_User::ghost()); } # ID 0
    protected function userSystem() { $this->user(GDO_User::system()); } # ID 1 
    protected function userGizmore() { $this->user($this->gizmore()); } # Admin 
    protected function userPeter() { $this->user($this->peter()); } # Staff
    protected function userMonica() { $this->user($this->monica()); } # Member
    protected function userGaston() { $this->user($this->gaston()); } # Guest
    
    protected function user(GDO_User $user) { GDO_User::setCurrent($user); }
    
    ###################
    ### Assert code ###
    ###################
    protected function assert200($message) { $this->assertCode(200, $message); }
    protected function assert405($message) { $this->assertCode(405, $message); }
    protected function assertCode($code, $message)
    {
        assertEquals($code, GDT_Response::$CODE, $message);
    }
    
}
