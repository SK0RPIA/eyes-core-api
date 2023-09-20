<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServerController extends Controller
{

    public function initPanel()
    {
        $this->setHeader();
        $output = shell_exec("cat /proc/cpuinfo | grep 'model name' | head -n 1 | awk -F ': ' '{print $2}'");
        $output2 = shell_exec("nproc --all");
        $cpuName = trim($output);
        $totalCore = floatval($output2);


        $jsonData = ['total_core' => $totalCore, 'cpu_name' => $cpuName,];


        return response()->json($jsonData);

    }

    public function getCpuPercentage()
    {
        $this->setHeader();
        $output = shell_exec("top -bn 1 | awk '/Cpu\(s\):/ {print $2}'");
        $totalUsage = floatval($output);

        // Conversion du pourcentage total d'utilisation en une chaîne JSON
        $jsonData = ['total_usage' => $totalUsage];


        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($jsonData);
    }

    public function getRamData()
    {
        $this->setHeader();

        $output = shell_exec('free');
        $lines = explode("\n", $output);

        $memoryValues = preg_split('/\s+/', $lines[1]);
        $jsonData = [
            'total' => $memoryValues[1],
            'utilized' => $memoryValues[2],
            'cache' => $memoryValues[5],
        ];



        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($jsonData);
    }






}