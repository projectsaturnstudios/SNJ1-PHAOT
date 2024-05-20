<h1>SNJ1-PHAOT (Preheat Ahead of Time)</h1>
<p align="center">
    Script to preheat the next extruder ahead of time, on gcode files sliced by OrcaSlicer for Snapmaker J1
</p>

<p align="center">
    No Badges Yet!
</p>

This tool was created to modify multicolor gCode files sliced with OrcaSlicer to be used with the Snapmaker J1.

Requires: PHP 8.2+ and Composer

To build a new executable 
```bash
$ composer install
$ php j1 app:build <app-name-string>
```
To use
```bash
$ ./<app-name-string> phoat <LH-preheat-temp> <RH-preheat-temp> <which-extruder-started-2nd> <file-path>
```

For example
```bash
$ ./j1 phoat 220 220 1 /USBStick/TwoColorBenchy.gcode
```


- If you leave any parameter blank, the script will prompt for an input.
- The script will save a new copy of the file with the prefix "updated-" in the same directory as the original file.
- The script looks for the 1st instance of the M2000 command, as well as every M220 B command and inserts the preheat commands between the instances as a point halfway.
- To find the inserted statement, open the gCode file in Sublime Text and search for "BATMAN"
- This script should work with any OrcaSlicer sliced gcode file specifically for the Snapmaker J1 when printing using both toolheads in normal mode, where each extruder takes turns.
- This tool is best used when you know relatively how long each parked extruder has to wait its turn, and you can adjust the standby temp (Softening Temp) such that combined with an M104 in yours filaments' start gcode can result in no wait time between swaps.



- This tool is not perfect, and does not come with a warranty of any kind. Use at your own risk.
------

## License

This tool is an open-source software licensed under the MIT license.
