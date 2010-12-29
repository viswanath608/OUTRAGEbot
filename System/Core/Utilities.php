<?php
/**
 *	OUTRAGEbot development
 */


/**
 *	Print a new line
 */
function println($sString)
{
	echo $sString.PHP_EOL;
}


class CoreUtilities
{
	/**
	 *	Parsing and modifying the plugin files.
	 *	Caution: Messy code! Needs improving!
	 */
	static function getPluginIdentifier($sPluginName)
	{
		$sPluginLocation = ROOT."/Plugins/{$sPluginName}/Default.php";
		
		if(!file_exists($sPluginLocation))
		{
			return false;
		}
		
		if(isset(Core::$aPluginCache[$sPluginName]))
		{
			$aPluginCache = Core::$aPluginCache[$sPluginName];
			
			clearstatcache();
			
			if($aPluginCache['modifytime'] >= filemtime($sPluginLocation))
			{
				return $aPluginCache['identifier'];
			}
		}

		$sIdentifier = substr($sPluginName, 0, 8).'_'.substr(sha1(microtime()."-".uniqid()), 2, 10);
		$sClass = file_get_contents($sPluginLocation); // Ouch, this has gotta hurt.

		if(!preg_match("/class[\s]+?".$sPluginName."[\s]+?extends[\s]+?Plugins[\s]+?{/", $sClass))
		{
			return false;
		}

		$sClass = preg_replace("/(class[\s]+?)".$sPluginName."([\s]+?extends[\s]+?Plugins[\s]+?{)/", "\\1".$sIdentifier."\\2", $sClass);
		$sFile = tempnam(dirname($sPluginLocation), "nat"); // Stops the __FILE__ bugs.

		file_put_contents($sFile, $sClass);				
		unset($sClass); // Weight off the shoulders anyone?

		include $sFile;
		unlink($sFile);

		Core::$aPluginCache[$sPluginName] = array
		(
			'modifytime' => filemtime($sPluginLocation),
			'identifier' => $sIdentifier,
		);

		return $sIdentifier;
	}
	
	
	/**
	 *	Internal: Function to get the date since something.
	 */
	static function Duration($iDate1, $iDate2 = null)
	{
		if(empty($iDate2))
		{
			$iDate2 = time();
		}

   		$aDifferences = array
		(
			'seconds' => 0,
			'minutes'=> 0,
			'hours' => 0,
			'days' => 0,
			'weeks' => 0,
			
			'totalSeconds' => 0,
			'totalMinutes' => 0,
			'totalHours' => 0,
			'totalDays' => 0,
			'totalWeeks' => 0,
		);

		if($iDate2 > $iDate1)
		{
			$iTemp = $iDate2 - $iDate1;
		}
		else
		{
			$iTemp = $iDate1 - $iDate2;
		}

		$iSeconds = $iTemp;

		$aDifferences['weeks'] = floor($iTemp / 604800);
		$iTemp -= $aDifferences['weeks'] * 604800;

		$aDifferences['days'] = floor($iTemp / 86400);
		$iTemp -= $aDifferences['days'] * 86400;

		$aDifferences['hours'] = floor($iTemp / 3600);
		$iTemp -= $aDifferences['hours'] * 3600;

		$aDifferences['minutes'] = floor($iTemp / 60);
		$iTemp -= $aDifferences['minutes'] * 60;

		$aDifferences['seconds'] = $iTemp;
		
		$aDifferences['totalWeeks'] = floor($iSeconds / 604800);
		$aDifferences['totalDays'] = floor($iSeconds / 86400);
		$aDifferences['totalHours'] = floor($iSeconds / 3600);
		$aDifferences['totalMinutes'] = floor($iSeconds / 60);
		$aDifferences['totalSeconds'] = $iSeconds;

		return $aDifferences;
	}
	
	
	/**
	 *	Replaces the character with a letter in a mode string.
	 */
	static function modeCharToLetter($sModeString)
	{
		return str_replace
		(
			array
			(
				'+',
				'%',
				'@',
				'&',
				'~',
			),
			
			array
			(
				'v',
				'h',
				'o',
				'a',
				'q',
			),
			
			$sModeString
		);
	}
	
	
	/**
	 *	Replaces the letter with a character in a mode string.
	 */
	static function modeLetterToChar($sModeString)
	{
		return str_replace
		(
			array
			(
				'v',
				'h',
				'o',
				'a',
				'q',
			),
			
			array
			(
				'+',
				'%',
				'@',
				'&',
				'~',
			),
			
			$sModeString
		);
	}
}