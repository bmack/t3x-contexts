<?php
require_once __DIR__ . '../../../../../Classes/Context/Abstract.php';
require_once __DIR__ . '../../../../../Classes/Context/Type/GetParam.php';
require_once __DIR__ . '../../../../../Classes/Context/Type/GetParam/TsfeService.php';


if (!class_exists('t3lib_div')
    && class_exists('TYPO3\CMS\Core\Utility\GeneralUtility')
) {
    /**
     * Class t3lib_div
     * @internal
     */
    class t3lib_div extends TYPO3\CMS\Core\Utility\GeneralUtility
    {
    }
}

class Tx_Contexts_Context_Type_GetParamTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        error_reporting(error_reporting() & ~E_NOTICE);

        foreach ($_GET as $key => $dummy) {
            unset($_GET[$key]);
        }
    }

    public function testMatchParameterMissing()
    {
        $getm = $this->getMock(
            'Tx_Contexts_Context_Type_GetParam',
            array('getConfValue')
        );
        $getm->setUseSession(false);
        $retValMap = array(
            array('field_name'       , null, 'sDEF', 'lDEF', 'vDEF', 'affID'),
            array('field_values'     , null, 'sDEF', 'lDEF', 'vDEF', '123'),
        );

        $getm->expects($this->any())
            ->method('getConfValue')
            ->will($this->returnValueMap($retValMap));

        $this->assertFalse($getm->match(), 'No parameter means no match');
    }

    public function testMatchParameterNoValue()
    {
        $_GET['affID'] = '';

        $getm = $this->getMock(
            'Tx_Contexts_Context_Type_GetParam',
            array('getConfValue')
        );
        $getm->setUseSession(false);
        $retValMap = array(
            array('field_name'       , null, 'sDEF', 'lDEF', 'vDEF', 'affID'),
            array('field_values'     , null, 'sDEF', 'lDEF', 'vDEF', '123'),
        );

        $getm->expects($this->any())
            ->method('getConfValue')
            ->will($this->returnValueMap($retValMap));

        $this->assertFalse($getm->match(), 'No value means no match');
    }

    public function testMatchParameterCorrectValue()
    {
        $_GET['affID'] = 123;

        $getm = $this->getMock(
            'Tx_Contexts_Context_Type_GetParam',
            array('getConfValue')
        );
        $getm->setUseSession(false);
        $retValMap = array(
            array('field_name'       , null, 'sDEF', 'lDEF', 'vDEF', 'affID'),
            array('field_values'     , null, 'sDEF', 'lDEF', 'vDEF', '123'),
        );

        $getm->expects($this->any())
            ->method('getConfValue')
            ->will($this->returnValueMap($retValMap));

        $this->assertTrue($getm->match(), 'Correct value');
    }

    public function testMatchParameterCorrectValueOfMany()
    {
        $_GET['affID'] = 125;

        $getm = $this->getMock(
            'Tx_Contexts_Context_Type_GetParam',
            array('getConfValue')
        );
        $getm->setUseSession(false);
        $retValMap = array(
            array('field_name'       , null, 'sDEF', 'lDEF', 'vDEF', 'affID'),
            array(
                'field_values'     , null, 'sDEF', 'lDEF', 'vDEF',
                "123\n124\n125\n"
            ),
        );

        $getm->expects($this->any())
            ->method('getConfValue')
            ->will($this->returnValueMap($retValMap));

        $this->assertTrue($getm->match(), 'Correct value');
    }

    public function testMatchParameterWrongValueOfMany()
    {
        $_GET['affID'] = 124125;

        $getm = $this->getMock(
            'Tx_Contexts_Context_Type_GetParam',
            array('getConfValue')
        );
        $getm->setUseSession(false);
        $retValMap = array(
            array('field_name'       , null, 'sDEF', 'lDEF', 'vDEF', 'affID'),
            array(
                'field_values'     , null, 'sDEF', 'lDEF', 'vDEF',
                "123\n124\n125\n"
            ),
        );

        $getm->expects($this->any())
            ->method('getConfValue')
            ->will($this->returnValueMap($retValMap));

        $this->assertFalse($getm->match(), 'value is not allowed');
    }

    public function testMatchParameterAnyValue()
    {
        $_GET['bla'] = 'aslkfj';

        $getm = $this->getMock(
            'Tx_Contexts_Context_Type_GetParam',
            array('getConfValue')
        );
        $getm->setUseSession(false);
        $retValMap = array(
            array('field_name'       , null, 'sDEF', 'lDEF', 'vDEF', 'affID'),
            array('field_values'     , null, 'sDEF', 'lDEF', 'vDEF', ''),
        );

        $getm->expects($this->any())
            ->method('getConfValue')
            ->will($this->returnValueMap($retValMap));

        $this->assertTrue($getm->match(), 'Any value is correct');
    }

    public function testMatchParameterAnyValueWrongName()
    {
        $_GET['affID'] = 'aslkfj';

        $getm = $this->getMock(
            'Tx_Contexts_Context_Type_GetParam',
            array('getConfValue')
        );
        $getm->setUseSession(false);
        $retValMap = array(
            array('field_name'       , null, 'sDEF', 'lDEF', 'vDEF', 'affID'),
            array('field_values'     , null, 'sDEF', 'lDEF', 'vDEF', ''),
        );

        $getm->expects($this->any())
            ->method('getConfValue')
            ->will($this->returnValueMap($retValMap));

        $this->assertTrue($getm->match(), 'Any value is correct');
    }

    /**
     * @expectedException Exception
     */
    public function testMatchUnconfiguredNoParameter()
    {
        $getm = $this->getMock(
            'Tx_Contexts_Context_Type_GetParam',
            array('getConfValue')
        );
        $getm->setUseSession(false);
        $retValMap = array(
            array('field_name'       , null, 'sDEF', 'lDEF', 'vDEF', ''),
            array('field_values'     , null, 'sDEF', 'lDEF', 'vDEF', ''),
        );

        $getm->expects($this->any())
            ->method('getConfValue')
            ->will($this->returnValueMap($retValMap));

        $getm->match();
    }

}
?>
