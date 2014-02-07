<?php
/**
 * @package ImpressPages

 *
 */
namespace Ip\Internal\Repository;


/**
 * 
 * Centralized repository to store files. Often the same image needs to be used by many 
 * modules / widgets. This class handles these dependences. Use this module to add new files to global
 * files repository. Bind new modules to already existing files. When the file is not bind to any module,
 * it is automatically removed. So bind to existing files, undbind from them and don't whorry if some other
 * modules uses the same files. This class will take care.
 *
 */
class Model{


    protected static $instance;

    protected function __construct()
    {

    }

    protected function __clone()
    {

    }

    /**
     * Get singleton instance
     * @return Model
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new Model();
        }

        return self::$instance;
    }

    
    public static function bindFile($file, $module, $instanceId) {
        $row = array(
            'filename' => $file,
            'module' => $module,
            'instanceId' => $instanceId,
            'date' => time()
        );
        ipDb()->insert('repository_file', $row);
    }

    public static function unbindFile($file, $module, $instanceId) {
        $condition = array(
            'fileName' => $file,
            'module' => $module,
            'instanceId' => $instanceId
        );

        $sql= 'DELETE FROM ' . ipTable('repository_file') . '
                WHERE filename = :fileName
                AND module = :module
                AND instanceId = :instanceId
                LIMIT 1'; // it is important to delete only one record

        ipDb()->execute($sql, $condition);

        $usages = self::whoUsesFile($file);
        if (empty($usages)) {
            $reflectionModel = ReflectionModel::instance();
            $reflectionModel->removeReflections($file);
        }

    }
    
    public static function whoUsesFile($file)
    {
        return ipDb()->selectAll('repository_file', '*', array('fileName' => $file));
    }
    
    /**
     * Find all files bind to particular module
     */
    public function findFiles($module, $instanceId = null)
    {
        $where = array (
            'module' => $module
        );

        if ($instanceId !== null) {
            $where['instanceId'] = $instanceId;
        }

        return ipDb()->selectAll('repository_file', '*', $where);
    }
    
    
}
