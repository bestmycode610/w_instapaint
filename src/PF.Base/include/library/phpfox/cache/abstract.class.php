<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

Phpfox::getLibClass('phpfox.cache.interface');

/**
 * Abstract class for cache storage classes. This class is used to work with the cache
 * system and certain methods do not require to work with a specific storage.
 * 
 * Example of deleting static JavaScript & CSS files:
 * <code>
 * Phpfox::getLib('cache')->removeStatic('sample.css');
 * </code>
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author			Raymond Benc
 * @package 		Phpfox
 * @version 		$Id: abstract.class.php 3054 2011-09-09 08:50:14Z Raymond_Benc $
 * @abstract 
 */
abstract class Phpfox_Cache_Abstract implements Phpfox_Cache_Interface
{
	/**
	 * Array of any special params we pass in case we need to do something.
	 *
	 * @var array
	 */
	protected $_aParams = array();	
	
	/**
	 * Array of stats holding the information about the cache files
	 *
	 * @var array
	 */
	protected $_aStats = array();
	
	/**
	 * Load all the custom params in a protected variable array.
	 *
	 * @param array $aParams Optional array of custom params
	 */
	public function __construct($aParams = array())
	{	
		$this->_aParams = $aParams;		
	}
	
	/**
	 * Returns all the static javascript and css files we have saved using the cache system.
	 *
	 * @return array List of cached files within the "file/static/" folder.
	 */
	public function getStatic()
	{
		$aFiles = array();
		if ($hDir = @opendir(PHPFOX_DIR_FILE . 'static' . PHPFOX_DS))
		{
			while ($sFile = readdir($hDir))
			{
				if (!preg_match("/(.*)\.(js|css)/i", $sFile))
				{
					continue;
				}
				
				$aFiles[] = array(
					'id' => md5($sFile),
					'name' => $sFile,
					'size' => filesize(PHPFOX_DIR_FILE . 'static' . PHPFOX_DS . $sFile),
					'date' => filemtime((PHPFOX_DIR_FILE . 'static' . PHPFOX_DS . $sFile)),
					'type' => 'Static'
				);	
			}
		}
		
		return $aFiles;
	}	
	
	/**
	 * Get all the stats about the cache storage system.
	 *
	 * @return array
	 */
	public function getStats()
	{
		return $this->_aStats;
	}
	
	/**
	 * List of all the cache files found within the "file/cache/" folder.
	 *
	 * @return array Array of all the files found within the "file/cache/" folder.
	 */
	public function getAll()
	{
		$aFiles = [];
		
		if ($hDir = @opendir(PHPFOX_DIR_CACHE))
		{			
			while ($sFile = readdir($hDir))
			{
				if ($sFile == '.' 
					|| $sFile == '..' 
					|| $sFile == '.svn'
					|| $sFile == '.htaccess'
					|| $sFile == 'index.html'
					|| $sFile == 'debug.php'					
				)
				{
					continue;
				}	
				
				$aFiles[] = array(
					'id' => md5($sFile),
					'name' => $sFile,
					'size' => filesize(PHPFOX_DIR_CACHE . $sFile),
					'date' => filemtime((PHPFOX_DIR_CACHE . $sFile)),
					'type' => 'File'
				);				
			}
			closedir($hDir);
			
			return $aFiles;
		}		
		
		return array();
	}

    public function setup($params = [])
    {

	}
	
	/**
	 * Removes a static javascript or css file.
	 *
	 * @param string $sFile Name of the static file. If nothing is passed then we delete all the files found within the "file/static/" folder.
	 * @return bool Returns true if we were able to delete the file.
	 */
	public function removeStatic($sFile = null)
	{		
		if ($sFile !== null)
		{
			if (file_exists(PHPFOX_DIR_FILE . 'static' . PHPFOX_DS . $sFile))
			{
				@unlink(PHPFOX_DIR_FILE . 'static' . PHPFOX_DS . $sFile);
			}
			
			return true;
		}
		
		$aFiles = Phpfox_File::instance()->getFiles(PHPFOX_DIR_FILE . 'static' . PHPFOX_DS);
		foreach ($aFiles as $sFile)
		{
			if (!preg_match("/(.*)\.(js|css)/i", $sFile))
			{
				continue;
			}

			@unlink(PHPFOX_DIR_FILE . 'static' . PHPFOX_DS . $sFile);
		}
		
		$aFiles = Phpfox_File::instance()->getFiles(PHPFOX_DIR_FILE . 'gzip' . PHPFOX_DS);
		foreach ($aFiles as $sFile)
		{
			if (!preg_match("/(.*)\.php/i", $sFile))
			{
				continue;
			}

			@unlink(PHPFOX_DIR_FILE . 'gzip' . PHPFOX_DS . $sFile);
		}		

		return true;
	}

	/**
	 * Get all the information of the cache file from the database to check if the file is cached.
	 *	
	 * NOTICE: We are not using this method anymore.
	 *
	 * @deprecated Deprecated since version 2.0.1	 
	 * @param int $iId ID of the cache file.
	 * @return string Data of the cache file.
	 */
	public function getData($iId)
	{
		$oDb = Phpfox_Database::instance();
		$aCache = $oDb->select('cache_id, cache_data')
			->from(Phpfox::getT('cache'))
			->where('cache_id = ' . (int) $iId)
			->execute('getSlaveRow');
			
		if (!isset($aCache['cache_id']))
		{
			return Phpfox_Error::set('Unable to find this cache file.');
		}
			
		$sData = str_replace('<?php defined(\'PHPFOX\') or exit(\'NO DICE!\'); ?>', '', $aCache['cache_data']);
		$sData = preg_replace("/<\?php \/\* Cached:(.*?) \*\/ \?>/i", "", $sData);
		$sData = trim($sData);
		
		return $sData;
	}

	
	/**
	 * Lock the cache system. When the cache system is locked you will not be able to cache anything new until it is unlocked.
	 *
	 * @see self::unlock()
	 */
	public function lock()
	{
		@touch(PHPFOX_DIR_CACHE . 'cache.lock');
	}
	
	/**
	 * Unlock the cache system.
	 *
	 */
	public function unlock()
	{
		if (file_exists(PHPFOX_DIR_CACHE . 'cache.lock'))
		{
			unlink(PHPFOX_DIR_CACHE . 'cache.lock');
		}
	}
	
	/**
	 * Get a params value in case we pass any information to this class when it was first constructed.
	 *
	 * @param string $sParam Name of the param
	 * @return mixed Returns the params value if it exists, if not we null it.
	 */
	protected function _getParam($sParam)
	{
		return (isset($this->_aParams[$sParam]) ? $this->_aParams[$sParam] : null);
	}

    /**
     * @param string $sGroupName
     * @param string $sCacheKey
     */
	public function group($sGroupName, $sCacheKey)
    {
        $sCacheKey = base64_encode($sCacheKey);
        $sCacheId = $this->set('cache_group_' . $sGroupName);
        $aListKeys = $this->get($sCacheId);
        if ($aListKeys === false) {
            $aListKeys = [$sCacheKey];
            $this->save($sCacheId, $aListKeys);
        } elseif (is_array($aListKeys) && !in_array($sCacheKey, $aListKeys)) {
            $aListKeys[] = $sCacheKey;
            $this->save($sCacheId, $aListKeys);
        }
    }

    /**
     * @param string|array $aGroupName
     */
    public function removeGroup($aGroupName)
    {
        if (!is_array($aGroupName)) {
            $aGroupName = [$aGroupName];
        }
        foreach ($aGroupName as $sGroupName) {
            $sCacheId = $this->set('cache_group_' . $sGroupName);
            $aListKeys = $this->get($sCacheId);
            if (is_array($aListKeys)) {
                foreach ($aListKeys as $sKey) {
                    $newKey = $this->set(base64_decode($sKey));
                    $this->remove($newKey);
                }
            }
            $this->remove($sCacheId);
        }
    }

}