<?php

namespace App\Console\Commands;

use App\Helpers\IRC\Server\Chanserv;
use App\Helpers\IRC\Server\Nickserv;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class Chanlist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dh:chanlist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chanlist from Darkscience';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = $this->ask('Username?', 'linky');
        $pass = $this->secret('Password?');

        [$response, $authToken] = with(new Nickserv())
            ->login($user, $pass);

        if ($response !== true) {
            throw new Exception('Auth failed..');
        }

        $this->comment('Starting chanlist mapping...');
        [$response, $channelList] = with(new Chanserv())
            ->getList($user, $authToken);

        $channelList = collect(explode("\n", $channelList))
            ->map(function($row) {
                preg_match('/- (.*) \((.*)\)/m', $row, $m);
                if (empty($m)) {
                    return [];
                }

                return [
                    'channel' => $m[1],
                    'owners' => $m[2]
                ];
            })
            ->filter();

        $this->comment(sprintf('Found %d channels...', $channelList->count()));

        file_put_contents(
            storage_path('app/irc/channels.txt'),
            implode("\n", $channelList->pluck('channel')->toArray())
        );
        dd($channelList);
    }
}
