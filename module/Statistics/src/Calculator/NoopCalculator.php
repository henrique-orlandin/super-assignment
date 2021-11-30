<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class NoopCalculator extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * array with unique users
     * @var array
     */
    private $users = [];
    
    /**
     * total number of posts
     *
     * @var integer
     */
    private $postCount = 0;

    /**
     * method that computes information from each post
     *
     * @param SocialPostTo $postTo
     * @return void
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        // count the number of posts
        $this->postCount++;

        // set an array of unique users using the user id
        $key = $postTo->getAuthorId();
        $this->users[$key] = $key;
    }

    /**
     * calculate average of posts per users from the accumulated data
     *
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        // total number of posts divided by the number of users
        $value = $this->postCount / count($this->users);

        return (new StatisticsTo())->setValue(round($value, 2));
    }
}
