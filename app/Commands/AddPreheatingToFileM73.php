<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class AddPreheatingToFileM73 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phoat73 {f? : filepath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserts M104 Commands 1 or 2 M73 Commands from the Toolchange Commands. Not Recommended to use.';

    protected string $temp0 = "220";
    protected string $temp1 = "220";
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // prompt the user if arguement is empty
        $path = $this->argument('f') ?? $this->ask('Enter the file path');
        $this->info("Adding preheating to file: $path");
        $file = file($path);

        $results = [];
        $cur_temp = 1;
        $first_M2000_found = false;
        if(count($file) > 0)
        {
            $segment = [];
            $chain = 0;
            foreach($file as $row_idx => $content)
            {
                if(!$first_M2000_found)
                {
                    if(!str_contains($content, 'M2000'))
                    {
                        $results[$row_idx] = $content;
                    }
                    else
                    {
                        $results[] = $content;
                        $first_M2000_found = true;
                    }
                }
                else
                {
                    if(str_contains($content, "M73 P"))
                    {
                        $chain++;
                        $segment[$chain][] = $content;
                    }
                    elseif(str_contains($content, "M220 B"))
                    {
                        $pos = count($segment) > 2 ? max(array_keys($segment)) - 2 : 1;
                        if(count($segment) > 1)
                        {
                            $suffix = "temp{$cur_temp}";
                            $temp = $this->$suffix;
                            $segment[$pos] = [
                                "M104 T{$cur_temp} S{$temp} C5 W0 ;BATMAN \n",
                                ...$segment[$pos]
                            ];
                            $this->info("Row - {$row_idx}: Found a tool change. Merged into segment {$pos}");
                        }
                        else
                        {
                            $this->info("Row - {$row_idx}: Found a tool change. Merged before next statement.");
                            $segment[$chain] = [
                                "M104 T{$cur_temp} S{$temp} C5 W0 ;SUPERMAN \n",
                                ...$segment[$chain]
                            ];
                        }

                        $segment[$chain][] = $content;

                        foreach($segment as $c => $subchain)
                        {
                            foreach($subchain as $tent)
                            {
                                $results[] = $tent;
                            }
                        }
                        $chain = 0;
                        $segment = [];
                        $cur_temp = ($cur_temp == 0) ? 1 : 0;

                    }
                    else
                    {
                        $segment[$chain][] = $content;
                    }
                }
            }

            $new_path = explode("/", $path);
            $new_path[count($new_path) -1] = 'updated-'.last($new_path);
            $new_path = implode("/", $new_path);
            $this->warn("saving...$new_path");
            foreach($results as $idx => $fuck_you)
            {
                if(is_array($fuck_you))
                {
                    dd("wtf? {$idx} ".count($results), $fuck_you);
                }
            }
            file_put_contents($new_path, $results);
            $this->info("All done! Saved the file to $new_path");

        }
        else
        {
            $this->error("No data found in file");
        }

    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
