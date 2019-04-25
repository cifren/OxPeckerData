<?php

namespace Cifren\OxPeckerDataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * 	Class for extend Command for handle errors.
 */
abstract class AdvancedCommand extends ContainerAwareCommand
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    protected $stopWatch;

    /**
     * @return \Symfony\Component\Stopwatch\Stopwatch
     */
    protected function getStopWatch()
    {
        if ($this->getContainer()->has('debug.stopwatch') === false) {
            $this->getContainer()->set('debug.stopwatch', new Stopwatch());
        }

        return $this->getContainer()->get('debug.stopwatch');
    }

    /**
     * @return \Symfony\Bridge\Monolog\Logger
     */
    protected function getLogger()
    {
        return $this->getContainer()->get('logger');
    }

    /**
     * @param string $id
     */
    protected function setStartTime($id = 'main')
    {
        $this->getStopWatch()->start($id);
    }

    /**
     * @param string $id
     */
    protected function setEndTime($id = 'main')
    {
        $this->getStopWatch()->stop($id);
    }

    /**
     * @param string $id
     *
     * @return \DateInterval
     */
    protected function getFinishTime($id = 'main')
    {
        $seconds = round($this->getStopWatch()->getEvent($id)->getDuration() / 1000, 0);

        $d1 = new \DateTime();
        $d2 = new \DateTime();
        $d2->add(new \DateInterval("PT{$seconds}S"));

        $duration = $d2->diff($d1);

        return $duration;
    }

    protected function noticeTime($id = 'main')
    {
        $message = "The script lasted {$this->getFinishTime($id)->format('%h Hours %i Minutes %s Seconds')}";
        $this->getLogger()->notice($message);
    }
}
