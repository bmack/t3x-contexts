<?php
namespace Bmack\Contexts\Context\Type;

/***************************************************************
*  Copyright notice
*
*  (c) 2013 Netresearch GmbH & Co. KG <typo3.org@netresearch.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use Bmack\Contexts\Context\AbstractContext;
use Bmack\Contexts\Service\FrontendControllerService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Matches on a GET parameter with a certain value
 *
 * @author     Christian Weiske <christian.weiske@netresearch.de>
 * @author     Christian Opitz <christian.opitz@netresearch.de>
 * @license    http://opensource.org/licenses/gpl-license GPLv2 or later
 */
class QueryParameterContext extends AbstractContext
{
    /**
     * Check if the context is active now.
     *
     * @param array $arDependencies Array of dependent context objects
     * @return bool True if the context is active, false if not
     * @throws \Exception
     */
    public function match(array $arDependencies = array())
    {
        $param = trim($this->getConfValue('field_name'));
        if ($param === '') {
            throw new \Exception(
                'Parameter name missing from GET Parameter'
                . ' context configuration'
            );
        }
        $value = GeneralUtility::_GET($param);

        if ($value === null) {
            //load from session if no param given
            list($bUseMatch, $bMatch) = $this->getMatchFromSession();
            if ($bUseMatch) {
                return $this->invert($bMatch);
            }
        }

        // Register param on TSFE service for cache and linkVars management
        FrontendControllerService::registerQueryParameter(
            $param, $value, !(bool) $this->use_session
        );

        return $this->invert($this->storeInSession(
            $this->matchParameters($value)
        ));
    }

    /**
     * Checks if the given value is one of the configured allowed values
     *
     * @param string $value Current parameter value
     *
     * @return bool True if the current paramter value is one of the
     *                 configured values
     */
    protected function matchParameters($value)
    {
        $arValues = explode("\n", $this->getConfValue('field_values'));

        //empty value list, so we allow any value
        if (count($arValues) == 1 && $arValues[0] == '') {
            return $value !== '';
        }

        $arValues = array_map('trim', $arValues);
        return in_array($value, $arValues, true);
    }
}
