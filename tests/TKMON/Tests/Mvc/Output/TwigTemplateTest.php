<?php

namespace TKMON\Tests\Mvc\Output;

class TwigTemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testTemplateContent1()
    {
        $d = DIRECTORY_SEPARATOR;
        $dataDir = dirname(dirname(dirname(dirname(__FILE__)))). $d. 'Data';
        $templateDir = $dataDir. $d. 'Template';

        $env = new \Twig_Environment(new \Twig_Loader_Filesystem($templateDir));

        $output = new \TKMON\Mvc\Output\TwigTemplate($env);
        $output->setTemplateName('test.html');
        $output['content'] = 'oka';

        $this->assertEquals('oka', $output->toString());
    }

    public function testTemplateContent2()
    {
        $d = DIRECTORY_SEPARATOR;
        $dataDir = dirname(dirname(dirname(dirname(__FILE__)))). $d. 'Data';
        $templateDir = $dataDir. $d. 'Template';

        $env = new \Twig_Environment(new \Twig_Loader_Filesystem($templateDir));

        $output = new \TKMON\Mvc\Output\TwigTemplate($env, 'test.html');
        $output['content'] = 'oka';

        $this->assertEquals('test.html', $output->getTemplateName());

        $this->assertEquals('oka', $output->toString());
        $this->assertEquals('oka', (string)$output);

        $output->setTwigEnvironment($env);
        $this->assertInstanceOf('\Twig_Environment', $output->getTwigEnvironment());

        $this->assertEquals(array('content' => 'oka'), $output->getData());
    }
}
