<?php

use App\Console\Commands\GrabEPGData;
use Illuminate\Support\Facades\Schedule;

Schedule::command("app:grab-epg")->everyTwoHours();
Schedule::command("storage:clean-old")->weekly();
