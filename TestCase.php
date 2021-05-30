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
use GDO\User\GDO_UserPermission;
use GDO\Core\Application;
use GDO\File\FileUtil;
use GDO\Language\Trans;

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
        # Increase Time
        Application::updateTime();
        
        # Increase IP
        GDT_IP::$CURRENT = $this->nextIP();
        
        # Clear input
        $_REQUEST = $_POST = $_GET = $_FILES = [];
        
        # Clear code
        GDT_Response::$CODE = 200;
        
        # Clear navs
        $p = GDT_Page::$INSTANCE;
        $p->reset();
        
        # Set gizmore user
        if (Module_User::instance()->isPersisted())
        {
            $user = count(MethodTest::$USERS) ? MethodTest::$USERS[0] : GDO_User::system();
            if ($user)
            {
                $this->user($user);
                if (!$user->isSystem())
                {
                    $this->restoreUserPermissions($user);
                }
            }
        }
    }
    
    /**
     * Restore gizmore because auto coverage messes with him a lot.
     * @param GDO_User $user
     */
    protected function restoreUserPermissions(GDO_User $user)
    {
        if (count(MethodTest::$USERS))
        {
            if ($user->getID() === MethodTest::$USERS[0]->getID())
            {
                $table = GDO_UserPermission::table();
                $table->grant($user, 'admin');
                $table->grant($user, 'staff');
                $table->grant($user, 'cronjob');
                $user->changedPermissions();
            }
        }
    }
    
    protected function session(GDO_User $user)
    {
        GDO_Session::$INSTANCE = $session = GDO_Session::blank();
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
        Trans::setISO($user->getLangISO());
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
    
    protected function fakeFileUpload($fieldName, $fileName, $path)
    {
        $dest = Module_Tests::instance()->tempPath($fileName);
        copy($path, $dest);
        $_FILES[$fieldName] = [
            'name' => $fileName,
            'type' => FileUtil::mimetype($dest),
            'tmp_name' => $dest,
            'error' => 0,
            'size' => filesize($dest),
        ];
    }
    
}
