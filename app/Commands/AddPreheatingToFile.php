<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class AddPreheatingToFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phoat {lh? : T0 Temp} {rh? : T0 Temp} {c? : who goes 2nd} {f? : filepath}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserts M104 Commands halfway between M220 B commands after the 1st M2000 command.';

    protected string $temp0 = "220";
    protected string $temp1 = "220";
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('f') ?? $this->ask('Enter the file path');
        $cur_temp = $this->argument('c') ?? $this->ask('Which hot end goes second? 0 or 1?', 1);
        $this->temp0 = $this->argument('lh') ?? $this->ask('T0/LH Preheat Temp?', 220);
        $this->temp1 = $this->argument('rh') ?? $this->ask('T1/RH Preheat Temp?', 220);
        $this->info("Adding preheating to file: $path");
        $file = file($path);

        $results = [];
        $first_M2000_found = false;

        if(count($file) > 0)
        {
            $segment = [];
            $spots = 0;
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
                    if(str_contains($content, "M220 B"))
                    {
                        $segment_size = count($segment);

                        $half = floor($segment_size / 2);

                        foreach($segment as $idx => $line)
                        {
                            $results[] = $line;
                            if($idx == $half)
                            {
                                $suffix = "temp{$cur_temp}";
                                $temp = $this->$suffix;
                                $results[] = "M104 T{$cur_temp} S{$temp} C5 W0 ;BATMAN \n";
                                $ugh = count($results);
                                $this->info("{$spots} - Added M104 T{$cur_temp} S{$temp} C5 W0; to line $ugh");
                                $spots++;
                            }
                        }

                        $segment = [];
                        $cur_temp = ($cur_temp == 0) ? 1 : 0;
                    }
                    else
                    {
                        $segment[] = $content;
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
