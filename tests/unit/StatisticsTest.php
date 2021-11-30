<?php

declare(strict_types = 1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;

use SocialPost\Service\Factory\SocialPostServiceFactory;
use Statistics\Service\Factory\StatisticsServiceFactory;
use Statistics\Extractor\StatisticsToExtractor;
use Statistics\Builder\ParamsBuilder;
use Statistics\Enum\StatsEnum;
use Dotenv\Dotenv;
use DateTime;

$dotEnv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotEnv->load();

\App\Config\Config::init();

/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class StatisticsTest extends TestCase
{
    private const STAT_LABELS = [
        StatsEnum::TOTAL_POSTS_PER_WEEK         => 'Total posts split by week',
        StatsEnum::AVERAGE_POST_NUMBER_PER_USER => 'Average number of posts per user in a given month',
        StatsEnum::AVERAGE_POST_LENGTH          => 'Average character length/post in a given month',
        StatsEnum::MAX_POST_LENGTH              => 'Longest post by character length in a given month',
    ];

    private $statsService;
    private $socialService;
    private $extractor;

    public function __construct()
    {
        $this->statsService   = StatisticsServiceFactory::create();
        $this->socialService  = SocialPostServiceFactory::create();
        $this->extractor      = new StatisticsToExtractor();

        parent::__construct();
    }

    /**
     * check a valid request to get posts statistics
     *
     * @return void
     */
    public function testValidRequest(): void
    {
        try {
            $currentMonth   = date('F, Y');
            $date           = DateTime::createFromFormat('F, Y', $currentMonth);
            $params         = ParamsBuilder::reportStatsParams($date);
            
            $posts = $this->socialService->fetchPosts();
            $stats = $this->statsService->calculateStats($posts, $params);
    
            $response = [
                'stats' => $this->extractor->extract($stats, self::STAT_LABELS),
            ];
            
            $this->assertTrue($stats && !empty($response['stats']['children']));
        } catch (\Throwable $th) {
            $this->assertFalse(true, "Couldn't get the expected information!");
        }
    }

    /**
     * check an invalid date for a statistics request
     *
     * @return void
     */
    public function testInvalidDate(): void
    {
        try {
            $invalidDate    = date('2030-05-05');
            $date           = DateTime::createFromFormat('F, Y', $invalidDate);
            $params         = ParamsBuilder::reportStatsParams($date);
            
            $posts = $this->socialService->fetchPosts();
            $stats = $this->statsService->calculateStats($posts, $params);
    
            $response = [
                'stats' => $this->extractor->extract($stats, self::STAT_LABELS),
            ];
            
            $this->assertFalse($stats || !empty($response['stats']['children']), 'Should not have get any results for invalid date!');
        } catch (\Throwable $th) {
            $this->assertFalse(false);
        }
    }
}
