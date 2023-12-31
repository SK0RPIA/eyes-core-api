<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServerController extends Controller
{

    public function initPanel()
    {
        $output = shell_exec("cat /proc/cpuinfo | grep 'model name' | head -n 1 | awk -F ': ' '{print $2}'");
        $output2 = shell_exec("nproc --all");
        $cpuName = trim($output);
        $totalCore = floatval($output2);


        $jsonData = ['total_core' => $totalCore, 'cpu_name' => $cpuName,];


        return response()->json($jsonData);

    }

    public function getCpuPercentage()
    {
        $output = shell_exec("top -bn 1 | awk '/Cpu\(s\):/ {print $2}'");
        $totalUsage = floatval($output);

        // Conversion du pourcentage total d'utilisation en une chaîne JSON
        $jsonData = ['total_usage' => $totalUsage];


        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($jsonData);
    }

    public function getRamData()
    {

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

    public function getRaidStatus()
    {

        // Exécution de la commande 'mdadm' pour obtenir le statut des raids
        $output = shell_exec('mdadm --detail /dev/md0'); // Remplacez /dev/md0 par le nom correct de votre RAID

        // Vérification si la sortie est vide, ce qui signifie que le RAID n'est pas configuré
        if (empty(trim($output))) {
            return response()->json(['error' => 'No RAID configuration found'], 404);
        }

        // Extraction des informations pertinentes de la sortie
        $lines = explode("\n", $output);
        $raidData = [];
        foreach ($lines as $line) {
            if (preg_match('/^State : (.+)/', $line, $matches)) {
                $raidData['state'] = trim($matches[1]);
            }
            if (preg_match('/^Raid Level : (.+)/', $line, $matches)) {
                $raidData['level'] = trim($matches[1]);
            }
            // ... (ajoutez d'autres expressions régulières pour extraire plus d'informations)
        }

        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($raidData);
    }

    public function getDiskData($targetDisks = [])
    {
        $partitions = []; // Tableau pour stocker les partitions



        $dfOutput = shell_exec('df /dev/md0');
        $dfLines = explode("\n", trim($dfOutput));

        if (count($dfLines) > 1) {
            $diskLine = $dfLines[1]; // Prendre la seconde ligne
            if (preg_match('/\/dev\/\S+\s+(\d+)\s+(\d+)\s+\d+\s+(\d+)%/', $diskLine, $dfMatches)) {
                $diskInfo = [
                    'name' => "md0",
                    'storage_total' => intval($dfMatches[1]),
                    // Convertir les blocs de 1K en octets
                    'storage_used' => intval($dfMatches[2]),
                    // Convertir les blocs de 1K en octets
                    'storage_usage_percentage' => $dfMatches[3] . '%',
                ];

                ; // Ajouter la partition à la liste des partitions
            }
        }
        array_push($partitions, $diskInfo);

        // Envoi de la chaîne JSON en tant que réponse
        return response()->json($partitions); // Renvoyer uniquement le tableau des partitions
    }

}