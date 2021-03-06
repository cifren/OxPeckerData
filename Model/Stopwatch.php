<?php

namespace Cifren\OxPeckerData\Model;

use Symfony\Component\Stopwatch\Stopwatch as StopwatchAlias;

/**
 * Earls\FlamingoCommandQueueBundle\Model\Stopwatch.
 */
class Stopwatch extends StopwatchAlias implements StopwatchInterface
{
    /**
     * @param string $name
     *
     * @return \DateInterval
     */
    public function getFinishTime($name)
    {
        $seconds = round($this->getEvent($name)->getDuration() / 1000, 0);
        $d1 = new \DateTime();
        $d2 = new \DateTime();
        $d2->add(new \DateInterval("PT{$seconds}S"));
        $duration = $d2->diff($d1);

        return $duration;
    }
}
