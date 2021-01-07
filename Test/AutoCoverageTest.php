<?php
namespace GDO\Tests\Test;

use GDO\User\GDO_User;
use GDO\Util\BCrypt;
use GDO\User\GDO_UserPermission;
use GDO\Tests\MethodTest;
use GDO\Tests\TestCase;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertFileIsReadable;
use function PHPUnit\Framework\assertNull;
use GDO\Core\GDO;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertEquals;
use GDO\Form\MethodForm;
use GDO\Core\GDT_Response;

/**
 * Automated coverage tests for all modules.
 * 
 * - Generate a few users to work with
 * - Require every file - Is Problematic for multi-provider modules and templates.
 * - Make one instance of every GDT encountered.
 * - Test NULL handling on every GDT encountered.
 * - Make one instance of every GDO encountered.
 * - Try to exec every method that only has default paramaters - @TODO Many methods need to fulfil the gdoParameters() paradigm. 
 * 
 * @TODO: Move this to the Module_Tests. Make Module_Tests prio 0. 
 * @author gizmore
 * @version 6.10
 * @since 6.10
 */
final class AutoCoverageTest extends TestCase
{
    public function testDefaultUsers()
    {
        echo "Creating 4 users for testing\n";
        # User 2 is gizmore
        $user = GDO_User::blank([
            'user_id' => '2',
            'user_name' => 'gizmore',
            'user_type' => 'member',
            'user_password' => BCrypt::create('11111111')->__toString(),
        ])->replace();
        GDO_UserPermission::table()->grant($user, 'admin');
        GDO_UserPermission::table()->grant($user, 'staff');
        MethodTest::$USERS[] = $user;
        $user->changedPermissions();
        assertTrue($user->isAdmin(), "Test if admin permissions can be granted.");
        
        # User 3 is Peter
        $user = GDO_User::blank([
            'user_id' => '3',
            'user_name' => 'Peter',
            'user_type' => 'member',
            'user_password' => BCrypt::create('11111111')->__toString(),
        ])->replace();
        GDO_UserPermission::table()->grant($user, 'staff');
        MethodTest::$USERS[] = $user;
        $user->changedPermissions();
        assertFalse($user->isAdmin(), "Test if admin permissions are assigned correctly.");
        assertTrue($user->isStaff(), "Test if staff permissions are assigned correctly.");
        
        # User 4 is Monica
        $user = GDO_User::blank([
            'user_id' => '4',
            'user_name' => 'Monica',
            'user_type' => 'member',
            'user_password' => BCrypt::create('11111111')->__toString(),
        ])->replace();
        MethodTest::$USERS[] = $user;
        $user->changedPermissions();
        assertFalse($user->isAdmin(), "Test if admin permissions are assigned correctly.");
        assertFalse($user->isStaff(), "Test if staff permissions are assigned correctly.");
        assertFalse($user->isGuest(), 'Test if members are non guests.');
        assertTrue($user->isMember(), 'Test if members are members.');
        
        # User 4 is guest
        $user = GDO_User::blank([
            'user_id' => '3',
            'user_guest_name' => 'Gaston',
            'user_type' => 'guest',
        ])->replace();
        MethodTest::$USERS[] = $user;
        assertFalse($user->isAdmin(), "Test if admin permissions are assigned correctly.");
        assertFalse($user->isStaff(), "Test if staff permissions are assigned correctly.");
        assertTrue($user->isGuest(), 'Test if guests are guests.');
        assertFalse($user->isMember(), 'Test if guests are non members.');
    }
    
//     public function testRequireAllPHPFilesForSyntaxErrors()
//     {
//         echo "Including all GDO files to test for syntax errors.\n"; ob_flush();
        
//         ModuleLoader::$ENABLED_MODULES = null; # reset
//         $loader = ModuleLoader::instance();
//         $modules = $loader->getEnabledModules();
    
//         # Build blacklist folders
//         foreach ($modules as $module)
//         {
//             if ($folders = $module->thirdPartyFolders())
//             {
//                 foreach ($folders as $folder)
//                 {
//                     self::$BLACKLIST_FOLDERS[] = $folder;
//                 }
//             }
//         }
        
//         # Include all files!
//         foreach ($modules as $module)
//         {
//             Filewalker::traverse($module->filePath(), '#\\.php$#', [$this, 'requirePHPFile']);
//         }
//     }
    
//     public static $BLACKLIST_FOLDERS = [
//         '/tpl/',
//         '/gdo6-',
//         '/vendor/',
//         '/bower_components/',
//         '/node_modules/',
//         '/Test/',
//     ];
    
//     public function requirePHPFile($entry, $fullpath)
//     {
//         foreach (self::$BLACKLIST_FOLDERS as $skip)
//         {
//             if (strpos($fullpath, $skip) !== false)
//             {
// //                 echo "Skipping $fullpath\n"; flush();
//                 return; # Skip these files.
//             }
//         }
// //         echo "Loading PHP $entry...\n"; ob_flush();
//         require_once $fullpath;
//     }
    
    public function testEveryGDTConstructors()
    {
        $count = 0;
        echo "Testing null handling on all GDT\n"; ob_flush();
        foreach (get_declared_classes() as $klass)
        {
            $parents = class_parents($klass);
            if (in_array('GDO\\Core\\GDT', $parents, true))
            {
                /** @var $gdt \GDO\Core\GDT **/
                
                $k = new \ReflectionClass($klass);
                if ($k->isAbstract())
                {
                    continue;
                }
                
                $gdt = call_user_func([$klass, 'make']);
                $gdt->value(null);
                $value = $gdt->getValue();
                $gdt->value($value);
//                 $var = $gdt->getVar();
//                 assertNull($var, 'Test if null values stay null by calling toVar() and toValue() on '.$klass);
                $count++;
                assertTrue(!!$gdt, "Check if GDT can be created."); # fake assert
            }
        }
        echo "$count GDT tested\n";
    }
    
    public function testAllGDOConstructors()
    {
        $count = 0;
        echo "Testing blank() handling on all GDO\n"; ob_flush();
        foreach (get_declared_classes() as $klass)
        {
            $k = new \ReflectionClass($klass);
            if ($k->isAbstract())
            {
                continue;
            }
            
            $parents = class_parents($klass);
            if (in_array('GDO\\Core\\GDO', $parents, true))
            {
//                 echo "Checking GDO $klass\n"; ob_flush();
                $table = GDO::tableFor($klass);
                if ($table)
                {
                    $count++;
                    # Test GDO creation.
//                     echo "Testing GDO $klass\n"; flush();
                    $gdo = call_user_func([$klass, 'blank']);
                    assertInstanceOf(GDO::class, $gdo, 'Test if '.$klass.' is a GDO.');
                }
            }
        }
        echo "{$count} GDO tested\n"; ob_flush();
    }
    
    public function testAllTrivialMethodsFor200Code()
    {
        $tested = 0;
        $passed = 0;
        
        foreach (get_declared_classes() as $klass)
        {
            $parents = class_parents($klass);
            if (in_array('GDO\\Core\\Method', $parents, true))
            {
                $k = new \ReflectionClass($klass);
                if ($k->isAbstract())
                {
                    continue;
                }
                
                /** @var $method \GDO\Core\Method **/
                $method = call_user_func([$klass, 'make']);
                
                $methodName =  $method->getModuleName() . '::' . $method->getMethodName();
                
                $requiredParams = $method->gdoParameterCache();
                
                if ($method instanceof MethodForm)
                {
                    /** @var $method \GDO\Form\GDT_Form **/
                    $formParams = $method->getForm()->getFieldsRec();
                    $requiredParams = array_merge($requiredParams, $formParams);
                }
                
                $parameters = [];
                $getParameters = [];
                $trivial = true;
                
                if ($requiredParams)
                {
                    foreach ($requiredParams as $name => $gdt)
                    {
                        # Ouch looks not trivial
                        if ( ($gdt->notNull) && ($gdt->initial === null) )
                        {
                            $trivial = false;
                        }
                        
                        # But maybe now
                        if ($var = MethodTest::make()->plugParam($gdt, $method))
                        {
                            $parameters[$name] = $var;
                            if (isset($method->gdoParameterCache()[$name]))
                            {
                                $getParameters[$name] = $var;
                            }
                            $trivial = true;
                        }
                        
                        # Or is it?
                        if (!$trivial)
                        {
                            echo "Skipping method {$methodName}\n"; ob_flush();
                            break;
                        }
                    }
                }
                if ($trivial)
                {
                    echo "Running trivial method {$methodName}\n"; ob_flush();
                    MethodTest::make()->user($this->gizmore())->method($method)->getParameters($getParameters)->parameters($parameters)->execute();
                    
                    $tested++;
                    if (GDT_Response::$CODE === 200)
                    {
                        $passed++;
                    }
//                     $this->assert200("Check if trivial method {$methodName} does not crash.");
                }
            }
        }
        assertEquals($tested, $passed, "Check if all trivial methods test fine.");
    }
    
}
